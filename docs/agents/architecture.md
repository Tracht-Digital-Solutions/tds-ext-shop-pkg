# Architecture

## Routes

| Area | Routes | Access |
|---|---|---|
| Public catalogue | `GET /content/shop`, `/content/shop/categories`, `/content/shop/{slug}`, `/content/shop/placement/{key}`, `/content/shop/offer/{id}/target` | site key |
| Panel catalogue | `/shop/products…`, `/shop/products/{id}/offers`, `/shop/categories…`, `/shop/placements…`, `/shop/affiliate/lookup`, `/shop/clicks`, `/shop/summary` | `shop:read` / `shop:write` |
| Sync | `/shop/sync/status`, `POST /shop/sync/enqueue` | `shop:sync` |
| Sync ping | `POST /shop/sync/tick` | token |
| Checkout | `GET /shop/payment-methods`, `POST /shop/quote`, `POST /shop/checkout`, `GET /shop/order/{token}` | public (guest checkout) |
| Orders | `/shop/orders`, `POST /shop/orders/{id}/fulfil`, `POST /shop/orders/{id}/invoice` | `shop:orders` |
| Webhooks | `POST /shop/payment/{provider}/webhook`, legacy `POST /shop/stripe/webhook` | provider signature |

Exact patterns live in `php/docs/api.php`.

## Four rules that look like detail and aren't

### 1. A price older than 24 hours never leaves the server

The Amazon PA-API licence allows a fetched price to be shown for one day, with its retrieval
time. `Support\PriceFreshness` strips an expired price in `ProductRepository::offersFor()`
before the payload is built, and tds-shared's `isPriceStale()` enforces the same rule again in
the renderer.

- **The duplication is deliberate.** The failure is invisible (last week's price renders
  fine) and costs the partner programme. Two independent floors mean a consumer that forgets
  the check still can't display one.
- The timestamp is cleared with the price. A retrieval time left on a stripped price reads as
  "checked, and free".
- A hand-typed **affiliate** price gets no timestamp (`ProductRepository::setOffers()`), so it
  isn't shown until the sync confirms it. An **own** price is current by definition.

### 2. The advertising label is not a setting

§ 5a Abs. 4 UWG. `/content/shop/placement/{key}` serves `label` even from its degraded path,
and tds-shared's `ProductCard` renders it whenever an affiliate offer is present; an empty
`affiliateLabel` still yields the default. Three surfaces render the card; a label each decides
for itself is one they eventually forget.

### 3. A product is a language-neutral core plus translations

`shop_product` + `shop_product_translation`, **not** the blog's row-per-language. ASIN,
network, price, availability and retrieval time are language-neutral; duplicating them per
language lets an interrupted sync leave DE at 249 € and EN at 259 €. DE and EN slugs may differ
freely, because the pair joins on `product_id`. Don't "fix" this to match the blog.

### 4. `editorial_status` gates indexing

Only `published` (a product with its own written assessment) goes into the sitemap with
`<meta robots index>`. `stub` and `none` render but stay out of the index. This stops a bulk
import of hundreds of ASINs from turning the domain into a thin-affiliate site. It is a separate
axis from `status`.

## Site-key boundary

`siteKeyRoutes()` returns exactly `['/content/shop']`, matched segment-wise by
`SiteKeyMiddleware::matches()`. Outside it:

- the `/shop/*` panel routes (permission-gated; a key would be a second door),
- anything a visitor's browser calls (a key in a client bundle isn't a key),
- payment webhooks: no provider holds a site key, so they live under `/shop/payment/…`.

Never widen it to `/content`.

## Public reads degrade; panel routes don't

Every `/content/shop*` handler catches and answers an empty payload, because a 500 on a public
fetch silently drops a section. Panel routes deliberately **don't**: staff can act on a database
error, and a calm empty state would send them hunting for products that were never missing.
`ShopModuleTest` pins both halves with a PDO stub that always throws.

## Widgets

The only permissionless widget is `shop-picks`. Gating it hides the placement from customers;
ungating another leaks catalogue counts to every logged-in customer. The order widget counts
**paid but unfulfilled** orders.

## Enabling it happens elsewhere

Nothing here can verify the last step: `new ShopModule()` plus the Composer require in
`tds-core-frontend-api`'s `Modules::enabled()`, `shop` in `SiteKeyPolicy::KNOWN`, and the
manifest in both products' extension lists. A green suite here says nothing about whether the
extension is switched on.

`SHOP_PUBLIC_URL` on the API host overrides the shop origin used for product and click-redirect
URLs. It has a coded default, so the catalogue answers on an unconfigured host.

## Prepared catalogue

`php/db/seed/*.php` holds the prepared products and categories as plain arrays;
`Support\CatalogueSeed` writes them from migrations (`20261010000002`). Everything lands as a
draft that already passes `Support\ProductReadiness`, so the operator only presses „Freigeben“.

- Add products in a NEW migration with a new data file; a ran migration never re-reads its file.
- Own price = landing-page hourly rate × hours; the body says „inklusive 19 %“ (the shop shows gross).
- Affiliate rows carry only the ASIN. Price, partner-tagged URL and cover come from `OfferSync`.
- Affiliate slugs are embedded in seeded journal articles (tds-ext-blog-cms, `{{produkt:<slug>}}`,
  per language). Renaming one blanks that card silently; the blog-cms seed test catches it.
- Cover prompts: `php scripts/image-prompts.php` → `docs/product-image-prompts.md`.
