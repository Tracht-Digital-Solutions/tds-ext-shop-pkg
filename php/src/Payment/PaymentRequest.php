<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/**
 * What a provider needs to start a payment, in provider-neutral terms.
 *
 * Everything here is computed on OUR server. In particular `$grossCents` comes
 * from the stored net price and the stored VAT rate — never from the request.
 * A posted price is a price the customer chose, and the one thing every
 * provider will happily charge is whatever number it is handed.
 */
final class PaymentRequest
{
    /**
     * @param string                $orderNo     human-readable, on the invoice
     * @param string                $token       our order token; travels as the
     *                                           provider's customer reference and
     *                                           is what comes back in a webhook
     * @param string                $description one line, shown at the provider
     * @param int                   $grossCents  the total the customer pays
     * @param string                $currency    ISO 4217, upper case
     * @param string                $email
     * @param string                $successUrl  where the customer lands after paying
     * @param string                $cancelUrl   where an abandoned payment returns to
     * @param array<string,string>  $metadata    carried through to the webhook
     */
    public function __construct(
        public readonly string $orderNo,
        public readonly string $token,
        public readonly string $description,
        public readonly int $grossCents,
        public readonly string $currency,
        public readonly string $email,
        public readonly string $successUrl,
        public readonly string $cancelUrl,
        public readonly array $metadata = [],
    ) {
    }

    /**
     * The amount as a decimal string, e.g. `49.99`.
     *
     * Stripe takes minor units; PayPal and most European A2A providers take a
     * decimal string. Doing the conversion here, once, keeps every provider
     * from re-deriving it — and keeps the derivation out of float arithmetic,
     * where 4999/100 is famously not 49.99.
     */
    public function amountDecimal(): string
    {
        $sign = $this->grossCents < 0 ? '-' : '';
        $abs = abs($this->grossCents);
        return $sign . intdiv($abs, 100) . '.' . str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT);
    }
}
