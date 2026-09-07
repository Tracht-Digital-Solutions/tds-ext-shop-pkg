<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Advertising slots, and the manual pinning that fills them.
 *
 * All THREE placement mechanisms the shop supports resolve through this one
 * table, and that is the point: a slot is either filled by hand (`strategy =
 * manual`, rows in `shop_placement_item`) or matched automatically against a
 * category or tag (`category`/`tag`/`auto`, criteria in `selector`). The
 * consumer — journal sidebar, article end, portal dashboard — asks for a key
 * and renders what comes back. It never learns which strategy answered, so the
 * editorial decision stays in the panel instead of being re-implemented in
 * three frontends.
 *
 * `surface` exists so the panel can show an operator where a slot actually
 * appears; nothing enforces it at read time, because a key is already unique.
 */
final class ShopCreatePlacement extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_placement', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('key', 'string', ['limit' => 60])
            ->addColumn('label', 'string', ['limit' => 120, 'default' => ''])
            // Where it renders: 'blog' | 'panel' | 'shop'. Documentation for the
            // operator, not a filter.
            ->addColumn('surface', 'string', ['limit' => 16, 'default' => 'blog'])
            // 'manual' | 'category' | 'tag' | 'auto'
            ->addColumn('strategy', 'string', ['limit' => 16, 'default' => 'auto'])
            ->addColumn('selector', 'text', ['null' => true])
            ->addColumn('heading_de', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('heading_en', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('max_items', 'integer', ['signed' => false, 'default' => 3])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['key'], ['unique' => true, 'name' => 'uniq_shop_placement_key'])
            ->create();

        $this->table('shop_placement_item', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('placement_id', 'integer', ['signed' => false])
            ->addColumn('product_id', 'integer', ['signed' => false])
            ->addColumn('position', 'integer', ['signed' => false, 'default' => 0])
            ->addIndex(['placement_id', 'position'], ['name' => 'idx_shop_placement_item'])
            ->addIndex(['placement_id', 'product_id'], ['unique' => true, 'name' => 'uniq_shop_placement_item'])
            ->addForeignKey('placement_id', 'shop_placement', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('product_id', 'shop_product', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
