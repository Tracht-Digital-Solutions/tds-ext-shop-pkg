<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Service;

/** A failed Stripe API call. */
final class StripeException extends \RuntimeException
{
    public function __construct(string $reason, public readonly int $status)
    {
        parent::__construct("Stripe: {$reason} (HTTP {$status})");
    }
}
