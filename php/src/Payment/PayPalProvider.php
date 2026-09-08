<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/**
 * PayPal, through Orders v2, directly rather than through another PSP.
 *
 * ### The shape of the flow, and how it differs from Stripe's
 *
 * With Stripe, `checkout.session.completed` means the money moved. With PayPal
 * it does not: `intent: CAPTURE` creates an order the buyer APPROVES, and the
 * money only moves when the merchant then captures it. Approval without capture
 * is a promise that expires.
 *
 * So this adapter does the capture itself, on the `CHECKOUT.ORDER.APPROVED`
 * webhook, and reports "paid" only on `PAYMENT.CAPTURE.COMPLETED`. Capturing on
 * the webhook rather than when the buyer returns to the success page is the
 * whole reason it is done this way: a buyer who approves and then closes the
 * tab has still paid, and a shop that only captures on the return page would
 * quietly lose that order. It is also why {@see PaymentProvider::receiveWebhook()}
 * is allowed to call the provider back.
 *
 * ### Identifying the order
 *
 * `custom_id` on the purchase unit carries OUR order token, and PayPal echoes
 * it on both the approval and the capture. The PayPal order id is stored too,
 * but the token is what the match runs on: it is the identifier we control, and
 * the capture event only carries the order id under
 * `supplementary_data.related_ids` — a field documented as supplementary and
 * therefore not something to build a payment state machine on.
 *
 * ### Verifying the webhook
 *
 * Through PayPal's own `/v1/notifications/verify-webhook-signature`, which is
 * the documented route and needs no certificate handling. Its one wart is that
 * the call takes the event as PARSED JSON, so the bytes are decoded and
 * re-encoded on the way — unlike Stripe's HMAC, which is why that one insists
 * on the raw body. If PayPal ever changes its canonicalisation this breaks
 * loudly (every event 400s) rather than silently, which is the right direction
 * for a failure of this kind to point.
 */
final class PayPalProvider implements PaymentProvider
{
    public const LIVE = 'https://api-m.paypal.com';
    public const SANDBOX = 'https://api-m.sandbox.paypal.com';

    private ?string $token = null;
    private int $tokenExpiresAt = 0;

    public function __construct(
        private readonly string $clientId,
        private readonly string $secret,
        private readonly string $webhookId,
        private readonly string $baseUrl = self::LIVE,
    ) {
    }

    public function id(): string
    {
        return 'paypal';
    }

    public function label(): string
    {
        return 'PayPal';
    }

    /**
     * The webhook id counts as a credential here.
     *
     * Without it an incoming event cannot be verified, and a provider that can
     * start a payment it can never confirm is worse than one that is simply
     * absent from the checkout — the customer would pay and the order would sit
     * at `pending` forever.
     */
    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->secret !== '' && $this->webhookId !== '';
    }

    public function start(PaymentRequest $r): PaymentHandoff
    {
        if (!$this->isConfigured()) {
            throw new PaymentNotConfigured('paypal');
        }

        $order = HttpJson::expectJson('paypal', 'POST', "{$this->baseUrl}/v2/checkout/orders", [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $r->orderNo,
                // On the buyer's PayPal statement.
                'description' => mb_substr($r->description, 0, 127),
                'invoice_id' => $r->orderNo,
                // Our token. The field the webhook match runs on.
                'custom_id' => $r->token,
                'amount' => [
                    'currency_code' => strtoupper($r->currency),
                    'value' => $r->amountDecimal(),
                ],
            ]],
            'payment_source' => [
                'paypal' => [
                    'email_address' => $r->email,
                    'experience_context' => [
                        // "Jetzt bezahlen" rather than "Weiter": the order was
                        // already placed on our page, under a button reading
                        // "Zahlungspflichtig bestellen" (§ 312j Abs. 3 BGB).
                        // A second "continue" step here would suggest the
                        // commitment happens at PayPal, which it does not.
                        'user_action' => 'PAY_NOW',
                        'shipping_preference' => 'NO_SHIPPING',
                        'return_url' => $r->successUrl,
                        'cancel_url' => $r->cancelUrl,
                    ],
                ],
            ],
        ], $this->authHeaders());

        $id = (string) ($order['id'] ?? '');
        $url = $this->approvalLink($order);
        if ($id === '' || $url === null) {
            throw new PaymentFailed('paypal', 'order carried no approval link');
        }

        return new PaymentHandoff($url, $id);
    }

    public function receiveWebhook(string $rawBody, array $headers): ?PaymentEvent
    {
        if (!$this->isConfigured()) {
            throw new PaymentNotConfigured('paypal');
        }

        try {
            $event = json_decode($rawBody, true, 32, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            throw new WebhookNotVerified('paypal');
        }
        if (!is_array($event) || !$this->verify($event, $headers)) {
            throw new WebhookNotVerified('paypal');
        }

        $type = (string) ($event['event_type'] ?? '');
        $resource = (array) ($event['resource'] ?? []);

        switch ($type) {
            case 'CHECKOUT.ORDER.APPROVED':
                // Approved is not paid. Capture, and let the resulting
                // PAYMENT.CAPTURE.COMPLETED be what marks the order — so there
                // is exactly one code path that says "paid", whether the
                // capture happened here or was retried by PayPal.
                $this->capture((string) ($resource['id'] ?? ''));
                return null;

            case 'PAYMENT.CAPTURE.COMPLETED':
                return new PaymentEvent(
                    PaymentEvent::PAID,
                    reference: $this->relatedOrderId($resource),
                    paymentRef: (string) ($resource['id'] ?? ''),
                    orderToken: isset($resource['custom_id']) ? (string) $resource['custom_id'] : null,
                );

            case 'PAYMENT.CAPTURE.REFUNDED':
            case 'PAYMENT.CAPTURE.REVERSED':
                return new PaymentEvent(
                    PaymentEvent::REFUNDED,
                    // For a refund the capture is the thing being reversed, and
                    // PayPal puts it in the up-link rather than in `id` (which
                    // is the refund's own id).
                    paymentRef: $this->capturedFrom($resource),
                    orderToken: isset($resource['custom_id']) ? (string) $resource['custom_id'] : null,
                );

            default:
                return null;
        }
    }

    /* --- internals ------------------------------------------------------- */

    /**
     * Capture an approved order.
     *
     * A 422 `ORDER_ALREADY_CAPTURED` is a SUCCESS as far as this is concerned:
     * PayPal redelivers `CHECKOUT.ORDER.APPROVED` until it gets a 2xx, so the
     * second delivery necessarily finds the order captured, and treating that
     * as an error would keep the retry loop alive forever.
     */
    private function capture(string $orderId): void
    {
        if ($orderId === '') {
            return;
        }
        $res = HttpJson::request(
            'paypal',
            'POST',
            "{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture",
            '{}',
            $this->authHeaders(),
        );
        if ($res['status'] < 400) {
            return;
        }
        $issue = (string) ($res['json']['details'][0]['issue'] ?? '');
        if ($issue === 'ORDER_ALREADY_CAPTURED') {
            return;
        }
        throw new PaymentFailed('paypal', "capture failed: {$issue}", $res['status']);
    }

    /** @param array<string,mixed> $event */
    private function verify(array $event, array $headers): bool
    {
        $required = [
            'transmission_id' => 'paypal-transmission-id',
            'transmission_time' => 'paypal-transmission-time',
            'cert_url' => 'paypal-cert-url',
            'auth_algo' => 'paypal-auth-algo',
            'transmission_sig' => 'paypal-transmission-sig',
        ];
        $payload = ['webhook_id' => $this->webhookId, 'webhook_event' => $event];
        foreach ($required as $field => $header) {
            $value = $headers[$header] ?? '';
            if ($value === '') {
                return false;
            }
            $payload[$field] = $value;
        }

        $res = HttpJson::request(
            'paypal',
            'POST',
            "{$this->baseUrl}/v1/notifications/verify-webhook-signature",
            $payload,
            $this->authHeaders(),
        );
        return $res['status'] < 400
            && ($res['json']['verification_status'] ?? '') === 'SUCCESS';
    }

    /**
     * An OAuth2 access token, cached for the life of the process.
     *
     * PayPal's tokens last hours; re-minting one per webhook would double the
     * round trips on every event for nothing. Sixty seconds are shaved off the
     * expiry so a token cannot go stale between the check and the call.
     *
     * @return list<string>
     */
    private function authHeaders(): array
    {
        if ($this->token === null || time() >= $this->tokenExpiresAt) {
            $res = HttpJson::expectJson(
                'paypal',
                'POST',
                "{$this->baseUrl}/v1/oauth2/token",
                'grant_type=client_credentials',
                [
                    'Authorization: Basic ' . base64_encode("{$this->clientId}:{$this->secret}"),
                    'Content-Type: application/x-www-form-urlencoded',
                ],
            );
            $this->token = (string) ($res['access_token'] ?? '');
            $this->tokenExpiresAt = time() + max(60, (int) ($res['expires_in'] ?? 300)) - 60;
            if ($this->token === '') {
                throw new PaymentFailed('paypal', 'no access token in token response');
            }
        }

        return [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
        ];
    }

    /** @param array<string,mixed> $order */
    private function approvalLink(array $order): ?string
    {
        foreach ((array) ($order['links'] ?? []) as $link) {
            // `payer-action` is what the v2 flow returns when a payment_source
            // is supplied; `approve` is the older name and still appears
            // without one. Accept either rather than guessing which shape the
            // account is on.
            $rel = (string) ($link['rel'] ?? '');
            if (($rel === 'payer-action' || $rel === 'approve') && isset($link['href'])) {
                return (string) $link['href'];
            }
        }
        return null;
    }

    /** @param array<string,mixed> $resource */
    private function relatedOrderId(array $resource): ?string
    {
        $id = $resource['supplementary_data']['related_ids']['order_id'] ?? null;
        return is_string($id) && $id !== '' ? $id : null;
    }

    /** @param array<string,mixed> $resource */
    private function capturedFrom(array $resource): ?string
    {
        foreach ((array) ($resource['links'] ?? []) as $link) {
            if (($link['rel'] ?? '') === 'up' && isset($link['href'])) {
                $parts = explode('/', rtrim((string) $link['href'], '/'));
                $last = end($parts);
                return is_string($last) && $last !== '' ? $last : null;
            }
        }
        return null;
    }
}
