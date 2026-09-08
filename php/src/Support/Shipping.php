<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

/**
 * What delivery costs, and what tax it carries.
 *
 * ### The rule that is wrong in most shops
 *
 * Shipping is an **ancillary service** (unselbständige Nebenleistung). It has no
 * VAT rate of its own — it takes the rate of the goods it delivers
 * (Abschn. 3.10 UStAE). With every physical line at 19 % that is invisible.
 * With a mixed basket — a 19 % gadget and a 7 % book — the charge has to be
 * split across the rates **by net value** and taxed in parts.
 *
 * Charging one blanket 19 % on the whole shipping line is the common shortcut,
 * and it is wrong in the customer's favour or ours depending on the mix, which
 * is exactly why nobody notices it until a VAT audit does.
 *
 * ### What it does not do
 *
 * Weight, zones, carriers, dimensional pricing. One flat rate for the allowed
 * delivery countries with a free-shipping threshold, because that is what this
 * shop actually charges. Everything here is a setting, so widening it is a
 * business decision rather than a migration — the same reasoning as
 * `SHOP_ALLOWED_COUNTRIES`.
 */
final class Shipping
{
    /**
     * @param int $flatCents     the charge itself, gross-neutral (a NET figure —
     *                           the tax is computed on top, per rate bucket)
     * @param int $freeFromCents order net at or above which delivery is free.
     *                           0 disables the threshold.
     */
    public function __construct(
        private readonly int $flatCents = 0,
        private readonly int $freeFromCents = 0,
    ) {
    }

    /**
     * Split a shipping charge across the VAT rates of the goods it delivers.
     *
     * `$lines` are the PHYSICAL lines only, as `['net' => int, 'rate' => int]`
     * where the rate is in basis points. Digital lines are not delivered and do
     * not pull shipping tax toward their rate.
     *
     * The apportionment is by net value, and the LAST bucket absorbs the
     * rounding remainder so the parts always add back to the whole. Distributing
     * the remainder some other way would be equally defensible; leaving the
     * parts not summing to the charge would not be, because the invoice line and
     * the tax lines have to reconcile to the cent.
     *
     * @param  list<array{net:int,rate:int}> $lines
     * @return array{net:int,tax:int,gross:int}
     */
    public function forLines(array $lines): array
    {
        if ($lines === [] || $this->flatCents <= 0) {
            return ['net' => 0, 'tax' => 0, 'gross' => 0];
        }

        $goodsNet = 0;
        foreach ($lines as $line) {
            $goodsNet += $line['net'];
        }
        if ($this->freeFromCents > 0 && $goodsNet >= $this->freeFromCents) {
            return ['net' => 0, 'tax' => 0, 'gross' => 0];
        }

        $net = $this->flatCents;

        // One rate — the ordinary case, and no apportionment to get wrong.
        $rates = array_values(array_unique(array_column($lines, 'rate')));
        if (count($rates) === 1) {
            $tax = (int) round($net * $rates[0] / 10000);
            return ['net' => $net, 'tax' => $tax, 'gross' => $net + $tax];
        }

        // Mixed rates: apportion by net value.
        $byRate = [];
        foreach ($lines as $line) {
            $byRate[$line['rate']] = ($byRate[$line['rate']] ?? 0) + $line['net'];
        }
        // Deterministic order, so the same basket always produces the same
        // split — the remainder lands in a predictable bucket rather than
        // wherever the array happened to be ordered.
        ksort($byRate);

        $tax = 0;
        $assigned = 0;
        $last = array_key_last($byRate);
        foreach ($byRate as $rate => $share) {
            $portion = $rate === $last
                ? $net - $assigned          // the remainder, so the parts sum
                : (int) floor($net * $share / max(1, $goodsNet));
            $assigned += $portion;
            $tax += (int) round($portion * $rate / 10000);
        }

        return ['net' => $net, 'tax' => $tax, 'gross' => $net + $tax];
    }

    /** True when this basket would be delivered free. */
    public function isFreeAt(int $goodsNetCents): bool
    {
        return $this->flatCents <= 0
            || ($this->freeFromCents > 0 && $goodsNetCents >= $this->freeFromCents);
    }

    public function flatCents(): int
    {
        return $this->flatCents;
    }

    public function freeFromCents(): int
    {
        return $this->freeFromCents;
    }
}
