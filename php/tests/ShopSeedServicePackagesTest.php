<?php
declare(strict_types=1);

namespace Phinx\Migration {
    // The seed extends Phinx's base class, which this package does not install —
    // the core API brings Phinx at runtime. Reading the seed's constants needs
    // the name to exist, nothing more.
    if (!\class_exists(AbstractMigration::class)) {
        abstract class AbstractMigration
        {
        }
    }
}

namespace Tds\Ext\Shop\Tests {
    use PHPUnit\Framework\TestCase;
    use Tds\Ext\Shop\Domain\OrderRepository;
    use Tds\Ext\Shop\ShopModule;
    use Tds\Ext\Shop\Support\CategoryName;

    /**
     * The seeded service packages, checked without a database.
     *
     * Every rule here is one the panel or the shop would apply to the same data —
     * a seed writes past the panel's validation, so nothing else would notice a
     * slug the panel rejects, a snippet Google truncates, or a body the shop
     * renders as a code block because a heredoc lost its indentation.
     */
    final class ShopSeedServicePackagesTest extends TestCase
    {
        private const LANGS = ['de', 'en'];

        private const SECTIONS = [
            'de' => ['## Das ist enthalten', '## So läuft es ab', '## Nicht enthalten', '## Gut zu wissen'],
            'en' => ['## What is included', '## How it works', '## Not included', '## Good to know'],
        ];

        /** Reserved by the shop's routes; the panel refuses them as slugs. */
        private const RESERVED_SLUGS = ['categories', 'placement', 'offer', 'media'];

        public static function setUpBeforeClass(): void
        {
            require_once (new ShopModule())->migrations()[0] . '/20260915000001_shop_seed_service_packages.php';
        }

        /** @return list<array<string,mixed>> */
        private static function packages(): array
        {
            return \ShopSeedServicePackages::PACKAGES;
        }

        public function testThereAreSixPackages(): void
        {
            self::assertCount(6, self::packages());
        }

        public function testSlugsPassThePanelRulesAndAreUniquePerLanguage(): void
        {
            foreach (self::LANGS as $lang) {
                $slugs = array_map(static fn (array $p): string => $p[$lang]['slug'], self::packages());
                foreach ($slugs as $slug) {
                    self::assertMatchesRegularExpression('/^[a-z0-9-]{2,120}$/', $slug);
                    self::assertNotContains($slug, self::RESERVED_SLUGS);
                }
                self::assertSame(count($slugs), count(array_unique($slugs)), "Doppelter Slug ({$lang})");
            }
        }

        public function testTheCategoryPassesThePanelRule(): void
        {
            $category = \ShopSeedServicePackages::CATEGORY;
            self::assertMatchesRegularExpression(CategoryName::SLUG_PATTERN, $category['slug']);
            self::assertLessThanOrEqual(80, mb_strlen($category['name_de']));
            self::assertLessThanOrEqual(80, mb_strlen($category['name_en']));
        }

        public function testTextsFitTheirColumnsAndTheSearchSnippet(): void
        {
            foreach (self::packages() as $package) {
                foreach (self::LANGS as $lang) {
                    $text = $package[$lang];
                    $where = "{$lang}/{$text['slug']}";
                    self::assertNotSame('', trim($text['title']), $where);
                    self::assertLessThanOrEqual(200, mb_strlen($text['title']), $where);
                    self::assertNotSame('', trim($text['teaser']), $where);
                    self::assertLessThanOrEqual(400, mb_strlen($text['teaser']), $where);
                    // The description budget every public site keeps: long
                    // enough to say something, short enough not to be cut.
                    $meta = mb_strlen($text['meta']);
                    self::assertGreaterThanOrEqual(80, $meta, "{$where}: Meta {$meta} Zeichen");
                    self::assertLessThanOrEqual(160, $meta, "{$where}: Meta {$meta} Zeichen");
                }
            }
        }

        public function testBodiesAreMarkdownWithTheFourSections(): void
        {
            foreach (self::packages() as $package) {
                foreach (self::LANGS as $lang) {
                    $body = $package[$lang]['body'];
                    $where = "{$lang}/{$package[$lang]['slug']}";
                    foreach (self::SECTIONS[$lang] as $heading) {
                        self::assertStringContainsString("\n{$heading}\n", $body, $where);
                    }
                    // Four leading spaces make a Markdown code block. A heredoc
                    // whose closing marker moves leaves exactly that behind.
                    self::assertDoesNotMatchRegularExpression('/^(?: {4}|\t)/m', $body, $where);
                }
            }
        }

        public function testTagsAreSlugShaped(): void
        {
            foreach (self::packages() as $package) {
                $tags = explode(',', $package['tags']);
                self::assertLessThanOrEqual(20, count($tags));
                foreach ($tags as $tag) {
                    self::assertMatchesRegularExpression('/^[a-z0-9-]{1,60}$/', $tag);
                }
            }
        }

        public function testSaleTermsAreSellableAndPricedLikeTheCheckout(): void
        {
            foreach (self::packages() as $package) {
                self::assertIsInt($package['net_cents']);
                self::assertGreaterThan(0, $package['net_cents']);
                self::assertSame(1900, $package['vat_rate_bp']);
                self::assertContains($package['fulfilment'], ['ticket', 'project', 'manual']);
                // The seed's own gross (for the sort helper) must be the one
                // the checkout charges, cent for cent.
                self::assertSame(
                    OrderRepository::price($package['net_cents'], $package['vat_rate_bp'])['gross'],
                    \ShopSeedServicePackages::grossCents($package['net_cents'], $package['vat_rate_bp']),
                );
            }
        }

        public function testCoversAreTheLandingPagesServicePhotos(): void
        {
            foreach (self::packages() as $package) {
                self::assertMatchesRegularExpression(
                    '#^https://tracht-digital\.de/images/services/0[1-4]-[a-z]+-800\.webp$#',
                    $package['image'],
                );
            }
        }

        public function testNoCopyBreaksThePositioning(): void
        {
            // No free or time-boxed offer anywhere on the sites, and no client
            // named. The seed is published prose like any page.
            $forbidden = '/kostenlos|kostenfrei|gratis|minute|hofladen|\bfree\b/iu';
            $problems = [];
            $packages = self::packages();
            array_walk_recursive($packages, static function (mixed $value) use ($forbidden, &$problems): void {
                if (is_string($value) && preg_match($forbidden, $value, $m) === 1) {
                    $problems[] = $m[0];
                }
            });
            self::assertSame([], $problems);
        }
    }
}
