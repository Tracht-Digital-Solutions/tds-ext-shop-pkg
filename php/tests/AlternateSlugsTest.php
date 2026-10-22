<?php

declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Domain\ProductRepository;

/**
 * The other language's slug per product — what the shop pairs hreflang on.
 * Slugs may differ per language, so the pairing must come from the product id.
 */
final class AlternateSlugsTest extends TestCase
{
    private function repository(): ProductRepository
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is not available.');
        }
        $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('CREATE TABLE shop_product_translation (product_id INTEGER NOT NULL, lang TEXT NOT NULL, slug TEXT NOT NULL)');
        $pdo->exec("INSERT INTO shop_product_translation (product_id, lang, slug) VALUES
            (1, 'de', 'webauftritt'), (1, 'en', 'website'),
            (2, 'de', 'nur-deutsch'),
            (3, 'de', 'gleich'), (3, 'en', 'gleich'),
            (4, 'en', '')");
        return new ProductRepository($pdo);
    }

    public function testDifferentSlugsArePairedOnTheProduct(): void
    {
        self::assertSame([1 => 'website', 3 => 'gleich'], $this->repository()->alternateSlugs([1, 2, 3], 'en'));
    }

    public function testTheOtherDirection(): void
    {
        self::assertSame([1 => 'webauftritt'], $this->repository()->alternateSlugs([1], 'de'));
    }

    public function testMissingOrEmptyTranslationsAreLeftOut(): void
    {
        self::assertSame([], $this->repository()->alternateSlugs([2, 4], 'en'));
        self::assertSame([], $this->repository()->alternateSlugs([], 'en'));
    }
}
