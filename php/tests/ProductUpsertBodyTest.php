<?php

declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Domain\ProductRepository;

/**
 * `upsert` against a real MySQL/MariaDB (`ON DUPLICATE KEY UPDATE` is the
 * point). Set TDS_TEST_DB_DSN (+ _USER/_PASS) to run; skipped otherwise.
 */
final class ProductUpsertBodyTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $dsn = getenv('TDS_TEST_DB_DSN') ?: '';
        if ($dsn === '') {
            self::markTestSkipped('Set TDS_TEST_DB_DSN to run the product upsert test.');
        }
        $this->pdo = new PDO($dsn, getenv('TDS_TEST_DB_USER') ?: null, getenv('TDS_TEST_DB_PASS') ?: null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['shop_product_translation', 'shop_product'] as $t) {
            $this->pdo->exec("DROP TABLE IF EXISTS {$t}");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $this->pdo->exec("CREATE TABLE shop_product (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, kind VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL,
            editorial_status VARCHAR(20) NOT NULL, category VARCHAR(60) NOT NULL, tags VARCHAR(500) NULL,
            brand VARCHAR(200) NULL, published_at DATETIME NULL) ENGINE=InnoDB");
        $this->pdo->exec("CREATE TABLE shop_product_translation (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id INT UNSIGNED NOT NULL, lang CHAR(2) NOT NULL,
            slug VARCHAR(200) NOT NULL, title VARCHAR(300) NOT NULL, teaser VARCHAR(500) NOT NULL,
            body MEDIUMTEXT NULL, body_format VARCHAR(20) NOT NULL DEFAULT 'blocks',
            meta_description VARCHAR(300) NULL, meta_title VARCHAR(70) NULL, summary VARCHAR(400) NULL,
            facts TEXT NULL, faq TEXT NULL, machine_translated TINYINT NOT NULL DEFAULT 0,
            UNIQUE KEY uq_product_lang (product_id, lang),
            FOREIGN KEY (product_id) REFERENCES shop_product(id) ON DELETE CASCADE) ENGINE=InnoDB");
        $this->pdo->exec("INSERT INTO shop_product (id, kind, status, editorial_status, category)
            VALUES (1, 'digital', 'draft', 'published', 'leistungspakete')");
        $this->pdo->exec("INSERT INTO shop_product_translation (product_id, lang, slug, title, teaser, body, body_format)
            VALUES (1, 'de', 'paket', 'Paket', 'Kurz', '## Umfang', 'markdown')");
    }

    public function test_saving_metadata_without_a_body_keeps_the_text(): void
    {
        (new ProductRepository($this->pdo))->upsert(1, 'de', [
            'slug' => 'paket', 'title' => 'Paket neu', 'teaser' => 'Kurz', 'kind' => 'digital',
            'status' => 'published', 'editorialStatus' => 'published', 'category' => 'leistungspakete',
        ]);

        $row = $this->pdo->query("SELECT title, body, body_format FROM shop_product_translation WHERE product_id = 1")->fetch();
        self::assertSame('Paket neu', $row['title']);
        self::assertSame('## Umfang', $row['body'], 'the panel form carries no body; saving it must not wipe one');
        self::assertSame('markdown', $row['body_format']);
    }

    public function test_a_sent_body_still_replaces_the_text(): void
    {
        (new ProductRepository($this->pdo))->upsert(1, 'de', [
            'slug' => 'paket', 'title' => 'Paket', 'teaser' => 'Kurz', 'kind' => 'digital',
            'body' => '## Neu', 'bodyFormat' => 'markdown', 'category' => 'leistungspakete',
        ]);

        self::assertSame('## Neu', $this->pdo->query('SELECT body FROM shop_product_translation WHERE product_id = 1')->fetchColumn());
    }
}
