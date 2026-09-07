<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * TDShop catalogue — the LANGUAGE-NEUTRAL half of a product.
 *
 * ### Why this is split from its translations, unlike blog_post
 *
 * The blog stores DE and EN as separate rows sharing a slug, because an article
 * has no language-neutral core: title, excerpt and body are each written whole
 * in one language. A product does have one — the ASIN, the network, the price,
 * the availability, the retrieval timestamp. Duplicating that per language and
 * then pointing a price sync at it means the German row can say 249 € while the
 * English row says 259 € because one sync tick was interrupted. A price that
 * differs by language is exactly the failure the 24-hour rule exists to prevent,
 * so the core lives here once and `shop_product_translation` carries the prose.
 *
 * ### editorial_status is a publishing gate, not a label
 *
 * Only `published` — meaning somebody wrote a genuine assessment, not a
 * rephrased manufacturer blurb — reaches the sitemap and gets `<meta robots
 * index>`. `stub` and `none` render and are reachable but stay out of the
 * index. That is what stops a bulk import of 300 ASINs from turning the domain
 * into the thin-affiliate pattern search engines demote. It is deliberately a
 * separate axis from `status`: a product can be perfectly published and on sale
 * while its write-up is still a stub.
 *
 * Module-prefixed class name and a migration band (`20260907*`) no other module
 * claims — every enabled module's migrations run in one process against one
 * phinxlog, and a filename/classname mismatch aborts migrations for ALL of them.
 */
final class ShopCreateProduct extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_product', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            // `affiliate` = somebody else sells it; `digital` = we do.
            ->addColumn('kind', 'string', ['limit' => 16, 'default' => 'affiliate'])
            ->addColumn('status', 'string', ['limit' => 16, 'default' => 'draft'])
            ->addColumn('editorial_status', 'string', ['limit' => 16, 'default' => 'none'])
            ->addColumn('category', 'string', ['limit' => 60, 'default' => 'allgemein'])
            ->addColumn('tags', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('brand', 'string', ['limit' => 120, 'null' => true])
            // Denormalised from the primary offer purely so a catalogue page can
            // sort by price without joining and aggregating every offer row.
            // Never rendered — a rendered price must go through the freshness
            // check, and this column carries no timestamp.
            ->addColumn('sort_price_cents', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('published_at', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['status', 'published_at'], ['name' => 'idx_shop_product_published'])
            ->addIndex(['category'], ['name' => 'idx_shop_product_category'])
            ->create();
    }
}
