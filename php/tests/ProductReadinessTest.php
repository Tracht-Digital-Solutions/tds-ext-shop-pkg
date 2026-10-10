<?php

declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Support\PairList;
use Tds\Ext\Shop\Support\ProductReadiness;

final class ProductReadinessTest extends TestCase
{
    /** @return array<string,mixed> */
    private static function complete(): array
    {
        $tr = [
            'title' => 'Website-Check mit Maßnahmenliste',
            'bodyLength' => 900,
            'bodyFormat' => 'markdown',
            'metaDescription' => str_repeat('x', 120),
            'metaTitle' => null,
        ];
        return [
            'kind' => 'digital',
            'category' => 'webauftritt',
            'categoryNamed' => true,
            'hasCover' => true,
            'ownPrice' => true,
            'freshAffiliatePrice' => false,
            'translations' => ['de' => $tr, 'en' => $tr],
        ];
    }

    /** @param list<array{code:string,message:string}> $problems */
    private static function codes(array $problems): array
    {
        return array_column($problems, 'code');
    }

    public function test_a_complete_own_product_is_ready(): void
    {
        self::assertSame([], ProductReadiness::problems(self::complete()));
    }

    public function test_a_body_in_blocks_format_counts_as_missing(): void
    {
        // The shop renders markdown only; a `blocks` body is an empty page.
        $p = self::complete();
        $p['translations']['en']['bodyFormat'] = 'blocks';
        self::assertSame(['body_en'], self::codes(ProductReadiness::problems($p)));
    }

    public function test_meta_description_bounds_are_inclusive(): void
    {
        $p = self::complete();
        $p['translations']['de']['metaDescription'] = str_repeat('ä', 80);
        $p['translations']['en']['metaDescription'] = str_repeat('a', 160);
        self::assertSame([], ProductReadiness::problems($p), 'multibyte characters count as one');

        $p['translations']['de']['metaDescription'] = str_repeat('a', 79);
        $p['translations']['en']['metaDescription'] = str_repeat('a', 161);
        self::assertSame(['meta_de', 'meta_en'], self::codes(ProductReadiness::problems($p)));
    }

    public function test_a_meta_title_rescues_a_long_heading(): void
    {
        $p = self::complete();
        $p['translations']['de']['title'] = str_repeat('Lang ', 20);
        self::assertSame(['title_de'], self::codes(ProductReadiness::problems($p)));

        $p['translations']['de']['metaTitle'] = 'Kurz und gut';
        self::assertSame([], ProductReadiness::problems($p));
    }

    public function test_missing_translation_cover_category_and_price(): void
    {
        $p = self::complete();
        unset($p['translations']['en']);
        $p['hasCover'] = false;
        $p['categoryNamed'] = false;
        $p['ownPrice'] = false;
        self::assertSame(['translation_en', 'cover', 'category', 'price'], self::codes(ProductReadiness::problems($p)));
    }

    public function test_an_affiliate_product_needs_a_fresh_partner_price_not_an_own_one(): void
    {
        $p = self::complete();
        $p['kind'] = 'affiliate';
        $p['ownPrice'] = false;
        self::assertSame(['price'], self::codes(ProductReadiness::problems($p)));
        $p['freshAffiliatePrice'] = true;
        self::assertSame([], ProductReadiness::problems($p));
    }

    public function test_pair_list_drops_half_rows_and_caps_length(): void
    {
        $raw = [['q' => ' Wie lange? ', 'a' => 'Zwei  Wochen.'], ['q' => 'Ohne Antwort'], 'kaputt', ['a' => 'Ohne Frage']];
        self::assertSame([['q' => 'Wie lange?', 'a' => 'Zwei Wochen.']], PairList::clean($raw, 'q', 'a'));

        $many = array_fill(0, 30, ['label' => 'Dauer', 'value' => '1 Tag']);
        self::assertCount(PairList::MAX_ITEMS, PairList::clean($many, 'label', 'value'));
    }

    public function test_pair_list_round_trips_and_stores_empty_as_null(): void
    {
        self::assertNull(PairList::encode([]));
        $list = [['q' => 'Gilt das für Österreich?', 'a' => 'Nein, nur für Deutschland.']];
        $stored = PairList::encode($list);
        self::assertStringContainsString('Österreich', (string) $stored, 'stored readable, not \\u-escaped');
        self::assertSame($list, PairList::decode($stored, 'q', 'a'));
        self::assertSame([], PairList::decode('{nicht json', 'q', 'a'));
    }
}
