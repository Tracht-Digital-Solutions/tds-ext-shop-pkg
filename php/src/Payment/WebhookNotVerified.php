<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/**
 * A webhook whose signature did not check out.
 *
 * Answered with 400 and nothing else. Do not log the body and do not say which
 * part failed: an endpoint that explains why a forgery was rejected is an
 * oracle for producing one that is not.
 */
final class WebhookNotVerified extends PaymentException
{
    public function __construct(string $provider)
    {
        parent::__construct("{$provider}: webhook signature not verified");
    }
}
