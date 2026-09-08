<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/** A provider call failed — transport, or an API error response. */
final class PaymentFailed extends PaymentException
{
    public function __construct(string $provider, string $reason, public readonly int $status = 0)
    {
        parent::__construct("{$provider}: {$reason} (HTTP {$status})");
    }
}
