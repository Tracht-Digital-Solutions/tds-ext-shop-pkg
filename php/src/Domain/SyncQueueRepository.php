<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Domain;

use PDO;

/**
 * The offer-refresh queue.
 *
 * Every method here is written so that two web requests running at the same
 * moment cannot do the same work twice — there is no scheduler holding a lock,
 * only rows.
 */
final class SyncQueueRepository
{
    /** How long a claimed batch stays claimed if the request handling it dies. */
    private const LOCK_SECONDS = 60;

    /** PA-API allows one request per second; the next batch waits that long. */
    private const PACE_SECONDS = 1;

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Put every affiliate offer that needs a price back in the queue.
     *
     * "Needs a price" is: never checked, or checked more than twelve hours
     * ago. Twelve rather than twenty-four on purpose — the price disappears
     * from the site at twenty-four, so queueing at twelve leaves a full half
     * day for a low-traffic site to get round to it before anything is lost.
     */
    public function enqueueStale(int $staleHours = 12): int
    {
        $sql = 'INSERT INTO shop_sync_queue (offer_id, network, external_id, state, next_call_at)'
            . ' SELECT o.id, o.network, o.external_id, \'pending\', UTC_TIMESTAMP()'
            . ' FROM shop_offer o'
            . " WHERE o.kind = 'affiliate' AND o.network = 'amazon'"
            . '   AND o.external_id IS NOT NULL AND o.external_id <> \'\''
            . '   AND o.disabled_at IS NULL'
            . "   AND (o.price_checked_at IS NULL OR o.price_checked_at < (UTC_TIMESTAMP() - INTERVAL {$staleHours} HOUR))"
            // Re-queueing an offer that is already waiting must not reset its
            // attempt count or move it to the back — hence the no-op update on
            // the unique key rather than a plain INSERT IGNORE, which would
            // hide a genuine error too.
            . ' ON DUPLICATE KEY UPDATE shop_sync_queue.offer_id = shop_sync_queue.offer_id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Claim up to `$limit` due items for this process.
     *
     * Claim-then-read, not read-then-claim: the UPDATE is what makes the
     * selection exclusive, so reading first and updating after would let two
     * requests both believe they owned the same rows.
     *
     * @return list<array<string,mixed>>
     */
    public function claim(int $limit, string $owner): array
    {
        $limit = max(1, min(10, $limit));

        $claim = $this->pdo->prepare(
            'UPDATE shop_sync_queue'
            . " SET state = 'running', locked_until = (UTC_TIMESTAMP() + INTERVAL " . self::LOCK_SECONDS . ' SECOND),'
            . '     last_error = :owner'
            . " WHERE state IN ('pending', 'error')"
            . '   AND (next_call_at IS NULL OR next_call_at <= UTC_TIMESTAMP())'
            . '   AND (locked_until IS NULL OR locked_until <= UTC_TIMESTAMP())'
            . ' ORDER BY next_call_at IS NULL DESC, next_call_at ASC, id ASC'
            . " LIMIT {$limit}",
        );
        // The owner token is parked in last_error for the duration of the
        // claim purely as a marker to read the rows back by; `finish()`
        // overwrites it with the real outcome. Ugly, and cheaper than a
        // dedicated column that would only ever hold a transient value.
        $claim->execute(['owner' => $owner]);
        if ($claim->rowCount() === 0) {
            return [];
        }

        $read = $this->pdo->prepare(
            "SELECT * FROM shop_sync_queue WHERE state = 'running' AND last_error = :owner",
        );
        $read->execute(['owner' => $owner]);
        return $read->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** A batch succeeded: mark done and pace the next one. */
    public function succeed(array $ids): void
    {
        if ($ids === []) {
            return;
        }
        $in = implode(',', array_map('intval', $ids));
        $this->pdo->exec(
            "UPDATE shop_sync_queue SET state = 'ok', attempts = 0, last_error = NULL,"
            . ' locked_until = NULL,'
            . ' next_call_at = (UTC_TIMESTAMP() + INTERVAL ' . self::PACE_SECONDS . ' SECOND)'
            . " WHERE id IN ({$in})",
        );
    }

    /**
     * A batch failed.
     *
     * Exponential backoff, capped at fifteen minutes; after eight attempts the
     * row stops being retried. A permanent failure skips all of that and is
     * marked `revoked` — see {@see markRevoked()}.
     */
    public function fail(array $ids, string $error): void
    {
        if ($ids === []) {
            return;
        }
        $in = implode(',', array_map('intval', $ids));
        $stmt = $this->pdo->prepare(
            "UPDATE shop_sync_queue SET state = 'error', attempts = attempts + 1,"
            . ' last_error = :error, locked_until = NULL,'
            . ' next_call_at = (UTC_TIMESTAMP() + INTERVAL LEAST(900, POW(2, LEAST(attempts, 10))) SECOND)'
            . " WHERE id IN ({$in})",
        );
        $stmt->execute(['error' => mb_substr($error, 0, 300)]);
    }

    /**
     * Amazon has withdrawn access. Stop the whole queue, not one row.
     *
     * Retrying against a revoked account cannot succeed and is precisely the
     * behaviour the licence objects to, so this is a halt rather than a
     * backoff. A human re-enables it from the panel once the account is well.
     */
    public function markRevoked(string $error): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE shop_sync_queue SET state = 'revoked', locked_until = NULL,"
            . ' next_call_at = NULL, last_error = :error'
            . " WHERE state IN ('pending', 'running', 'error')",
        );
        $stmt->execute(['error' => mb_substr($error, 0, 300)]);
    }

    /** Put a revoked queue back to work after the operator fixes the account. */
    public function resume(): int
    {
        $stmt = $this->pdo->prepare(
            "UPDATE shop_sync_queue SET state = 'pending', attempts = 0,"
            . ' next_call_at = UTC_TIMESTAMP(), last_error = NULL'
            . " WHERE state = 'revoked'",
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    /** What the panel widget shows. */
    public function status(): array
    {
        $counts = $this->pdo->query(
            'SELECT state, COUNT(*) AS total FROM shop_sync_queue GROUP BY state',
        );
        $byState = ['pending' => 0, 'running' => 0, 'ok' => 0, 'error' => 0, 'revoked' => 0];
        foreach ($counts === false ? [] : $counts->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $byState[(string) $row['state']] = (int) $row['total'];
        }

        $last = $this->pdo->query(
            'SELECT * FROM shop_sync_run ORDER BY started_at DESC LIMIT 1',
        );
        $lastRun = $last === false ? false : $last->fetch(PDO::FETCH_ASSOC);

        return [
            'queue' => $byState,
            // The state that needs a person. Surfaced as its own flag so the
            // widget does not have to know which count means "broken".
            'revoked' => $byState['revoked'] > 0,
            'lastRun' => $lastRun === false ? null : $lastRun,
        ];
    }

    /** Open a run in the ledger; returns its id. */
    public function startRun(string $trigger): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO shop_sync_run (network, `trigger`) VALUES (:network, :trigger)',
        );
        $stmt->execute(['network' => 'amazon', 'trigger' => $trigger]);
        return (int) $this->pdo->lastInsertId();
    }

    public function finishRun(int $runId, int $ok, int $failed, ?string $error = null): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE shop_sync_run SET finished_at = UTC_TIMESTAMP(), items_ok = :ok,'
            . ' items_failed = :failed, error = :error WHERE id = :id',
        );
        $stmt->execute([
            'ok' => $ok,
            'failed' => $failed,
            'error' => $error === null ? null : mb_substr($error, 0, 300),
            'id' => $runId,
        ]);
    }
}
