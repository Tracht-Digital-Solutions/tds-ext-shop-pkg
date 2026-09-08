<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/**
 * One way to take money.
 *
 * ### Why this interface exists at all
 *
 * Until it did, the shop took card payments and the fact lived in one array
 * literal: `'payment_method_types' => ['card']` in `StripeClient`. That is fine
 * for one provider and unworkable for three — the order table carried
 * Stripe-named columns, the webhook route was Stripe-named, and the client was
 * injected by concrete class. Adding PayPal on the same terms would have meant
 * a second copy of all of it, and Wero a third.
 *
 * So: three methods, and everything a provider disagrees about stays behind
 * them. The rest of the shop knows about "a provider", "a handoff" and "an
 * event", and nothing about sessions, intents, captures or approvals.
 *
 * ### The contract
 *
 * - {@see isConfigured()} is the ONLY gate. A provider with no credentials must
 *   answer false and must never be offered in the checkout. That is what lets a
 *   half-finished adapter live in the tree without endangering a live shop —
 *   see {@see WeroProvider}.
 * - {@see start()} must not mutate the order. The caller writes the reference
 *   it returns.
 * - {@see receiveWebhook()} may call the provider back. PayPal turns an
 *   approval into money only when the merchant captures, so "verify this
 *   request" and "and therefore capture it" are one step there. Naming it
 *   `receive` rather than `parse` is deliberate: a method called `parse` that
 *   moves money is a lie.
 */
interface PaymentProvider
{
    /** Stable machine id — 'stripe', 'paypal', 'wero'. Appears in URLs and rows. */
    public function id(): string;

    /** What the customer sees in the checkout. */
    public function label(): string;

    /** False when credentials are missing. An unconfigured provider is invisible. */
    public function isConfigured(): bool;

    /**
     * Begin a payment and say where to send the customer.
     *
     * @throws PaymentNotConfigured when called without credentials
     * @throws PaymentFailed        on any transport or API error
     */
    public function start(PaymentRequest $request): PaymentHandoff;

    /**
     * Verify an incoming webhook and translate it.
     *
     * Returns null for a verified event this shop does not act on — which must
     * still be answered 200, or the provider retries it forever.
     *
     * @param  string                $rawBody the exact bytes; a re-encoded
     *                                        payload will not verify
     * @param  array<string,string>  $headers lower-cased header names
     * @throws PaymentNotConfigured  when no signing secret is configured. Fail
     *                               CLOSED: an unverifiable webhook that is
     *                               accepted is a way to mark any order paid.
     * @throws WebhookNotVerified    when the signature does not check out
     */
    public function receiveWebhook(string $rawBody, array $headers): ?PaymentEvent;
}
