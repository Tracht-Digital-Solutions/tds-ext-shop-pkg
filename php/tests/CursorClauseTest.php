<?php

declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Domain\ProductRepository;

/**
 * The catalogue's keyset cursor.
 *
 * The host runs PDO with native prepares (`ATTR_EMULATE_PREPARES => false`),
 * where a named parameter may occur only ONCE per statement. The clause used
 * `:cur_date` twice; the statement threw, the fail-soft route answered an empty
 * page, and every product after the 48th was unreachable. Nothing else shows it.
 */
final class CursorClauseTest extends TestCase
{
    public function test_every_named_parameter_occurs_once_and_is_bound(): void
    {
        $method = new \ReflectionMethod(ProductRepository::class, 'cursorClause');
        $cursor = rtrim(strtr(base64_encode('2026-10-10 13:55:16|8'), '+/', '-_'), '=');
        [$sql, $args] = $method->invoke(null, $cursor);

        preg_match_all('/:([a-z_0-9]+)/', (string) $sql, $m);
        self::assertSame(array_unique($m[1]), $m[1], 'a named parameter is reused');
        self::assertEqualsCanonicalizing($m[1], array_keys($args), 'every placeholder has exactly one binding');
        self::assertSame(8, $args['cur_id']);
    }
}
