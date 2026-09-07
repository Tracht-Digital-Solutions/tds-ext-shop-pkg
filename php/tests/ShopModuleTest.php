<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tds\Ext\Shop\ShopModule;
use Tds\Frontend\Contract\ModuleRegistry;
use Tds\Frontend\Contract\UserContext;

/**
 * Routes, RBAC and the two behaviours that only look correct when they are
 * wrong: a public route that degrades instead of 500ing, and an advertising
 * label that is served even when nothing else is.
 *
 * No database. The PDO handed to the container deliberately fails on use, which
 * is exactly the condition the fail-soft branches exist for — so the "empty
 * payload" tests below assert real behaviour rather than an empty table.
 */
final class ShopModuleTest extends TestCase
{
    private static function app(?UserContext $user = null): \Slim\App
    {
        $container = new class ($user) implements ContainerInterface {
            public function __construct(private readonly ?UserContext $user)
            {
            }

            /** @var array<string,mixed> */
            private array $bound = [];

            public function set(string $id, mixed $value): void
            {
                $this->bound[$id] = $value;
            }

            public function get(string $id): mixed
            {
                if ($id === UserContext::class) {
                    return $this->user ?? new AnonymousUser();
                }
                if ($id === PDO::class) {
                    // Always fails. There is no sqlite driver in this PHP and a
                    // MySQL-shaped repository would not run on one anyway, so
                    // rather than skipping these tests without a database, the
                    // suite pins the behaviour that MATTERS when the database is
                    // gone — which is the case nobody exercises by hand.
                    throw new \RuntimeException('no database');
                }
                $value = $this->bound[$id] ?? null;
                return is_callable($value) ? $value($this) : $value;
            }

            public function has(string $id): bool
            {
                return true; // mirrors PHP-DI's autowiring answer, deliberately
            }
        };

        $app = AppFactory::create(null, $container);
        $app->addRoutingMiddleware();
        (new ModuleRegistry([new ShopModule()]))->registerAll($app);
        return $app;
    }

    private static function call(\Slim\App $app, string $method, string $path): \Psr\Http\Message\ResponseInterface
    {
        return $app->handle((new ServerRequestFactory())->createServerRequest($method, $path));
    }

    /* --- RBAC ------------------------------------------------------------- */

    public function testAdminRouteRejectsAnonymous(): void
    {
        $res = self::call(self::app(), 'GET', '/shop/summary');
        self::assertSame(401, $res->getStatusCode());
    }

    public function testAdminRouteRejectsAuthenticatedUserWithoutThePermission(): void
    {
        // The distinction that matters: 403 says "you are known and may not",
        // 401 says "log in". Collapsing them sends a logged-in user to a login
        // page that will not help.
        $res = self::call(self::app(new FakeUser(['shop:read'])), 'GET', '/shop/placements');
        self::assertSame(403, $res->getStatusCode());
    }

    public function testAdminRoutePassesTheGateWithThePermission(): void
    {
        // Proving "it got past the gate" without a database: the handler
        // reaches the repository and the stub PDO throws. A 401 or a 403 would
        // mean it never got there, so the exception IS the assertion.
        //
        // Note what this also pins: admin routes are deliberately NOT
        // fail-soft. The public reads degrade to an empty payload because a
        // visitor cannot act on a database error; a staff member looking at the
        // panel can, and hiding it behind a calm empty state would send them
        // hunting for products that were never missing.
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('no database');
        self::call(self::app(new FakeUser(['shop:read'])), 'GET', '/shop/summary');
    }

    /* --- fail-soft public reads ------------------------------------------- */

    public function testCatalogueAnswersEmptyRatherThanFailingWhenTheDatabaseIsDown(): void
    {
        // A public site's content fetch is fail-soft, so a 500 here does not
        // surface as an error anywhere — it surfaces as a page that silently
        // drops a section. Answering 200-with-nothing does the same thing
        // without also filling the log.
        $res = self::call(self::app(), 'GET', '/content/shop');
        self::assertSame(200, $res->getStatusCode());
        $body = json_decode((string) $res->getBody(), true);
        self::assertSame([], $body['products']);
        self::assertNull($body['nextCursor']);
    }

    public function testCategoriesAnswerEmptyRatherThanFailing(): void
    {
        $res = self::call(self::app(), 'GET', '/content/shop/categories');
        self::assertSame(200, $res->getStatusCode());
        self::assertSame([], json_decode((string) $res->getBody(), true)['categories']);
    }

    /* --- the advertising label -------------------------------------------- */

    public function testAnUnresolvablePlacementStillCarriesItsAdvertisingLabel(): void
    {
        // The label is § 5a Abs. 4 UWG, not decoration, and three surfaces
        // render this slot. Serving it even from the degraded path is what
        // makes it impossible for a consumer to render an unlabelled one.
        $res = self::call(self::app(), 'GET', '/content/shop/placement/blog-article-end');
        self::assertSame(200, $res->getStatusCode());
        $body = json_decode((string) $res->getBody(), true);
        self::assertSame('Anzeige', $body['label']);
        self::assertSame([], $body['products']);
    }

    public function testTheAdvertisingLabelFollowsTheRequestedLanguage(): void
    {
        $res = self::call(self::app(), 'GET', '/content/shop/placement/blog-article-end?lang=en');
        self::assertSame('Advertisement', json_decode((string) $res->getBody(), true)['label']);
    }

    /* --- input validation -------------------------------------------------- */

    private static function post(string $path, array $body, UserContext $user): \Psr\Http\Message\ResponseInterface
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', $path)
            ->withParsedBody($body);
        return self::app($user)->handle($request);
    }

    public function testRejectsAnInvalidSlugBeforeTouchingTheDatabase(): void
    {
        // 422 rather than the stub PDO's exception proves validation runs
        // first — which is what keeps an unreachable catalogue entry from
        // being written at all.
        $res = self::post('/shop/products', ['slug' => 'Nicht Erlaubt!', 'title' => 'X'], new FakeUser(['shop:write']));
        self::assertSame(422, $res->getStatusCode());
    }

    public function testRejectsASlugThatCollidesWithAnApiRoute(): void
    {
        // `/content/shop/categories` is a literal route. A product with that
        // slug is editable in the panel and unreachable on the site — FastRoute
        // resolves static segments before variable ones, so the product page
        // would never win. Refusing here is cheaper than debugging there.
        $res = self::post('/shop/products', ['slug' => 'categories', 'title' => 'X'], new FakeUser(['shop:write']));
        self::assertSame(422, $res->getStatusCode());
        self::assertStringContainsString('reserviert', (string) $res->getBody());
    }

    public function testRejectsAProductWithoutATitle(): void
    {
        $res = self::post('/shop/products', ['slug' => 'gueltig', 'title' => '  '], new FakeUser(['shop:write']));
        self::assertSame(422, $res->getStatusCode());
    }

    public function testPermissionIsCheckedBeforeValidation(): void
    {
        // Order matters: a reader without write rights must get 403, not a
        // helpful 422 telling them what to fix about a request they may not
        // make in the first place.
        $res = self::post('/shop/products', ['slug' => '!!', 'title' => ''], new FakeUser(['shop:read']));
        self::assertSame(403, $res->getStatusCode());
    }

    /* --- site-key surface -------------------------------------------------- */

    public function testSiteKeyPrefixCoversPublicReadsAndNothingElse(): void
    {
        $prefixes = (new ShopModule())->siteKeyRoutes();
        self::assertSame(['/content/shop'], $prefixes);

        // The two failures this guards. A prefix of `/content` would swallow
        // every other module's public routes; a prefix reaching `/shop` would
        // put a second door on the permission-gated admin API.
        foreach ($prefixes as $prefix) {
            self::assertStringStartsWith('/content/', $prefix);
            self::assertNotSame('/content', rtrim($prefix, '/'));
            self::assertStringNotContainsString('/shop/stripe', $prefix);
        }
    }

    public function testAdminRoutesAreNotUnderTheSiteKeyPrefix(): void
    {
        // Matched the way SiteKeyMiddleware::matches() does it — segment-wise,
        // so `/content/shopping` would not be covered by `/content/shop` but
        // `/content/shop/anything` would.
        $prefix = (new ShopModule())->siteKeyRoutes()[0];
        foreach (['/shop/summary', '/shop/placements', '/shop/clicks', '/shop/stripe/webhook'] as $path) {
            self::assertFalse(
                $path === $prefix || str_starts_with($path . '/', $prefix . '/'),
                "Admin-Route unter dem Site-Key-Prefix: {$path}",
            );
        }
    }

    /* --- manifest ---------------------------------------------------------- */

    public function testDeclaresItsPermissions(): void
    {
        $ids = array_map(static fn ($p): string => $p->id, (new ShopModule())->permissions());
        self::assertSame(['shop:read', 'shop:write', 'shop:orders', 'shop:sync'], $ids);
    }
}

/** Anonymous principal — what the base yields for an unauthenticated request. */
final class AnonymousUser implements UserContext
{
    public function isAuthenticated(): bool
    {
        return false;
    }

    public function userId(): ?int
    {
        return null;
    }

    public function email(): ?string
    {
        return null;
    }

    public function isAdmin(): bool
    {
        return false;
    }

    public function permissions(): array
    {
        return [];
    }

    public function has(string $permission): bool
    {
        return false;
    }

    public function activeCompanyId(): ?int
    {
        return null;
    }
}

/** A signed-in principal holding exactly the listed permissions. */
final class FakeUser implements UserContext
{
    /** @param list<string> $permissions */
    public function __construct(private readonly array $permissions = [])
    {
    }

    public function isAuthenticated(): bool
    {
        return true;
    }

    public function userId(): ?int
    {
        return 1;
    }

    public function email(): ?string
    {
        return 'test@example.com';
    }

    public function isAdmin(): bool
    {
        return false;
    }

    public function permissions(): array
    {
        return $this->permissions;
    }

    public function has(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function activeCompanyId(): ?int
    {
        return null;
    }
}
