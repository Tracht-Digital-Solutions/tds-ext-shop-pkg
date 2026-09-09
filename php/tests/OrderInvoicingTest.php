<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tds\Ext\Shop\Domain\OrderRepository;
use Tds\Ext\Shop\Service\OrderInvoiceBuilder;
use Tds\Ext\Shop\Service\OrderInvoicing;

/**
 * The promises invoicing makes to its caller.
 *
 * The caller is a payment webhook, and that shapes every rule here. A provider
 * retries anything that is not a 2xx, so an exception escaping this service
 * would turn a successful payment into an endless redelivery. And the Lexware
 * package is genuinely optional — a shop deployed without it is a valid
 * deployment, not a broken one, and must not record a failure on every order it
 * takes.
 *
 * No database, deliberately: the same choice `ShopModuleTest` documents. The
 * PDO here always throws, which is exactly the hostile case — it pins what
 * happens when the storage under this service is gone, which is the situation
 * nobody exercises by hand.
 */
final class OrderInvoicingTest extends TestCase
{
    /** A container that resolves nothing — i.e. no Lexware package installed. */
    private static function emptyContainer(): ContainerInterface
    {
        return new class implements ContainerInterface {
            public function get(string $id): mixed
            {
                throw new \RuntimeException('not bound: ' . $id);
            }

            public function has(string $id): bool
            {
                // Mirrors PHP-DI's autowiring answer on purpose: `has()` is not
                // a usable question and this service must not ask it.
                return true;
            }
        };
    }

    private static function service(?ContainerInterface $container = null): OrderInvoicing
    {
        // Constructing the repository runs no query, so a PDO that would throw
        // on use is fine to hand it here.
        $pdo = new class ('sqlite::memory:') extends PDO {
            public function __construct(string $dsn)
            {
                // Never actually connects; every call below is the failure case.
            }

            public function prepare(string $query, array $options = []): \PDOStatement|false
            {
                throw new \RuntimeException('no database');
            }
        };

        return new OrderInvoicing(
            new OrderRepository($pdo),
            new OrderInvoiceBuilder(),
            $container ?? self::emptyContainer(),
        );
    }

    /**
     * Without the Lexware package there is nothing to invoice through, and
     * saying so is not the same as failing.
     */
    public function testUnavailableWithoutTheLexwarePackage(): void
    {
        self::assertFalse(self::service()->isAvailable());
    }

    public function testUnavailableWithoutAContainer(): void
    {
        $service = new OrderInvoicing(
            new OrderRepository(new class ('sqlite::memory:') extends PDO {
                public function __construct(string $dsn)
                {
                }
            }),
            new OrderInvoiceBuilder(),
            null,
        );
        self::assertFalse($service->isAvailable());
    }

    /**
     * THE rule. A database that is gone, a container that resolves nothing, an
     * order that does not exist — none of it may reach the webhook as an
     * exception, because the provider would retry a payment it already took.
     */
    public function testNeverThrowsAtItsCaller(): void
    {
        $result = self::service()->invoice(1);

        self::assertIsArray($result);
        self::assertSame('failed', $result['status']);
        self::assertNotNull($result['error']);
    }

    /**
     * The result shape is part of the contract: the panel route reads
     * `status`, `number` and `id` off it.
     */
    public function testResultCarriesTheDocumentedKeys(): void
    {
        $result = self::service()->invoice(1);
        self::assertSame(['status', 'number', 'id', 'error'], array_keys($result));
    }

    /**
     * The ceiling exists so a payload Lexware permanently refuses stops being
     * offered. A generous number is fine; an absent one is not.
     */
    public function testAttemptCeilingIsFiniteAndAllowsRetries(): void
    {
        self::assertGreaterThan(1, OrderInvoicing::MAX_ATTEMPTS);
        self::assertLessThan(20, OrderInvoicing::MAX_ATTEMPTS);
    }

    /**
     * The client is named as a string, never imported.
     *
     * A `use` statement for a class in an optional package is a fatal error the
     * moment the package is absent — which is precisely the deployment this
     * service is built to survive.
     */
    public function testTheLexwareClientIsReferencedByNameOnly(): void
    {
        $source = file_get_contents(__DIR__ . '/../src/Service/OrderInvoicing.php');

        self::assertIsString($source);
        self::assertStringNotContainsString('use Tds\\Ext\\Lexware\\', $source);
        self::assertStringContainsString('class_exists', $source);
        // `has()` is the trap this platform has paid for repeatedly: PHP-DI
        // answers it out of autowiring, so it is true for any concrete class.
        // The prose above the code is allowed to name it; the code is not.
        self::assertStringNotContainsString('$this->container->has(', $source);
    }
}
