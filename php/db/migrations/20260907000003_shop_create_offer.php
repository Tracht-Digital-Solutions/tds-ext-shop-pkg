<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * One way to buy a product. A product may carry several — "our setup package"
 * alongside "the hardware for it at Amazon" — which is why this is a child
 * table rather than columns on the product.
 *
 * ### price_checked_at is not bookkeeping
 *
 * It is the column the Amazon Product Advertising API licence turns on: a
 * displayed price must come from the API, carry its retrieval time, and
 * disappear after 24 hours. `price_cents` without `price_checked_at` may never
 * be rendered, and the public API refuses to emit one past the deadline
 * (`Support\PriceFreshness`), independently of the frontend's own check. Two
 * unrelated layers enforce it because forgetting costs the partner programme
 * and looks like nothing at all — yesterday's price renders perfectly.
 *
 * There is deliberately NO price-history table. The same licence forbids
 * displaying historical prices, and a table that exists gets rendered
 * eventually. `raw` keeps the last API response for diagnosis only.
 *
 * `unique (network, external_id)` stops the same ASIN being imported twice
 * under two products, which would then drift to two different prices.
 */
final class ShopCreateOffer extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_offer', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('product_id', 'integer', ['signed' => false])
            // 'own' | 'affiliate' — mirrors SHOP_OFFER_KINDS in tds-shared.
            ->addColumn('kind', 'string', ['limit' => 16, 'default' => 'affiliate'])
            // 'amazon' | 'awin' | 'belboon' | 'digistore' | 'direct'
            ->addColumn('network', 'string', ['limit' => 20, 'default' => 'direct'])
            ->addColumn('external_id', 'string', ['limit' => 64, 'null' => true])
            ->addColumn('merchant', 'string', ['limit' => 120, 'default' => ''])
            ->addColumn('url', 'text')
            ->addColumn('price_cents', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('currency', 'string', ['limit' => 3, 'default' => 'EUR'])
            ->addColumn('price_checked_at', 'datetime', ['null' => true])
            ->addColumn('availability', 'string', ['limit' => 16, 'default' => 'unknown'])
            ->addColumn('position', 'integer', ['signed' => false, 'default' => 0])
            // Set when a network stops answering for this item; the offer keeps
            // its link (a revoked API does not revoke the partner programme).
            ->addColumn('disabled_at', 'datetime', ['null' => true])
            ->addColumn('raw', 'text', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['product_id', 'position'], ['name' => 'idx_shop_offer_product'])
            ->addIndex(['network', 'external_id'], ['unique' => true, 'name' => 'uniq_shop_offer_external'])
            ->addIndex(['price_checked_at'], ['name' => 'idx_shop_offer_checked'])
            ->addForeignKey('product_id', 'shop_product', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
