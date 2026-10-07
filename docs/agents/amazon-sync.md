# Amazon offer sync

## How it runs on a host with no cron

The production host has no SSH, no guaranteed scheduler, and `proc_open` is disabled. So the
sync follows the core's `MigrationRunner`: **work happens on an ordinary request, after the
response has been sent.** `Support\SyncTicker` claims a marker file under a non-blocking
`flock`, calls `fastcgi_finish_request()`, then runs one bounded batch.

Triggers, most reliable first:

1. **Request-driven** (`/content/shop`), primary and configuration-free.
2. **Panel button** (`POST /shop/sync/enqueue`), after an import or an account fix.
3. **External ping** (`POST /shop/sync/tick`, token-gated): an uptime monitor, a scheduled
   workflow or a host task. An accelerator, **not** a prerequisite.

An API with no traffic doesn't sync. After a quiet spell the first visitor sees prices withheld
rather than stale; that is the 24-hour rule working.

## Four rules in `SyncTicker`, each a bug if dropped

1. Claim the marker **before** the slow work (or two requests start the same batch).
2. Use a **non-blocking** lock (or the sync's slowness becomes the visitor's).
3. **Flush the response first.**
4. **Swallow everything**; a sync failure must never be a 500 on a requested page.

The per-tick budget (three API calls, four seconds) is not tuning. Even after
`fastcgi_finish_request()` the PHP worker stays occupied.

## Revoked is a state, not an error

Amazon withdraws API access when qualifying sales stop. Retrying can't help and hammering a
revoked account violates the licence, so `PaApiException::isPermanent()` routes it to
`markRevoked()`: the queue halts and the panel says why. Affiliate links keep working, and
prices age out within a day. A revoked account has **no other symptom**, which is why
`SyncWidget` exists.

## Timestamps

`price_checked_at` is stamped in exactly one place: **`OfferSync::apply()`**. It claims the price
came from the API at a known moment, and the 24-hour rule reads it. Nothing else may set it.

Two conventions, one per column, **never mixed in one condition**:

| Convention | Columns | Compare with |
|---|---|---|
| UTC (`UTC_TIMESTAMP()`) | `price_checked_at`, `published_at`, `shop_order.withdrawal_consent_at`, `fulfilled_at`, sync queue/run times (`next_call_at`, `locked_until`, `finished_at`) | `UTC_TIMESTAMP()` |
| Berlin wall clock (`CURRENT_TIMESTAMP` / `NOW()`) | `created_at`, `updated_at`, `invoiced_at` | `NOW()` |

- The host pins PHP and every DB session to Europe/Berlin.
- Read UTC columns back with `Support\UtcDateTime`, never a bare `strtotime()` (which uses the
  default timezone and would shorten the 24-hour window).
- `PriceFreshnessTest` and `UtcDateTimeTest` run in Europe/Berlin for that reason.

## Signing

`Support\PaApiSigner` is pure and takes its clock as an argument; it is the only part provable
without an Amazon account. A wrong signature shows up in production as
`IncompleteSignatureException`, without saying which SigV4 step failed. `PaApiSignerTest` pins
the canonical request, scope, key derivation and signature.
