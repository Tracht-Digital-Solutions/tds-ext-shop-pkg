<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\ShopModule;

/**
 * The migration files, checked without a database.
 *
 * Every enabled extension's migrations run in ONE process against ONE phinxlog
 * ledger. So a mistake here is not this module's problem — the core's
 * `MigrationRunner` pre-flights filename/class/version collisions and aborts
 * migrations for EVERY module when it finds one. A typo in this directory can
 * therefore stop the whole platform from migrating, on the first request after
 * a deploy, with nothing in this repository failing.
 *
 * Hence: assert the invariants here, where they are cheap, instead of finding
 * out in production.
 */
final class ShopMigrationsTest extends TestCase
{
    /**
     * Version bands already claimed by other modules on this platform.
     *
     * Not exhaustive by construction — it cannot be, this repository cannot see
     * its siblings — but it pins the ones that existed when TDShop was written,
     * so an accidental copy of another module's band fails here.
     */
    private const CLAIMED_BANDS = [
        '20260713', '20260719', '20260720', '20260722', '20260725',
        '20260726', '20260727', '20260728', '20260801', '20260826', '20260901',
    ];

    /**
     * The date bands THIS module claims.
     *
     * A list rather than one value, because a module keeps adding migrations
     * and each lands on the day it was written. Adding a band here is the
     * deliberate act that says "this day is ours" — which is the point, since
     * the whole platform shares one `phinxlog` and a reused version aborts
     * migrations for every module at once.
     */
    private const OUR_BANDS = ['20260907', '20260908', '20260909'];

    /** @return list<string> absolute paths */
    private static function files(): array
    {
        $dir = (new ShopModule())->migrations()[0];
        $files = glob($dir . '/*.php');
        return $files === false ? [] : array_values($files);
    }

    public function testThereAreMigrations(): void
    {
        self::assertNotEmpty(self::files(), 'Keine Migrationsdateien gefunden');
    }

    public function testEveryFileNameMapsToItsClassName(): void
    {
        // Phinx derives the class from the file name. A mismatch does not skip
        // one migration — it aborts the scan for every module sharing the run.
        $problems = [];
        foreach (self::files() as $file) {
            $base = basename($file, '.php');
            [, $snake] = explode('_', $base, 2);
            $expected = str_replace(' ', '', ucwords(str_replace('_', ' ', $snake)));

            $source = (string) file_get_contents($file);
            if (!preg_match('/final class (\w+) extends AbstractMigration/', $source, $m)) {
                $problems[] = "{$base}: keine Migrationsklasse gefunden";
                continue;
            }
            if ($m[1] !== $expected) {
                $problems[] = "{$base}: Klasse {$m[1]}, erwartet {$expected}";
            }
        }
        self::assertSame([], $problems);
    }

    public function testEveryClassIsModulePrefixed(): void
    {
        // One shared ledger means one shared class namespace: `CreateProduct`
        // would collide with any other module's identically obvious name.
        foreach (self::files() as $file) {
            $source = (string) file_get_contents($file);
            preg_match('/final class (\w+) extends AbstractMigration/', $source, $m);
            self::assertStringStartsWith('Shop', $m[1] ?? '', basename($file));
        }
    }

    public function testEveryVersionIsInOurOwnBandAndUnique(): void
    {
        $versions = [];
        foreach (self::files() as $file) {
            $version = explode('_', basename($file), 2)[0];
            $band = substr($version, 0, 8);
            self::assertContains(
                $band,
                self::OUR_BANDS,
                'Band nicht in OUR_BANDS eingetragen: ' . basename($file),
            );
            self::assertNotContains(
                $band,
                self::CLAIMED_BANDS,
                'Band gehört einem anderen Modul: ' . basename($file),
            );
            $versions[] = $version;
        }
        self::assertSame(count($versions), count(array_unique($versions)), 'Doppelte Versionsnummer');
    }

    public function testForeignKeysAreUnsignedToSurviveMysql8(): void
    {
        // Production is MySQL 8, local is often MariaDB, and MariaDB silently
        // corrects the signedness mismatch that MySQL 8 rejects outright. So a
        // migration can pass every local run and fail on the first request
        // after deploy. Every `*_id` column that is not the primary key must
        // carry `'signed' => false`.
        // Only INTEGER `*_id` columns: `external_id` is an ASIN, a string, and
        // signedness means nothing for it. Matching on the name alone flagged
        // it — a reminder that a lint reading names rather than types reports
        // the wrong thing confidently.
        $problems = [];
        foreach (self::files() as $file) {
            foreach (file($file) ?: [] as $n => $line) {
                if (!preg_match("/->addColumn\('(\w+_id)', 'integer'/", $line, $m)) {
                    continue;
                }
                if (!str_contains($line, "'signed' => false")) {
                    $problems[] = basename($file) . ':' . ($n + 1) . " {$m[1]}";
                }
            }
        }
        self::assertSame([], $problems, 'FK-Spalten ohne signed => false');
    }
}
