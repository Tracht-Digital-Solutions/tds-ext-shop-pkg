<?php

declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Support\CatalogueSeed;
use Tds\Ext\Shop\Support\CategoryName;
use Tds\Ext\Shop\Support\ProductReadiness;

/**
 * The prepared catalogue, without a database.
 *
 * "Only release in the panel" holds only if every seeded product would pass the
 * publish gate as it lands. A meta description of 161 characters fails nowhere
 * at seed time — it fails as a greyed-out "Freigeben" button weeks later — so
 * the gate's own limits are checked here against every row.
 */
final class ShopCatalogueSeedTest extends TestCase
{
    private const OWN_FILES = [
        'own-webauftritt.php', 'own-seo-sichtbarkeit.php', 'own-wartung-betrieb.php',
        'own-recht-datenschutz.php', 'own-e-mail-domain.php', 'own-digitalisierung.php', 'own-schulung.php',
    ];

    /** Slugs of the six packages seeded by `20260915000001`, which must not collide. */
    private const EXISTING_SLUGS = [
        'digital-check-fuer-ihren-betrieb', 'digital-check-for-your-business',
        'website-check-mit-massnahmenliste', 'website-check-with-action-list',
        'google-unternehmensprofil-einrichten', 'google-business-profile-setup',
        'ablauf-analyse-ein-arbeitsablauf', 'workflow-analysis-one-process',
        'machbarkeitspruefung-schnittstelle', 'integration-feasibility-check',
        'pflegekontingent-webseite-5-stunden', 'website-care-5-hours',
    ];

    /** @return list<array<string,mixed>> */
    private static function own(): array
    {
        $all = [];
        foreach (self::OWN_FILES as $file) {
            foreach (CatalogueSeed::load($file) as $product) {
                $product['_file'] = $file;
                $all[] = $product;
            }
        }
        return $all;
    }

    /** @return list<array<string,mixed>> */
    private static function all(): array
    {
        $affiliate = is_file(CatalogueSeed::dir() . '/affiliate.php') ? CatalogueSeed::load('affiliate.php') : [];
        foreach ($affiliate as &$p) {
            $p['_file'] = 'affiliate.php';
        }
        return array_merge(self::own(), $affiliate);
    }

    public function test_there_are_at_least_fifty_own_products(): void
    {
        self::assertGreaterThanOrEqual(50, count(self::own()));
    }

    public function test_slugs_are_valid_and_unique_per_language(): void
    {
        $seen = ['de' => [], 'en' => []];
        foreach (self::EXISTING_SLUGS as $i => $slug) {
            $seen[$i % 2 === 0 ? 'de' : 'en'][$slug] = 'existing';
        }
        foreach (self::all() as $p) {
            foreach (['de', 'en'] as $lang) {
                $slug = $p[$lang]['slug'];
                self::assertMatchesRegularExpression('/^[a-z0-9-]{2,120}$/', $slug, "{$p['_file']}: {$slug}");
                self::assertNotContains($slug, ['categories', 'placement', 'offer', 'media'], $slug);
                self::assertArrayNotHasKey($slug, $seen[$lang], "{$slug} ({$lang}) is used twice");
                $seen[$lang][$slug] = $p['_file'];
            }
        }
    }

    public function test_every_product_passes_the_publish_gate_as_seeded(): void
    {
        $categories = array_column(CatalogueSeed::load('categories.php'), null, 'slug');
        foreach (self::all() as $p) {
            $own = ($p['kind'] ?? 'digital') === 'digital';
            $translations = [];
            foreach (['de', 'en'] as $lang) {
                $t = $p[$lang];
                $translations[$lang] = [
                    'title' => $t['title'],
                    'bodyLength' => mb_strlen(trim($t['body'])),
                    'bodyFormat' => 'markdown',
                    'metaDescription' => $t['meta'],
                    'metaTitle' => $t['meta_title'] ?? null,
                ];
            }
            $problems = ProductReadiness::problems([
                'kind' => $own ? 'digital' : 'affiliate',
                'category' => $p['category'],
                'categoryNamed' => isset($categories[$p['category']]),
                'hasCover' => $own ? isset($p['image']) : true, // affiliate: the sync supplies it
                'ownPrice' => $own,
                'freshAffiliatePrice' => !$own, // likewise
            ] + ['translations' => $translations]);
            self::assertSame([], $problems, "{$p['_file']} {$p['de']['slug']}: " . json_encode($problems, JSON_UNESCAPED_UNICODE));
        }
    }

    public function test_answer_fields_are_complete(): void
    {
        foreach (self::all() as $p) {
            foreach (['de', 'en'] as $lang) {
                $t = $p[$lang];
                $where = "{$p['_file']} {$t['slug']}";
                self::assertNotSame('', trim($t['teaser']), "{$where}: teaser");
                $summary = mb_strlen($t['summary']);
                self::assertTrue($summary >= 80 && $summary <= 400, "{$where}: summary has {$summary} characters");
                self::assertGreaterThanOrEqual(3, count($t['faq']), "{$where}: FAQ");
                self::assertLessThanOrEqual(5, count($t['faq']), "{$where}: FAQ");
                self::assertGreaterThanOrEqual(3, count($t['facts']), "{$where}: facts");
                foreach ($t['faq'] as $qa) {
                    self::assertNotSame('', trim($qa['q'] ?? ''), "{$where}: FAQ question");
                    self::assertNotSame('', trim($qa['a'] ?? ''), "{$where}: FAQ answer");
                }
                self::assertStringContainsString('## ', $t['body'], "{$where}: body has headings");
            }
            self::assertSame(count($p['de']['faq']), count($p['en']['faq']), "{$p['de']['slug']}: DE/EN FAQ count differs");
        }
    }

    public function test_own_prices_follow_the_hourly_rates(): void
    {
        // The landing page's rates: Webauftritt 65, Prozesse/Lösungen 70, Beratung 75.
        foreach (self::own() as $p) {
            self::assertContains($p['rate_cents'], [6500, 7000, 7500], $p['de']['slug']);
            self::assertGreaterThan(0, $p['hours'], $p['de']['slug']);
            self::assertContains($p['fulfilment'], ['project', 'ticket', 'manual'], $p['de']['slug']);
            self::assertStringStartsWith('https://', $p['image'], $p['de']['slug']);
            self::assertNotSame('', trim($p['prompt'] ?? ''), "{$p['de']['slug']}: image prompt");
            // Shown gross; the body must not claim "zuzüglich".
            self::assertStringNotContainsString('zuzüglich', $p['de']['body'], $p['de']['slug']);
            self::assertStringContainsString('inklusive 19 %', $p['de']['body'], $p['de']['slug']);
        }
        self::assertSame(7735, CatalogueSeed::grossCents(6500));
    }

    public function test_categories_are_complete_and_valid(): void
    {
        $slugs = [];
        foreach (CatalogueSeed::load('categories.php') as $c) {
            self::assertMatchesRegularExpression(CategoryName::SLUG_PATTERN, $c['slug']);
            foreach (['name_de', 'name_en', 'intro_de', 'intro_en'] as $field) {
                self::assertNotSame('', trim($c[$field]), "{$c['slug']}.{$field}");
            }
            self::assertGreaterThanOrEqual(3, count($c['faq_de']), $c['slug']);
            self::assertSame(count($c['faq_de']), count($c['faq_en']), $c['slug']);
            $slugs[] = $c['slug'];
        }
        foreach (self::all() as $p) {
            self::assertContains($p['category'], $slugs, "{$p['de']['slug']}: category has no seed entry");
        }
    }

    public function test_affiliate_products_carry_an_asin_and_no_price(): void
    {
        if (!is_file(CatalogueSeed::dir() . '/affiliate.php')) {
            self::markTestSkipped('No affiliate seed yet.');
        }
        $affiliate = CatalogueSeed::load('affiliate.php');
        self::assertGreaterThanOrEqual(20, count($affiliate));
        foreach ($affiliate as $p) {
            self::assertSame('affiliate', $p['kind']);
            self::assertMatchesRegularExpression('/^B0[0-9A-Z]{8}$/', $p['asin'], $p['de']['slug']);
            self::assertArrayNotHasKey('hours', $p, 'a partner price never comes from the seed');
            self::assertArrayNotHasKey('image', $p, 'Amazon images come from the sync, never a copy');
        }
    }
}
