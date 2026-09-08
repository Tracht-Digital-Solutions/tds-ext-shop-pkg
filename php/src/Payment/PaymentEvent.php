<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/**
 * A verified webhook, reduced to the only two things this shop acts on.
 *
 * Providers send dozens of event types and disagree about all of them. What an
 * order actually needs to know is "it is paid" or "it was refunded" — so the
 * translation happens in the provider, where the vocabulary is, and everything
 * downstream sees these two.
 *
 * ### Why there are two ways to identify the order
 *
 * `reference` is the provider's id for the payment attempt, and it is the
 * primary key of the match. But PayPal's capture events do not carry the order
 * id at the top level — it hides under `supplementary_data.related_ids`, which
 * is documented as *supplementary* and has moved before. What PayPal DOES echo
 * reliably is `custom_id`, which we set to our own order token.
 *
 * So a provider fills in whichever it can prove, and the repository matches on
 * the token when there is one. Preferring our own identifier over a foreign
 * one is the right default anyway: it is the field we control.
 */
final class PaymentEvent
{
    public const PAID = 'paid';
    public const REFUNDED = 'refunded';

    /**
     * @param string  $kind      self::PAID | self::REFUNDED
     * @param ?string $reference the provider's id for the payment attempt
     * @param ?string $paymentRef the provider's id for the money movement
     *                            (Stripe payment intent, PayPal capture) —
     *                            what a later refund event refers back to
     * @param ?string $orderToken our own order token, when the provider echoed it
     */
    public function __construct(
        public readonly string $kind,
        public readonly ?string $reference = null,
        public readonly ?string $paymentRef = null,
        public readonly ?string $orderToken = null,
    ) {
    }
}
