<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * What TDS sells itself, hanging off the offer rather than the product.
 *
 * An offer already answers "how is this bought"; a product may carry several.
 * Putting the sale terms here rather than on `shop_product` means a product can
 * be both — our setup package *and* the hardware for it at Amazon — without
 * either half knowing about the other.
 *
 * `net_cents` + `vat_rate_bp` rather than a gross price: the gross is derived
 * once, at purchase, and frozen into the order. Storing gross and deriving net
 * loses a cent to rounding on every third amount, and it is the net that a VAT
 * return is built from.
 *
 * `fulfilment` says what happens after payment. These are **services**, not
 * downloads — there is no file to deliver, so nothing here needs a storage
 * path, a signed URL or a download counter. That is why this table is four
 * columns instead of a subsystem.
 */
final class ShopAddOwnProductSales extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_own_product', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('offer_id', 'integer', ['signed' => false])
            ->addColumn('net_cents', 'integer', ['signed' => false, 'default' => 0])
            // Basis points. 1900 = 19 %. A percentage as a float is how
            // rounding errors get into invoices.
            ->addColumn('vat_rate_bp', 'integer', ['signed' => false, 'default' => 1900])
            // ticket | project | manual — what the paid order turns into.
            ->addColumn('fulfilment', 'string', ['limit' => 16, 'default' => 'manual'])
            ->addColumn('delivery_note', 'text', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['offer_id'], ['unique' => true, 'name' => 'uniq_shop_own_offer'])
            ->addForeignKey('offer_id', 'shop_offer', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
