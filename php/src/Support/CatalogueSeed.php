<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

use PDO;

/**
 * Writes the prepared catalogue (`php/db/seed/*.php`) into the shop tables.
 *
 * Called from migrations, kept here so the rules live in one place and the data
 * files stay plain arrays a test can read without a database:
 *
 * - **Everything lands as a draft.** `status = 'draft'`, `published_at = NULL`;
 *   the operator releases with "Freigeben" in the panel. `editorial_status =
 *   'published'` is set because the text IS the product's own assessment.
 * - **Idempotent.** A product is skipped when its German or English slug is
 *   taken; a category's names, intro and FAQ are only filled where empty, so
 *   nothing the operator edited is overwritten.
 * - **Prepared statements only** — never an adapter internal such as
 *   `quoteValue()`, which once stopped every module's migrations.
 * - **Own prices** go into `shop_own_product` (the single source); the offer
 *   carries the derived gross for sorting only and NO `price_checked_at`: that
 *   column claims an API fetch, and is stamped by `OfferSync::apply()` alone.
 * - **Affiliate offers** carry the ASIN and no price. The sync fills price and
 *   cover; until then the publish gate keeps the product a draft.
 */
final class CatalogueSeed
{
    public const MERCHANT = 'Tracht Digital Solutions';

    /** Directory of the data files. */
    public static function dir(): string
    {
        return dirname(__DIR__, 2) . '/db/seed';
    }

    /** @return list<array<string,mixed>> */
    public static function load(string $file): array
    {
        /** @var list<array<string,mixed>> $data */
        $data = require self::dir() . '/' . $file;
        return $data;
    }

    /** Net cents of an own product: hourly rate × hours, as on the landing page. */
    public static function netCents(array $product): int
    {
        return (int) round($product['rate_cents'] * $product['hours']);
    }

    /** Gross in cents, rounded the way `OrderRepository::price()` rounds. */
    public static function grossCents(int $netCents, int $vatRateBp = 1900): int
    {
        return $netCents + (int) round($netCents * $vatRateBp / 10000);
    }

    /**
     * Create missing categories and fill empty names, intros and FAQs.
     *
     * @param list<array<string,mixed>> $categories
     */
    public static function categories(PDO $pdo, array $categories): void
    {
        $find = $pdo->prepare('SELECT name_de, name_en, intro_de, intro_en, faq_de, faq_en FROM shop_category WHERE slug = :s');
        $insert = $pdo->prepare(
            'INSERT INTO shop_category (slug, name_de, name_en, intro_de, intro_en, faq_de, faq_en)'
            . ' VALUES (:s, :name_de, :name_en, :intro_de, :intro_en, :faq_de, :faq_en)',
        );
        $update = $pdo->prepare(
            'UPDATE shop_category SET name_de = :name_de, name_en = :name_en, intro_de = :intro_de,'
            . ' intro_en = :intro_en, faq_de = :faq_de, faq_en = :faq_en WHERE slug = :s',
        );
        foreach ($categories as $c) {
            $seed = [
                'name_de' => $c['name_de'],
                'name_en' => $c['name_en'],
                'intro_de' => $c['intro_de'],
                'intro_en' => $c['intro_en'],
                'faq_de' => PairList::encode(PairList::clean($c['faq_de'], 'q', 'a')),
                'faq_en' => PairList::encode(PairList::clean($c['faq_en'], 'q', 'a')),
            ];
            $find->execute(['s' => $c['slug']]);
            $current = $find->fetch(PDO::FETCH_ASSOC);
            if ($current === false) {
                $insert->execute(['s' => $c['slug']] + $seed);
                continue;
            }
            // Fill only what is empty: a name or intro written in the panel stays.
            $merged = [];
            foreach ($seed as $col => $value) {
                $merged[$col] = ($current[$col] ?? null) !== null && $current[$col] !== '' ? $current[$col] : $value;
            }
            $update->execute(['s' => $c['slug']] + $merged);
        }
    }

    /**
     * Insert products that do not exist yet. Returns the number written.
     *
     * @param list<array<string,mixed>> $products
     */
    public static function products(PDO $pdo, array $products): int
    {
        $slugTaken = $pdo->prepare('SELECT 1 FROM shop_product_translation WHERE lang = :l AND slug = :s LIMIT 1');
        $product = $pdo->prepare(
            'INSERT INTO shop_product (kind, status, editorial_status, category, tags, brand, sort_price_cents, published_at)'
            . " VALUES (:kind, 'draft', 'published', :c, :t, :brand, :sort, NULL)",
        );
        $translation = $pdo->prepare(
            'INSERT INTO shop_product_translation'
            . ' (product_id, lang, slug, title, teaser, body, body_format, meta_description, meta_title, summary, facts, faq, machine_translated)'
            . " VALUES (:p, :l, :s, :t, :teaser, :body, 'markdown', :m, :mt, :summary, :facts, :faq, 0)",
        );
        $ownOffer = $pdo->prepare(
            'INSERT INTO shop_offer (product_id, kind, network, merchant, url, price_cents, currency, price_checked_at, availability, position)'
            . " VALUES (:p, 'own', 'direct', :merchant, '', :price, 'EUR', NULL, 'in_stock', 0)",
        );
        $terms = $pdo->prepare(
            'INSERT INTO shop_own_product (offer_id, net_cents, vat_rate_bp, fulfilment, requires_shipping)'
            . ' VALUES (:o, :net, :vat, :f, 0)',
        );
        $affiliateOffer = $pdo->prepare(
            'INSERT INTO shop_offer (product_id, kind, network, external_id, merchant, url, price_cents, currency, price_checked_at, availability, position)'
            . " VALUES (:p, 'affiliate', 'amazon', :asin, 'Amazon', :url, NULL, 'EUR', NULL, 'unknown', 0)",
        );
        $cover = $pdo->prepare(
            "INSERT INTO shop_media (product_id, role, source, url, alt, sort) VALUES (:p, 'cover', 'remote', :u, :a, 0)",
        );

        $written = 0;
        foreach ($products as $item) {
            foreach (['de', 'en'] as $lang) {
                $slugTaken->execute(['l' => $lang, 's' => $item[$lang]['slug']]);
                if ($slugTaken->fetch() !== false) {
                    continue 2;
                }
            }

            $own = ($item['kind'] ?? 'digital') === 'digital';
            $net = $own ? self::netCents($item) : 0;
            $gross = $own ? self::grossCents($net) : null;

            // Phinx already wraps each migration in a transaction; nesting one
            // throws "There is already an active transaction" and stops every
            // module's migrations. Own one only when called outside a migration.
            $owned = !$pdo->inTransaction() && $pdo->beginTransaction();
            try {
                $product->execute([
                    'kind' => $own ? 'digital' : 'affiliate',
                    'c' => $item['category'],
                    't' => $item['tags'],
                    'brand' => $item['brand'] ?? null,
                    'sort' => $gross,
                ]);
                $productId = (int) $pdo->lastInsertId();

                foreach (['de', 'en'] as $lang) {
                    $text = $item[$lang];
                    $translation->execute([
                        'p' => $productId,
                        'l' => $lang,
                        's' => $text['slug'],
                        't' => $text['title'],
                        'teaser' => $text['teaser'],
                        'body' => $text['body'],
                        'm' => $text['meta'],
                        'mt' => $text['meta_title'] ?? null,
                        'summary' => $text['summary'],
                        'facts' => PairList::encode(PairList::clean($text['facts'], 'label', 'value')),
                        'faq' => PairList::encode(PairList::clean($text['faq'], 'q', 'a')),
                    ]);
                }

                if ($own) {
                    $ownOffer->execute(['p' => $productId, 'merchant' => self::MERCHANT, 'price' => $gross]);
                    $terms->execute([
                        'o' => (int) $pdo->lastInsertId(),
                        'net' => $net,
                        'vat' => 1900,
                        'f' => $item['fulfilment'] ?? 'project',
                    ]);
                } else {
                    $affiliateOffer->execute([
                        'p' => $productId,
                        'asin' => $item['asin'],
                        // The plain product page; the partner tag is added when
                        // the sync writes Amazon's own DetailPageURL.
                        'url' => 'https://www.amazon.de/dp/' . $item['asin'],
                    ]);
                }

                if (isset($item['image'])) {
                    $cover->execute(['p' => $productId, 'u' => $item['image'], 'a' => $item['alt'] ?? $item['de']['title']]);
                }
                if ($owned) {
                    $pdo->commit();
                }
                $written++;
            } catch (\Throwable $e) {
                if ($owned) {
                    $pdo->rollBack();
                }
                throw $e;
            }
        }
        return $written;
    }

    /**
     * Fill "Kurz gesagt", facts and FAQ on existing translations, by language
     * and slug — only where each field is still empty.
     *
     * @param array<string, array<string, array{summary: string, facts: list<array<string,string>>, faq: list<array<string,string>>}>> $answers
     */
    public static function answers(PDO $pdo, array $answers): void
    {
        $update = $pdo->prepare(
            'UPDATE shop_product_translation SET'
            . " summary = CASE WHEN summary IS NULL OR summary = '' THEN :summary ELSE summary END,"
            . ' facts = COALESCE(facts, :facts), faq = COALESCE(faq, :faq)'
            . ' WHERE lang = :l AND slug = :s',
        );
        foreach ($answers as $lang => $bySlug) {
            foreach ($bySlug as $slug => $a) {
                $update->execute([
                    'summary' => $a['summary'],
                    'facts' => PairList::encode(PairList::clean($a['facts'], 'label', 'value')),
                    'faq' => PairList::encode(PairList::clean($a['faq'], 'q', 'a')),
                    'l' => $lang,
                    's' => $slug,
                ]);
            }
        }
    }

    /**
     * Correct the VAT wording of the September packages: the shop shows the
     * GROSS price, so "zuzüglich 19 %" next to it reads as 19 % on top. Only the
     * exact seeded phrase is replaced; a hand-edited body is left alone.
     *
     * @param list<string> $slugs
     */
    public static function grossWording(PDO $pdo, array $slugs): void
    {
        $fix = $pdo->prepare(
            'UPDATE shop_product_translation SET body = REPLACE(REPLACE(body,'
            . " 'Festpreis zuzüglich 19 % Umsatzsteuer', 'Festpreis inklusive 19 % Umsatzsteuer'),"
            . " 'fixed price plus 19 % VAT', 'fixed price including 19 % VAT')"
            . ' WHERE slug = :s',
        );
        foreach ($slugs as $slug) {
            $fix->execute(['s' => $slug]);
        }
    }

    /**
     * Remove seeded products whose German title and body are unchanged.
     * CASCADE takes translations, offers, terms and cover.
     *
     * @param list<array<string,mixed>> $products
     */
    public static function removeProducts(PDO $pdo, array $products): void
    {
        $find = $pdo->prepare(
            "SELECT product_id FROM shop_product_translation WHERE lang = 'de' AND slug = :s AND title = :t AND body = :b LIMIT 1",
        );
        $delete = $pdo->prepare('DELETE FROM shop_product WHERE id = :id');
        foreach ($products as $item) {
            $find->execute(['s' => $item['de']['slug'], 't' => $item['de']['title'], 'b' => $item['de']['body']]);
            $id = $find->fetchColumn();
            if ($id !== false && $id !== null) {
                $delete->execute(['id' => (int) $id]);
            }
        }
    }
}
