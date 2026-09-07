<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Support\PriceFreshness;

/**
 * The 24-hour rule, server side.
 *
 * This is the licence term the Amazon partner programme is revoked over, and
 * its failure mode is invisible: a price from last week renders perfectly.
 * There is no visual regression to catch it and no exception to log, so the
 * test is the only thing standing between a working page and a closed account.
 *
 * `tds-shared`'s `isPriceStale()` enforces the same rule in the renderer. The
 * duplication is deliberate — see the class doc on {@see PriceFreshness}.
 */
final class PriceFreshnessTest extends TestCase
{
    private const NOW = 1788000000; // fixed clock; the rule is about elapsed time

    public function testAQuoteInsideTheWindowIsFresh(): void
    {
        $checked = gmdate('Y-m-d H:i:s', self::NOW - 23 * 3600);
        self::assertTrue(PriceFreshness::isFresh($checked, self::NOW));
        self::assertSame(4999, PriceFreshness::publishablePrice(4999, $checked, self::NOW));
    }

    public function testAQuoteBeyondTheWindowIsNot(): void
    {
        $checked = gmdate('Y-m-d H:i:s', self::NOW - 25 * 3600);
        self::assertFalse(PriceFreshness::isFresh($checked, self::NOW));
        self::assertNull(PriceFreshness::publishablePrice(4999, $checked, self::NOW));
    }

    public function testTheBoundaryIsInclusive(): void
    {
        // Guards the comparison operator. Exactly 24 hours old is still
        // permitted; one second later is not.
        $exact = gmdate('Y-m-d H:i:s', self::NOW - PriceFreshness::MAX_AGE_SECONDS);
        $past = gmdate('Y-m-d H:i:s', self::NOW - PriceFreshness::MAX_AGE_SECONDS - 1);
        self::assertTrue(PriceFreshness::isFresh($exact, self::NOW));
        self::assertFalse(PriceFreshness::isFresh($past, self::NOW));
    }

    public function testANeverCheckedQuoteIsNotFresh(): void
    {
        // The dangerous default. If null meant "no expiry yet", an unverified
        // price would be displayed forever.
        self::assertFalse(PriceFreshness::isFresh(null, self::NOW));
        self::assertFalse(PriceFreshness::isFresh('', self::NOW));
        self::assertNull(PriceFreshness::publishablePrice(4999, null, self::NOW));
    }

    public function testAnUnparseableTimestampIsNotFresh(): void
    {
        // A malformed row must degrade to "no price", never throw: it would
        // take down the whole product grid over one bad offer.
        self::assertFalse(PriceFreshness::isFresh('not-a-date', self::NOW));
        self::assertNull(PriceFreshness::publishablePrice(4999, 'not-a-date', self::NOW));
    }

    public function testAMissingPriceStaysNullEvenWhenTheTimestampIsFresh(): void
    {
        $checked = gmdate('Y-m-d H:i:s', self::NOW - 60);
        self::assertNull(PriceFreshness::publishablePrice(null, $checked, self::NOW));
    }

    public function testAFreePriceIsNotConfusedWithAMissingOne(): void
    {
        // 0 is a real price. `if (!$priceCents)` would silently drop it.
        $checked = gmdate('Y-m-d H:i:s', self::NOW - 60);
        self::assertSame(0, PriceFreshness::publishablePrice(0, $checked, self::NOW));
    }
}
