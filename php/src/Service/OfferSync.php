<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Service;

use PDO;
use Tds\Ext\Shop\Domain\SyncQueueRepository;

/**
 * One tick of the offer refresh: claim a batch, ask Amazon, write the prices.
 *
 * ### How this runs at all, on a host with no cron
 *
 * The production host has no SSH, no guaranteed scheduler, and `proc_open` is
 * disabled — so there is no process to hold a loop. This is modelled on the
 * core's `MigrationRunner`, which faces the same constraint: **work happens on
 * an ordinary request, after the response has been sent**, guarded by a
 * non-blocking `flock` so concurrent requests do not duplicate it.
 *
 * (An earlier draft of the plan proposed the Plesk task scheduler as the
 * primary trigger. That was a guess against a documented fact about this host,
 * and it is now demoted: an external ping is an accelerator, never a
 * prerequisite.)
 *
 * The honest consequence belongs in the operator docs: **an API with no
 * traffic does not sync.** For a shop that is tolerable and even fitting — the
 * pages being visited drive the refresh of the prices they show — but after a
 * quiet spell the first visitor sees prices withheld rather than stale ones.
 * That is the 24-hour rule doing its job, not a bug.
 *
 * ### The budget is not tuning
 *
 * Three API calls and four seconds per tick. Even after
 * `fastcgi_finish_request()` the PHP worker is occupied, so an unbounded tick
 * would take a worker out of the pool for as long as Amazon felt like taking.
 * Ten ASINs per call means three calls still move thirty products.
 */
final class OfferSync
{
    public const MAX_CALLS_PER_TICK = 3;
    public const MAX_SECONDS_PER_TICK = 4;

    public function __construct(
        private readonly PDO $pdo,
        private readonly SyncQueueRepository $queue,
        private readonly ?AmazonPaApiClient $client,
    ) {
    }

    /** False when no credentials are configured — the shop then simply has no sync. */
    public function isConfigured(): bool
    {
        return $this->client !== null;
    }

    /**
     * Run one bounded tick.
     *
     * Never throws. A sync failure must not turn into a 500 on a page the
     * visitor asked for — especially since this usually runs after the
     * response has already gone out, where an exception would only reach a log.
     *
     * @return array{ok:int,failed:int,calls:int,stopped:?string}
     */
    public function tick(string $trigger = 'request'): array
    {
        if ($this->client === null) {
            return ['ok' => 0, 'failed' => 0, 'calls' => 0, 'stopped' => 'not_configured'];
        }

        $owner = bin2hex(random_bytes(8));
        $deadline = microtime(true) + self::MAX_SECONDS_PER_TICK;
        $runId = $this->queue->startRun($trigger);

        $ok = 0;
        $failed = 0;
        $calls = 0;
        $stopped = null;

        try {
            while ($calls < self::MAX_CALLS_PER_TICK && microtime(true) < $deadline) {
                $batch = $this->queue->claim(AmazonPaApiClient::MAX_ITEMS_PER_CALL, $owner);
                if ($batch === []) {
                    $stopped = 'queue_empty';
                    break;
                }
                $calls++;

                $ids = array_map(static fn (array $r): int => (int) $r['id'], $batch);
                $asins = array_map(static fn (array $r): string => (string) $r['external_id'], $batch);

                try {
                    $items = $this->client->getItems($asins);
                } catch (PaApiException $e) {
                    if ($e->isPermanent()) {
                        // Not a backoff. Retrying a withdrawn account cannot
                        // succeed and is what the licence objects to.
                        $this->queue->markRevoked($e->getMessage());
                        $failed += count($ids);
                        $stopped = 'revoked';
                        break;
                    }
                    $this->queue->fail($ids, $e->getMessage());
                    $failed += count($ids);
                    if ($e->isThrottled()) {
                        $stopped = 'throttled';
                        break;
                    }
                    continue;
                }

                [$wrote, $missed] = $this->apply($batch, $items);
                $ok += $wrote;
                $failed += count($missed);
                $this->queue->succeed(array_diff($ids, $missed));
                if ($missed !== []) {
                    // An ASIN Amazon no longer knows. Not an outage — it will
                    // never come back, so it fails rather than retrying
                    // forever, and the offer keeps its link and loses its price.
                    $this->queue->fail($missed, 'item_not_returned');
                }
            }
        } catch (\Throwable $e) {
            $stopped = 'error';
            $this->queue->finishRun($runId, $ok, $failed, $e->getMessage());
            return ['ok' => $ok, 'failed' => $failed, 'calls' => $calls, 'stopped' => $stopped];
        }

        $this->queue->finishRun($runId, $ok, $failed, null);
        return ['ok' => $ok, 'failed' => $failed, 'calls' => $calls, 'stopped' => $stopped];
    }

    /**
     * Write one batch's prices back onto the offers.
     *
     * `price_checked_at` is stamped here and **only** here — it is the claim
     * that a price came from the API at a known moment, and it is what the
     * 24-hour rule reads. Nothing else in this package may set it.
     *
     * @param  list<array<string,mixed>>            $batch
     * @param  array<string, array<string,mixed>>   $items keyed by ASIN
     * @return array{0:int, 1:list<int>} written count, and the queue ids Amazon did not answer for
     */
    private function apply(array $batch, array $items): array
    {
        $update = $this->pdo->prepare(
            'UPDATE shop_offer SET price_cents = :price, currency = :currency,'
            . ' availability = :availability, price_checked_at = UTC_TIMESTAMP(),'
            . ' raw = :raw WHERE id = :id',
        );

        $written = 0;
        $missed = [];
        foreach ($batch as $row) {
            $asin = (string) $row['external_id'];
            $item = $items[$asin] ?? null;
            if ($item === null) {
                $missed[] = (int) $row['id'];
                continue;
            }
            $update->execute([
                'price' => $item['priceCents'],
                'currency' => $item['currency'],
                'availability' => $item['availability'],
                'raw' => json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'id' => (int) $row['offer_id'],
            ]);
            $written++;
        }

        // Keep the catalogue's sort helper in step with the prices just written.
        $this->pdo->exec(
            'UPDATE shop_product p SET sort_price_cents ='
            . ' (SELECT MIN(price_cents) FROM shop_offer o'
            . '  WHERE o.product_id = p.id AND o.price_cents IS NOT NULL)'
            . ' WHERE p.id IN (SELECT product_id FROM shop_offer WHERE id IN ('
            . implode(',', array_map(static fn (array $r): int => (int) $r['offer_id'], $batch))
            . '))',
        );

        return [$written, $missed];
    }
}
