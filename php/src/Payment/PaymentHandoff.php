<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/** Where to send the customer, and what the provider is calling this payment. */
final class PaymentHandoff
{
    /**
     * @param string $redirectUrl the provider's hosted page
     * @param string $reference   the provider's id for this attempt (Stripe's
     *                            Checkout Session id, PayPal's order id). Stored
     *                            on the order so an incoming webhook can be tied
     *                            back to it.
     */
    public function __construct(
        public readonly string $redirectUrl,
        public readonly string $reference,
    ) {
    }
}
