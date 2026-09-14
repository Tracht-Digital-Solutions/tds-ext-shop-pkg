<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

/**
 * Reads a DATETIME value back as the UTC time it was written as.
 *
 * `price_checked_at` and `published_at` are stamped with `UTC_TIMESTAMP()` or
 * `gmdate()`: UTC wall-clock times with no zone attached. `strtotime()` reads
 * such a string in PHP's default timezone, and the production host runs east of
 * UTC — a price quoted 22 hours ago counted as a day old, and the retrieval time
 * published beside it was off by the same offset. The same reading made every
 * site pairing expire at birth (tds-core-frontend-api 0.19.3).
 *
 * A value that names its own zone (ISO 8601 with `Z` or an offset) is taken as
 * written.
 */
final class UtcDateTime
{
    public static function timestamp(string $value): int|false
    {
        $value = trim($value);
        $utc = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value, new \DateTimeZone('UTC'));
        if ($utc !== false) {
            return $utc->getTimestamp();
        }
        return strtotime($value);
    }
}
