<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Support\Withdrawal;

/**
 * The two withdrawal regimes, and what delivery costs.
 *
 * These moved out of `ShopModuleTest` when the checkout learned about baskets.
 * The consent requirement is no longer a constant — it depends on WHAT is in
 * the basket — so it can no longer be asserted through a route with no
 * database behind it. The rule itself is pure, and this is where it lives now.
 */
final class WithdrawalTest extends TestCase
{
    public function testAServiceBasketNeedsTheEarlyPerformanceConsent(): void
    {
        // § 356 Abs. 4 BGB: the right lapses on full performance only if the
        // customer expressly asked for it to begin early AND confirmed they
        // knew that costs them the right. Without both, TDS performs and the
        // customer may still withdraw.
        $regime = Withdrawal::regime(hasDigital: true, hasPhysical: false);
        self::assertSame(Withdrawal::DIGITAL, $regime);
        self::assertTrue(Withdrawal::requiresConsent($regime));
    }

    public function testAGoodsBasketMustNotBeAskedForConsentAtAll(): void
    {
        // The one that is wrong in most shops. The 14-day right on goods is not
        // the customer's to give up, so a tick box for it is a consent with no
        // legal object — demanding it looks like diligence and is the wrong
        // question asked of half the customers.
        $regime = Withdrawal::regime(hasDigital: false, hasPhysical: true);
        self::assertSame(Withdrawal::GOODS, $regime);
        self::assertFalse(Withdrawal::requiresConsent($regime));
    }

    public function testAMixedBasketNeedsTheConsentForItsServiceHalfOnly(): void
    {
        $regime = Withdrawal::regime(hasDigital: true, hasPhysical: true);
        self::assertSame(Withdrawal::MIXED, $regime);
        self::assertTrue(Withdrawal::requiresConsent($regime));

        // …and the wording has to say that the goods half is untouched, or the
        // customer has agreed to something broader than what was meant.
        self::assertStringContainsString('Waren', Withdrawal::defaultText(Withdrawal::MIXED));
        self::assertStringContainsString('vierzehn', Withdrawal::defaultText(Withdrawal::MIXED));
    }

    public function testEveryRegimeHasAWordingAndTheyDiffer(): void
    {
        $texts = array_map(
            static fn (string $r): string => Withdrawal::defaultText($r),
            [Withdrawal::DIGITAL, Withdrawal::GOODS, Withdrawal::MIXED],
        );
        foreach ($texts as $text) {
            self::assertNotSame('', trim($text));
        }
        self::assertSame(3, count(array_unique($texts)), 'a regime is reusing another one\'s wording');
    }

    public function testTheGoodsWordingIsInformationRatherThanAWaiver(): void
    {
        // Nothing is agreed for goods. What is stored is the INFORMATION given
        // before the order (Art. 246a § 1 Abs. 2 EGBGB), and it must not read
        // like a waiver — the digital wording's "verliere" has no business
        // being on a parcel.
        $goods = Withdrawal::defaultText(Withdrawal::GOODS);
        self::assertStringNotContainsString('verliere', $goods);
        self::assertStringContainsString('vierzehn Tagen', $goods);
    }
}
