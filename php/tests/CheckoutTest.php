<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Domain\OrderRepository;
use Tds\Ext\Shop\Service\WebhookVerifier;

/**
 * The checkout's two load-bearing pieces: the money, and the webhook.
 *
 * Both fail quietly when they are wrong. A cent lost to rounding shows up in a
 * VAT return months later; an unverified webhook marks orders paid for anybody
 * who can POST.
 */
final class CheckoutTest extends TestCase
{
    /* --- money ------------------------------------------------------------ */

    public function testGrossIsNetPlusTaxAtNineteenPercent(): void
    {
        self::assertSame(
            ['net' => 10000, 'tax' => 1900, 'gross' => 11900],
            OrderRepository::price(10000, 1900),
        );
    }

    public function testTaxIsRoundedHalfUpOnTheTaxItself(): void
    {
        // 49.99 € net at 19 % is 9.4981 € — the tax rounds to 9.50 and the
        // gross follows. Computing a gross first and deriving the tax back out
        // of it loses a cent on roughly a third of amounts, and it is the NET
        // figure a VAT return is built from.
        self::assertSame(
            ['net' => 4999, 'tax' => 950, 'gross' => 5949],
            OrderRepository::price(4999, 1900),
        );
    }

    public function testEveryAmountIsAnIntegerNumberOfCents(): void
    {
        // Money is never a float here. This is the assertion that catches a
        // well-meant refactor to `float` before an invoice does.
        foreach ([1, 99, 4999, 123456] as $net) {
            foreach (OrderRepository::price($net, 1900) as $part) {
                self::assertIsInt($part);
            }
        }
    }

    public function testAZeroRateProducesNoTax(): void
    {
        // The shape a Kleinunternehmer (§ 19 UStG) would run in. It is a
        // configuration of the same code path, not a second one.
        self::assertSame(['net' => 5000, 'tax' => 0, 'gross' => 5000], OrderRepository::price(5000, 0));
    }

    /* --- webhook ---------------------------------------------------------- */

    private const SECRET = 'whsec_test_secret';

    private static function sign(string $payload, int $timestamp, string $secret = self::SECRET): string
    {
        return 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
    }

    public function testAcceptsAGenuineSignature(): void
    {
        $payload = '{"type":"checkout.session.completed"}';
        $now = 1788782400;
        self::assertTrue(
            WebhookVerifier::verify($payload, self::sign($payload, $now), self::SECRET, 300, $now),
        );
    }

    public function testRejectsATamperedPayload(): void
    {
        // The whole point: the signature covers the exact bytes, so a payload
        // edited in flight no longer verifies.
        $now = 1788782400;
        $header = self::sign('{"type":"checkout.session.completed"}', $now);
        self::assertFalse(
            WebhookVerifier::verify('{"type":"checkout.session.completed","x":1}', $header, self::SECRET, 300, $now),
        );
    }

    public function testRejectsAForeignSecret(): void
    {
        $payload = '{"a":1}';
        $now = 1788782400;
        self::assertFalse(
            WebhookVerifier::verify($payload, self::sign($payload, $now, 'someone_elses'), self::SECRET, 300, $now),
        );
    }

    public function testRejectsAReplayOutsideTheToleranceWindow(): void
    {
        // A captured-and-replayed request is otherwise perfectly valid — the
        // signature still matches. Only the timestamp catches it.
        $payload = '{"a":1}';
        $signedAt = 1788782400;
        self::assertFalse(
            WebhookVerifier::verify($payload, self::sign($payload, $signedAt), self::SECRET, 300, $signedAt + 600),
        );
        self::assertTrue(
            WebhookVerifier::verify($payload, self::sign($payload, $signedAt), self::SECRET, 300, $signedAt + 60),
        );
    }

    public function testRejectsAnEmptySecretRatherThanAcceptingEverything(): void
    {
        // The dangerous default. An unconfigured secret must fail closed —
        // otherwise a host that forgot to set it accepts any POST as payment.
        $payload = '{"a":1}';
        $now = 1788782400;
        self::assertFalse(WebhookVerifier::verify($payload, self::sign($payload, $now), '', 300, $now));
        self::assertFalse(WebhookVerifier::verify($payload, '', self::SECRET, 300, $now));
    }

    public function testRejectsAMalformedHeader(): void
    {
        $now = 1788782400;
        foreach (['nonsense', 't=,v1=', 'v1=abc', 't=abc,v1=def'] as $header) {
            self::assertFalse(
                WebhookVerifier::verify('{"a":1}', $header, self::SECRET, 300, $now),
                $header,
            );
        }
    }
}
