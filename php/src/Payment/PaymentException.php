<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Payment;

/** Base for everything a payment provider can go wrong with. */
abstract class PaymentException extends \RuntimeException
{
}
