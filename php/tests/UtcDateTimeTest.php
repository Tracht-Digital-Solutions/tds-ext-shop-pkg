<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Support\UtcDateTime;

/**
 * DATETIME values written by UTC_TIMESTAMP() or gmdate() carry no zone. They
 * must read back as UTC whatever PHP's default timezone is — CLI PHP on a dev
 * machine defaults to UTC, so only a test that sets another zone sees the bug.
 */
final class UtcDateTimeTest extends TestCase
{
    private string $timezone = 'UTC';

    protected function setUp(): void
    {
        $this->timezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Berlin');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->timezone);
    }

    public function testAZonelessDatabaseValueIsUtc(): void
    {
        self::assertSame(gmmktime(20, 0, 0, 9, 13, 2026), UtcDateTime::timestamp('2026-09-13 20:00:00'));
    }

    public function testAValueThatNamesItsZoneIsTakenAsWritten(): void
    {
        self::assertSame(gmmktime(20, 0, 0, 9, 13, 2026), UtcDateTime::timestamp('2026-09-13T22:00:00+02:00'));
        self::assertSame(gmmktime(20, 0, 0, 9, 13, 2026), UtcDateTime::timestamp('2026-09-13T20:00:00Z'));
    }

    public function testGarbageIsNotATime(): void
    {
        self::assertFalse(UtcDateTime::timestamp('not-a-date'));
    }
}
