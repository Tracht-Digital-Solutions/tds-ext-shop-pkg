<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Support\Shipping;

/**
 * Delivery cost, and the tax rule that is wrong in most shops.
 */
final class ShippingTest extends TestCase
{
    public function testNothingPhysicalCostsNothingToDeliver(): void
    {
        $shipping = new Shipping(495, 0);
        self::assertSame(['net' => 0, 'tax' => 0, 'gross' => 0], $shipping->forLines([]));
    }

    public function testASingleRateBasketIsJustTheRate(): void
    {
        $shipping = new Shipping(495, 0);
        // 495 net at 19 % → 94 tax (rounded half up), 589 gross.
        self::assertSame(
            ['net' => 495, 'tax' => 94, 'gross' => 589],
            $shipping->forLines([['net' => 10000, 'rate' => 1900]]),
        );
    }

    public function testTheFreeShippingThresholdIsOnTheGoodsNet(): void
    {
        $shipping = new Shipping(495, 5000);
        self::assertSame(0, $shipping->forLines([['net' => 5000, 'rate' => 1900]])['gross']);
        self::assertSame(589, $shipping->forLines([['net' => 4999, 'rate' => 1900]])['gross']);
    }

    public function testAMixedRateBasketApportionsTheShippingTax(): void
    {
        // Shipping is an ancillary service: it takes the rate of the goods it
        // delivers (Abschn. 3.10 UStAE). Half the net at 7 % and half at 19 %
        // means the charge is taxed in two parts — not at a blanket 19 %, which
        // is the common shortcut and wrong in someone's favour depending on the
        // mix. Nobody notices until a VAT audit does.
        $shipping = new Shipping(1000, 0);
        $result = $shipping->forLines([
            ['net' => 5000, 'rate' => 700],
            ['net' => 5000, 'rate' => 1900],
        ]);

        self::assertSame(1000, $result['net']);
        // 500 @ 7 % = 35, 500 @ 19 % = 95 → 130. A blanket 19 % would be 190.
        self::assertSame(130, $result['tax']);
        self::assertSame(1130, $result['gross']);
    }

    public function testTheApportionedPartsAlwaysAddBackToTheCharge(): void
    {
        // The invoice line and the tax lines have to reconcile to the cent, so
        // the last bucket absorbs the rounding remainder. A charge that does not
        // equal the sum of its parts is an accounting problem, not a rounding
        // preference.
        $shipping = new Shipping(999, 0);
        for ($a = 1; $a <= 9; $a++) {
            $result = $shipping->forLines([
                ['net' => $a * 111, 'rate' => 700],
                ['net' => (10 - $a) * 111, 'rate' => 1900],
            ]);
            self::assertSame(999, $result['net'], "net changed at split {$a}");
            self::assertSame($result['net'] + $result['tax'], $result['gross'], "gross broke at split {$a}");
        }
    }

    public function testTheSplitIsDeterministic(): void
    {
        // Same basket, same numbers — the remainder must land in a predictable
        // bucket rather than wherever the array happened to be ordered.
        $shipping = new Shipping(997, 0);
        $lines = [['net' => 3333, 'rate' => 1900], ['net' => 1111, 'rate' => 700]];
        self::assertSame($shipping->forLines($lines), $shipping->forLines(array_reverse($lines)));
    }

    public function testNoChargeConfiguredMeansFreeDelivery(): void
    {
        $shipping = new Shipping(0, 0);
        self::assertTrue($shipping->isFreeAt(100000));
        self::assertSame(0, $shipping->forLines([['net' => 100, 'rate' => 1900]])['gross']);
    }
}
