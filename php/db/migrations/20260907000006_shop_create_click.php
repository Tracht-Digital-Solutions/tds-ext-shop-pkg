<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Affiliate click counter — aggregated per day, and that is a privacy decision
 * as much as a storage one.
 *
 * A row per click would grow without bound to answer a question ("what is
 * working?") that a daily total answers just as well, and it would be a
 * processing of visitor behaviour that this way simply never comes into
 * existence. No IP, no cookie, no user id, no timestamp finer than a date —
 * so there is nothing here to consent to and nothing to erase on request.
 *
 * The unique index over every dimension makes the write an idempotent UPSERT
 * (`ON DUPLICATE KEY UPDATE clicks = clicks + 1`), which is what lets the
 * counter be fire-and-forget: a lost response costs one click, never a
 * duplicate row.
 */
final class ShopCreateClick extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_click', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('day', 'date')
            ->addColumn('offer_id', 'integer', ['signed' => false])
            ->addColumn('product_id', 'integer', ['signed' => false])
            // Which property the click came from: 'shop' | 'blog' | 'customer'.
            ->addColumn('source', 'string', ['limit' => 16, 'default' => 'shop'])
            ->addColumn('placement_key', 'string', ['limit' => 60, 'null' => true])
            ->addColumn('lang', 'string', ['limit' => 2, 'default' => 'de'])
            ->addColumn('clicks', 'integer', ['signed' => false, 'default' => 0])
            ->addIndex(
                ['day', 'offer_id', 'source', 'placement_key', 'lang'],
                ['unique' => true, 'name' => 'uniq_shop_click_day'],
            )
            ->addIndex(['product_id', 'day'], ['name' => 'idx_shop_click_product'])
            ->addForeignKey('offer_id', 'shop_offer', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('product_id', 'shop_product', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
