<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Service;

/**
 * Verifies a Stripe webhook signature (the `Stripe-Signature` header) against the
 * raw request body + the endpoint's signing secret, WITHOUT the Stripe SDK.
 *
 * Stripe signs `"{t}.{payload}"` with HMAC-SHA256 under the webhook secret and
 * sends `t=<unix>,v1=<hex>[,v1=<hex>…]`. We recompute the HMAC, constant-time
 * compare against each `v1`, and reject a timestamp outside the tolerance window
 * (replay guard). Pure + static → fully unit-testable. Ported verbatim (third time in this platform) from
 * tds-ext-billing (the checkout webhook shares Stripe's signing scheme).
 *
 * @see https://stripe.com/docs/webhooks/signatures
 */
final class WebhookVerifier
{
    public static function verify(
        string $payload,
        string $sigHeader,
        string $secret,
        int $tolerance = 300,
        ?int $now = null,
    ): bool {
        if ($secret === '' || $sigHeader === '') {
            return false;
        }
        [$timestamp, $signatures] = self::parseHeader($sigHeader);
        if ($timestamp === null || $signatures === []) {
            return false;
        }
        if ($tolerance > 0) {
            $now ??= time();
            if (abs($now - $timestamp) > $tolerance) {
                return false;
            }
        }
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                return true;
            }
        }
        return false;
    }

    /** @return array{0: int|null, 1: string[]} [timestamp, v1 signatures] */
    private static function parseHeader(string $header): array
    {
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) !== 2) {
                continue;
            }
            [$key, $value] = $kv;
            if ($key === 't' && ctype_digit($value)) {
                $timestamp = (int) $value;
            } elseif ($key === 'v1' && $value !== '') {
                $signatures[] = $value;
            }
        }
        return [$timestamp, $signatures];
    }
}
