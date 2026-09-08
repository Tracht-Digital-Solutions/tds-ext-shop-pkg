# AGENTS.md — tds-ext-shop-pkg

**TDShop** — the catalogue behind `shop.tracht-digital.de`, plus the placements
that embed its products in the journal and the customer portal. Read
`tds-frontend-contract-pkg`'s AGENTS.md first; extensions implement that
contract. Seeded from `tds-ext-template-pkg`, so the general extension rules
below still hold verbatim.

## Rules specific to this module

Four decisions that look like implementation detail and are not. Changing any of
them breaks something that will not show up as an error.

### 1. A price older than 24 hours must never leave the server

The Amazon Product Advertising API licence permits a fetched price to be shown
for one day, carrying its retrieval time. `Support\PriceFreshness` strips an
expired price in `ProductRepository::offersFor()` before the payload is built,
and `tds-shared`'s `isPriceStale()` enforces the same rule again in the
renderer.

**The duplication is deliberate — do not "simplify" it to one layer.** The
failure is invisible: last week's price renders perfectly, no exception, no
visual regression. Its cost is the partner programme, not a broken page. Two
independent floors mean a new consumer that forgets the check still cannot
display one.

The timestamp is cleared alongside the price. A retrieval time left behind on a
stripped price reads as "checked, and free".

A hand-typed **affiliate** price gets no timestamp at all (see
`ProductRepository::setOffers()`), so it is not displayed until the sync
confirms it. An **own** price is ours to state and is current by definition.

### 2. The advertising label is not a setting

§ 5a Abs. 4 UWG. `/content/shop/placement/{key}` serves `label` even from its
degraded path, and `ProductCard` in `tds-shared` renders it whenever an
affiliate offer is present — passing an empty `affiliateLabel` still yields the
default. Three surfaces render this card; a label each of them decides for
itself is a label one of them eventually forgets.

### 3. A product is a language-neutral core plus translations

`shop_product` + `shop_product_translation`, **not** the blog's one-row-per-
language arrangement. An article has no language-neutral core; a product does —
ASIN, network, price, availability, retrieval time. Duplicating that per
language and pointing a sync at it is how the German row lands on 249 € and the
English one on 259 € because a tick was interrupted.

Consequence worth keeping: DE and EN slugs may differ freely, because the pair
is joined on `product_id` rather than by mirroring a slug. Do not "fix" this to
match the blog.

### 4. `editorial_status` gates indexing

Only `published` — a product carrying its own written assessment — belongs in
the sitemap with `<meta robots index>`. `stub` and `none` render and are
reachable but stay out of the index. This is what stops a bulk import of 300
ASINs from turning the domain into the thin-affiliate pattern search engines
demote. It is a separate axis from `status`: a product can be on sale while its
write-up is still a stub.

### Where the site-key boundary runs

`siteKeyRoutes()` returns exactly `['/content/shop']`, matched segment-wise by
`SiteKeyMiddleware::matches()`. Two things must stay outside it:

- the `/shop/*` panel routes — they are gated on the user's permissions, and
  listing them here would offer a second door;
- anything a visitor's own browser calls. A key in a client bundle is not a key.
  The Stripe webhook is the sharp case: Stripe holds no site key, so a webhook
  under `/content/shop/...` would be rejected outright. It belongs at
  `/shop/stripe/webhook` and authenticates by signature.

And never widen it to `/content` — that swallows every other module's public
routes.

### Public reads degrade; panel routes do not

Every `/content/shop*` handler catches and answers an empty payload. A public
site's content fetch is fail-soft, so a 500 there surfaces as a page that
silently drops a section, not as an error anybody sees. The panel routes
deliberately do **not** do this: a staff member can act on a database error, and
a calm empty state would send them hunting for products that were never missing.

`ShopModuleTest` pins both halves with a container stub whose PDO always throws.

## Shape (identical to any extension)

- `src/index.ts` — the `defineExtension({...})` manifest.
- `pages/*.astro` / `widgets/*.astro` / `islands/*` — the route/widget/settings
  slots' entrypoints (package subpaths in `exports`).
- `php/src/*Module.php` — the backend `Module`.
- `php/db/migrations/*` — Phinx migrations, class names **prefixed with the
  module id** (in-process auto-migrator = one process = no name reuse) — and the
  **file name must map to the class** (`<version>_shop_create_product.php` ⇒
  `ShopCreateProduct`), so the prefix goes first in both. A mismatch throws
  `Could not find class …` during the *scan* and aborts every extension's
  migrations, not just yours.
- `php/docs/api.php` — one entry per mounted route (summary, params, responses,
  required permission), returned by the Module's `apiDocs()`. The admin
  frontend's API reference joins it onto the introspected Slim routes by
  `"<METHOD> <pattern>"`, so the pattern must be **verbatim**, inline regex
  included. `php/tests/*ApiDocsTest.php` asserts the documented set and the
  registered set are the same set — **keep both files when cloning**; adding a
  route without describing it then fails your own suite instead of quietly
  leaving a blank row in the reference.
- `.github/workflows/*` — inline dual pipeline (phpunit + npm publish).

## Styling: use the shared primitives, never invent a class name

**An extension ships no CSS.** There is no stylesheet in this package and there
must not be one — every token and component comes from `tds-shared`, which the
product already installs (declared here as a **peer** dependency, the same
treatment astro and react get). The host renders this markup inside the `panel`
surface, so the geometry is already decided.

The scaffold's markup is the reference. Use exactly these:

| Slot | Class |
|---|---|
| page shell | `tds-page` + `tds-page__head` > `h1.tds-page__title` (+ `tds-page__lede`) |
| dashboard widget | `article.tds-widget` > `h3.tds-widget__title`, figure `tds-widget__metric` |
| settings slot | `div.tds-settings-section__body` |
| record list | `ul.tds-list` > `li.tds-list__row` |
| card / table / empty | `tds-card` · `tds-table` · `tds-empty` |
| button | `btn` + `btn-primary` / `-accent` / `-ghost` / `-danger` (**both** classes) |
| inline label | `chip` + `chip--{neutral,success,warning,danger,info,cat-*}` |
| block message | `tds-alert` (+ `--success` / `--warning` / `--danger`) |
| label + control | `tds-field-row` · toggle row `tds-toggle-row` |
| message thread | `tds-thread` > `tds-thread__item--own` / `--other` |
| loading | `<Spinner />` from `tds-shared/components` |
| destructive confirm | `<ConfirmDialog />` from `tds-shared/components` — **never `window.confirm()`** |

**Do not invent a bespoke BEM name for any of the above.** Every extension used to
carry its own (`page page--x`, `widget widget--x`, `settings-section--x`,
`widget__metric`, `danger`, `<p>Wird geladen …</p>`) and **none of them had a CSS
rule anywhere** — they were a contract of intent that nothing implemented, so
those regions rendered as raw unstyled HTML. Undoing that took a sweep across all
14 extensions.

Three traps, each of which shipped as a real bug:

- **Never interpolate a class name.** `` className={`chip chip--${status}`} ``
  fails twice over: Tailwind cannot statically extract it, and a value matching no
  variant renders an unstyled element. Map explicitly, with a fallback. When the
  value comes from the **database**, use `resolveChipVariant()` from
  `@tracht-digital-solutions/tds-shared/design` — it is guaranteed to return a
  class that exists. (`badge badge--${status}` shipped in two islands; `.badge`
  never existed at all.)
- **`.status-pill` is an inline label, not a banner.** For a block message use
  `.tds-alert`. A stretched `<p class="status-pill">` was the most common misuse
  in the platform, at 24 sites.
- **Call the API with `apiFetch`, NEVER a relative `fetch`.**

  ```tsx
  import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";

  const api = apiFetch; // sends the session cookie, resolves the API base
  ```

  Every extension used to define its own
  `const api = (path, init) => fetch(path, { credentials: "include", ...init })`
  — with a **relative** path. In a product that resolves against the product's
  own static host, and its SPA fallback answers unknown paths with **200 +
  HTML**: `res.ok` is `true`, `res.json()` throws, and the usual
  `.catch(() => setRows([]))` renders a calm, permanent empty state. No error,
  no console warning. The contact inbox reported "Keine Anfragen." for months
  with the rows in the database. `apiFetch` resolves the base from
  `<meta name="tds-api-base">` (written by the frontend host) and also routes
  401s through the host's session backstop.

  A mocked-fetch test cannot catch a regression here — a relative path satisfies
  every behavioural assertion — so **assert the absolute host explicitly** in at
  least one test.
- **Report every mutation's outcome, and report it with a toast.**

  ```tsx
  import { toast } from "@tracht-digital-solutions/tds-shared/components";

  const res = await api("/thing", { method: "PUT", body });
  if (res.ok) toast.success("Gespeichert.");
  else toast.danger(`Speichern fehlgeschlagen (HTTP ${res.status}).`);
  ```

  Rules that come with it:
  - **Never `await` a mutation and drop the response.** That was the single most
    common defect across the extensions — a 403 looked exactly like success:
    the dialog closed, the draft cleared, the list reloaded, and the row was
    still there. Optimistic UI must also roll back on failure.
  - **Failure messages carry the HTTP status.** It is what separates "session
    expired" from "service down" in a bug report.
  - **Transient outcome → toast. Persistent state → in-flow `.tds-alert`.**
    Load failures, form validation and "X is not configured" hints stay in the
    flow — the first two name something to fix, the third names something an
    operator has to go and set. Anything the user must **read or copy** (a
    temporary password, a one-time link) never goes in a toast.
  - **Never mount a `ToastHost`.** The frontend host mounts the only one; a
    second would double every toast.
  - The banner that keeps only failures gets `.tds-alert--danger`; several
    extensions were rendering "Fehler: …" in the info hue.
- **A destructive action needs a `<ConfirmDialog>`, and it is controlled.** Park
  the target in state from the row button, and let the dialog perform the action;
  pass `busy` while the request is in flight so it cannot be double-submitted
  (blocking `window.confirm()` gave that away for free — a non-blocking dialog
  must do it explicitly). Auditing every `method: "DELETE"` against its gate
  found **only 3 of 10 destructive actions confirmed at all** — invoices,
  customers, blog posts, FAQ entries, docs and milestones each deleted on a
  single unguarded click. The missing gate, not the ugly native prompt, is the
  failure mode to watch for; grep `method: "DELETE"` when you add one.
- **A JSX comment cannot sit in an expression position** — not after `=> (`, not
  in a ternary branch, not in a `.map()` return. It is valid only as JSX
  *children*. Put the note above the `return`; otherwise the build fails with a
  bare `Expected ")"` pointing at the comment's own closing line. The same applies
  to multi-line `{/* … */}` in an `.astro` template body.

For a component's **internal** layout, reach for the generic primitives before
inventing anything: `.tds-stack` (+ `--tight` / `--loose`) for a vertical stack —
form bodies, detail panels, reply lists; `.tds-row` (+ `--between`) for a
wrapping horizontal row — header rows, filter bars, tab strips; `.tds-compose`
(+ `__actions`) for a reply box. Those three plus the existing `.tds-toolbar`
(action rows) and `.tds-marginalia`-style `.marginalia` (metadata and hint text)
absorbed 46 of the class names extensions had invented for exactly these shapes.

**~31 names across the platform legitimately stay bespoke** and are knowingly
unstyled — genuinely singular internals such as `cms-editor__blocks`,
`live-chat-settings__matrix`, `blog-editor__preview`, `api-wiki__routes`,
`time-tracker__timer`. If you add one, expect it to render on browser defaults
until someone gives it a rule; that is the accepted trade, not an oversight.
(`widget-slot__*` looks orphan but is styled by an inline `<style>` in the host's
dashboard page.)

## Conventions baked in (don't regress)

- Depends on the **published** `tds-frontend-contract` (`^0.2.0`), not a path link —
  npm from GitHub Packages (via `.npmrc` + `NPM_TOKEN`), Composer from the public
  VCS repo. No local path repo — Composer fatals on a missing path repo in CI, so
  extensions resolve the contract purely via VCS (a clone, not a sibling).
- CI installs with **`npm install --no-package-lock`** (win32 lockfile breaks the
  Linux runner) — never `npm ci` + a committed lockfile here.
- `PACKAGE_TOKEN` (a public-Packages-friendly PAT) both installs the contract and
  publishes this package; set `NPM_TOKEN` from it in CI.
- **The npm and Composer versions move independently** — bump `package.json` for
  a frontend-only change (markup, islands, styling) and `composer.json` only when
  the PHP `Module` actually changes. The pushed tag is the Composer release ref.
  Every extension in the platform has its npm version ahead of its Composer one
  for exactly this reason; an earlier revision of this file claimed they move "in
  lockstep", which no repo has ever done.
- Declares `tds-shared` as a **peer** dependency (`>=0.14.0`), like astro and
  react: the product installs it, and a second copy in the extension would mean
  two token sets. An extension that omits it still builds — the product's copy
  resolves — so the omission is invisible until someone installs the package
  standalone. Keep it declared.

## Enabling it lives in other repositories

Nothing in this repository can verify the last step, because it happens
elsewhere: `new ShopModule()` in `tds-core-frontend-api`'s `Modules::enabled()`
plus the Composer require, `shop` in `SiteKeyPolicy::KNOWN`, and the manifest
appended to `extensions` in both products' `astro.config.mjs`. A green suite
here says nothing about whether the extension is switched on.

`SHOP_PUBLIC_URL` on the API host overrides the shop origin used to build
product and click-redirect URLs. It has a coded default, because the catalogue
must answer on a host where nobody has configured anything yet.

## Tests
- **CI runs `test:run` since 2026-08-25 — before that, none of these suites
  ever ran on a runner.** `_build.yml` had type-check, lint:primitives and
  build. That included the `ApiDocSource` parity test, whose entire job is to
  fail when a route gains or loses documentation.
- **The suites used to run against a tds-shared a dozen minors old, and the
  first honest run cost 30 failures across the twelve shipping extensions.**
  This package declares tds-shared as a **peer** with a `>=0.19.0` floor, so a
  fresh install resolved 0.19.0 while every product build composes the current
  one. Three separate behaviours had moved underneath the tests, and each is
  worth knowing because a new suite will hit them again:
  - `apiFetch` consults the host-side runtime config (`/tds-runtime.json`)
    before it resolves a URL, so `fetch.mock.calls[0]` is that probe, not the
    endpoint. Call **`primeRuntimeConfig(null)`** in `beforeEach` — the panel
    products never ship that file (they render `<meta name="tds-api-base">`),
    so "absent" is also what happens in production.
  - `apiFetch` is **async**: the request leaves on a later microtask than the
    render. Reading `mock.calls` on the line after `render(...)` yields
    `undefined`; `await waitFor(() => expect(fetch).toHaveBeenCalled())` first.
  - A multipart upload now carries an **empty** `headers` object rather than
    `undefined`. Identical to the browser — the boundary is still the
    browser's to set — so assert "no content-type header", never
    "headers is undefined".


```bash
npm run test:run    # vitest, 40 tests (jsdom per-file via a @vitest-environment docblock)
```

The suites target the failures that have no other symptom.

- **`php/tests/PriceFreshnessTest.php`** — the 24-hour rule, including the
  inclusive boundary and the two dangerous defaults: a never-checked quote must
  read as unusable, and `0` must survive as a real price (`if (!$cents)` would
  drop it).
- **`php/tests/ShopModuleTest.php`** — RBAC (401 vs 403 kept distinct), the
  fail-soft public reads, and the advertising label surviving the degraded path.
  Its container stub's PDO **always throws**, on purpose: that is the state
  nobody exercises by hand, and it is the one the public routes exist to
  survive. The "passes the gate" test asserts the DB exception rather than a
  status code — reaching the repository at all is the proof.
- **`php/tests/ShopMigrationsTest.php`** — filename↔classname, module prefix,
  band ownership, and `'signed' => false` on integer foreign keys. All without a
  database, because the thing it guards (one bad file aborting migrations for
  *every* module) surfaces on the first request after a deploy and not here.
  The signedness lint reads the column **type**, not just the name: matching
  `*_id` alone flagged `external_id`, which is an ASIN.
- **`php/tests/ShopApiDocsTest.php`** — documented routes and mounted routes are
  the same set, both directions.
- **`src/index.test.ts`** — the manifest as a product build sees it. Note the
  ungated-widget test: it pins the set of permissionless widgets to exactly
  `["shop-picks"]`, because that failure runs both ways — gating it hides the
  placement from the customers it exists for, ungating another leaks catalogue
  counts to every logged-in customer.
- **`islands/WidgetBody.test.tsx`** — the widget shows a dash, never a zero,
  when its request fails, and reaches its endpoint through an absolute URL.
- **`tests/packaging.test.ts`** — every manifest specifier resolves, is
  exported, and ships.

Verified by mutation on the load-bearing rule: flipping `>` to `>=` in the
freshness comparison is caught by the boundary test in both the PHP and the
`tds-shared` suite.

## Mobile layout

This package ships **no CSS**, so every layout decision is a shared class or a
Tailwind utility, and neither is checked by anything at runtime. Two rules:

- **A row of more than two things — or any row holding a full-width field —
  goes on `.tds-row`, `.tds-list__row` or `.tds-toolbar`.** All three wrap.
  A hand-rolled `flex` does not, and on a 375px screen the overflow is not
  even visible: `body { overflow-x: hidden }` clips it, so the content simply
  is not there.
- **A `<table>` needs `tds-table` and nothing else.** The primitive turns
  itself into a horizontal scroller below 40rem; an extra `overflow-x`
  wrapper or an inline style is redundant. A table with no focusable cell
  also needs `tabindex="0"` + `role="region"` + a label, or its scrollport
  cannot be reached by keyboard.

`npm run lint:primitives` enforces the class part of this (including a
`<table>` without `tds-table` and a flex/grid table cell, which silently
drops the cell out of the column algorithm). It is a **regex scan**, so a tag
name written inside a comment counts as markup — name elements in prose.

**`scripts/lint-primitives.mjs` here is the seed for all 20 repos that carry it**
(14 `tds-ext-*`, 4 `tds-tool-*`, `tds-core-frontend-pkg`, `tds-tools-frontend`)
and every copy is byte-identical. Reusable workflows are org-blocked, so copying
is the mechanism — change it here, then propagate to all 20 and re-run each one.

It also checks that a `btn-*` variant actually exists in tds-shared (`btn
btn-secondary` used to pass while matching no rule at all — geometry and a touch
target, no colour) and accepts `.tds-dropdown__trigger`/`__item` as shared
classes, because forcing `.btn` onto a menu row would give it pill radius and
button padding. Those two checks lived only in `tds-core-frontend-pkg` until
2026-08-16; they are merged in, so don't fork the script again.

> **Fixed 2026-08-16 — read this before "fixing" a false positive.** Tags used to
> be matched with `[^>]*>`, which stops at the **first `>`**, and an arrow handler
> (`onClick={() => …}`) supplies one. A correctly classed control written after its
> handler was therefore reported as bare, and every repo had absorbed that by
> putting `className` first — a convention nobody chose, enforced by a bug. It also
> under-reported in silence: the `<td>`/`<th>` rule looks *for* a class, so a
> truncated tag meant a `flex` cell was never found at all. `readTag()` now walks
> the tag tracking string state and `{}` depth. `classOf()` additionally resolves a
> local `const field = "…"`, so the check no longer depends on what a variable is
> *named* (`{field}` passed, `{area}` did not). All 20 repos were re-run after the
> fix with **zero findings**, so nothing had been hiding behind it.

## The Amazon offer sync

### How it runs at all, on a host with no cron

The production host has no SSH, no guaranteed scheduler, and `proc_open` is
disabled — there is no process to hold a loop. So the sync is modelled on the
core's `MigrationRunner`: **work happens on an ordinary request, after the
response has been sent.** `Support\SyncTicker` claims a marker file under a
non-blocking `flock`, calls `fastcgi_finish_request()`, then runs one bounded
batch.

Three triggers, in order of how much they can be relied on:

1. **Request-driven** (`/content/shop`) — primary, and the only one that needs
   no configuration.
2. **The panel button** (`POST /shop/sync/enqueue`) — after an import, or after
   fixing an Amazon account.
3. **An external ping** (`POST /shop/sync/tick`, token-gated) — an uptime
   monitor, a GitHub Actions schedule, a Plesk task if the host has one. An
   accelerator, explicitly **not** a prerequisite. An earlier plan named the
   Plesk scheduler as primary; that was a guess against a documented fact about
   this host.

**The honest consequence: an API with no traffic does not sync.** For a shop
that is fitting — the pages being read drive the refresh of the prices they
show — but after a quiet spell the first visitor sees prices withheld rather
than stale. That is the 24-hour rule working, not a bug.

### Four rules in SyncTicker, each a bug if dropped

Claim the marker **before** the slow work (or two simultaneous requests both
start the same batch); use a **non-blocking** lock (or the sync's slowness
becomes the visitor's); **flush the response first**; **swallow everything** (a
sync failure must never be a 500 on a page somebody asked for).

The per-tick budget — three API calls, four seconds — is not tuning. Even after
`fastcgi_finish_request()` the PHP worker is occupied, so an unbounded tick
takes a worker out of the pool for as long as Amazon feels like taking.

### Revoked is a state, not an error

Amazon withdraws API access when qualifying sales stop. Retrying cannot help
and hammering a revoked account is what the licence objects to, so
`PaApiException::isPermanent()` routes it to `markRevoked()` — the whole queue
halts and the panel says why. Everything else keeps working: the affiliate
links are not the API, and prices age out of view within a day on their own.

That is exactly why `SyncWidget` exists. A revoked account has **no other
symptom** — the shop looks fine, prices just quietly stop appearing.

### `price_checked_at` is stamped in exactly one place

`OfferSync::apply()`. It is the claim that a price came from the API at a known
moment, and it is what the 24-hour rule reads. Nothing else in this package may
set it — `setOffers()` deliberately leaves it null for a hand-typed affiliate
price, so such a price is stored but not displayed until the sync confirms it.

### The signing is split out so it can be tested

`Support\PaApiSigner` is pure and takes its clock as an argument, because it is
the only part of the integration provable without an Amazon account. A wrong
signature surfaces in production as `IncompleteSignatureException` from a
server that will not say which of the four SigV4 steps it disagreed with.
`PaApiSignerTest` pins the canonical request, the scope, the key derivation and
the resulting signature.

## The checkout (own digital service packages)

### Three legal requirements that shaped the code

**§ 356 Abs. 4 BGB — the withdrawal confirmation.** For a digital *service* the
right of withdrawal lapses on full performance only if the customer expressly
agreed beforehand and confirmed they knew what they were giving up. So
`POST /shop/checkout` **refuses** without `withdrawalConsent: true`, and
`shop_order.withdrawal_consent_text` stores the **wording** rather than a flag —
what has to be provable later is which sentence they agreed to, and that
sentence will be edited over the years. A boolean would leave every past order
pointing at today's text.

**§ 312j Abs. 3 BGB — the order button.** It must read "Zahlungspflichtig
bestellen", with the mandatory details immediately above it. Stripe's hosted
button says "Bezahlen". So the compliant order is: **our** `/kasse` page carries
the details, the confirmation and the correctly-labelled button; pressing it
creates the session, and Stripe is only the payment step that follows. Sending a
visitor straight to Stripe skips the declaration.

**§ 3a Abs. 5 UStG — where we may sell.** An electronically supplied service to
a consumer elsewhere in the EU shifts the place of supply to their country and
eventually means an OSS registration. `SHOP_ALLOWED_COUNTRIES` defaults to `DE`
and the check happens on **our** server, before Stripe, so the refusal can be
explained.

### Money

Integer cents throughout, and `vat_rate_bp` in basis points (1900 = 19 %). A
percentage as a float is how rounding errors reach an invoice.

`OrderRepository::price()` rounds **the tax**, then adds. Computing a gross
first and deriving the tax back out of it loses a cent on roughly a third of
amounts — and it is the net figure a VAT return is built from.

Every amount is frozen into the order row at purchase and never recomputed. A
receipt must still show what was actually charged after a price or rate change.

**The price is read from the database, never from the request.** A posted price
is a price the customer chose.

### The webhook

Mounted at `/shop/stripe/webhook`, deliberately **outside** `/content/shop`:
`SiteKeyMiddleware::matches()` compares segment-wise, and Stripe holds no site
key, so under the prefix every delivery would be rejected. It authenticates by
signature over the **raw** body — a parsed-and-re-encoded payload will not
verify.

`markPaid()` is idempotent through its `WHERE status = 'pending'` clause,
because Stripe retries until it gets a 2xx and a second delivery must not
fulfil twice. An unhandled event type also answers 200; anything else makes
Stripe retry it forever.

A missing webhook secret answers **503**, not 200. Failing open here would mean
a host that forgot to configure it accepts any POST as payment.

### These are services, not downloads

Nothing is delivered automatically, and there is no file, no signed URL and no
download counter anywhere in this package. A paid order sits in
`/shop/bestellungen` until somebody does the work and marks it done — which is
why the widget counts *paid but unfulfilled* rather than *paid*.
