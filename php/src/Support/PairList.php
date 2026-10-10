<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

/**
 * The two JSON lists a product page carries: FAQ (`{q, a}`) and facts
 * (`{label, value}`), and the same FAQ shape on a category.
 *
 * One cleaner for both, because they fail the same way: a half-filled row from
 * the panel editor. An entry missing either side is dropped rather than stored —
 * a question without an answer renders as a dangling heading and, worse, as a
 * FAQPage entry a search engine flags.
 */
final class PairList
{
    /** Hard caps. A page with forty FAQ entries is a content farm, not a help. */
    public const MAX_ITEMS = 12;
    private const MAX_KEY = 300;
    private const MAX_VALUE = 2000;

    /**
     * Normalise an incoming list into `[{$k: string, $v: string}]`.
     *
     * @param  string $k first key ("q" or "label")
     * @param  string $v second key ("a" or "value")
     * @return list<array<string,string>>
     */
    public static function clean(mixed $raw, string $k, string $v): array
    {
        if (is_string($raw)) {
            $raw = self::decodeRaw($raw);
        }
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $key = self::text($item[$k] ?? null, self::MAX_KEY);
            $value = self::text($item[$v] ?? null, self::MAX_VALUE);
            if ($key === '' || $value === '') {
                continue;
            }
            $out[] = [$k => $key, $v => $value];
            if (count($out) >= self::MAX_ITEMS) {
                break;
            }
        }
        return $out;
    }

    /** Encode for a text column; an empty list is stored as NULL, not "[]". */
    public static function encode(array $list): ?string
    {
        return $list === [] ? null : json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * Decode a stored column. Anything unreadable is an empty list: a broken
     * FAQ must cost the page its FAQ, never the page.
     *
     * @return list<array<string,string>>
     */
    public static function decode(mixed $stored, string $k, string $v): array
    {
        return is_string($stored) && $stored !== '' ? self::clean($stored, $k, $v) : [];
    }

    private static function decodeRaw(string $raw): mixed
    {
        try {
            return json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function text(mixed $value, int $max): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return '';
        }
        $s = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');
        return mb_substr($s, 0, $max);
    }
}
