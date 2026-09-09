<?php
declare(strict_types=1);

namespace Tds\Ext\Shop;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Tds\Ext\Shop\Domain\ClickRepository;
use Tds\Ext\Shop\Domain\OrderRepository;
use Tds\Ext\Shop\Domain\PlacementRepository;
use Tds\Ext\Shop\Domain\ProductRepository;
use Tds\Ext\Shop\Domain\SyncQueueRepository;
use Tds\Ext\Shop\Payment\PaymentEvent;
use Tds\Ext\Shop\Payment\PaymentFailed;
use Tds\Ext\Shop\Payment\PaymentNotConfigured;
use Tds\Ext\Shop\Payment\PaymentRegistry;
use Tds\Ext\Shop\Payment\PaymentRequest;
use Tds\Ext\Shop\Payment\PayPalProvider;
use Tds\Ext\Shop\Payment\StripeProvider;
use Tds\Ext\Shop\Payment\WebhookNotVerified;
use Tds\Ext\Shop\Payment\WeroProvider;
use Tds\Ext\Shop\Service\AmazonPaApiClient;
use Tds\Ext\Shop\Service\OfferSync;
use Tds\Ext\Shop\Service\OrderInvoiceBuilder;
use Tds\Ext\Shop\Service\OrderInvoicing;
use Tds\Ext\Shop\Service\PaApiException;
use Tds\Ext\Shop\Service\StripeClient;
use Tds\Ext\Shop\Support\PaApiSigner;
use Tds\Ext\Shop\Support\Shipping;
use Tds\Ext\Shop\Support\SyncTicker;
use Tds\Ext\Shop\Support\Withdrawal;
use Tds\Frontend\Contract\AbstractModule;
use Tds\Frontend\Contract\ApiDocSource;
use Tds\Frontend\Contract\PermissionDef;
use Tds\Frontend\Contract\SettingsStore;
use Tds\Frontend\Contract\SiteKeyProtected;
use Tds\Frontend\Contract\UserContext;

/**
 * TDShop backend: the catalogue, the placements that embed it elsewhere, the
 * click counter, and the Amazon offer sync.
 *
 * The Stripe checkout is a separate checkpoint; nothing here depends on it.
 *
 * The sync is optional by construction — without Amazon credentials the client
 * is null, `OfferSync::isConfigured()` is false, and the catalogue works
 * exactly as before with prices that were entered by hand or not at all. That
 * matters more than it sounds: Amazon withdraws API access when qualifying
 * sales stop, so "no sync" is a state this shop has to survive, not an
 * installation step it is waiting on.
 */
final class ShopModule extends AbstractModule implements ApiDocSource, SiteKeyProtected
{
    private const LANGS = ['de', 'en'];

    /** Settings namespace. Per-extension, so keys cannot collide in the shared store. */
    private const SETTINGS_NS = 'shop';

    public function id(): string
    {
        return 'shop';
    }

    /** @return PermissionDef[] */
    public function permissions(): array
    {
        return [
            new PermissionDef('shop:read', 'Shop ansehen', 'shop'),
            new PermissionDef('shop:write', 'Produkte und Platzierungen bearbeiten', 'shop'),
            new PermissionDef('shop:orders', 'Bestellungen verwalten', 'shop'),
            new PermissionDef('shop:sync', 'Angebotsabgleich steuern', 'shop'),
        ];
    }

    /** @return string[] */
    public function migrations(): array
    {
        return [__DIR__ . '/../db/migrations'];
    }

    public function register(App $app): void
    {
        $c = $app->getContainer();

        // NEVER guard these with `!$c->has(X)`.
        //
        // PHP-DI answers `has()` from its definition sources, and autowiring is
        // one of them: for any concrete, instantiable class the answer is true
        // whether or not anything was ever bound. The guard therefore skips the
        // binding and the container silently autowires instead — harmless for a
        // repository whose only argument is the bound PDO, fatal for anything
        // whose constructor takes a string PHP-DI cannot guess. Six modules in
        // this platform bound nothing at all for a release because of it.
        $c?->set(ProductRepository::class, static function ($c): ProductRepository {
            // Env with a coded default rather than a required setting: the
            // catalogue must answer on a host where nobody has configured
            // anything yet, and the production origin is not a secret.
            $base = rtrim((string) (getenv('SHOP_PUBLIC_URL') ?: ''), '/');
            return $base === ''
                ? new ProductRepository($c->get(PDO::class))
                : new ProductRepository($c->get(PDO::class), $base);
        });
        $c?->set(PlacementRepository::class, static fn ($c) => new PlacementRepository($c->get(PDO::class)));
        $c?->set(ClickRepository::class, static fn ($c) => new ClickRepository($c->get(PDO::class)));
        $c?->set(SyncQueueRepository::class, static fn ($c) => new SyncQueueRepository($c->get(PDO::class)));

        $c?->set(AmazonPaApiClient::class, static function ($c): ?AmazonPaApiClient {
            $creds = self::amazonCredentials($c);
            if ($creds === null) {
                return null;
            }
            return new AmazonPaApiClient(
                new PaApiSigner($creds['access'], $creds['secret'], $creds['region'], $creds['host']),
                $creds['tag'],
                $creds['host'],
                $creds['marketplace'],
            );
        });

        $c?->set(OfferSync::class, static fn ($c) => new OfferSync(
            $c->get(PDO::class),
            $c->get(SyncQueueRepository::class),
            $c->get(AmazonPaApiClient::class),
        ));

        $c?->set(OrderRepository::class, static fn ($c) => new OrderRepository($c->get(PDO::class)));

        $c?->set(OrderInvoiceBuilder::class, static fn () => new OrderInvoiceBuilder());

        // The container itself goes in, because the Lexware client lives in a
        // package that may not be installed and is therefore resolved by name
        // at call time — see OrderInvoicing. Not `$c->has()`: for a concrete
        // class PHP-DI answers that out of autowiring and always says yes.
        $c?->set(OrderInvoicing::class, static fn ($c) => new OrderInvoicing(
            $c->get(OrderRepository::class),
            $c->get(OrderInvoiceBuilder::class),
            $c,
        ));

        $c?->set(StripeClient::class, static function ($c): ?StripeClient {
            $key = self::setting($c, 'stripe_secret_key', 'SHOP_STRIPE_SECRET_KEY', true);
            return $key === '' ? null : new StripeClient($key);
        });

        /**
         * The payment providers, in the order the checkout offers them.
         *
         * Every one is constructed unconditionally, including the ones with no
         * credentials — an unconfigured provider answers `isConfigured() ===
         * false` and is filtered out of the menu by
         * {@see PaymentRegistry::configured()}. Building the list from what
         * happens to be configured would mean the SHAPE of this container
         * changed with the settings, and a webhook arriving for a provider
         * whose secret was just cleared would 404 instead of answering 503 —
         * which is the difference between "we cannot verify this right now"
         * and "this endpoint does not exist".
         *
         * PayPal first: it is the method German consumers reach for, and the
         * first entry is what a customer with no preference takes.
         */
        $c?->set(PaymentRegistry::class, static function ($c): PaymentRegistry {
            $sandbox = self::setting($c, 'paypal_sandbox', 'SHOP_PAYPAL_SANDBOX', false) !== '';

            return new PaymentRegistry([
                new PayPalProvider(
                    self::setting($c, 'paypal_client_id', 'SHOP_PAYPAL_CLIENT_ID', false),
                    self::setting($c, 'paypal_secret', 'SHOP_PAYPAL_SECRET', true),
                    self::setting($c, 'paypal_webhook_id', 'SHOP_PAYPAL_WEBHOOK_ID', false),
                    $sandbox ? PayPalProvider::SANDBOX : PayPalProvider::LIVE,
                ),
                new StripeProvider(
                    $c->get(StripeClient::class),
                    self::setting($c, 'stripe_webhook_secret', 'SHOP_STRIPE_WEBHOOK_SECRET', true),
                ),
                // Seated, not finished. Invisible until a PSP is configured —
                // see the class doc and docs/wero-adapter.md.
                new WeroProvider(
                    self::setting($c, 'wero_psp', 'SHOP_WERO_PSP', false),
                    self::setting($c, 'wero_api_key', 'SHOP_WERO_API_KEY', true),
                    self::setting($c, 'wero_webhook_secret', 'SHOP_WERO_WEBHOOK_SECRET', true),
                ),
            ]);
        });

        $this->registerPublic($app, $c);
        $this->registerAdmin($app, $c);
        $this->registerSync($app, $c);
        $this->registerCheckout($app, $c);
    }

    /**
     * Amazon credentials, DB-first with an env fallback — the platform's
     * pattern. Null when anything is missing, which is what disables the sync
     * rather than half-configuring it.
     *
     * @return array{access:string,secret:string,tag:string,region:string,host:string,marketplace:string}|null
     */
    private static function amazonCredentials(ContainerInterface $c): ?array
    {
        $store = null;
        try {
            $store = $c->get(SettingsStore::class);
        } catch (\Throwable) {
            // No store bound (isolated tests, or a host without a database
            // yet). Env only, which is the pre-settings behaviour.
        }

        $plain = static function (string $key, string $env, string $default = '') use ($store): string {
            $stored = null;
            try {
                $stored = $store?->get(self::SETTINGS_NS, $key);
            } catch (\Throwable) {
                $stored = null;
            }
            $value = trim((string) ($stored ?? ''));
            return $value !== '' ? $value : (trim((string) (getenv($env) ?: '')) ?: $default);
        };
        $secret = static function (string $key, string $env) use ($store): string {
            $stored = null;
            try {
                $stored = $store?->getSecret(self::SETTINGS_NS, $key);
            } catch (\Throwable) {
                $stored = null;
            }
            $value = trim((string) ($stored ?? ''));
            return $value !== '' ? $value : trim((string) (getenv($env) ?: ''));
        };

        $access = $secret('amazon_access_key', 'SHOP_AMAZON_ACCESS_KEY');
        $secretKey = $secret('amazon_secret_key', 'SHOP_AMAZON_SECRET_KEY');
        $tag = $plain('amazon_partner_tag', 'SHOP_AMAZON_PARTNER_TAG');

        // All three or nothing. A client built from two of them fails on every
        // call with a signature error that reads like a code bug.
        if ($access === '' || $secretKey === '' || $tag === '') {
            return null;
        }

        return [
            'access' => $access,
            'secret' => $secretKey,
            'tag' => $tag,
            'region' => $plain('amazon_region', 'SHOP_AMAZON_REGION', 'eu-west-1'),
            'host' => $plain('amazon_host', 'SHOP_AMAZON_HOST', 'webservices.amazon.de'),
            'marketplace' => $plain('amazon_marketplace', 'SHOP_AMAZON_MARKETPLACE', 'www.amazon.de'),
        ];
    }

    /* --- public routes ---------------------------------------------------- */

    /**
     * Read routes for the shop site, the journal and the portal.
     *
     * Every one degrades to an empty payload on a database error rather than a
     * 500. A public site's content fetch is fail-soft by design, so a 500 here
     * does not surface as an error anywhere — it surfaces as a page that
     * silently drops a section. An empty payload does the same thing without
     * also filling the log with noise from a dependency the visitor cannot fix.
     */
    private function registerPublic(App $app, ?ContainerInterface $c): void
    {
        $app->get('/content/shop', function (Request $req, Response $res) use ($c): Response {
            // The sync's primary trigger. Deferred to after the response, at
            // most once a minute, and only if nobody else is already at it —
            // see SyncTicker. The catalogue route is chosen because it is what
            // the shop site hits on every cache miss, so the pages being read
            // drive the refresh of the prices they show.
            self::deferSyncTick($c);
            try {
                $q = $req->getQueryParams();
                $page = $c->get(ProductRepository::class)->publicList(
                    self::lang($q['lang'] ?? null),
                    isset($q['limit']) ? (int) $q['limit'] : 12,
                    isset($q['cursor']) ? (string) $q['cursor'] : null,
                    isset($q['category']) ? (string) $q['category'] : null,
                    isset($q['tag']) ? (string) $q['tag'] : null,
                );
                return self::json($res, $page);
            } catch (\Throwable) {
                return self::json($res, ['products' => [], 'nextCursor' => null]);
            }
        });

        $app->get('/content/shop/categories', function (Request $req, Response $res) use ($c): Response {
            try {
                $lang = self::lang($req->getQueryParams()['lang'] ?? null);
                return self::json($res, ['categories' => $c->get(ProductRepository::class)->publicCategories($lang)]);
            } catch (\Throwable) {
                return self::json($res, ['categories' => []]);
            }
        });

        $app->get('/content/shop/placement/{key:[a-z0-9-]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            $lang = self::lang($req->getQueryParams()['lang'] ?? null);
            $key = (string) $args['key'];
            try {
                $placement = $c->get(PlacementRepository::class)->active($key);
                if ($placement === null) {
                    return self::json($res, self::emptyPlacement($key, $lang));
                }
                $products = $c->get(ProductRepository::class)->forPlacement(
                    $placement,
                    $lang,
                    isset($req->getQueryParams()['category']) ? (string) $req->getQueryParams()['category'] : null,
                );
                $heading = (string) ($placement[$lang === 'en' ? 'heading_en' : 'heading_de'] ?? '');
                return self::json($res, [
                    'key' => $key,
                    'heading' => $heading !== '' ? $heading : null,
                    // Served rather than left to each consumer: three surfaces
                    // render this slot, and a label one of them forgets is a
                    // labelling failure, not a cosmetic one.
                    'label' => self::adLabel($lang),
                    'products' => $products,
                ]);
            } catch (\Throwable) {
                return self::json($res, self::emptyPlacement($key, $lang));
            }
        });

        // Registered after the literal sub-paths above for readability, not out
        // of necessity: FastRoute resolves static segments from its own map
        // before trying any variable route, so `/content/shop/categories` wins
        // over this pattern regardless of order. Worth knowing, because it also
        // means a future product whose slug is literally "categories" would be
        // unreachable — hence the reserved-slug check belongs in the editor.
        $app->get('/content/shop/{slug:[a-z0-9-]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            try {
                $lang = self::lang($req->getQueryParams()['lang'] ?? null);
                $product = $c->get(ProductRepository::class)->publicOne((string) $args['slug'], $lang);
                if ($product === null) {
                    return self::json($res, ['error' => 'Not found'], 404);
                }
                return self::json($res, $product);
            } catch (\Throwable) {
                return self::json($res, ['error' => 'Not found'], 404);
            }
        });

        /**
         * Resolve an offer's outbound target and count the click.
         *
         * The shop's `/go/{id}` route calls this. Going through a redirect
         * rather than linking straight out keeps the partner tag in exactly one
         * place — otherwise it is embedded in every article and product row
         * that ever mentioned the offer, and changing it becomes a
         * search-and-replace across three repositories.
         */
        $app->get('/content/shop/offer/{id:[0-9]+}/target', function (Request $req, Response $res, array $args) use ($c): Response {
            try {
                $offer = $c->get(ProductRepository::class)->offerTarget((int) $args['id']);
                if ($offer === null) {
                    return self::json($res, ['error' => 'Not found'], 404);
                }
                $q = $req->getQueryParams();
                try {
                    $c->get(ClickRepository::class)->record(
                        (int) $offer['id'],
                        (int) $offer['product_id'],
                        isset($q['source']) ? (string) $q['source'] : 'shop',
                        isset($q['placement']) ? (string) $q['placement'] : null,
                        self::lang($q['lang'] ?? null),
                    );
                } catch (\Throwable) {
                    // Counting is never worth failing a click over. The visitor
                    // gets their redirect; we lose one number.
                }
                return self::json($res, ['url' => (string) $offer['url']]);
            } catch (\Throwable) {
                return self::json($res, ['error' => 'Not found'], 404);
            }
        });
    }

    /* --- admin routes ----------------------------------------------------- */

    private function registerAdmin(App $app, ?ContainerInterface $c): void
    {
        $app->get('/shop/summary', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:read', $res)) !== null) {
                return $deny;
            }
            return self::json($res, $c->get(ProductRepository::class)->summary());
        });

        $app->get('/shop/products', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:read', $res)) !== null) {
                return $deny;
            }
            return self::json($res, ['products' => $c->get(ProductRepository::class)->adminList()]);
        });

        $app->get('/shop/products/{id:[0-9]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:read', $res)) !== null) {
                return $deny;
            }
            $product = $c->get(ProductRepository::class)->adminOne((int) $args['id']);
            return $product === null
                ? self::json($res, ['error' => 'Not found'], 404)
                : self::json($res, $product);
        });

        $app->post('/shop/products', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:write', $res)) !== null) {
                return $deny;
            }
            $body = (array) ($req->getParsedBody() ?? []);
            if (($error = self::validateProduct($body)) !== null) {
                return self::json($res, ['error' => $error], 422);
            }
            $id = $c->get(ProductRepository::class)->upsert(null, self::lang($body['lang'] ?? null), $body);
            return self::json($res, ['id' => $id], 201);
        });

        $app->put('/shop/products/{id:[0-9]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:write', $res)) !== null) {
                return $deny;
            }
            $repo = $c->get(ProductRepository::class);
            if ($repo->adminOne((int) $args['id']) === null) {
                return self::json($res, ['error' => 'Not found'], 404);
            }
            $body = (array) ($req->getParsedBody() ?? []);
            if (($error = self::validateProduct($body)) !== null) {
                return self::json($res, ['error' => $error], 422);
            }
            $repo->upsert((int) $args['id'], self::lang($body['lang'] ?? null), $body);
            return self::json($res, ['ok' => true]);
        });

        $app->delete('/shop/products/{id:[0-9]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:write', $res)) !== null) {
                return $deny;
            }
            return $c->get(ProductRepository::class)->delete((int) $args['id'])
                ? self::json($res, ['ok' => true])
                : self::json($res, ['error' => 'Not found'], 404);
        });

        $app->put('/shop/products/{id:[0-9]+}/offers', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:write', $res)) !== null) {
                return $deny;
            }
            $body = (array) ($req->getParsedBody() ?? []);
            $offers = is_array($body['offers'] ?? null) ? $body['offers'] : [];
            $c->get(ProductRepository::class)->setOffers((int) $args['id'], $offers);
            return self::json($res, ['ok' => true]);
        });

        $app->get('/shop/placements', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:write', $res)) !== null) {
                return $deny;
            }
            return self::json($res, ['placements' => $c->get(PlacementRepository::class)->all()]);
        });

        $app->put('/shop/placements/{key:[a-z0-9-]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:write', $res)) !== null) {
                return $deny;
            }
            $body = (array) ($req->getParsedBody() ?? []);
            $repo = $c->get(PlacementRepository::class);
            $placement = $repo->active((string) $args['key']);
            if ($placement === null) {
                return self::json($res, ['error' => 'Not found'], 404);
            }
            $repo->update((string) $args['key'], $body);
            if (isset($body['productIds']) && is_array($body['productIds'])) {
                $repo->setItems((int) $placement['id'], array_map('intval', $body['productIds']));
            }
            return self::json($res, ['ok' => true]);
        });

        $app->get('/shop/clicks', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:read', $res)) !== null) {
                return $deny;
            }
            $days = (int) ($req->getQueryParams()['days'] ?? 30);
            return self::json($res, ['products' => $c->get(ClickRepository::class)->topProducts($days)]);
        });
    }

    /* --- offer sync ------------------------------------------------------- */

    /**
     * Ask the ticker to run a batch after this response has been sent.
     *
     * Registered as a shutdown function rather than executed inline, so the
     * decision costs the visitor nothing even when it declines. The ticker
     * itself then flushes the response before doing any work.
     *
     * Everything here is swallowed. This runs on a public content route: a
     * sync problem must never become a 500 on a page somebody asked for, and
     * by the time the shutdown function fires there is nobody left to tell.
     */
    private static function deferSyncTick(?ContainerInterface $c): void
    {
        if ($c === null) {
            return;
        }
        static $armed = false;
        if ($armed) {
            return; // one attempt per request, whatever else it touches
        }
        $armed = true;

        register_shutdown_function(static function () use ($c): void {
            try {
                $sync = $c->get(OfferSync::class);
                if (!$sync->isConfigured()) {
                    return;
                }
                $dir = rtrim((string) (getenv('SHOP_SYNC_DIR') ?: sys_get_temp_dir()), '/\\')
                    . '/tds-shop-sync';
                (new SyncTicker($dir, static function () use ($c, $sync): void {
                    $c->get(SyncQueueRepository::class)->enqueueStale();
                    $sync->tick('request');
                }))->maybeTick();
            } catch (\Throwable $e) {
                error_log('[tds-shop] deferred sync tick failed: ' . $e->getMessage());
            }
        });
    }

    private function registerSync(App $app, ?ContainerInterface $c): void
    {
        $app->get('/shop/sync/status', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:sync', $res)) !== null) {
                return $deny;
            }
            $status = $c->get(SyncQueueRepository::class)->status();
            $status['configured'] = $c->get(OfferSync::class)->isConfigured();
            return self::json($res, $status);
        });

        $app->post('/shop/sync/enqueue', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:sync', $res)) !== null) {
                return $deny;
            }
            $queue = $c->get(SyncQueueRepository::class);
            // "Resume" and "refresh now" are one button in the panel: an
            // operator who has just fixed their Amazon account wants both, and
            // making them two invites doing only the first and concluding the
            // sync is broken.
            $resumed = $queue->resume();
            $queued = $queue->enqueueStale();
            $result = $c->get(OfferSync::class)->tick('manual');
            return self::json($res, ['resumed' => $resumed, 'queued' => $queued] + $result);
        });

        /**
         * The optional external trigger.
         *
         * Token-gated rather than permission-gated, because whatever calls it
         * is a machine: an uptime monitor, a GitHub Actions schedule, a Plesk
         * task if the host happens to have one. Deliberately NOT a
         * prerequisite — the request-driven ticker is what actually keeps the
         * catalogue current, and this only makes it faster. Without
         * `SHOP_SYNC_TOKEN` it answers 503 rather than running unauthenticated.
         */
        $app->post('/shop/sync/tick', function (Request $req, Response $res) use ($c): Response {
            $expected = trim((string) (getenv('SHOP_SYNC_TOKEN') ?: ''));
            if ($expected === '') {
                return self::json($res, ['error' => 'sync token not configured'], 503);
            }
            $presented = trim($req->getHeaderLine('X-TDS-Sync-Token'));
            // Constant-time: this is a bearer secret on a public route.
            if ($presented === '' || !hash_equals($expected, $presented)) {
                return self::json($res, ['error' => 'Unauthorized'], 401);
            }
            $c->get(SyncQueueRepository::class)->enqueueStale();
            return self::json($res, $c->get(OfferSync::class)->tick('token'));
        });

        $app->post('/shop/affiliate/lookup', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:write', $res)) !== null) {
                return $deny;
            }
            $client = $c->get(AmazonPaApiClient::class);
            if ($client === null) {
                return self::json($res, ['error' => 'Amazon ist nicht konfiguriert.'], 503);
            }
            $body = (array) ($req->getParsedBody() ?? []);
            $asin = strtoupper(trim((string) ($body['asin'] ?? '')));
            $keywords = trim((string) ($body['keywords'] ?? ''));

            try {
                if ($asin !== '') {
                    return self::json($res, ['items' => array_values($client->getItems([$asin]))]);
                }
                if ($keywords === '') {
                    return self::json($res, ['error' => 'ASIN oder Suchbegriff angeben.'], 422);
                }
                return self::json($res, ['items' => $client->searchItems($keywords)]);
            } catch (PaApiException $e) {
                // The operator is standing right there, so the reason is
                // reported rather than swallowed — and a revoked account reads
                // very differently from a typo in an ASIN.
                return self::json(
                    $res,
                    ['error' => $e->getMessage(), 'permanent' => $e->isPermanent()],
                    $e->isPermanent() ? 502 : 503,
                );
            }
        });
    }

    /* --- checkout --------------------------------------------------------- */

    /**
     * Selling TDS's own digital service packages.
     *
     * These routes are called by a **visitor's browser**, so none of them is
     * site-key protected — a key that ships in a client bundle is not a key —
     * and the Stripe webhook is deliberately mounted outside `/content/shop`,
     * because `SiteKeyMiddleware::matches()` compares segment-wise and Stripe
     * holds no key at all.
     */
    private function registerCheckout(App $app, ?ContainerInterface $c): void
    {
        /**
         * Create a Checkout Session.
         *
         * The price is read from the database, never from the request. A
         * posted price is a price the customer chose.
         *
         * The withdrawal confirmation is a hard precondition, not a field: for
         * a digital SERVICE the right of withdrawal only lapses if the
         * customer expressly agreed and confirmed they knew what they were
         * giving up (§ 356 Abs. 4 BGB). Without that, TDS has performed the
         * service and the customer may still withdraw.
         */
        /**
         * Price a basket without ordering anything.
         *
         * The basket page needs the total it is about to charge, and the total
         * includes delivery — which depends on what is in the basket, on the
         * free-shipping threshold, and on an apportionment of tax across the
         * goods' VAT rates. None of that can be computed in a browser from the
         * catalogue alone, and a basket that shows a different number than the
         * checkout is worse than one that shows none.
         *
         * So money is computed in exactly one place, and this is a read of it.
         * Nothing is written, nothing is reserved and no order exists
         * afterwards.
         */
        $app->post('/shop/quote', function (Request $req, Response $res) use ($c): Response {
            $body = (array) ($req->getParsedBody() ?? []);
            $basket = self::resolveBasket($c, $body);
            if ($basket === null) {
                return self::json($res, ['error' => 'Dieses Angebot gibt es nicht.'], 404);
            }

            return self::json($res, [
                'lines' => array_map(static fn (array $l): array => [
                    'slug' => (string) $l['slug'],
                    'title' => (string) $l['title'],
                    'quantity' => (int) ($l['quantity'] ?? 1),
                    'netCents' => (int) $l['line_net'],
                    'taxCents' => (int) $l['line_tax'],
                    'grossCents' => (int) $l['line_gross'],
                    'requiresShipping' => (bool) ($l['requires_shipping'] ?? false),
                ], $basket['totals']['lines']),
                'shipping' => [
                    'netCents' => $basket['shipping']['net'],
                    'taxCents' => $basket['shipping']['tax'],
                    'grossCents' => $basket['shipping']['gross'],
                    'required' => $basket['hasPhysical'],
                    'freeFromCents' => $basket['rate']->freeFromCents(),
                ],
                'netCents' => $basket['totals']['net'],
                'taxCents' => $basket['totals']['tax'],
                'grossCents' => $basket['totals']['gross'],
                'currency' => (string) ($basket['lines'][0]['currency'] ?? 'EUR'),
                // Which withdrawal blocks the checkout has to show, and whether
                // it has to ask for the early-performance consent at all.
                'withdrawalRegime' => $basket['regime'],
                'withdrawalConsentRequired' => Withdrawal::requiresConsent($basket['regime']),
                'addressRequired' => $basket['hasPhysical'],
            ]);
        });

        $app->post('/shop/checkout', function (Request $req, Response $res) use ($c): Response {
            $body = (array) ($req->getParsedBody() ?? []);
            $email = trim((string) ($body['email'] ?? ''));
            $country = strtoupper(trim((string) ($body['country'] ?? 'DE')));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return self::json($res, ['error' => 'Bitte eine gültige E-Mail-Adresse angeben.'], 422);
            }
            // Germany only, checked HERE rather than at the provider so the
            // refusal can be explained. Selling an electronically supplied
            // service to a consumer elsewhere in the EU moves the place of
            // supply to their country and eventually means OSS registration —
            // an obligation the shop must not acquire by accident.
            if (!in_array($country, self::allowedCountries(), true)) {
                return self::json($res, [
                    'error' => 'Wir liefern derzeit nur nach Deutschland.',
                ], 422);
            }

            $basket = self::resolveBasket($c, $body);
            if ($basket === null) {
                return self::json($res, ['error' => 'Dieses Angebot gibt es nicht.'], 404);
            }

            /**
             * The withdrawal check depends on WHAT is in the basket.
             *
             * A basket of goods needs no consent at all — the 14-day right is
             * not the customer's to give up, and demanding a tick for it would
             * be a consent with no legal object. A basket containing a service
             * does need one: without the express request to begin early plus
             * the acknowledgement of what that costs, TDS performs the service
             * and the customer may still withdraw (§ 356 Abs. 4 BGB).
             *
             * So this used to be an unconditional requirement and is now a
             * conditional one. Demanding it always was not the safe direction —
             * it was the wrong question asked of half the customers.
             */
            if (Withdrawal::requiresConsent($basket['regime']) && ($body['withdrawalConsent'] ?? false) !== true) {
                return self::json($res, [
                    'error' => 'Ohne die Bestätigung zum Widerrufsrecht kann nicht bestellt werden.',
                ], 422);
            }

            // Something to deliver means somewhere to deliver it. Checked on the
            // server because the address is part of what was ordered, not a
            // convenience of the form.
            $address = self::deliveryAddress($body, $country);
            if ($basket['hasPhysical'] && $address === null) {
                return self::json($res, [
                    'error' => 'Für den Versand brauchen wir eine vollständige Lieferanschrift.',
                ], 422);
            }

            $registry = $c->get(PaymentRegistry::class);
            // No `provider` in the request means "whatever this shop leads
            // with" — a page that has not been updated for a new method still
            // works. `usable()` is what stops a hand-crafted request from
            // naming one that is registered but has no credentials.
            $providerId = trim((string) ($body['provider'] ?? '')) ?: (string) $registry->defaultId();
            $provider = $registry->usable($providerId);
            if ($provider === null) {
                return self::json($res, ['error' => 'Der Kauf ist derzeit nicht möglich.'], 503);
            }

            $orders = $c->get(OrderRepository::class);
            $withdrawalText = trim((string) ($body['withdrawalText'] ?? ''))
                ?: Withdrawal::defaultText($basket['regime']);

            $order = $orders->openCart(
                $basket['lines'],
                $email,
                trim((string) ($body['name'] ?? '')) ?: null,
                $withdrawalText,
                $country,
                $basket['shipping'],
                $basket['hasPhysical'] ? $address : null,
            );
            $base = self::shopBaseUrl();
            $first = $basket['lines'][0];
            // One line on the provider's page: its name, or the first item and
            // a count. A provider that shows "Ein Paket" for a basket of four
            // is not describing the purchase, and PayPal in particular puts
            // this on the buyer's statement.
            $description = count($basket['lines']) === 1
                ? (string) $first['title']
                : sprintf('%s + %d weitere', (string) $first['title'], count($basket['lines']) - 1);

            try {
                $handoff = $provider->start(new PaymentRequest(
                    $order['orderNo'],
                    $order['token'],
                    $description,
                    $order['gross'],
                    (string) ($first['currency'] ?? 'EUR'),
                    $email,
                    "{$base}/bestellung/{$order['token']}",
                    // Cancelling returns to the basket now, not to a product —
                    // with several lines there is no single product to go back
                    // to, and the basket is where the decision was made.
                    count($basket['lines']) === 1
                        ? "{$base}/produkt/{$first['slug']}"
                        : "{$base}/warenkorb",
                ));
            } catch (PaymentNotConfigured) {
                // Between `usable()` above and here somebody cleared a secret.
                return self::json($res, ['error' => 'Der Kauf ist derzeit nicht möglich.'], 503);
            } catch (PaymentFailed) {
                // The order stays `pending` with no reference attached, which is
                // exactly what it is: an attempt that never reached a provider.
                // It is NOT deleted — an order row that vanishes is one nobody
                // can reconcile against a payment that arrives late anyway.
                return self::json($res, ['error' => 'Die Zahlung konnte nicht gestartet werden.'], 502);
            }

            $orders->attachPayment($order['id'], $provider->id(), $handoff->reference);
            return self::json($res, ['url' => $handoff->redirectUrl, 'token' => $order['token']]);
        });

        /**
         * Which payment methods this shop can actually complete right now.
         *
         * The checkout renders this list rather than a hard-coded one, and that
         * is what keeps an unfinished adapter harmless: Wero is registered, but
         * it answers `isConfigured() === false`, so it never appears here and
         * cannot be selected. The day a PSP is configured it shows up with no
         * frontend change at all.
         *
         * Unauthenticated and site-key-free, like the rest of `/shop/*`: it is
         * called by a visitor's browser, and it discloses nothing the checkout
         * page would not show them a second later.
         */
        $app->get('/shop/payment-methods', function (Request $req, Response $res) use ($c): Response {
            return self::json($res, ['methods' => $c->get(PaymentRegistry::class)->configured()]);
        });

        /**
         * One webhook endpoint per provider.
         *
         * Outside `/content/shop` on purpose — a site-key prefix would reject
         * every provider, none of which holds a key. They authenticate by
         * signature instead, and each verifies its own: the schemes have
         * nothing in common (Stripe signs an HMAC over the raw body, PayPal
         * wants a round trip to its own verification endpoint).
         *
         * The RAW body goes down untouched. Stripe's signature covers the exact
         * bytes, so a parsed-and-re-encoded payload will not verify — and the
         * gateway relays bodies byte-for-byte precisely so that this works.
         */
        $webhook = function (Request $req, Response $res, array $args) use ($c): Response {
            $provider = $c->get(PaymentRegistry::class)->get((string) ($args['provider'] ?? 'stripe'));
            if ($provider === null) {
                return self::json($res, ['error' => 'Unknown provider'], 404);
            }

            // Slim reports header names as they were sent; providers document
            // them in mixed case and send them in another. Lower-case once,
            // here, so no adapter has to guess.
            $headers = [];
            foreach ($req->getHeaders() as $name => $values) {
                $headers[strtolower((string) $name)] = implode(',', $values);
            }

            try {
                $event = $provider->receiveWebhook((string) $req->getBody(), $headers);
            } catch (PaymentNotConfigured) {
                // Fail CLOSED. An endpoint that accepted unverifiable webhooks
                // would be a way to mark any order paid.
                return self::json($res, ['error' => 'webhook secret not configured'], 503);
            } catch (WebhookNotVerified) {
                // Nothing else. An endpoint that explains why a forgery was
                // rejected is an oracle for producing one that is not.
                return self::json($res, ['error' => 'Invalid signature'], 400);
            } catch (PaymentFailed) {
                // A verified event whose follow-up call failed — PayPal's
                // capture, say. 502 so the provider RETRIES it; swallowing this
                // as a 200 would lose an approved payment for good.
                return self::json($res, ['error' => 'Upstream call failed'], 502);
            }

            if ($event !== null) {
                $orders = $c->get(OrderRepository::class);
                if ($event->kind === PaymentEvent::PAID) {
                    // Idempotent by its WHERE clause: providers retry until they
                    // get a 2xx, so this arrives more than once and must fulfil
                    // only the first time.
                    $orders->markPaid(
                        $provider->id(),
                        $event->reference,
                        $event->paymentRef,
                        $event->orderToken,
                    );

                    /*
                     * Invoice it — Lexware assigns the number and keeps the PDF.
                     *
                     * Deliberately OUTSIDE the `markPaid()` return value. That
                     * returns true only for the delivery that flipped the row,
                     * so gating on it would mean a Lexware outage during the
                     * first delivery loses the invoice for good: every later
                     * redelivery finds the order already paid and would skip.
                     * Running on every PAID delivery instead lets a provider's
                     * own retries heal a transient outage, and `invoice()` is
                     * idempotent — it refuses an order that already has one.
                     *
                     * `OrderInvoicing` swallows its own failures by contract:
                     * nothing it does may turn a taken payment into a non-2xx
                     * and an endless redelivery. The try/catch is the second
                     * floor, for the container itself failing to build it.
                     */
                    try {
                        $orderId = $orders->idForPayment(
                            $provider->id(),
                            $event->reference,
                            $event->orderToken,
                        );
                        if ($orderId !== null) {
                            $c->get(OrderInvoicing::class)->invoice($orderId);
                        }
                    } catch (\Throwable $e) {
                        error_log('[tds-shop] invoicing after payment failed: ' . $e->getMessage());
                    }
                } elseif ($event->kind === PaymentEvent::REFUNDED) {
                    $orders->markRefunded($provider->id(), $event->paymentRef, $event->orderToken);
                }
            }

            // 200 for a verified event we do not act on, too. A non-2xx makes
            // the provider retry it forever.
            return self::json($res, ['received' => true]);
        };

        $app->post('/shop/payment/{provider:[a-z]+}/webhook', $webhook);

        /**
         * The original Stripe endpoint, kept as an alias.
         *
         * This URL is configured in the Stripe dashboard and has been receiving
         * live events. Renaming a route a third party calls is a way to lose
         * payments silently — Stripe would retry into a 404 for three days and
         * then give up. It stays until the dashboard is repointed at
         * `/shop/payment/stripe/webhook` and the logs are quiet.
         */
        $app->post(
            '/shop/stripe/webhook',
            fn (Request $req, Response $res) => $webhook($req, $res, ['provider' => 'stripe']),
        );

        /** The customer's own order view. The token is the authorisation. */
        $app->get('/shop/order/{token:[a-f0-9]{32}}', function (Request $req, Response $res, array $args) use ($c): Response {
            $order = $c->get(OrderRepository::class)->byToken((string) $args['token']);
            return $order === null
                ? self::json($res, ['error' => 'Not found'], 404)
                : self::json($res, $order);
        });

        /* --- panel ---------------------------------------------------------- */

        $app->get('/shop/orders', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:orders', $res)) !== null) {
                return $deny;
            }
            return self::json($res, ['orders' => $c->get(OrderRepository::class)->recent()]);
        });

        $app->post('/shop/orders/{id:[0-9]+}/fulfil', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:orders', $res)) !== null) {
                return $deny;
            }
            $body = (array) ($req->getParsedBody() ?? []);
            $done = $c->get(OrderRepository::class)->markFulfilled(
                (int) $args['id'],
                trim((string) ($body['note'] ?? '')) ?: null,
            );
            return $done
                ? self::json($res, ['ok' => true])
                : self::json($res, ['error' => 'Nicht bezahlt oder unbekannt.'], 409);
        });

        /**
         * Invoice an order through Lexware Office, over the API.
         *
         * The payment webhook already does this the moment an order is paid, so
         * this is not the normal path — it is the one that exists because the
         * normal path can fail. Lexware can be down for the minute a webhook
         * arrives, and a taken payment must not be left without a document
         * because of it.
         *
         * Idempotent, so calling it on an already-invoiced order is safe and
         * answers with the invoice it already has rather than creating a
         * second. That matters: two documents for one sale is a bookkeeping
         * problem that is far harder to undo than a missing one.
         *
         * 409 for "not paid" or "attempts exhausted" — those are states, and a
         * caller can tell them apart by the message. 503 for "Lexware is not
         * set up here", because that is a deployment fact and not this order's
         * fault.
         */
        $app->post('/shop/orders/{id:[0-9]+}/invoice', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'shop:orders', $res)) !== null) {
                return $deny;
            }
            $result = $c->get(OrderInvoicing::class)->invoice((int) $args['id']);

            if ($result['status'] === 'ok') {
                return self::json($res, [
                    'ok' => true,
                    'invoiceNumber' => $result['number'],
                    'lexwareId' => $result['id'],
                ]);
            }
            if ($result['status'] === 'skipped') {
                $unavailable = !$c->get(OrderInvoicing::class)->isAvailable();
                return self::json($res, ['error' => $result['error']], $unavailable ? 503 : 409);
            }
            return self::json($res, ['error' => $result['error']], 409);
        });
    }


    /**
     * Turn a posted basket into priced lines, or null if any of it is unsellable.
     *
     * The single place where a basket becomes money. `/shop/quote` reads it and
     * `/shop/checkout` acts on it, so the number shown on the basket page and
     * the number charged cannot drift — which they would the moment there were
     * two implementations, because only one of them would learn about the next
     * change to the free-shipping threshold.
     *
     * Accepts the old single-`slug` body as well as `items[]`: the checkout page
     * in the shop frontend still sends the former, and the two repositories
     * release separately.
     *
     * @param  array<string,mixed> $body
     * @return array{lines:list<array<string,mixed>>,totals:array<string,mixed>,shipping:array{net:int,tax:int,gross:int},hasPhysical:bool,hasDigital:bool,regime:string,rate:Shipping}|null
     */
    private static function resolveBasket(ContainerInterface $c, array $body): ?array
    {
        $items = is_array($body['items'] ?? null) ? $body['items'] : [];
        if ($items === []) {
            $slug = trim((string) ($body['slug'] ?? ''));
            if ($slug === '') {
                return null;
            }
            $items = [['slug' => $slug, 'quantity' => (int) ($body['quantity'] ?? 1)]];
        }

        $lines = $c->get(OrderRepository::class)->sellableMany($items, self::lang($body['lang'] ?? null));
        if ($lines === null || $lines === []) {
            return null;
        }

        $hasPhysical = false;
        $hasDigital = false;
        $physical = [];
        foreach ($lines as $line) {
            $qty = max(1, (int) ($line['quantity'] ?? 1));
            if ((bool) ($line['requires_shipping'] ?? false)) {
                $hasPhysical = true;
                // Only the delivered lines pull the shipping tax toward their
                // rate; a service in the same basket is not being delivered.
                $physical[] = [
                    'net' => (int) $line['net_cents'] * $qty,
                    'rate' => (int) $line['vat_rate_bp'],
                ];
            } else {
                $hasDigital = true;
            }
        }

        $rate = self::shippingRate($c);
        $shipping = $rate->forLines($physical);

        return [
            'lines' => $lines,
            'totals' => OrderRepository::priceCart($lines, $shipping),
            'shipping' => $shipping,
            'hasPhysical' => $hasPhysical,
            'hasDigital' => $hasDigital,
            'regime' => Withdrawal::regime($hasDigital, $hasPhysical),
            'rate' => $rate,
        ];
    }

    /**
     * What delivery costs here.
     *
     * Settings rather than constants, for the same reason as
     * `SHOP_ALLOWED_COUNTRIES`: changing a shipping charge is a business
     * decision, and one that should not need a release.
     */
    private static function shippingRate(ContainerInterface $c): Shipping
    {
        return new Shipping(
            (int) self::setting($c, 'shipping_flat_cents', 'SHOP_SHIPPING_FLAT_CENTS', false),
            (int) self::setting($c, 'shipping_free_from_cents', 'SHOP_SHIPPING_FREE_FROM_CENTS', false),
        );
    }

    /**
     * The delivery address, or null when it is not complete.
     *
     * All-or-nothing on purpose. A half-filled address is not a lesser address,
     * it is a parcel that does not arrive — and storing the fragments would
     * leave a row that looks like it was checked. `line2` is the one genuinely
     * optional part.
     *
     * @param  array<string,mixed> $body
     * @return array<string,string>|null
     */
    private static function deliveryAddress(array $body, string $fallbackCountry): ?array
    {
        $get = static fn (string $key): string => trim((string) ($body[$key] ?? ''));

        $address = [
            'name' => $get('shipName') ?: $get('name'),
            'line1' => $get('shipLine1'),
            'line2' => $get('shipLine2'),
            'postcode' => $get('shipPostcode'),
            'city' => $get('shipCity'),
            'country' => strtoupper($get('shipCountry')) ?: $fallbackCountry,
        ];

        foreach (['name', 'line1', 'postcode', 'city', 'country'] as $required) {
            if ($address[$required] === '') {
                return null;
            }
        }
        return $address;
    }
    /**
     * Where the shop may sell.
     *
     * A setting rather than a constant, because widening it is a business
     * decision (and a tax one), not a code change — but the default is the
     * cautious one.
     *
     * @return list<string>
     */
    private static function allowedCountries(): array
    {
        $raw = trim((string) (getenv('SHOP_ALLOWED_COUNTRIES') ?: 'DE'));
        $out = [];
        foreach (explode(',', $raw) as $code) {
            $code = strtoupper(trim($code));
            if (preg_match('/^[A-Z]{2}$/', $code)) {
                $out[] = $code;
            }
        }
        return $out === [] ? ['DE'] : $out;
    }

    /** The default withdrawal wording, used when the page does not send its own. */
    private const WITHDRAWAL_TEXT = 'Ich verlange ausdrücklich, dass Sie vor Ende der '
        . 'Widerrufsfrist mit der Leistung beginnen. Mir ist bekannt, dass ich mein '
        . 'Widerrufsrecht mit vollständiger Erbringung der Leistung verliere.';

    private static function shopBaseUrl(): string
    {
        $base = rtrim((string) (getenv('SHOP_PUBLIC_URL') ?: ''), '/');
        return $base !== '' ? $base : 'https://shop.tracht-digital.de';
    }

    /** One shop setting, DB-first with an env fallback. Any provider's, not just Stripe's. */
    private static function setting(
        ContainerInterface $c,
        string $key,
        string $env,
        bool $secret,
    ): string {
        try {
            $store = $c->get(SettingsStore::class);
            $value = $secret ? $store->getSecret(self::SETTINGS_NS, $key) : $store->get(self::SETTINGS_NS, $key);
            $value = trim((string) ($value ?? ''));
            if ($value !== '') {
                return $value;
            }
        } catch (\Throwable) {
            // No store bound — env only, the pre-settings behaviour.
        }
        return trim((string) (getenv($env) ?: ''));
    }

    /* --- helpers ---------------------------------------------------------- */

    private static function require(UserContext $user, string $permission, Response $res): ?Response
    {
        if (!$user->isAuthenticated()) {
            return self::json($res, ['error' => 'Unauthorized'], 401);
        }
        if (!$user->has($permission)) {
            return self::json($res, ['error' => 'Forbidden'], 403);
        }
        return null;
    }

    /**
     * Validate a product payload. Returns a German message, or null when fine.
     *
     * Deliberately thin: only the fields whose absence would produce an
     * unreachable or unnamed catalogue entry. Everything else is constrained by
     * the repository's `oneOf()` fallbacks rather than rejected, so adding a
     * vocabulary value later is not a breaking change.
     *
     * @param array<string,mixed> $body
     */
    private static function validateProduct(array $body): ?string
    {
        $slug = (string) ($body['slug'] ?? '');
        if (!preg_match('/^[a-z0-9-]{2,120}$/', $slug)) {
            return 'Slug muss aus 2–120 Kleinbuchstaben, Ziffern und Bindestrichen bestehen.';
        }
        // A product whose slug is a literal route segment of the public API is
        // reachable in the panel and 404s (or worse, resolves to the wrong
        // handler) on the site. Cheaper to refuse here than to debug there.
        if (in_array($slug, ['categories', 'placement', 'offer', 'media'], true)) {
            return "Der Slug \"{$slug}\" ist für eine API-Route reserviert.";
        }
        if (trim((string) ($body['title'] ?? '')) === '') {
            return 'Titel fehlt.';
        }
        return null;
    }

    private static function lang(mixed $value): string
    {
        $v = is_string($value) ? strtolower($value) : '';
        return in_array($v, self::LANGS, true) ? $v : 'de';
    }

    /** The advertising label, § 5a Abs. 4 UWG. */
    private static function adLabel(string $lang): string
    {
        return $lang === 'en' ? 'Advertisement' : 'Anzeige';
    }

    /** @return array<string,mixed> */
    private static function emptyPlacement(string $key, string $lang): array
    {
        return ['key' => $key, 'heading' => null, 'label' => self::adLabel($lang), 'products' => []];
    }

    private static function json(Response $res, mixed $data, int $status = 200): Response
    {
        $res->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));
        return $res->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    /**
     * Route documentation for the admin frontend's API reference. Kept in its
     * own file so the prose does not sit in the middle of the wiring.
     *
     * @return list<array<string, mixed>>
     */
    public function apiDocs(): array
    {
        return require __DIR__ . '/../docs/api.php';
    }

    /**
     * The public read prefix a site key may gate.
     *
     * ONE prefix, and it is matched segment-wise by `SiteKeyMiddleware::matches()`
     * — so `/content/shop/categories`, `/content/shop/{slug}` and
     * `/content/shop/placement/{key}` are all covered by this single entry.
     *
     * Two things deliberately stay outside it:
     *
     * - `/shop/*` (the admin routes). A site key must never reach them; they are
     *   gated on the user's permissions, and listing them here would offer a
     *   second door.
     * - Anything a visitor's own browser calls directly. A key that ships in a
     *   client bundle is not a key. The payment webhooks are the sharpest
     *   case: no provider holds a site key, so a webhook under this prefix
     *   would be rejected outright. They live under
     *   `/shop/payment/{provider}/webhook` and authenticate by signature
     *   instead — each provider verifying its own scheme.
     *
     * And never widen this to `/content` — that would swallow the routes of
     * every other module that publishes there.
     *
     * @return list<string>
     */
    public function siteKeyRoutes(): array
    {
        return ['/content/shop'];
    }
}
