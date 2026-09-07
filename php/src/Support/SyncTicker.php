<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

/**
 * Runs a tick on an ordinary request, without holding up the response.
 *
 * Modelled on the core's `MigrationRunner`, which solves the same problem on
 * the same host: no SSH, no guaranteed cron, `proc_open` disabled — so there
 * is no process to schedule work in, and the only thing that reliably happens
 * is a web request.
 *
 * Four rules, each of which is a bug if dropped:
 *
 * 1. **Claim the marker BEFORE the slow work**, not after. Two requests
 *    arriving together would otherwise both find the marker old and both start
 *    the same batch.
 * 2. **A non-blocking lock.** `LOCK_EX | LOCK_NB`: a request that cannot get
 *    the lock does nothing and returns immediately. Waiting for it would make
 *    the sync's slowness the visitor's slowness, which is the one thing this
 *    design exists to avoid.
 * 3. **Flush the response first.** `fastcgi_finish_request()` where available,
 *    so the visitor has their page before Amazon is called at all.
 * 4. **Swallow everything.** A sync failure must never become a 500 on a page
 *    somebody asked for.
 *
 * The interval is deliberately generous. This is opportunistic work: skipping
 * a tick costs nothing, because a price that ages out is withheld rather than
 * shown wrongly.
 */
final class SyncTicker
{
    /** Minimum gap between request-driven ticks. */
    public const INTERVAL_SECONDS = 60;

    /** @param callable():void $work */
    public function __construct(
        private readonly string $markerDir,
        private readonly mixed $work,
    ) {
    }

    /**
     * Run a tick if one is due and nothing else is already running one.
     *
     * Returns whether work was actually attempted — used by the tests, and by
     * the token route to report honestly instead of always claiming success.
     */
    public function maybeTick(): bool
    {
        $marker = $this->markerDir . '/sync.tick';
        if (!$this->prepareDir()) {
            return false;
        }

        // Rule 1: the freshness check and the claim happen under the same lock,
        // before any work.
        $handle = @fopen($marker, 'c+');
        if ($handle === false) {
            return false;
        }

        try {
            // Rule 2: non-blocking. Somebody else is ticking; that is fine.
            if (!flock($handle, LOCK_EX | LOCK_NB)) {
                return false;
            }

            $stamp = (int) trim((string) stream_get_contents($handle));
            if ($stamp > 0 && (time() - $stamp) < self::INTERVAL_SECONDS) {
                return false;
            }

            // Claim now, so a request arriving one millisecond later sees a
            // fresh stamp and leaves.
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, (string) time());
            fflush($handle);

            $this->flushResponse();

            try {
                ($this->work)();
            } catch (\Throwable $e) {
                // Rule 4. The response is already gone; there is nobody left to
                // tell but the log.
                error_log('[tds-shop] sync tick failed: ' . $e->getMessage());
            }
            return true;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function prepareDir(): bool
    {
        if (is_dir($this->markerDir)) {
            return is_writable($this->markerDir);
        }
        return @mkdir($this->markerDir, 0o770, true) || is_dir($this->markerDir);
    }

    /**
     * Send the response and keep working.
     *
     * Only FPM offers this. Under any other SAPI the work still runs, just
     * before the response is released — which is why the per-tick budget in
     * {@see \Tds\Ext\Shop\Service\OfferSync} is small enough to be survivable
     * even then.
     */
    private function flushResponse(): void
    {
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
            return;
        }
        if (function_exists('litespeed_finish_request')) {
            @litespeed_finish_request();
        }
    }
}
