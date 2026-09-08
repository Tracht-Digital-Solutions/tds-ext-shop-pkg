<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Orders for TDS's own digital service packages.
 *
 * ### withdrawal_consent_text stores the WORDING, not a flag
 *
 * A digital service is fully performed the moment it is delivered, so the
 * customer's right of withdrawal only lapses if they expressly agreed to that
 * beforehand and confirmed they knew what they were giving up
 * (§ 356 Abs. 4 BGB). What has to be provable later is not "a box was ticked"
 * but **which sentence they agreed to** — and that sentence will be edited over
 * the years. A boolean would leave every past order pointing at today's
 * wording. So the exact text shown at the time is copied into the row, next to
 * the timestamp.
 *
 * ### The money is frozen into the row
 *
 * `net_cents`, `tax_cents`, `gross_cents` and `tax_rate_bp` are written at
 * purchase and never recomputed. A receipt must still show what was actually
 * charged after a price change or a VAT-rate change; recalculating from the
 * product would quietly rewrite history.
 *
 * `tax_rate_bp` is basis points (1900 = 19 %), because a percentage as a float
 * is how rounding errors get into invoices.
 *
 * ### token is the access credential
 *
 * A guest buys without an account, so the order page is reachable only by an
 * unguessable token, mailed to them and put in the Stripe success URL. Forcing
 * an account on a small digital purchase costs more in conversion than it buys
 * in convenience.
 */
final class ShopCreateOrder extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_order', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('token', 'string', ['limit' => 40])
            ->addColumn('order_no', 'string', ['limit' => 24])
            ->addColumn('email', 'string', ['limit' => 200])
            ->addColumn('name', 'string', ['limit' => 200, 'null' => true])
            // Nullable: a guest purchase has no account behind it.
            ->addColumn('user_id', 'integer', ['signed' => false, 'null' => true])
            // pending | paid | refunded | failed
            ->addColumn('status', 'string', ['limit' => 16, 'default' => 'pending'])
            ->addColumn('net_cents', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('tax_cents', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('gross_cents', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('tax_rate_bp', 'integer', ['signed' => false, 'default' => 1900])
            ->addColumn('currency', 'string', ['limit' => 3, 'default' => 'EUR'])
            // Germany only for now; the column exists so widening later is a
            // configuration change and not a migration.
            ->addColumn('country', 'string', ['limit' => 2, 'default' => 'DE'])
            ->addColumn('stripe_session_id', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('stripe_payment_intent', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('withdrawal_consent_at', 'datetime', ['null' => true])
            ->addColumn('withdrawal_consent_text', 'text', ['null' => true])
            ->addColumn('fulfilled_at', 'datetime', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['token'], ['unique' => true, 'name' => 'uniq_shop_order_token'])
            ->addIndex(['order_no'], ['unique' => true, 'name' => 'uniq_shop_order_no'])
            // The idempotency key for the webhook: Stripe retries, and a
            // duplicate delivery must not produce a second paid order.
            ->addIndex(['stripe_session_id'], ['unique' => true, 'name' => 'uniq_shop_order_session'])
            ->addIndex(['status', 'created_at'], ['name' => 'idx_shop_order_status'])
            ->create();

        $this->table('shop_order_item', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('order_id', 'integer', ['signed' => false])
            // ON DELETE SET NULL, not CASCADE: deleting a product from the
            // catalogue must not erase the record of it having been sold.
            ->addColumn('product_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('offer_id', 'integer', ['signed' => false, 'null' => true])
            // A snapshot of what was bought, so the receipt survives the
            // product being renamed, repriced or withdrawn.
            ->addColumn('title', 'string', ['limit' => 200])
            ->addColumn('slug', 'string', ['limit' => 120])
            ->addColumn('quantity', 'integer', ['signed' => false, 'default' => 1])
            ->addColumn('net_cents', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('tax_cents', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('gross_cents', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('fulfilment', 'string', ['limit' => 16, 'default' => 'manual'])
            ->addIndex(['order_id'], ['name' => 'idx_shop_order_item_order'])
            ->addForeignKey('order_id', 'shop_order', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('product_id', 'shop_product', 'id', ['delete' => 'SET_NULL'])
            ->addForeignKey('offer_id', 'shop_offer', 'id', ['delete' => 'SET_NULL'])
            ->create();
    }
}
