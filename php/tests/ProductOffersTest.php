<?php

declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Domain\ProductRepository;

/**
 * `setOffers` against a real MySQL/MariaDB (foreign keys and cascades are the
 * point). Set TDS_TEST_DB_DSN (+ _USER/_PASS) to run; skipped otherwise.
 */
final class ProductOffersTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $dsn = getenv('TDS_TEST_DB_DSN') ?: '';
        if ($dsn === '') {
            self::markTestSkipped('Set TDS_TEST_DB_DSN to run the offer persistence test.');
        }
        $this->pdo = new PDO($dsn, getenv('TDS_TEST_DB_USER') ?: null, getenv('TDS_TEST_DB_PASS') ?: null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['shop_own_product', 'shop_offer', 'shop_product'] as $t) {
            $this->pdo->exec("DROP TABLE IF EXISTS {$t}");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $this->pdo->exec('CREATE TABLE shop_product (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, sort_price_cents INT NULL) ENGINE=InnoDB');
        $this->pdo->exec('CREATE TABLE shop_offer (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id INT UNSIGNED NOT NULL, kind VARCHAR(20) NOT NULL,
            network VARCHAR(20) NOT NULL, external_id VARCHAR(100) NULL, merchant VARCHAR(200) NOT NULL,
            url VARCHAR(2000) NOT NULL, price_cents INT NULL, currency CHAR(3) NOT NULL,
            price_checked_at DATETIME NULL, availability VARCHAR(20) NOT NULL, position INT NOT NULL,
            FOREIGN KEY (product_id) REFERENCES shop_product(id) ON DELETE CASCADE) ENGINE=InnoDB');
        $this->pdo->exec('CREATE TABLE shop_own_product (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, offer_id INT UNSIGNED NOT NULL UNIQUE,
            FOREIGN KEY (offer_id) REFERENCES shop_offer(id) ON DELETE CASCADE) ENGINE=InnoDB');
        $this->pdo->exec('INSERT INTO shop_product (id) VALUES (1)');
        $this->pdo->exec("INSERT INTO shop_offer (id, product_id, kind, network, merchant, url, price_cents, currency, price_checked_at, availability, position)
            VALUES (10, 1, 'own', 'direct', 'TDS', '', 4900, 'EUR', NOW(), 'in_stock', 0),
                   (11, 1, 'affiliate', 'amazon', 'Amazon', 'https://a', 1999, 'EUR', '2026-10-01 08:00:00', 'in_stock', 1)");
        $this->pdo->exec('INSERT INTO shop_own_product (offer_id) VALUES (10)');
    }

    public function test_saving_offers_keeps_ids_sale_terms_and_confirmed_prices(): void
    {
        (new ProductRepository($this->pdo))->setOffers(1, [
            ['id' => 10, 'kind' => 'own', 'network' => 'direct', 'merchant' => 'TDS', 'priceCents' => 5900],
            ['id' => 11, 'kind' => 'affiliate', 'network' => 'amazon', 'merchant' => 'Amazon', 'url' => 'https://a', 'priceCents' => 1999],
        ]);

        // Delete-and-reinsert cascaded into shop_own_product: the own product
        // became unsellable on its first offer save.
        self::assertSame(1, (int) $this->pdo->query('SELECT COUNT(*) FROM shop_own_product WHERE offer_id = 10')->fetchColumn());
        self::assertSame([10, 11], array_map('intval', $this->pdo->query('SELECT id FROM shop_offer ORDER BY id')->fetchAll(PDO::FETCH_COLUMN)));
        self::assertSame(
            '2026-10-01 08:00:00',
            $this->pdo->query('SELECT price_checked_at FROM shop_offer WHERE id = 11')->fetchColumn(),
            'an unchanged affiliate price keeps its confirmation',
        );
        self::assertSame(5900, (int) $this->pdo->query('SELECT price_cents FROM shop_offer WHERE id = 10')->fetchColumn());
    }

    public function test_a_changed_affiliate_price_waits_for_the_sync_and_a_removed_offer_goes(): void
    {
        (new ProductRepository($this->pdo))->setOffers(1, [
            ['id' => 11, 'kind' => 'affiliate', 'network' => 'amazon', 'merchant' => 'Amazon', 'url' => 'https://a', 'priceCents' => 2499],
            ['kind' => 'affiliate', 'network' => 'awin', 'merchant' => 'Other', 'url' => 'https://b'],
        ]);

        self::assertNull($this->pdo->query('SELECT price_checked_at FROM shop_offer WHERE id = 11')->fetchColumn());
        self::assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM shop_offer WHERE id = 10')->fetchColumn());
        self::assertSame(2, (int) $this->pdo->query('SELECT COUNT(*) FROM shop_offer')->fetchColumn());
    }
}
