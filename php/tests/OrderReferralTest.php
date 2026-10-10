<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Support\OrderReferral;
use Tds\Frontend\Contract\Commerce\ReferralMatch;
use Tds\Frontend\Contract\Commerce\ReferralResolver;
use Tds\Frontend\Contract\Commerce\SaleEvent;
use Tds\Frontend\Contract\Commerce\SaleEvents;
use Tds\Frontend\Contract\Commerce\SaleListener;

/**
 * The shop records who recommended a purchase and reports the paid order. It
 * keeps no commission logic — that is the referral module's, behind the
 * contract — so what is tested here is the hand-over in both directions.
 */
final class OrderReferralTest extends TestCase
{
    private static function events(): SaleEvents
    {
        $resolver = new class implements ReferralResolver {
            public function resolveReferral(string $code): ?ReferralMatch
            {
                return strtoupper($code) === 'ANNA-1' ? new ReferralMatch('ANNA-1', 'Anna B.') : null;
            }
        };
        return new SaleEvents([], [$resolver]);
    }

    public function testAKnownCodeIsStoredInItsCanonicalSpelling(): void
    {
        $ref = OrderReferral::fromCheckout(['referral' => ['code' => 'anna-1', 'via' => 'link']], self::events());
        self::assertSame(['code' => 'ANNA-1', 'via' => 'link', 'note' => null], $ref);

        $typed = OrderReferral::fromCheckout(['referral' => ['code' => 'ANNA-1', 'via' => 'whatever']], self::events());
        self::assertSame('code', $typed['via'], 'anything but link counts as typed');
    }

    public function testAnUnknownCodeIsDroppedButTheNoteSurvives(): void
    {
        $ref = OrderReferral::fromCheckout([
            'referral' => ['code' => 'NOBODY', 'via' => 'code'],
            'referredBy' => '  Mein Nachbar Bernd ',
        ], self::events());
        self::assertSame(['code' => null, 'via' => null, 'note' => 'Mein Nachbar Bernd'], $ref);
    }

    public function testWithoutAReferralModuleNoCodeIsStored(): void
    {
        $ref = OrderReferral::fromCheckout(['referral' => ['code' => 'ANNA-1']], null);
        self::assertNull($ref['code']);
    }

    public function testMalformedInputIsIgnored(): void
    {
        $ref = OrderReferral::fromCheckout(['referral' => 'ANNA-1', 'referredBy' => str_repeat('x', 900)], self::events());
        self::assertNull($ref['code']);
        self::assertSame(500, mb_strlen((string) $ref['note']));
    }

    public function testThePaidOrderIsReportedWithGoodsNetOnly(): void
    {
        $order = [
            'id' => 42,
            'email' => 'buyer@example.org',
            'net_cents' => 10495, // includes 495 shipping
            'referral_code' => 'ANNA-1',
            'referral_via' => 'link',
            'referred_by_note' => '',
        ];
        $items = [
            ['product_id' => 7, 'net_cents' => 6000, 'title' => 'Website-Check'],
            ['product_id' => 9, 'net_cents' => 4000, 'title' => 'Router'],
        ];
        $sale = OrderReferral::saleEvent($order, $items);

        self::assertSame('shop', $sale->source);
        self::assertSame('42', $sale->sourceId);
        self::assertSame(10000, $sale->netCents, 'shipping is not something a partner brought in');
        self::assertSame('7', $sale->lines[0]->productId);
        self::assertSame('ANNA-1', $sale->referralCode);
        self::assertSame('link', $sale->referralVia);
        self::assertNull($sale->referredByNote);
    }

    public function testDispatchReachesAListener(): void
    {
        $seen = new \ArrayObject();
        $listener = new class ($seen) implements SaleListener {
            public function __construct(private readonly \ArrayObject $seen)
            {
            }

            public function onSalePaid(SaleEvent $sale): void
            {
                $this->seen[] = $sale->sourceId;
            }

            public function onSaleReversed(string $source, string $sourceId): void
            {
            }
        };
        (new SaleEvents([$listener]))->paid(OrderReferral::saleEvent(['id' => 5], []));
        self::assertSame(['5'], $seen->getArrayCopy());
    }
}
