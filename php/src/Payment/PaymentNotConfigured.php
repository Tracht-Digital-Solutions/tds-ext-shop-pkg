<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/**
 * Credentials are missing.
 *
 * Answered with 503, never 500: nothing is broken, the shop simply cannot take
 * this payment method right now, and a monitoring page should say so rather
 * than page somebody.
 */
final class PaymentNotConfigured extends PaymentException
{
    public function __construct(string $provider)
    {
        parent::__construct("{$provider}: not configured");
    }
}
