<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Support\PaApiSigner;

/**
 * AWS SigV4 for the Product Advertising API.
 *
 * This is the only part of the Amazon integration that can be proven without
 * an Amazon account, which is exactly why the signing was split out of the
 * client. A wrong signature surfaces in production as
 * `IncompleteSignatureException` from a server that will not say which of the
 * four steps it disagreed with — so the steps are pinned here instead.
 *
 * The expected signature below is not copied from anywhere: it is computed by
 * the same algorithm, and its value is to be **stable**. If a refactor changes
 * it, the refactor changed the signature, and Amazon will reject every call.
 */
final class PaApiSignerTest extends TestCase
{
    private const NOW = 1788782400; // 2026-09-07T12:00:00Z, frozen
    private const BODY = '{"ItemIds":["B08H93ZRK9"]}';

    private static function signer(): PaApiSigner
    {
        return new PaApiSigner(
            'AKIAEXAMPLEKEY',
            'wJalrXUtnFEMIexampleSECRETkey',
            'eu-west-1',
            'webservices.amazon.de',
        );
    }

    private static function headers(): array
    {
        return self::signer()->headers(
            'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems',
            '/paapi5/getitems',
            self::BODY,
            self::NOW,
        );
    }

    public function testCarriesEveryHeaderPaApiRequires(): void
    {
        $headers = self::headers();
        foreach (['content-encoding', 'content-type', 'host', 'x-amz-date', 'x-amz-target', 'Authorization'] as $name) {
            self::assertArrayHasKey($name, $headers, $name);
        }
        // `amz-1.0` is not decoration: PA-API rejects a request without it.
        self::assertSame('amz-1.0', $headers['content-encoding']);
    }

    public function testTheDateIsTheOneItWasGiven(): void
    {
        // Injected rather than read from the clock — AWS rejects a request
        // more than five minutes out, so a signer that quietly used its own
        // time would be untestable AND wrong under clock skew.
        self::assertSame('20260907T120000Z', self::headers()['x-amz-date']);
    }

    public function testTheAuthorizationHeaderIsWellFormed(): void
    {
        $auth = self::headers()['Authorization'];
        self::assertStringStartsWith('AWS4-HMAC-SHA256 Credential=AKIAEXAMPLEKEY/20260907/eu-west-1/ProductAdvertisingAPI/aws4_request', $auth);
        self::assertStringContainsString(
            'SignedHeaders=content-encoding;content-type;host;x-amz-date;x-amz-target',
            $auth,
        );
        self::assertMatchesRegularExpression('/Signature=[0-9a-f]{64}$/', $auth);
    }

    public function testSignedHeadersAreSortedAndMatchTheHeadersSent(): void
    {
        // The canonical request and the SignedHeaders list must agree exactly;
        // AWS compares strings, not intent. A header added to one and not the
        // other is a signature mismatch with no diagnostic.
        $headers = self::headers();
        preg_match('/SignedHeaders=([^,]+)/', $headers['Authorization'], $m);
        $listed = explode(';', $m[1]);

        $sent = array_map('strtolower', array_keys($headers));
        $sent = array_values(array_diff($sent, ['authorization']));
        sort($sent);

        self::assertSame($sent, $listed);
        self::assertSame($listed, array_values(array_unique($listed)));
    }

    public function testTheSignatureIsStable(): void
    {
        // The regression guard. Any change to the canonical request, the scope
        // or the key derivation moves this value — and the only other place it
        // would show up is Amazon refusing every call in production.
        self::assertSame(
            'Signature=' . hash_hmac(
                'sha256',
                implode("\n", [
                    'AWS4-HMAC-SHA256',
                    '20260907T120000Z',
                    '20260907/eu-west-1/ProductAdvertisingAPI/aws4_request',
                    hash('sha256', implode("\n", [
                        'POST',
                        '/paapi5/getitems',
                        '',
                        "content-encoding:amz-1.0\ncontent-type:application/json; charset=utf-8\n"
                            . "host:webservices.amazon.de\nx-amz-date:20260907T120000Z\n"
                            . "x-amz-target:com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems\n",
                        'content-encoding;content-type;host;x-amz-date;x-amz-target',
                        hash('sha256', self::BODY),
                    ])),
                ]),
                self::signingKey(),
            ),
            substr(self::headers()['Authorization'], strpos(self::headers()['Authorization'], 'Signature=')),
        );
    }

    public function testADifferentBodyProducesADifferentSignature(): void
    {
        // Proves the body is actually inside the signature. A canonical request
        // that hashed an empty string instead would pass every other assertion
        // here and fail every real call.
        $a = self::headers()['Authorization'];
        $b = self::signer()->headers(
            'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems',
            '/paapi5/getitems',
            '{"ItemIds":["B0OTHER0001"]}',
            self::NOW,
        )['Authorization'];
        self::assertNotSame($a, $b);
    }

    public function testADifferentSecretProducesADifferentSignature(): void
    {
        $other = new PaApiSigner('AKIAEXAMPLEKEY', 'a-different-secret', 'eu-west-1', 'webservices.amazon.de');
        self::assertNotSame(
            self::headers()['Authorization'],
            $other->headers(
                'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems',
                '/paapi5/getitems',
                self::BODY,
                self::NOW,
            )['Authorization'],
        );
    }

    /** The four chained HMACs, restated independently of the implementation. */
    private static function signingKey(): string
    {
        $k = hash_hmac('sha256', '20260907', 'AWS4wJalrXUtnFEMIexampleSECRETkey', true);
        $k = hash_hmac('sha256', 'eu-west-1', $k, true);
        $k = hash_hmac('sha256', 'ProductAdvertisingAPI', $k, true);
        return hash_hmac('sha256', 'aws4_request', $k, true);
    }
}
