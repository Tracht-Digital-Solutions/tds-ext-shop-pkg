<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tds\Ext\Shop\ShopModule;
use Tds\Frontend\Contract\CacheResult;
use Tds\Frontend\Contract\ReportingSiteCache;
use Tds\Frontend\Contract\SettingsStore;
use Tds\Frontend\Contract\SiteCache;
use Tds\Frontend\Contract\UserContext;

final class ShopCacheTest extends TestCase
{
    private function request(string $path, ?array $body = null, bool $failing = false, bool $configured = true): array
    {
        $written = false;
        $pdo = $this->createMock(PDO::class);
        $statement = function (string $sql) use (&$written): PDOStatement {
            $stmt = $this->createMock(PDOStatement::class);
            $stmt->method('execute')->willReturnCallback(function () use ($sql, &$written): bool {
                if (str_starts_with($sql, 'UPDATE')) $written = true;
                return true;
            });
            $rows = match (true) {
                str_contains($sql, 'body_length') => array_map(static fn ($lang) => [
                    'kind' => 'digital', 'category' => 'services', 'lang' => $lang,
                    'title' => 'Website check', 'meta_description' => str_repeat('a', 90),
                    'meta_title' => null, 'body_format' => 'markdown', 'body_length' => 100,
                ], ['de', 'en']),
                str_contains($sql, 'SELECT DISTINCT') => [1],
                str_contains($sql, 'name_de') => [['slug' => 'services', 'name_de' => 'Leistungen', 'name_en' => 'Services']],
                str_contains($sql, 'SELECT p.category, t.slug') => [
                    ['category' => 'services', 'slug' => 'pruefung', 'lang' => 'de'],
                    ['category' => 'services', 'slug' => 'review', 'lang' => 'en'],
                ],
                default => [],
            };
            $stmt->method('fetchAll')->willReturn($rows);
            $stmt->method('fetchColumn')->willReturn(1);
            return $stmt;
        };
        $pdo->method('prepare')->willReturnCallback($statement);
        $pdo->method('query')->willReturnCallback($statement);
        $user = $this->createMock(UserContext::class);
        $user->method('isAuthenticated')->willReturn(true);
        $user->method('has')->willReturn(true);
        $settings = $this->createMock(SettingsStore::class);
        $settings->method('get')->willReturn('https://shop.example.test');
        $settings->method('getSecret')->willReturn('test-cache-token');
        $cache = $this->createMock(ReportingSiteCache::class);
        $cache->method('isConfigured')->willReturn($configured);
        $cache->expects($configured ? self::once() : self::never())->method('rebuildWithResult')
            ->willReturnCallback(function ($url, $token, $events) use ($path, &$written, $failing): CacheResult {
                if ($path !== '/shop/cache/rebuild') self::assertTrue($written, 'Refresh must run after persistence');
                self::assertSame('https://shop.example.test', $url);
                self::assertSame('test-cache-token', $token);
                self::assertSame([
                    ['type' => 'product'],
                    ['type' => 'product', 'id' => 'pruefung', 'lang' => 'de'],
                    ['type' => 'category', 'id' => 'services'],
                    ['type' => 'product', 'id' => 'review', 'lang' => 'en'],
                ], array_map(static fn ($event) => $event->toArray(), $events));
                if ($failing) throw new \RuntimeException('offline');
                return new CacheResult(CacheResult::REFRESHED, rebuilt: ['/']);
            });
        $container = new class ([PDO::class => $pdo, UserContext::class => $user, SettingsStore::class => $settings, SiteCache::class => $cache]) implements ContainerInterface {
            public function __construct(private array $entries) {}
            public function set(string $id, mixed $value): void { $this->entries[$id] = $value; }
            public function has(string $id): bool { return isset($this->entries[$id]); }
            public function get(string $id): mixed {
                $value = $this->entries[$id];
                return $value instanceof \Closure ? $value($this) : $value;
            }
        };
        $app = AppFactory::create(null, $container);
        $app->addRoutingMiddleware();
        (new ShopModule())->register($app);
        $request = (new ServerRequestFactory())->createServerRequest('POST', $path);
        if ($body !== null) $request = $request->withParsedBody($body);
        $response = $app->handle($request);
        self::assertSame(200, $response->getStatusCode());
        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }

    public function testSinglePublicationRefreshesAfterSaving(): void
    {
        self::assertTrue($this->request('/shop/products/1/publish')['cached']);
    }

    public function testBulkPublicationRefreshesOnce(): void
    {
        $result = $this->request('/shop/products/publish', ['ids' => [1, 1]]);
        self::assertSame([1], $result['published']);
        self::assertTrue($result['cached']);
    }

    public function testWithdrawalRefreshesBothTranslations(): void
    {
        self::assertTrue($this->request('/shop/products/1/unpublish')['cached']);
    }

    public function testManualRebuildRepairsAlreadyPublishedCatalogue(): void
    {
        self::assertTrue($this->request('/shop/cache/rebuild')['cached']);
    }

    public function testCacheFailureDoesNotUndoPublication(): void
    {
        $result = $this->request('/shop/products/1/publish', failing: true);
        self::assertTrue($result['ok']);
        self::assertFalse($result['cached']);
        self::assertSame('failed', $result['cache_status']);
    }

    public function testMissingTokenIsReportedWithoutTransport(): void
    {
        $result = $this->request('/shop/products/1/publish', configured: false);
        self::assertTrue($result['ok']);
        self::assertSame('not_configured', $result['cache_status']);
    }
}
