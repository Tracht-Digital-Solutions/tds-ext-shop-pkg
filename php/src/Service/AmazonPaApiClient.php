<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Service;

use Tds\Ext\Shop\Support\PaApiSigner;

/**
 * Amazon Product Advertising API v5 — the two operations the catalogue needs.
 *
 * Plain `ext-curl`, no SDK: the extension convention, and one third-party call
 * does not justify a vendor tree. The signing lives in {@see PaApiSigner},
 * which is pure and tested; this class is transport.
 *
 * ### The one thing that is not obvious
 *
 * `getItems()` takes up to **ten** ASINs per call. That is not an optimisation
 * — it is what makes the rate limit a non-problem. PA-API starts every account
 * at one request per second; ten items per request means six hundred products
 * a minute, so the budget is never the constraint. Fetching one ASIN per call
 * would make it one.
 *
 * ### Access is revocable, and the client says so
 *
 * Amazon withdraws API access when qualifying sales stop. The failure is a
 * plain 401/403 and it is permanent until sales resume, so retrying is
 * pointless and hammering makes it worse. {@see PaApiException::isPermanent()}
 * separates that from a throttle, and the sync uses it to stop rather than
 * back off. Everything else in the shop keeps working: the affiliate links are
 * not the API, and prices simply age out of view within a day.
 */
final class AmazonPaApiClient
{
    /** PA-API's own ceiling per GetItems call. */
    public const MAX_ITEMS_PER_CALL = 10;

    private const RESOURCES = [
        'ItemInfo.Title',
        'ItemInfo.ByLineInfo',
        'ItemInfo.Features',
        'Images.Primary.Large',
        'Offers.Listings.Price',
        'Offers.Listings.Availability.Message',
    ];

    public function __construct(
        private readonly PaApiSigner $signer,
        private readonly string $partnerTag,
        private readonly string $host = 'webservices.amazon.de',
        private readonly string $marketplace = 'www.amazon.de',
        private readonly int $timeoutSeconds = 8,
    ) {
    }

    /**
     * Fetch up to ten items.
     *
     * @param  list<string> $asins
     * @return array<string, array<string,mixed>> keyed by ASIN; missing items
     *         are simply absent — Amazon drops an unknown ASIN rather than
     *         erroring, and so do we.
     * @throws PaApiException
     */
    public function getItems(array $asins): array
    {
        $asins = array_values(array_unique(array_filter($asins)));
        if ($asins === []) {
            return [];
        }
        if (count($asins) > self::MAX_ITEMS_PER_CALL) {
            throw new PaApiException('too_many_items', 0);
        }

        $response = $this->post('GetItems', '/paapi5/getitems', [
            'ItemIds' => $asins,
            'ItemIdType' => 'ASIN',
            'Resources' => self::RESOURCES,
            'PartnerTag' => $this->partnerTag,
            'PartnerType' => 'Associates',
            'Marketplace' => $this->marketplace,
        ]);

        $out = [];
        foreach ($response['ItemsResult']['Items'] ?? [] as $item) {
            $asin = (string) ($item['ASIN'] ?? '');
            if ($asin !== '') {
                $out[$asin] = self::normalise($item);
            }
        }
        return $out;
    }

    /**
     * Keyword search, for the panel's import screen.
     *
     * @return list<array<string,mixed>>
     * @throws PaApiException
     */
    public function searchItems(string $keywords, int $limit = 10): array
    {
        $response = $this->post('SearchItems', '/paapi5/searchitems', [
            'Keywords' => $keywords,
            'ItemCount' => max(1, min(10, $limit)),
            'Resources' => self::RESOURCES,
            'PartnerTag' => $this->partnerTag,
            'PartnerType' => 'Associates',
            'Marketplace' => $this->marketplace,
        ]);

        $out = [];
        foreach ($response['SearchResult']['Items'] ?? [] as $item) {
            $out[] = self::normalise($item);
        }
        return $out;
    }

    /**
     * Flatten one PA-API item into the shape the repository stores.
     *
     * The price is returned in **minor units** (`Amount` is a float in the
     * payload; money never stays a float here — see the schema note). The
     * image URL is kept verbatim: the licence requires Amazon's own CDN URL to
     * be served, so rewriting or proxying it is a breach, not an optimisation.
     *
     * @param  array<string,mixed> $item
     * @return array<string,mixed>
     */
    private static function normalise(array $item): array
    {
        $listing = $item['Offers']['Listings'][0] ?? null;
        $amount = $listing['Price']['Amount'] ?? null;

        return [
            'asin' => (string) ($item['ASIN'] ?? ''),
            'title' => (string) ($item['ItemInfo']['Title']['DisplayValue'] ?? ''),
            'brand' => isset($item['ItemInfo']['ByLineInfo']['Brand']['DisplayValue'])
                ? (string) $item['ItemInfo']['ByLineInfo']['Brand']['DisplayValue']
                : null,
            'features' => array_values(array_map(
                'strval',
                $item['ItemInfo']['Features']['DisplayValues'] ?? [],
            )),
            'imageUrl' => isset($item['Images']['Primary']['Large']['URL'])
                ? (string) $item['Images']['Primary']['Large']['URL']
                : null,
            'url' => (string) ($item['DetailPageURL'] ?? ''),
            // Rounded, not cast: (int)(19.99 * 100) is 1998 on a binary float.
            'priceCents' => $amount === null ? null : (int) round(((float) $amount) * 100),
            'currency' => (string) ($listing['Price']['Currency'] ?? 'EUR'),
            'availability' => self::availability(
                (string) ($listing['Availability']['Message'] ?? ''),
            ),
        ];
    }

    /**
     * Amazon reports availability as free German prose ("Auf Lager."), so this
     * is a coarse read of it. Anything unrecognised stays `unknown` rather than
     * being guessed as in stock — a wrong "available" sends a reader to a page
     * that cannot sell them anything.
     */
    private static function availability(string $message): string
    {
        $m = mb_strtolower(trim($message));
        if ($m === '') {
            return 'unknown';
        }
        foreach (['nicht verfügbar', 'nicht auf lager', 'derzeit nicht', 'unavailable', 'out of stock'] as $needle) {
            if (str_contains($m, $needle)) {
                return 'out_of_stock';
            }
        }
        foreach (['auf lager', 'in stock', 'verfügbar', 'available'] as $needle) {
            if (str_contains($m, $needle)) {
                return 'in_stock';
            }
        }
        return 'unknown';
    }

    /**
     * @param  array<string,mixed> $payload
     * @return array<string,mixed>
     * @throws PaApiException
     */
    private function post(string $operation, string $path, array $payload): array
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $headers = $this->signer->headers(
            'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.' . $operation,
            $path,
            $body,
            time(),
        );

        $curlHeaders = [];
        foreach ($headers as $name => $value) {
            $curlHeaders[] = "{$name}: {$value}";
        }

        $ch = curl_init("https://{$this->host}{$path}");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $curlHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => 4,
            // A redirect on a signed request cannot be followed: the signature
            // covers the host and would not match the new one.
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new PaApiException("transport: {$error}", 0);
        }

        $decoded = json_decode((string) $raw, true);
        if ($status >= 400 || !is_array($decoded)) {
            $reason = is_array($decoded)
                ? (string) ($decoded['Errors'][0]['Code'] ?? $decoded['__type'] ?? 'unknown')
                : 'unparseable_response';
            throw new PaApiException($reason, $status);
        }
        return $decoded;
    }
}
