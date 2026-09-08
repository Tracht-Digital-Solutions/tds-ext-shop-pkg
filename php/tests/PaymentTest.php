<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Payment\PaymentEvent;
use Tds\Ext\Shop\Payment\PaymentNotConfigured;
use Tds\Ext\Shop\Payment\PaymentRegistry;
use Tds\Ext\Shop\Payment\PaymentRequest;
use Tds\Ext\Shop\Payment\PayPalProvider;
use Tds\Ext\Shop\Payment\StripeProvider;
use Tds\Ext\Shop\Payment\WebhookNotVerified;
use Tds\Ext\Shop\Payment\WeroProvider;
use Tds\Ext\Shop\Service\StripeClient;

/**
 * The payment abstraction.
 *
 * Two things here are worth more than the rest of the file. The registry's
 * configured/registered distinction is what lets an unfinished adapter live in
 * the tree without endangering a live shop — if that breaks, Wero becomes
 * selectable and a customer can start a payment nothing can finish. And the
 * six webhook cases are the ones that decide whether a stranger can mark an
 * order paid; they were already pinned at the `WebhookVerifier` level, and
 * they are pinned again HERE because the provider is what the route now calls,
 * and a wrapper can lose a check that the thing it wraps still performs.
 */
final class PaymentTest extends TestCase
{
    private const SECRET = 'whsec_test_secret';

    private static function sign(string $payload, int $timestamp, string $secret = self::SECRET): string
    {
        return 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
    }

    /** @param array<string,mixed> $event */
    private static function stripeEvent(array $event, ?string $secret = null): array
    {
        $payload = json_encode($event, JSON_THROW_ON_ERROR);
        return [$payload, ['stripe-signature' => self::sign($payload, time(), $secret ?? self::SECRET)]];
    }

    private static function stripe(string $secret = self::SECRET): StripeProvider
    {
        return new StripeProvider(new StripeClient('sk_test_x'), $secret);
    }

    /* --- the amount ---------------------------------------------------- */

    public function testMinorUnitsBecomeADecimalStringWithoutFloatArithmetic(): void
    {
        // PayPal and most European A2A providers take `49.99`, not 4999. Doing
        // it with floats is how 4999/100 stops being 49.99.
        $cases = [4999 => '49.99', 100 => '1.00', 5 => '0.05', 0 => '0.00', 123456 => '1234.56'];
        foreach ($cases as $cents => $expected) {
            self::assertSame($expected, self::request($cents)->amountDecimal(), "for {$cents} cents");
        }
    }

    private static function request(int $cents): PaymentRequest
    {
        return new PaymentRequest(
            'TDS-20260909-ABC123',
            str_repeat('a', 32),
            'Ein Paket',
            $cents,
            'EUR',
            'kunde@example.de',
            'https://shop.example/bestellung/x',
            'https://shop.example/produkt/y',
        );
    }

    /* --- the registry --------------------------------------------------- */

    public function testAnUnconfiguredProviderIsNeverOffered(): void
    {
        // The single gate the whole "seat an unfinished adapter" idea rests on.
        $registry = new PaymentRegistry([self::stripe(), new WeroProvider()]);

        $ids = array_column($registry->configured(), 'id');
        self::assertSame(['stripe'], $ids);
        self::assertNull($registry->usable('wero'));
    }

    public function testAWebhookStillReachesAnUnconfiguredProvider(): void
    {
        // `get()` deliberately returns it: an endpoint that 404s when its
        // secret is missing is indistinguishable from a wrong URL, and the
        // provider's own 503 says the useful thing.
        $registry = new PaymentRegistry([new WeroProvider()]);
        self::assertNotNull($registry->get('wero'));
        self::assertNull($registry->usable('wero'));
    }

    public function testTheDefaultIsTheFirstCONFIGUREDProvider(): void
    {
        // Not simply the first registered one — otherwise a checkout with no
        // stated preference would pick an adapter that cannot complete.
        $registry = new PaymentRegistry([new WeroProvider(), self::stripe()]);
        self::assertSame('stripe', $registry->defaultId());
    }

    public function testAnUnknownProviderIsNotUsable(): void
    {
        $registry = new PaymentRegistry([self::stripe()]);
        self::assertNull($registry->get('klarna'));
        self::assertNull($registry->usable('klarna'));
    }

    public function testWithNothingConfiguredThereIsNoDefault(): void
    {
        // The checkout answers 503 off this, rather than starting a payment
        // with a provider it picked out of an empty list.
        $registry = new PaymentRegistry([new WeroProvider()]);
        self::assertSame([], $registry->configured());
        self::assertNull($registry->defaultId());
    }

    /* --- Stripe: the six cases, at the provider level -------------------- */

    public function testAcceptsAGenuineSignatureAndReportsPaid(): void
    {
        [$payload, $headers] = self::stripeEvent([
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_test_1',
                'payment_intent' => 'pi_test_1',
                'metadata' => ['token' => 'tok_abc'],
            ]],
        ]);

        $event = self::stripe()->receiveWebhook($payload, $headers);

        self::assertNotNull($event);
        self::assertSame(PaymentEvent::PAID, $event->kind);
        self::assertSame('cs_test_1', $event->reference);
        self::assertSame('pi_test_1', $event->paymentRef);
        // Our own identifier, echoed back through metadata — preferred over
        // the provider's when the order is matched.
        self::assertSame('tok_abc', $event->orderToken);
    }

    public function testRejectsATamperedPayload(): void
    {
        [$payload, $headers] = self::stripeEvent(['type' => 'checkout.session.completed']);
        $this->expectException(WebhookNotVerified::class);
        self::stripe()->receiveWebhook($payload . ' ', $headers);
    }

    public function testRejectsAForeignSecret(): void
    {
        [$payload, $headers] = self::stripeEvent(
            ['type' => 'checkout.session.completed'],
            'someone_elses_secret',
        );
        $this->expectException(WebhookNotVerified::class);
        self::stripe()->receiveWebhook($payload, $headers);
    }

    public function testRejectsAReplayOutsideTheToleranceWindow(): void
    {
        // The bytes and the signature are genuine; only the age is wrong.
        $payload = '{"type":"checkout.session.completed"}';
        $headers = ['stripe-signature' => self::sign($payload, time() - 3600)];
        $this->expectException(WebhookNotVerified::class);
        self::stripe()->receiveWebhook($payload, $headers);
    }

    public function testFailsClosedWithoutASecretRatherThanAcceptingEverything(): void
    {
        // Distinct from a bad signature on purpose: this is 503, "we cannot
        // verify right now", not 400, "this is a forgery".
        [$payload, $headers] = self::stripeEvent(['type' => 'checkout.session.completed']);
        $this->expectException(PaymentNotConfigured::class);
        (new StripeProvider(new StripeClient('sk_test_x'), ''))->receiveWebhook($payload, $headers);
    }

    public function testRejectsAMalformedSignatureHeader(): void
    {
        $this->expectException(WebhookNotVerified::class);
        self::stripe()->receiveWebhook('{"type":"x"}', ['stripe-signature' => 'garbage']);
    }

    public function testRejectsAMissingSignatureHeader(): void
    {
        $this->expectException(WebhookNotVerified::class);
        self::stripe()->receiveWebhook('{"type":"x"}', []);
    }

    /* --- Stripe: which events are acted on ------------------------------- */

    public function testAVerifiedButUninterestingEventIsIgnoredRatherThanRejected(): void
    {
        // Null, not an exception: the route answers 200, or Stripe retries the
        // same irrelevant event forever.
        [$payload, $headers] = self::stripeEvent(['type' => 'customer.created', 'data' => ['object' => []]]);
        self::assertNull(self::stripe()->receiveWebhook($payload, $headers));
    }

    public function testARefundIsReportedAgainstThePaymentReference(): void
    {
        [$payload, $headers] = self::stripeEvent([
            'type' => 'charge.refunded',
            'data' => ['object' => ['payment_intent' => 'pi_test_1']],
        ]);

        $event = self::stripe()->receiveWebhook($payload, $headers);

        self::assertNotNull($event);
        self::assertSame(PaymentEvent::REFUNDED, $event->kind);
        self::assertSame('pi_test_1', $event->paymentRef);
    }

    public function testStripeWithoutAKeyIsNotConfigured(): void
    {
        self::assertFalse((new StripeProvider(null, self::SECRET))->isConfigured());
        self::assertFalse((new StripeProvider(new StripeClient(''), self::SECRET))->isConfigured());
    }

    /* --- PayPal ---------------------------------------------------------- */

    public function testPayPalNeedsItsWebhookIdToCountAsConfigured(): void
    {
        // Credentials alone would let it START a payment it could never
        // confirm — the customer pays and the order sits at `pending` forever.
        // That is worse than the method simply not being offered.
        self::assertFalse((new PayPalProvider('id', 'secret', ''))->isConfigured());
        self::assertFalse((new PayPalProvider('', 'secret', 'wh'))->isConfigured());
        self::assertFalse((new PayPalProvider('id', '', 'wh'))->isConfigured());
        self::assertTrue((new PayPalProvider('id', 'secret', 'wh'))->isConfigured());
    }

    public function testPayPalRefusesToStartWithoutCredentials(): void
    {
        $this->expectException(PaymentNotConfigured::class);
        (new PayPalProvider('', '', ''))->start(self::request(4999));
    }

    public function testPayPalSandboxAndLiveAreDifferentHosts(): void
    {
        // Cheap, and it has been wrong elsewhere: a sandbox constant pointing
        // at the live host takes real money in a test.
        self::assertNotSame(PayPalProvider::LIVE, PayPalProvider::SANDBOX);
        self::assertStringContainsString('sandbox', PayPalProvider::SANDBOX);
        self::assertStringNotContainsString('sandbox', PayPalProvider::LIVE);
    }

    /* --- Wero ------------------------------------------------------------ */

    public function testWeroIsInvisibleUntilAPspIsConfigured(): void
    {
        self::assertFalse((new WeroProvider())->isConfigured());
        self::assertFalse((new WeroProvider('payone', 'key', ''))->isConfigured());
        self::assertTrue((new WeroProvider('payone', 'key', 'secret'))->isConfigured());
    }

    public function testWeroRefusesBothLiveMethodsRatherThanPretending(): void
    {
        $wero = new WeroProvider();

        try {
            $wero->start(self::request(4999));
            self::fail('start() should refuse while the adapter is unfinished');
        } catch (PaymentNotConfigured) {
            self::assertTrue(true);
        }

        // Fails CLOSED. An endpoint that accepted unverifiable webhooks would
        // be a way to mark any order paid, and "not finished yet" is not a
        // reason to make that reachable.
        $this->expectException(PaymentNotConfigured::class);
        $wero->receiveWebhook('{}', []);
    }

    public function testEveryProviderKeepsItsIdStable(): void
    {
        // These strings are in URLs and in a database column. Renaming one
        // orphans every row that carries it.
        self::assertSame('stripe', self::stripe()->id());
        self::assertSame('paypal', (new PayPalProvider('a', 'b', 'c'))->id());
        self::assertSame('wero', (new WeroProvider())->id());
    }
}
