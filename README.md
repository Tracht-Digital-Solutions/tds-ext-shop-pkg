# tds-ext-shop-pkg

**TDShop** — the panel half of `shop.tracht-digital.de`. Maintains a catalogue of
digitalisation and technology products (affiliate offers and TDS's own digital
service packages), and serves the placements that embed those products in the
journal and the customer portal.

One repository, two published packages: an npm package (`@tracht-digital-solutions/tds-ext-shop`)
for the frontend manifest, pages, widgets and islands, and a Composer package
(`tracht-digital-solutions/tds-ext-shop`) for the PHP `Module`. Versions move in
lockstep; the git tag is the Composer ref.

## What it contributes

| Slot | What |
|---|---|
| Nav + route | `/shop` (catalogue), `/shop/platzierungen` (advertising slots) |
| Widgets | `shop-summary` (staff), `shop-picks` (**ungated** — the customer-portal placement) |
| Settings | A placeholder until the Amazon and Stripe keys arrive |
| Permissions | `shop:read`, `shop:write`, `shop:orders`, `shop:sync` |
| Public API | `/content/shop*` — catalogue, categories, one product, a resolved placement, an offer's redirect target |
| Panel API | `/shop/*` — product CRUD, offers, placements, click stats, summary |

## Two rules that are not preferences

**Prices expire after 24 hours.** The Amazon Product Advertising API licence
allows a fetched price to be displayed for one day, with the time it was
retrieved. `Support\PriceFreshness` strips an expired price *server-side*, so it
never reaches a browser, and `tds-shared`'s `isPriceStale()` enforces the same
rule again in the renderer. The duplication is deliberate: the failure is
invisible — last week's price renders perfectly — and it costs the partner
programme rather than a layout.

**Affiliate offers are labelled as advertising.** § 5a Abs. 4 UWG. The label is
served by the placement endpoint and rendered by the shared `ProductCard`, so no
consuming surface can forget it. It is not a setting.

## Two things worth knowing about the schema

- **A product is split into a language-neutral core (`shop_product`) and its
  translations (`shop_product_translation`)** — unlike `blog_post`, which stores
  one row per language. An article has no language-neutral core; a product does
  (ASIN, network, price, availability). Duplicating that per language and then
  pointing a price sync at it is how the German row ends up at 249 € and the
  English one at 259 €.
- **`editorial_status` is a publishing gate.** Only `published` — a product with
  its own written assessment — reaches the sitemap and gets `<meta robots index>`.
  Everything else renders but stays out of the index, which is what stops a bulk
  import of 300 ASINs from turning the domain into the thin-affiliate pattern
  search engines demote.

## Develop

```bash
npm install --no-package-lock   # a Windows lockfile breaks Linux CI
npm run type-check
npm run lint:primitives         # shared CSS classes only — this package ships no CSS
npm run test:run
npm run build

php ~/composer.phar install
php vendor/bin/phpunit
```

Database-backed tests skip without `TDS_TEST_DB_DSN`. The PHP suite otherwise
runs against a container stub whose PDO always fails — which is not a shortcut
but the point: it pins the fail-soft behaviour of the public routes, the case
nobody exercises by hand.

Migrations use the `20260907*` band. Every enabled module's migrations run in one
process against one `phinxlog`, so a filename/class mismatch or a reused version
aborts migrations for **every** module. `ShopMigrationsTest` checks all of that
without a database — including that foreign keys carry `'signed' => false`, which
MariaDB silently corrects and MySQL 8 (production) rejects.

## Enable it

Two edits in two other repositories, neither of which any test here can catch:

1. `tds-core-frontend-api` — `composer require tracht-digital-solutions/tds-ext-shop`,
   then `new ShopModule()` in `src/Modules.php::enabled()`, and `shop` in
   `SiteKeyPolicy::KNOWN`.
2. `tds-admin-frontend` and `tds-customer-frontend` — import the manifest and
   append it to `extensions` in `astro.config.mjs`.

Optionally set `SHOP_PUBLIC_URL` on the API host if the shop does not live at
`https://shop.tracht-digital.de`; product and click-redirect URLs are built from
it, because two of the three consuming surfaces render on a different origin.

## Scope of this version

Checkpoint 1: catalogue, placements, click counting. The Amazon offer sync
(`shop:sync`) and the Stripe checkout (`shop:orders`) are later checkpoints —
their permissions are declared because the backend declares them, but neither
contributes a nav entry, widget or route yet. A menu item leading to an empty
screen teaches an operator to distrust the menu.
