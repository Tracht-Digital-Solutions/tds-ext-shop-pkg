<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * The offer-refresh queue, and the ledger of what the sync has been doing.
 *
 * ### Why a queue table rather than a loop
 *
 * The production host runs without SSH, without a guaranteed cron, and with
 * `proc_open` disabled. There is no process to hold a loop, so the sync is
 * driven by ordinary requests — a few items at a time, after the response has
 * already been sent. That only works if "where was I" survives between
 * requests, which is what this table is.
 *
 * `next_call_at` is the rate limiter, expressed as state rather than as
 * `sleep()`: a tick takes only rows whose time has come and stamps the next
 * one a second out. PA-API allows one request per second and ten items per
 * request, so the ceiling is six hundred items a minute — the limit is never
 * the constraint, and a `sleep()` inside a web request would be.
 *
 * `locked_until` is what keeps two concurrent requests off the same batch. It
 * expires rather than being released, because a request that dies mid-tick
 * cannot release anything.
 *
 * `state = 'revoked'` is its own value, not an error count: Amazon withdraws
 * API access when qualifying sales stop, and that is a standing condition a
 * human has to resolve, not something to retry into.
 */
final class ShopCreateSyncQueue extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_sync_queue', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('offer_id', 'integer', ['signed' => false])
            ->addColumn('network', 'string', ['limit' => 20, 'default' => 'amazon'])
            ->addColumn('external_id', 'string', ['limit' => 64])
            // pending | running | ok | error | revoked
            ->addColumn('state', 'string', ['limit' => 16, 'default' => 'pending'])
            ->addColumn('attempts', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('next_call_at', 'datetime', ['null' => true])
            ->addColumn('locked_until', 'datetime', ['null' => true])
            ->addColumn('last_error', 'string', ['limit' => 300, 'null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['offer_id'], ['unique' => true, 'name' => 'uniq_shop_sync_offer'])
            // The claim query orders on exactly this.
            ->addIndex(['state', 'next_call_at'], ['name' => 'idx_shop_sync_due'])
            ->addForeignKey('offer_id', 'shop_offer', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('shop_sync_run', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('network', 'string', ['limit' => 20, 'default' => 'amazon'])
            // request | manual | token — which of the three triggers fired.
            ->addColumn('trigger', 'string', ['limit' => 16, 'default' => 'request'])
            ->addColumn('started_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('finished_at', 'datetime', ['null' => true])
            ->addColumn('items_ok', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('items_failed', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('error', 'string', ['limit' => 300, 'null' => true])
            ->addIndex(['started_at'], ['name' => 'idx_shop_sync_run_started'])
            ->create();
    }
}
