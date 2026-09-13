<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

/**
 * What a reader sees for a category slug.
 *
 * One place for the fallback order, because three responses carry a category
 * name (the catalogue, one product, the category list) and a name resolved two
 * different ways is a chip that says "Networking" above a heading that says
 * "Netzwerk".
 *
 * The order: the name in the requested language, then the German name, then
 * the slug with a capital first letter. German second because the shop is
 * German-first and a German name on the English page is still a name somebody
 * chose; the slug last because it is the only thing that always exists.
 *
 * The slug fallback mirrors `categoryLabel()` in tds-shop-frontend's
 * `src/lib/i18n.ts` — the site uses it when talking to an older API build that
 * serves no name at all, and the two must agree.
 */
final class CategoryName
{
    /**
     * A category slug the shop can route. The site's `/kategorie/{slug}` and
     * `/en/category/{slug}` pages accept exactly this and answer 404 otherwise.
     */
    public const SLUG_PATTERN = '/^[a-z0-9-]{2,60}$/';

    /** Matches `shop_category.name_de` / `name_en`. */
    public const MAX_LENGTH = 80;

    public static function resolve(string $slug, ?string $nameDe, ?string $nameEn, string $lang): string
    {
        $preferred = $lang === 'en' ? $nameEn : $nameDe;
        foreach ([$preferred, $nameDe] as $name) {
            $name = self::clean($name);
            if ($name !== null) {
                return $name;
            }
        }
        return self::fromSlug($slug);
    }

    /** "netzwerk" → "Netzwerk", "smart-home" → "Smart home". First letter only. */
    public static function fromSlug(string $slug): string
    {
        $words = trim(str_replace('-', ' ', $slug));
        if ($words === '') {
            return '';
        }
        return mb_strtoupper(mb_substr($words, 0, 1)) . mb_substr($words, 1);
    }

    /** An incoming name as stored: trimmed, and null when there is nothing left. */
    public static function clean(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';
        return $value === '' ? null : $value;
    }
}
