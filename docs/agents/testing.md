# Testing

```bash
npm run test:run   # vitest; islands opt into jsdom per file via a @vitest-environment docblock
composer test      # phpunit
```

CI (`_build.yml`) runs both suites, plus type-check, `lint:primitives` and build, on
every run.

## Test environment against the current tds-shared

tds-shared is a **peer** dependency, so a fresh standalone install may resolve an old
version while every product build composes the current one. Keep these in mind:

- `apiFetch` first consults the host runtime config (`/tds-runtime.json`), so
  `fetch.mock.calls[0]` is that probe. Call **`primeRuntimeConfig(null)`** in
  `beforeEach`; panel products never ship that file, so "absent" matches production.
- `apiFetch` is **async**. `await waitFor(() => expect(fetch).toHaveBeenCalled())`
  before reading `mock.calls`.
- A multipart upload carries an **empty** `headers` object. Assert "no content-type
  header", never "headers is undefined".
- Error-path tests answer with a **populated** body and a non-OK status. Against an
  empty error body the ok-check is unobservable.
- At least one assertion per suite pins the **absolute API host** (see
  [frontend-conventions.md](frontend-conventions.md#api-calls-apifetch-never-a-relative-fetch)).

## Suites

The suites target failures with no other symptom.

| Suite | Covers |
|---|---|
| `php/tests/PriceFreshnessTest.php` | 24-hour rule incl. the inclusive boundary; a never-checked quote is unusable; `0` survives as a real price (`if (!$cents)` would drop it) |
| `php/tests/ShopModuleTest.php` | RBAC (401 vs 403 distinct), fail-soft public reads, the label surviving the degraded path. Its PDO stub **always throws**; "passes the gate" asserts the DB exception |
| `php/tests/ShopMigrationsTest.php` | File ↔ class, prefix, band, unsigned integer FKs, without a DB |
| `php/tests/ShopApiDocsTest.php` | Documented = mounted routes |
| `php/tests/CheckoutTest.php`, `WithdrawalTest.php`, `ShippingTest.php`, `PaymentTest.php` | Basket resolution, conditional consent, shipping tax split, provider behaviour |
| `php/tests/OrderInvoiceBuilderTest.php`, `OrderInvoicingTest.php` | Lexware invoice payload and failure handling |
| `php/tests/PaApiSignerTest.php`, `UtcDateTimeTest.php`, `ProductOffersTest.php` | SigV4 signing, UTC reads (run in Europe/Berlin), offers |
| `php/tests/ShopSeedServicePackagesTest.php`, `CategoryNameTest.php` | Seeded service packages, category names |
| `php/tests/ProductReadinessTest.php` | The publish gate: markdown-only bodies, inclusive 80–160 meta bounds (multibyte), meta title rescuing a long heading, own vs. fresh partner price; `PairList` dropping half rows |
| `php/tests/ShopCatalogueSeedTest.php` | Every prepared product (`php/db/seed/`) passes the publish gate as seeded, slugs are unique incl. the September packages, prices follow the hourly rates, bodies say „inklusive“ |
| `php/tests/ProductUpsertBodyTest.php` (DB) | A save without `body` keeps the text — the panel form once wiped every seeded package |
| `src/index.test.ts` | Manifest; the permissionless widget set is exactly `["shop-picks"]` |
| `islands/WidgetBody.test.tsx`, `islands/PicksBody.test.tsx` | Dash (never zero) on failure; absolute URL |
| `tests/packaging.test.ts` | Every specifier resolves, is exported and ships |
| `tests/islandToasts.test.ts` | Every `toast.<method>(…)` in `islands/` exists on the installed tds-shared toast. Nothing else type-checks islands, and nine `toast.error(…)` calls once threw TypeErrors on failure paths |

Mutation check on the load-bearing rule: flipping `>` to `>=` in the freshness comparison is caught
by the boundary test in both the PHP and the tds-shared suite.
