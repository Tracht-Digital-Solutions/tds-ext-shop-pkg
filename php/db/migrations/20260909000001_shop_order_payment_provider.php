<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Make the order's payment columns provider-neutral.
 *
 * `shop_order` was written when there was one provider, so it says so out loud:
 * `stripe_session_id`, `stripe_payment_intent`. With PayPal alongside — and
 * Wero behind it — those names stop describing what is in them, and the
 * uniqueness that carries the webhook's idempotency stops being right: two
 * providers can perfectly well hand out the same string.
 *
 * So: an explicit `payment_provider`, and one pair of neutral columns whose
 * uniqueness is scoped to the provider that issued the value.
 *
 * ### The old columns stay, for now
 *
 * They are backfilled FROM and then left alone. Dropping them in the same
 * migration that introduces their replacement means a rollback has nowhere to
 * put the data back, and this table records money. `OrderRepository` keeps
 * writing `stripe_session_id` on a Stripe order for the same reason — one
 * release of overlap, then a follow-up migration drops both columns once the
 * new path has run in production.
 *
 * Nothing reads the old columns after this migration.
 */
final class ShopOrderPaymentProvider extends AbstractMigration
{
    public function up(): void
    {
        $this->table('shop_order')
            // Which provider issued the reference below. Defaulted to 'stripe'
            // because every row that exists when this runs came from Stripe.
            ->addColumn('payment_provider', 'string', [
                'limit' => 16,
                'default' => 'stripe',
                'after' => 'country',
                'comment' => 'stripe | paypal | wero',
            ])
            // The provider's id for the payment ATTEMPT — Stripe's Checkout
            // Session, PayPal's order. What a webhook is matched back on.
            ->addColumn('provider_session_id', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'payment_provider',
            ])
            // The provider's id for the MONEY MOVEMENT — Stripe's payment
            // intent, PayPal's capture. What a later refund refers back to.
            ->addColumn('provider_payment_ref', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'provider_session_id',
            ])
            ->update();

        // Backfill before the unique index goes on, or a duplicate that only
        // exists mid-migration would abort it.
        $this->execute(
            'UPDATE shop_order SET'
            . " payment_provider = 'stripe',"
            . ' provider_session_id = stripe_session_id,'
            . ' provider_payment_ref = stripe_payment_intent',
        );

        $this->table('shop_order')
            // Scoped to the provider: the idempotency of markPaid() rides on
            // this, and an unscoped unique would make two providers' id spaces
            // collide by accident.
            //
            // MySQL treats NULLs as distinct in a unique index, which is
            // exactly right here — every order is created before it has a
            // reference, so a row with NULL must not block the next one.
            ->addIndex(['payment_provider', 'provider_session_id'], [
                'unique' => true,
                'name' => 'uniq_shop_order_provider_session',
            ])
            ->addIndex(['payment_provider', 'provider_payment_ref'], [
                'name' => 'idx_shop_order_provider_payment',
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('shop_order')
            ->removeIndexByName('uniq_shop_order_provider_session')
            ->removeIndexByName('idx_shop_order_provider_payment')
            ->removeColumn('provider_payment_ref')
            ->removeColumn('provider_session_id')
            ->removeColumn('payment_provider')
            ->update();
    }
}
