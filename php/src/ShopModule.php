<?php
declare(strict_types=1);

namespace Tds\Ext\Shop;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Tds\Ext\Shop\Domain\ClickRepository;
use Tds\Ext\Shop\Domain\PlacementRepository;
use Tds\Ext\Shop\Domain\OrderRepository;
use Tds\Ext\Shop\Domain\ProductRepository;
use Tds\Ext\Shop\Domain\SyncQueueRepository;
use Tds\Ext\Shop\Service\AmazonPaApiClient;
use Tds\Ext\Shop\Service\OfferSync;
use Tds\Ext\Shop\Service\PaApiException;
use Tds\Ext\Shop\Service\StripeClient;
use Tds\Ext\Shop\Service\StripeException;
use Tds\Ext\Shop\Service\WebhookVerifier;
use Tds\Ext\Shop\Support\PaApiSigner;
use Tds\Ext\Shop\Support\SyncTicker;
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

        $c?->set(StripeClient::class, static function ($c): ?StripeClient {
            $key = self::stripeSetting($c, 'stripe_secret_key', 'SHOP_STRIPE_SECRET_KEY', true);
            return $key === '' ? null : new StripeClient($key);
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
        $app->post('/shop/checkout', function (Request $req, Response $res) use ($c): Response {
            $body = (array) ($req->getParsedBody() ?? []);
            $slug = trim((string) ($body['slug'] ?? ''));
            $email = trim((string) ($body['email'] ?? ''));
            $country = strtoupper(trim((string) ($body['country'] ?? 'DE')));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return self::json($res, ['error' => 'Bitte eine gültige E-Mail-Adresse angeben.'], 422);
            }
            if (($body['withdrawalConsent'] ?? false) !== true) {
                return self::json($res, [
                    'error' => 'Ohne die Bestätigung zum Widerrufsrecht kann nicht bestellt werden.',
                ], 422);
            }
            // Germany only, checked HERE rather than at Stripe so the refusal
            // can be explained. Selling an electronically supplied service to a
            // consumer elsewhere in the EU moves the place of supply to their
            // country and eventually means OSS registration — an obligation the
            // shop must not acquire by accident.
            if (!in_array($country, self::allowedCountries(), true)) {
                return self::json($res, [
                    'error' => 'Wir verkaufen diese Leistung derzeit nur nach Deutschland.',
                ], 422);
            }

            $stripe = $c->get(StripeClient::class);
            if ($stripe === null || !$stripe->isConfigured()) {
                return self::json($res, ['error' => 'Der Kauf ist derzeit nicht möglich.'], 503);
            }

            $orders = $c->get(OrderRepository::class);
            $offer = $orders->sellable($slug, self::lang($body['lang'] ?? null));
            if ($offer === null) {
                return self::json($res, ['error' => 'Dieses Angebot gibt es nicht.'], 404);
            }

            $withdrawalText = trim((string) ($body['withdrawalText'] ?? '')) ?: self::WITHDRAWAL_TEXT;
            $order = $orders->open(
                $offer,
                $email,
                trim((string) ($body['name'] ?? '')) ?: null,
                $withdrawalText,
                $country,
            );

            $base = self::shopBaseUrl();
            try {
                $session = $stripe->createCheckoutSession(
                    $order['orderNo'],
                    (string) $offer['title'],
                    $order['gross'],
                    (string) ($offer['currency'] ?? 'EUR'),
                    $email,
                    "{$base}/bestellung/{$order['token']}",
                    "{$base}/produkt/{$offer['slug']}",
                    ['order_no' => $order['orderNo'], 'token' => $order['token']],
                );
            } catch (StripeException $e) {
                return self::json($res, ['error' => 'Die Zahlung konnte nicht gestartet werden.'], 502);
            }

            $orders->attachSession($order['id'], $session['id']);
            return self::json($res, ['url' => $session['url'], 'token' => $order['token']]);
        });

        /**
         * Stripe's webhook.
         *
         * Outside `/content/shop` on purpose — a site-key prefix would reject
         * Stripe, which holds no key. It authenticates by signature instead.
         *
         * The raw body is required: the signature covers the exact bytes, so a
         * parsed-and-re-encoded payload will not verify.
         */
        $app->post('/shop/stripe/webhook', function (Request $req, Response $res) use ($c): Response {
            $secret = self::stripeSetting($c, 'stripe_webhook_secret', 'SHOP_STRIPE_WEBHOOK_SECRET', true);
            if ($secret === '') {
                return self::json($res, ['error' => 'webhook secret not configured'], 503);
            }
            $payload = (string) $req->getBody();
            if (!WebhookVerifier::verify($payload, $req->getHeaderLine('Stripe-Signature'), $secret)) {
                return self::json($res, ['error' => 'Invalid signature'], 400);
            }

            try {
                $event = json_decode($payload, true, 16, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                return self::json($res, ['error' => 'Invalid payload'], 400);
            }

            $type = (string) ($event['type'] ?? '');
            $object = (array) ($event['data']['object'] ?? []);
            $orders = $c->get(OrderRepository::class);

            if ($type === 'checkout.session.completed') {
                $sessionId = (string) ($object['id'] ?? '');
                $intent = isset($object['payment_intent']) ? (string) $object['payment_intent'] : null;
                // markPaid() is idempotent by its WHERE clause: Stripe retries
                // until it gets a 2xx, so this arrives more than once and must
                // fulfil only the first time.
                $orders->markPaid($sessionId, $intent);
            } elseif ($type === 'charge.refunded') {
                $orders->markRefunded((string) ($object['payment_intent'] ?? ''));
            }

            // 200 for an event we do not handle, too. A non-2xx makes Stripe
            // retry it forever.
            return self::json($res, ['received' => true]);
        });

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

    /** One Stripe setting, DB-first with an env fallback. */
    private static function stripeSetting(
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
     *   client bundle is not a key. The Stripe webhook, when it arrives in a
     *   later checkpoint, is the sharpest case: Stripe holds no site key, so a
     *   webhook under this prefix would be rejected outright. It belongs under
     *   `/shop/stripe/webhook` and authenticates by signature instead.
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
