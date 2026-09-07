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
use Tds\Ext\Shop\Domain\ProductRepository;
use Tds\Ext\Shop\Domain\SyncQueueRepository;
use Tds\Ext\Shop\Service\AmazonPaApiClient;
use Tds\Ext\Shop\Service\OfferSync;
use Tds\Ext\Shop\Service\PaApiException;
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

        $this->registerPublic($app, $c);
        $this->registerAdmin($app, $c);
        $this->registerSync($app, $c);
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
