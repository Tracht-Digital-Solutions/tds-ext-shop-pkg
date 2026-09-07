<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

/**
 * How long a fetched affiliate price may be shown: 24 hours.
 *
 * The Amazon Product Advertising API licence requires a displayed price to come
 * from the API, to carry the time it was retrieved, and to disappear once it is
 * older than a day. The same ceiling is applied to every network — a stale
 * price misleads a reader regardless of who quoted it, and one rule is one
 * thing to get right.
 *
 * ### Why this exists here as well as in the frontend
 *
 * `tds-shared`'s `isPriceStale()` already enforces it in the browser-facing
 * renderer. This is the second, independent floor: the public API strips the
 * price before it leaves the server, so a consumer that forgets the check — a
 * new site, a third-party integration, a debugging fetch pasted into a
 * template — cannot display one anyway. Duplication is deliberate. The failure
 * it guards against is invisible (yesterday's price renders perfectly) and its
 * cost is the partner programme rather than a broken layout, so one layer of
 * defence is not enough.
 *
 * There is deliberately no "grace period" and no configuration knob. A licence
 * term is not a preference.
 */
final class PriceFreshness
{
    public const MAX_AGE_SECONDS = 24 * 60 * 60;

    /**
     * Whether a quote may still be shown.
     *
     * A null or unparseable timestamp is NOT fresh: without a retrieval time we
     * cannot assert the price is current, so the honest answer is the same one a
     * stale price gets. Getting this default the other way round would display
     * an unverified price forever.
     */
    public static function isFresh(?string $checkedAt, ?int $now = null): bool
    {
        if ($checkedAt === null || trim($checkedAt) === '') {
            return false;
        }
        $ts = strtotime($checkedAt);
        if ($ts === false) {
            return false;
        }
        return ($now ?? time()) - $ts <= self::MAX_AGE_SECONDS;
    }

    /**
     * The price to publish, or null when it may not be shown.
     *
     * Callers hand the raw row through this rather than reading `price_cents`
     * themselves, so that stripping a stale price is one decision made in one
     * place instead of a rule every route has to remember.
     */
    public static function publishablePrice(?int $priceCents, ?string $checkedAt, ?int $now = null): ?int
    {
        if ($priceCents === null) {
            return null;
        }
        return self::isFresh($checkedAt, $now) ? $priceCents : null;
    }
}
