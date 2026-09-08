<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Service;

/**
 * Thin Stripe client (plain ext-curl, no SDK — the extension convention).
 *
 * Covers the one flow this shop needs: a Checkout Session for a single digital
 * service package, one-time payment. Modelled on the same client in
 * `tds-ext-tools-pkg`, which does the same thing for the premium-tool paywall.
 *
 * ### Why Checkout Session and not Payment Element
 *
 * SCA/3DS, the payment-method picker, address collection and the whole error
 * surface come with it, and the PCI scope stays at SAQ A. A Payment Element
 * would mean our own PaymentIntents, our own 3DS failure paths and our own
 * address UI — a lot of code for a handful of digital packages.
 *
 * ### But the ORDER BUTTON stays on our page
 *
 * § 312j Abs. 3 BGB requires a button reading "Zahlungspflichtig bestellen"
 * with the mandatory details immediately above it. Stripe's hosted button says
 * "Bezahlen". So the compliant order is: our `/kasse` page carries the details,
 * the withdrawal confirmation and the correctly-labelled button; pressing it
 * creates the session, and Stripe is only the payment step that follows.
 * Sending a visitor straight to Stripe would skip the declaration entirely.
 *
 * The live call cannot be exercised without a Stripe account; the signed
 * webhook path ({@see WebhookVerifier}) is the unit-tested half.
 */
final class StripeClient
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $baseUrl = 'https://api.stripe.com/v1',
    ) {
    }

    /** False when no secret key is configured — checkout is then disabled (503). */
    public function isConfigured(): bool
    {
        return $this->secretKey !== '';
    }

    /**
     * Create a one-time-payment Checkout Session.
     *
     * `$grossCents` is the total the customer pays, and it is computed on the
     * server from the stored net price — never taken from the request. A posted
     * price is a price the customer chose.
     *
     * @param  array<string,string> $metadata carried through to the webhook
     * @return array{id:string,url:?string}
     * @throws StripeException
     */
    public function createCheckoutSession(
        string $orderNo,
        string $productName,
        int $grossCents,
        string $currency,
        string $email,
        string $successUrl,
        string $cancelUrl,
        array $metadata = [],
    ): array {
        $session = $this->post('/checkout/sessions', [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $orderNo,
            'customer_email' => $email,
            // Collected because a receipt needs it. The country RESTRICTION is
            // not enforced here — it is checked on our own page before this
            // call, where a rejection can be explained. Selling an
            // electronically supplied service to a consumer elsewhere in the EU
            // moves the place of supply to their country (§ 3a Abs. 5 UStG) and
            // eventually means OSS registration, so the shop must not acquire
            // that obligation by accident.
            'billing_address_collection' => 'required',
            'payment_method_types' => ['card'],
            'metadata' => $metadata,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($currency),
                    'unit_amount' => $grossCents,
                    'product_data' => ['name' => $productName],
                ],
            ]],
        ]);

        return [
            'id' => (string) ($session['id'] ?? ''),
            'url' => isset($session['url']) ? (string) $session['url'] : null,
        ];
    }

    /**
     * @param  array<string,mixed> $payload
     * @return array<string,mixed>
     * @throws StripeException
     */
    private function post(string $path, array $payload): array
    {
        // Stripe takes form-encoded bodies with bracket notation for nesting,
        // not JSON. `http_build_query` produces exactly that shape.
        $body = http_build_query($this->flatten($payload));

        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->secretKey,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new StripeException("transport: {$error}", 0);
        }
        $decoded = json_decode((string) $raw, true);
        if ($status >= 400 || !is_array($decoded)) {
            $message = is_array($decoded)
                ? (string) ($decoded['error']['message'] ?? 'unknown')
                : 'unparseable_response';
            throw new StripeException($message, $status);
        }
        return $decoded;
    }

    /**
     * Flatten nested arrays into Stripe's bracket notation.
     *
     * @param  array<string,mixed> $data
     * @return array<string,scalar>
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }
            $name = $prefix === '' ? (string) $key : "{$prefix}[{$key}]";
            if (is_array($value)) {
                $out += $this->flatten($value, $name);
                continue;
            }
            $out[$name] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }
        return $out;
    }
}
