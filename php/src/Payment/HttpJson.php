<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/**
 * The JSON half of a provider client: one curl call, one decoded array.
 *
 * Plain ext-curl and no SDK — the extension convention across this platform,
 * and the reason `StripeClient` has never needed a dependency update. Stripe
 * takes form-encoded bodies and keeps its own poster; everyone else in this
 * namespace speaks JSON, and this is that.
 *
 * The timeouts are short on purpose. This runs inside a checkout request with a
 * customer waiting: a provider that has not answered in fifteen seconds is not
 * about to, and holding the request open only turns one stuck payment into a
 * stuck worker.
 */
final class HttpJson
{
    /**
     * @param  array<string,mixed>|string|null $body   array → JSON; string → sent verbatim
     * @param  list<string>                    $headers
     * @return array{status:int,json:array<mixed>|null,raw:string}
     * @throws PaymentFailed on a transport error (no response at all)
     */
    public static function request(
        string $provider,
        string $method,
        string $url,
        array|string|null $body = null,
        array $headers = [],
        int $timeout = 15,
    ): array {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new PaymentFailed($provider, 'curl_init failed');
        }

        $payload = is_array($body) ? json_encode($body, JSON_THROW_ON_ERROR) : $body;

        $opts = [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => $headers,
        ];
        if ($payload !== null) {
            $opts[CURLOPT_POSTFIELDS] = $payload;
        }
        curl_setopt_array($ch, $opts);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new PaymentFailed($provider, "transport: {$error}");
        }

        $decoded = json_decode((string) $raw, true);
        return [
            'status' => $status,
            'json' => is_array($decoded) ? $decoded : null,
            'raw' => (string) $raw,
        ];
    }

    /**
     * As {@see request()}, but a non-2xx or an unparseable body is an error.
     *
     * @param  array<string,mixed>|string|null $body
     * @param  list<string>                    $headers
     * @return array<mixed>
     * @throws PaymentFailed
     */
    public static function expectJson(
        string $provider,
        string $method,
        string $url,
        array|string|null $body = null,
        array $headers = [],
        int $timeout = 15,
    ): array {
        $res = self::request($provider, $method, $url, $body, $headers, $timeout);
        if ($res['status'] >= 400 || $res['json'] === null) {
            // The provider's own message where there is one — a bare status
            // code sends whoever reads the log to the wrong place.
            $message = $res['json']['message']
                ?? $res['json']['error_description']
                ?? $res['json']['error']['message']
                ?? ($res['json'] === null ? 'unparseable_response' : 'unknown');
            throw new PaymentFailed($provider, (string) $message, $res['status']);
        }
        return $res['json'];
    }
}
