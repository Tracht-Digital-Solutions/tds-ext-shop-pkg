<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Tests;

use PHPUnit\Framework\TestCase;
use Tds\Ext\Shop\Support\CategoryName;

/**
 * The fallback order for a category's display name.
 *
 * The English shop showed German category names for as long as this did not
 * exist, and nothing flagged it: a German word in an English chip renders
 * perfectly. These pin the order so a later "simplification" cannot quietly
 * put the slug ahead of a name somebody wrote.
 */
final class CategoryNameTest extends TestCase
{
    public function testUsesTheNameInTheRequestedLanguage(): void
    {
        self::assertSame('Networking', CategoryName::resolve('netzwerk', 'Netzwerk', 'Networking', 'en'));
        self::assertSame('Netzwerk', CategoryName::resolve('netzwerk', 'Netzwerk', 'Networking', 'de'));
    }

    public function testFallsBackToTheGermanNameOnTheEnglishPage(): void
    {
        self::assertSame('Netzwerk', CategoryName::resolve('netzwerk', 'Netzwerk', null, 'en'));
    }

    public function testNeverFallsBackFromGermanToEnglish(): void
    {
        // German-first: an English name must not surface on the German shop.
        self::assertSame('Netzwerk', CategoryName::resolve('netzwerk', null, 'Networking', 'de'));
    }

    public function testFallsBackToTheCapitalisedSlugWhenNothingIsNamed(): void
    {
        self::assertSame('Netzwerk', CategoryName::resolve('netzwerk', null, null, 'en'));
        self::assertSame('Smart home', CategoryName::resolve('smart-home', '  ', '', 'de'));
    }

    public function testCleansIncomingNames(): void
    {
        self::assertSame('Netzwerk', CategoryName::clean('  Netzwerk '));
        self::assertNull(CategoryName::clean('   '));
        self::assertNull(CategoryName::clean(42));
    }

    public function testTheSlugPatternMatchesTheShopsCategoryRoute(): void
    {
        // tds-shop-frontend's src/pages/kategorie/[cat].astro: /^[a-z0-9-]{2,60}$/
        self::assertSame(1, preg_match(CategoryName::SLUG_PATTERN, 'smart-home'));
        self::assertSame(0, preg_match(CategoryName::SLUG_PATTERN, 'Netzwerk & WLAN'));
        self::assertSame(0, preg_match(CategoryName::SLUG_PATTERN, 'x'));
        self::assertSame(0, preg_match(CategoryName::SLUG_PATTERN, str_repeat('a', 61)));
    }
}
