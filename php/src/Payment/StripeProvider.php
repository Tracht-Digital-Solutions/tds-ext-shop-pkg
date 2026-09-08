<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

use Tds\Ext\Shop\Service\StripeClient;
use Tds\Ext\Shop\Service\StripeException;
use Tds\Ext\Shop\Service\WebhookVerifier;

/**
 * Card payments, through the Checkout Session flow that was already here.
 *
 * This class is a WRAPPER and deliberately adds nothing. `StripeClient` and
 * `WebhookVerifier` are untouched, the session is created with the same
 * arguments, and the signature check is the same six-case one that
 * `CheckoutTest` already pins. That is the point: the abstraction went in
 * underneath a live payment path, and the way to keep a live payment path
 * working is to not rewrite it at the same time.
 *
 * The one behavioural change is where the identifier is written — see the
 * migration `shop_order_payment_provider`. The Stripe session id now lands in
 * `provider_session_id` as well as `stripe_session_id`, and the old column is
 * legacy.
 */
final class StripeProvider implements PaymentProvider
{
    public function __construct(
        private readonly ?StripeClient $client,
        private readonly string $webhookSecret,
    ) {
    }

    public function id(): string
    {
        return 'stripe';
    }

    public function label(): string
    {
        return 'Kredit- oder Debitkarte';
    }

    public function isConfigured(): bool
    {
        return $this->client !== null && $this->client->isConfigured();
    }

    public function start(PaymentRequest $r): PaymentHandoff
    {
        if ($this->client === null || !$this->client->isConfigured()) {
            throw new PaymentNotConfigured('stripe');
        }

        try {
            $session = $this->client->createCheckoutSession(
                $r->orderNo,
                $r->description,
                $r->grossCents,
                $r->currency,
                $r->email,
                $r->successUrl,
                $r->cancelUrl,
                $r->metadata + ['order_no' => $r->orderNo, 'token' => $r->token],
            );
        } catch (StripeException $e) {
            throw new PaymentFailed('stripe', $e->getMessage(), $e->status);
        }

        $url = $session['url'] ?? null;
        if (!is_string($url) || $url === '') {
            // A session with no URL is not something to redirect to, and the
            // failure is far more legible here than as a blank page later.
            throw new PaymentFailed('stripe', 'session carried no redirect url');
        }

        return new PaymentHandoff($url, (string) $session['id']);
    }

    public function receiveWebhook(string $rawBody, array $headers): ?PaymentEvent
    {
        if ($this->webhookSecret === '') {
            throw new PaymentNotConfigured('stripe');
        }
        if (!WebhookVerifier::verify($rawBody, $headers['stripe-signature'] ?? '', $this->webhookSecret)) {
            throw new WebhookNotVerified('stripe');
        }

        try {
            $event = json_decode($rawBody, true, 16, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            // A verified body that is not JSON cannot happen against real
            // Stripe, so this is a bad secret or a broken proxy — not an
            // event to act on either way.
            throw new WebhookNotVerified('stripe');
        }

        $type = (string) ($event['type'] ?? '');
        $object = (array) ($event['data']['object'] ?? []);

        return match ($type) {
            'checkout.session.completed' => new PaymentEvent(
                PaymentEvent::PAID,
                reference: (string) ($object['id'] ?? ''),
                paymentRef: isset($object['payment_intent']) ? (string) $object['payment_intent'] : null,
                orderToken: isset($object['metadata']['token']) ? (string) $object['metadata']['token'] : null,
            ),
            'charge.refunded' => new PaymentEvent(
                PaymentEvent::REFUNDED,
                paymentRef: (string) ($object['payment_intent'] ?? ''),
            ),
            // Everything else is verified and uninteresting. Null, not an
            // exception: the caller answers 200 or Stripe retries forever.
            default => null,
        };
    }
}
