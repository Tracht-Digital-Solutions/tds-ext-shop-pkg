<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Domain\OrderRepository;
use Tds\Ext\Shop\Service\OrderInvoiceBuilder;

/**
 * The invoice payload, checked as arithmetic.
 *
 * The thing under test is not the JSON shape — it is whether the document
 * Lexware produces adds up to the amount the customer was actually charged.
 * That failure is silent in every way that matters: the invoice renders, the
 * order looks fine, and the discrepancy surfaces weeks later as a
 * reconciliation that will not close.
 *
 * So the central test does not assert on literal numbers written by hand. It
 * prices a basket through {@see OrderRepository::priceCart()} — the same code
 * the checkout uses — and then re-derives the totals from the payload the way
 * Lexware will, and requires them to be equal to the cent.
 */
final class OrderInvoiceBuilderTest extends TestCase
{
    private OrderInvoiceBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new OrderInvoiceBuilder();
    }

    /**
     * Re-compute a `net` invoice the way Lexware does: per line, tax from the
     * line's net, then add the lines up.
     *
     * @param array<string,mixed> $payload
     * @return array{net:int,tax:int,gross:int}
     */
    private static function totals(array $payload): array
    {
        $net = 0;
        $tax = 0;
        foreach ($payload['lineItems'] as $line) {
            $unitNet = (int) round($line['unitPrice']['netAmount'] * 100);
            $lineNet = $unitNet * (int) $line['quantity'];
            $net += $lineNet;
            $tax += (int) round($lineNet * $line['unitPrice']['taxRatePercentage'] / 100);
        }
        return ['net' => $net, 'tax' => $tax, 'gross' => $net + $tax];
    }

    /** A cart line as `sellableMany()` produces it. */
    private static function line(int $netCents, int $rateBp, int $qty, string $title): array
    {
        return [
            'product_id' => 1,
            'offer_id' => 1,
            'title' => $title,
            'slug' => 'x',
            'net_cents' => $netCents,
            'vat_rate_bp' => $rateBp,
            'quantity' => $qty,
            'fulfilment' => 'manual',
            'requires_shipping' => false,
        ];
    }

    /** An order row as it is written, from a priced cart. */
    private static function order(array $totals, array $extra = []): array
    {
        return $extra + [
            'order_no' => 'TDS-1001',
            'currency' => 'EUR',
            'country' => 'DE',
            'name' => 'Erika Mustermann',
            'email' => 'erika@example.org',
            'tax_rate_bp' => $totals['rate'],
            'net_cents' => $totals['net'],
            'tax_cents' => $totals['tax'],
            'gross_cents' => $totals['gross'],
            'shipping_net_cents' => 0,
            'shipping_tax_cents' => 0,
            'shipping_gross_cents' => 0,
        ];
    }

    /** Order items as `open()` writes them: LINE totals, not unit prices. */
    private static function items(array $priced): array
    {
        $items = [];
        foreach ($priced['lines'] as $line) {
            $items[] = [
                'title' => $line['title'],
                'quantity' => $line['quantity'],
                'net_cents' => $line['line_net'],
                'tax_cents' => $line['line_tax'],
                'gross_cents' => $line['line_gross'],
            ];
        }
        return $items;
    }

    /**
     * @dataProvider baskets
     */
    public function testInvoiceTotalsMatchWhatWasCharged(array $lines, array $shipping): void
    {
        $priced = OrderRepository::priceCart($lines, $shipping);
        $order = self::order($priced, [
            'shipping_net_cents' => $shipping['net'],
            'shipping_tax_cents' => $shipping['tax'],
            'shipping_gross_cents' => $shipping['gross'],
        ]);

        $payload = $this->builder->build($order, self::items($priced), new DateTimeImmutable());
        $rebuilt = self::totals($payload);

        // `priceCart()` already folds shipping into its totals — those ARE the
        // figures frozen onto the order and charged to the card. The payload
        // has to reproduce them exactly, postage line included.
        self::assertSame($priced['net'], $rebuilt['net'], 'net');
        self::assertSame($priced['tax'], $rebuilt['tax'], 'tax');
        self::assertSame($priced['gross'], $rebuilt['gross'], 'gross');
        self::assertSame($priced['gross'], $rebuilt['net'] + $rebuilt['tax'], 'gross = net + tax');
    }

    public static function baskets(): array
    {
        $noShipping = ['net' => 0, 'tax' => 0, 'gross' => 0];
        return [
            'single line' => [[self::line(4900, 1900, 1, 'Paket S')], $noShipping],
            'quantity > 1' => [[self::line(1999, 1900, 3, 'Paket M')], $noShipping],
            // The case a per-basket rounding would get wrong: three lines whose
            // individual taxes each round, summed.
            'three lines that each round' => [
                [
                    self::line(333, 1900, 1, 'A'),
                    self::line(667, 1900, 1, 'B'),
                    self::line(1, 1900, 7, 'C'),
                ],
                $noShipping,
            ],
            // Mixed rates: the order carries ONE tax_rate_bp, so a builder that
            // trusted it instead of the line would put 19 % on the 7 % book.
            'mixed vat rates' => [
                [self::line(2500, 1900, 1, 'Hardware'), self::line(1900, 700, 2, 'Buch')],
                $noShipping,
            ],
            'with shipping' => [
                [self::line(4900, 1900, 2, 'Paket S')],
                ['net' => 495, 'tax' => 94, 'gross' => 589],
            ],
        ];
    }

    public function testMixedRatesKeepTheirOwnRatePerLine(): void
    {
        $priced = OrderRepository::priceCart(
            [self::line(2500, 1900, 1, 'Hardware'), self::line(1000, 700, 1, 'Buch')],
            ['net' => 0, 'tax' => 0, 'gross' => 0],
        );
        $payload = $this->builder->build(
            self::order($priced),
            self::items($priced),
            new DateTimeImmutable(),
        );

        $rates = array_map(
            static fn (array $l): float => $l['unitPrice']['taxRatePercentage'],
            $payload['lineItems'],
        );
        self::assertSame([19.0, 7.0], $rates);
    }

    /**
     * The unit price is the line total divided by the quantity, and the shop's
     * own pricing guarantees that division is exact.
     */
    public function testUnitPriceIsDerivedExactly(): void
    {
        $priced = OrderRepository::priceCart(
            [self::line(1999, 1900, 3, 'Paket M')],
            ['net' => 0, 'tax' => 0, 'gross' => 0],
        );
        $payload = $this->builder->build(
            self::order($priced),
            self::items($priced),
            new DateTimeImmutable(),
        );

        self::assertSame(19.99, $payload['lineItems'][0]['unitPrice']['netAmount']);
        self::assertSame(3, $payload['lineItems'][0]['quantity']);
    }

    public function testShippingBecomesItsOwnLine(): void
    {
        $priced = OrderRepository::priceCart(
            [self::line(4900, 1900, 1, 'Paket S')],
            ['net' => 495, 'tax' => 94, 'gross' => 589],
        );
        $payload = $this->builder->build(
            self::order($priced, [
                'shipping_net_cents' => 495,
                'shipping_tax_cents' => 94,
                'shipping_gross_cents' => 589,
            ]),
            self::items($priced),
            new DateTimeImmutable(),
        );

        self::assertCount(2, $payload['lineItems']);
        self::assertSame('Versand', $payload['lineItems'][1]['name']);
        self::assertSame(4.95, $payload['lineItems'][1]['unitPrice']['netAmount']);
    }

    public function testNoShippingLineWhenPostageIsFree(): void
    {
        $priced = OrderRepository::priceCart(
            [self::line(4900, 1900, 1, 'Paket S')],
            ['net' => 0, 'tax' => 0, 'gross' => 0],
        );
        $payload = $this->builder->build(
            self::order($priced),
            self::items($priced),
            new DateTimeImmutable(),
        );
        self::assertCount(1, $payload['lineItems']);
    }

    /**
     * A postal order invoices to the address it was shipped to; a digital one
     * has none and falls back to the buyer's name.
     */
    public function testPostalAddressIsUsedWhenPresent(): void
    {
        $priced = OrderRepository::priceCart(
            [self::line(4900, 1900, 1, 'Paket S')],
            ['net' => 0, 'tax' => 0, 'gross' => 0],
        );
        $payload = $this->builder->build(
            self::order($priced, [
                'ship_name' => 'Hof Beispiel GmbH',
                'ship_line1' => 'Musterweg 3',
                'ship_line2' => 'Haus B',
                'ship_postcode' => '56566',
                'ship_city' => 'Neuwied',
                'ship_country' => 'de',
            ]),
            self::items($priced),
            new DateTimeImmutable(),
        );

        self::assertSame([
            'name' => 'Hof Beispiel GmbH',
            'countryCode' => 'DE',
            'street' => 'Musterweg 3, Haus B',
            'zip' => '56566',
            'city' => 'Neuwied',
        ], $payload['address']);
    }

    public function testDigitalOrderFallsBackToTheBuyersName(): void
    {
        $priced = OrderRepository::priceCart(
            [self::line(4900, 1900, 1, 'Paket S')],
            ['net' => 0, 'tax' => 0, 'gross' => 0],
        );
        $payload = $this->builder->build(
            self::order($priced),
            self::items($priced),
            new DateTimeImmutable(),
        );

        self::assertSame(['name' => 'Erika Mustermann', 'countryCode' => 'DE'], $payload['address']);
    }

    /** Lexware rejects a nameless address; an order always has an email. */
    public function testAddressNeverEndsUpWithoutAName(): void
    {
        $priced = OrderRepository::priceCart(
            [self::line(4900, 1900, 1, 'Paket S')],
            ['net' => 0, 'tax' => 0, 'gross' => 0],
        );
        $order = self::order($priced);
        $order['name'] = '   ';
        $payload = $this->builder->build($order, self::items($priced), new DateTimeImmutable());

        self::assertSame('erika@example.org', $payload['address']['name']);
    }

    /** `net`, because that is how the basket was priced. See the class note. */
    public function testTaxTypeIsNet(): void
    {
        $priced = OrderRepository::priceCart(
            [self::line(4900, 1900, 1, 'Paket S')],
            ['net' => 0, 'tax' => 0, 'gross' => 0],
        );
        $payload = $this->builder->build(
            self::order($priced),
            self::items($priced),
            new DateTimeImmutable(),
        );

        self::assertSame(['taxType' => 'net'], $payload['taxConditions']);
        self::assertSame('EUR', $payload['totalPrice']['currency']);
    }

    /** The order number has to be traceable from the document. */
    public function testOrderNumberIsCarried(): void
    {
        $priced = OrderRepository::priceCart(
            [self::line(4900, 1900, 1, 'Paket S')],
            ['net' => 0, 'tax' => 0, 'gross' => 0],
        );
        $payload = $this->builder->build(
            self::order($priced),
            self::items($priced),
            new DateTimeImmutable(),
        );

        self::assertStringContainsString('TDS-1001', $payload['remark']);
        self::assertStringContainsString('TDS-1001', $payload['introduction']);
    }
}
