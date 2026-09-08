<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/**
 * Wero — the seat, not the adapter.
 *
 * ### Why this class exists in an unfinished state
 *
 * Wero is an account-to-account scheme on SEPA Instant, run by the European
 * Payments Initiative. A merchant does not integrate it directly: acceptance
 * comes through a payment service provider that supports it, and each of them
 * exposes it differently. Choosing that PSP is a contract decision, not a code
 * one, and it had not been made when the payment abstraction went in.
 *
 * The alternative to this file was to leave a hole in {@see PaymentRegistry}
 * and find out later which of the assumptions in the interface Wero breaks.
 * Writing the seat first is cheaper: the registry, the routing, the settings
 * and the checkout's method list are all exercised by a provider that answers
 * every question except the two that need the contract.
 *
 * ### What it does today
 *
 * {@see isConfigured()} returns false unless a PSP has been configured, and
 * false is what keeps it out of the checkout entirely — `/shop/payment-methods`
 * never lists it, so no customer can select something that cannot complete.
 * Both live methods refuse loudly rather than pretending.
 *
 * ### Finishing it
 *
 * See `docs/wero-adapter.md`. Three things are needed and nothing else in the
 * shop should have to change:
 *
 *  1. `start()` — create a payment at the PSP for `$r->grossCents` in
 *     `$r->currency`, carry `$r->token` as the merchant reference, and return
 *     the redirect URL plus the PSP's id for the attempt.
 *  2. `receiveWebhook()` — verify the PSP's signature over the RAW body, and
 *     map its terminal states onto {@see PaymentEvent::PAID} and
 *     {@see PaymentEvent::REFUNDED}. Fail closed with
 *     {@see PaymentNotConfigured} when the signing secret is absent.
 *  3. `isConfigured()` — extend to whatever the PSP's credential set actually
 *     is.
 *
 * One caution that is Wero's own, not the PSP's: settlement is near-instant and
 * **there is no chargeback**. A refund is a fresh transfer the merchant
 * initiates, so the refund path has to be a real implementation rather than a
 * mapping of some reversal event that will never arrive.
 */
final class WeroProvider implements PaymentProvider
{
    public function __construct(
        /** The PSP whose Wero acceptance is used. Empty until one is chosen. */
        private readonly string $psp = '',
        private readonly string $apiKey = '',
        private readonly string $webhookSecret = '',
    ) {
    }

    public function id(): string
    {
        return 'wero';
    }

    public function label(): string
    {
        return 'Wero';
    }

    /**
     * False until a PSP is configured — which is the whole safety mechanism.
     *
     * An unconfigured provider is invisible: `/shop/payment-methods` filters on
     * exactly this, so Wero cannot be offered, cannot be selected, and cannot
     * be reached by a hand-crafted request either (the checkout re-checks).
     */
    public function isConfigured(): bool
    {
        return $this->psp !== '' && $this->apiKey !== '' && $this->webhookSecret !== '';
    }

    public function start(PaymentRequest $r): PaymentHandoff
    {
        throw new PaymentNotConfigured('wero');
    }

    public function receiveWebhook(string $rawBody, array $headers): ?PaymentEvent
    {
        // Fail closed. An endpoint that accepted unverifiable webhooks would be
        // a way to mark any order paid, and "the adapter is not finished" is
        // not a reason to make that reachable.
        throw new PaymentNotConfigured('wero');
    }
}
