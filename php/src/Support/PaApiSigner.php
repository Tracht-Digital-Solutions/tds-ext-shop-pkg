<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

/**
 * AWS Signature Version 4, for the Amazon Product Advertising API.
 *
 * Hand-rolled against `ext-hash`, no SDK — the extension convention here is
 * plain curl and no vendor tree for a single third-party call.
 *
 * ### Why this is a separate, pure class
 *
 * It is the only part of the Amazon integration that can be verified without
 * an Amazon account: given a frozen timestamp, a known key and a known body,
 * the signature is a fixed string. Everything else in the client is network.
 * So the signing lives here, takes its clock as an argument, and is tested
 * directly — while `AmazonPaApiClient` stays a thin transport wrapper around
 * it.
 *
 * The four steps are AWS's, in order: canonical request, string to sign,
 * signing key, authorization header. Each is its own method so a mismatch can
 * be located rather than guessed at — a wrong signature otherwise surfaces
 * only as `IncompleteSignatureException` from a server that will not say which
 * part it disagreed with.
 */
final class PaApiSigner
{
    public const ALGORITHM = 'AWS4-HMAC-SHA256';
    public const SERVICE = 'ProductAdvertisingAPI';

    public function __construct(
        private readonly string $accessKey,
        private readonly string $secretKey,
        private readonly string $region,
        private readonly string $host,
    ) {
    }

    /**
     * The headers a signed request must carry, including `Authorization`.
     *
     * `$now` is injected rather than read from the clock so the result is
     * reproducible. AWS rejects a request whose timestamp is more than five
     * minutes out, so the caller must pass a real time in production — but a
     * test may pass any.
     *
     * @param  string $target the `x-amz-target` operation
     * @param  string $path   e.g. `/paapi5/getitems`
     * @param  string $body   the JSON payload, already encoded
     * @return array<string,string>
     */
    public function headers(string $target, string $path, string $body, int $now): array
    {
        $amzDate = gmdate('Ymd\THis\Z', $now);
        $dateStamp = gmdate('Ymd', $now);

        // The signed set, and it must stay sorted by lowercase name — the
        // canonical request and the SignedHeaders list have to agree exactly,
        // and AWS compares strings, not intent.
        $signed = [
            'content-encoding' => 'amz-1.0',
            'content-type' => 'application/json; charset=utf-8',
            'host' => $this->host,
            'x-amz-date' => $amzDate,
            'x-amz-target' => $target,
        ];
        ksort($signed);

        $scope = "{$dateStamp}/{$this->region}/" . self::SERVICE . '/aws4_request';
        $canonical = $this->canonicalRequest($path, $signed, $body);
        $toSign = implode("\n", [
            self::ALGORITHM,
            $amzDate,
            $scope,
            hash('sha256', $canonical),
        ]);
        $signature = hash_hmac('sha256', $toSign, $this->signingKey($dateStamp));

        $signedNames = implode(';', array_keys($signed));
        $authorization = self::ALGORITHM
            . " Credential={$this->accessKey}/{$scope}"
            . ", SignedHeaders={$signedNames}"
            . ", Signature={$signature}";

        return $signed + ['Authorization' => $authorization];
    }

    /**
     * Step 1. Note the trailing newline after the header block and the empty
     * query string — both are required, and omitting either produces a
     * signature that differs from AWS's with no hint as to why.
     *
     * @param array<string,string> $headers already sorted
     */
    private function canonicalRequest(string $path, array $headers, string $body): string
    {
        $canonicalHeaders = '';
        foreach ($headers as $name => $value) {
            $canonicalHeaders .= strtolower($name) . ':' . trim($value) . "\n";
        }

        return implode("\n", [
            'POST',
            $path,
            '', // no query string on PA-API
            $canonicalHeaders,
            implode(';', array_keys($headers)),
            hash('sha256', $body),
        ]);
    }

    /** Step 3. Four chained HMACs, each keyed by the previous digest (raw, not hex). */
    private function signingKey(string $dateStamp): string
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $this->secretKey, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', self::SERVICE, $kRegion, true);
        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }
}
