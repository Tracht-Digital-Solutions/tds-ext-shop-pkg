<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

use Tds\Frontend\Contract\Commerce\SaleEvent;
use Tds\Frontend\Contract\Commerce\SaleEvents;
use Tds\Frontend\Contract\Commerce\SaleLine;

/**
 * The referral half of an order: reading it from the checkout request and
 * reporting the paid order as a {@see SaleEvent}.
 *
 * Both directions are pure so they are testable without a database or a
 * webhook. The shop keeps no commission logic; it records the claim and tells
 * the contract's {@see SaleEvents}, which no-ops when nothing listens.
 */
final class OrderReferral
{
    /**
     * What the checkout should store. An unknown or inactive code is dropped
     * silently — it must never block a purchase — and with no resolver at all
     * (no referral module installed) nothing is stored either: a code nobody
     * can ever honour is noise in the order.
     *
     * @param array<string,mixed> $body the checkout request
     * @return array{code:?string,via:?string,note:?string}
     */
    public static function fromCheckout(array $body, ?SaleEvents $events): array
    {
        $ref = is_array($body['referral'] ?? null) ? $body['referral'] : [];
        $raw = trim((string) ($ref['code'] ?? ''));
        $code = null;
        $via = null;
        if ($raw !== '' && $events !== null) {
            $match = $events->resolveReferral($raw);
            if ($match !== null) {
                $code = $match->code;
                $via = ($ref['via'] ?? null) === 'link' ? 'link' : 'code';
            }
        }
        $note = trim((string) ($body['referredBy'] ?? ''));
        return [
            'code' => $code,
            'via' => $via,
            'note' => $note === '' ? null : mb_substr($note, 0, 500),
        ];
    }

    /**
     * The paid order as the contract describes a sale. Net is the GOODS net,
     * the sum of the lines — shipping is a cost passed through, not something a
     * partner brought in.
     *
     * @param array<string,mixed>       $order a `shop_order` row
     * @param list<array<string,mixed>> $items its `shop_order_item` rows
     */
    public static function saleEvent(array $order, array $items): SaleEvent
    {
        $lines = array_map(
            static fn (array $i): SaleLine => new SaleLine(
                isset($i['product_id']) ? (string) $i['product_id'] : null,
                (int) $i['net_cents'],
                (string) ($i['title'] ?? ''),
            ),
            $items,
        );
        return new SaleEvent(
            'shop',
            (string) $order['id'],
            array_sum(array_map(static fn (SaleLine $l): int => $l->netCents, $lines)),
            isset($order['email']) ? (string) $order['email'] : null,
            $lines,
            self::nullable($order['referral_code'] ?? null),
            self::nullable($order['referral_via'] ?? null),
            self::nullable($order['referred_by_note'] ?? null),
        );
    }

    private static function nullable(mixed $v): ?string
    {
        $s = trim((string) ($v ?? ''));
        return $s === '' ? null : $s;
    }
}
