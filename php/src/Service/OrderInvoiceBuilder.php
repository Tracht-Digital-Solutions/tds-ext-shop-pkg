<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Service;

use DateTimeInterface;

/**
 * Turns a paid order into a Lexware Office invoice payload.
 *
 * Pure and stateless, so the arithmetic — the part that must not be wrong — is
 * testable without an HTTP client, an API key or a database.
 *
 * ### Why `taxType: net` and not `gross`
 *
 * Because that is how the order was priced. `OrderRepository::priceCart()`
 * rounds the tax PER LINE from the line's net (`round(lineNet * rate / 10000)`)
 * and then adds the lines up. Lexware does exactly the same thing for a `net`
 * invoice. Handing it gross unit prices instead would make it derive the net
 * back out, and the two roundings meet in the middle at a cent that no longer
 * matches what the customer was actually charged.
 *
 * A receipt that disagrees with the card statement by one cent is not a
 * rounding curiosity; it is a support ticket and a bookkeeping correction.
 *
 * ### The unit price is derived, and that division is always exact
 *
 * `shop_order_item.net_cents` is the LINE total — `priceCart()` writes
 * `unitNet * quantity` into it. Dividing it back by the quantity therefore
 * always lands on a whole cent, and the value is the unit price the customer
 * saw. It is derived rather than read because the per-unit figure is not
 * stored: the line total is what the invoice needs and what was frozen.
 *
 * ### The tax rate comes from the LINE, not from the order
 *
 * `shop_order.tax_rate_bp` is one number for a basket that may legitimately
 * mix rates (books at 7 %, hardware at 19 %). Each line already carries the
 * net and the tax it was charged, so its own rate is recoverable from those
 * two and cannot drift from them. The order's rate is only the fallback for a
 * line whose net is zero, where there is nothing to divide.
 *
 * ### Shipping is a line, because on an invoice it is one
 *
 * Lexware has a `shippingConditions` block, but it describes WHEN a service
 * was rendered, not what it cost. Postage the customer paid is a taxable
 * position and belongs among the positions.
 */
final class OrderInvoiceBuilder
{
    /** Basis points per percent — 1900 bp = 19 %. */
    private const BP_PER_PERCENT = 100;

    /**
     * @param array<string,mixed>            $order rows from `shop_order`
     * @param list<array<string,mixed>>      $items rows from `shop_order_item`
     * @return array<string,mixed> the Lexware invoice JSON body
     */
    public function build(array $order, array $items, DateTimeInterface $voucherDate): array
    {
        $currency = strtoupper((string) ($order['currency'] ?? 'EUR')) ?: 'EUR';
        $orderRateBp = (int) ($order['tax_rate_bp'] ?? 1900);

        $lineItems = [];
        foreach ($items as $item) {
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $lineNet = (int) ($item['net_cents'] ?? 0);
            $lineTax = (int) ($item['tax_cents'] ?? 0);

            $lineItems[] = [
                'type' => 'custom',
                'name' => self::text($item['title'] ?? '', 'Position'),
                'quantity' => $quantity,
                'unitName' => 'Stück',
                'unitPrice' => [
                    'currency' => $currency,
                    // Exact by construction — see the class note.
                    'netAmount' => round($lineNet / $quantity / 100, 2),
                    'taxRatePercentage' => self::rateFor($lineNet, $lineTax, $orderRateBp),
                ],
            ];
        }

        $shippingNet = (int) ($order['shipping_net_cents'] ?? 0);
        if ($shippingNet > 0) {
            $shippingTax = (int) ($order['shipping_tax_cents'] ?? 0);
            $lineItems[] = [
                'type' => 'custom',
                'name' => 'Versand',
                'quantity' => 1,
                'unitName' => 'Pauschale',
                'unitPrice' => [
                    'currency' => $currency,
                    'netAmount' => round($shippingNet / 100, 2),
                    'taxRatePercentage' => self::rateFor($shippingNet, $shippingTax, $orderRateBp),
                ],
            ];
        }

        $stamp = $voucherDate->format('Y-m-d\TH:i:s.000P');

        return [
            'voucherDate' => $stamp,
            'address' => self::address($order),
            'lineItems' => $lineItems,
            'totalPrice' => ['currency' => $currency],
            'taxConditions' => ['taxType' => 'net'],
            'shippingConditions' => [
                // `service` and one date: the goods or the digital delivery are
                // rendered on the day the order was paid. A period would claim
                // a delivery window nothing here records.
                'shippingType' => 'service',
                'shippingDate' => $stamp,
            ],
            'title' => 'Rechnung',
            'introduction' => sprintf(
                'Ihre Bestellung %s im TDShop.',
                self::text($order['order_no'] ?? '', '—'),
            ),
            // The order number, so a payment on the bank statement can be
            // traced back to a basket without opening the shop.
            'remark' => sprintf('Bestellnummer %s', self::text($order['order_no'] ?? '', '—')),
        ];
    }

    /**
     * The invoice address.
     *
     * A shipping address is used when the order carries one — it is the only
     * postal address a guest purchase ever collects, and an invoice without one
     * is not a proper invoice for anything that had to be posted. A digital
     * order has none, and then the buyer's name is all there is; Lexware
     * accepts that as a free-text address.
     *
     * `countryCode` falls back to the order's `country`, which the schema
     * defaults to DE, rather than to a hardcoded DE here: the column exists so
     * that widening beyond Germany is configuration and not a migration, and a
     * builder that ignores it would quietly undo that.
     *
     * @param array<string,mixed> $order
     * @return array<string,mixed>
     */
    private static function address(array $order): array
    {
        $country = strtoupper(self::text(
            $order['ship_country'] ?? $order['country'] ?? '',
            'DE',
        ));

        $name = self::text(
            $order['ship_name'] ?? $order['name'] ?? '',
            // Never blank: Lexware rejects an address with no name, and an
            // order always has the address it was confirmed to.
            self::text($order['email'] ?? '', 'Kunde'),
        );

        $address = ['name' => $name, 'countryCode' => $country];

        $street = self::text($order['ship_line1'] ?? '', '');
        if ($street !== '') {
            $line2 = self::text($order['ship_line2'] ?? '', '');
            $address['street'] = $line2 === '' ? $street : $street . ', ' . $line2;
            $address['zip'] = self::text($order['ship_postcode'] ?? '', '');
            $address['city'] = self::text($order['ship_city'] ?? '', '');
        }

        return $address;
    }

    /**
     * The tax rate of one line as a percentage, recovered from its own figures.
     *
     * Rounded to a whole percent because every German rate is one (19, 7, 0)
     * and because the alternative — a float carried through from a division —
     * is how 19.000000000000004 ends up in a payload Lexware then rejects.
     */
    private static function rateFor(int $netCents, int $taxCents, int $fallbackBp): float
    {
        if ($netCents <= 0) {
            return round($fallbackBp / self::BP_PER_PERCENT);
        }
        return round($taxCents / $netCents * 100);
    }

    /** Trimmed string, or the fallback when there is nothing usable. */
    private static function text(mixed $value, string $fallback): string
    {
        $text = is_string($value) ? trim($value) : '';
        return $text === '' ? $fallback : $text;
    }
}
