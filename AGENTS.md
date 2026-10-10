# AGENTS.md — tds-ext-shop-pkg

**TDShop**: the catalogue behind `shop.tracht-digital.de` (affiliate products via the Amazon
PA-API and our own service packages and goods), the placements that embed products in the
journal and customer portal, and a guest checkout with Stripe / PayPal and Lexware invoicing.
It contributes a PHP `Module` to `tds-core-frontend-api` and product, category, placement,
order, sync and settings screens to the panel products.

Read `tds-frontend-contract-pkg/AGENTS.md` first. Seeded from `tds-ext-template-pkg`.

## Commands

```bash
npm install --no-package-lock   # never npm ci
npm run type-check
npm run lint:primitives
npm run test:run                # vitest
npm run build                   # tsup
composer install && composer test   # phpunit
```

## Hard rules

- **A price older than 24 hours never leaves the server.** Keep both checks (PHP and tds-shared).
- The advertising label is not a setting; it survives every path.
- Products are a language-neutral core plus translations; DE/EN slugs may differ.
- Only `editorial_status = published` is indexable.
- `siteKeyRoutes()` is exactly `['/content/shop']`; webhooks live outside it.
- Public `/content/shop*` reads fail soft; panel routes do not.
- `price_checked_at` is set only in `OfferSync::apply()`. UTC and Berlin columns are never mixed.
- Prices always come from the database, never the request. Integer cents, VAT in basis points.
- Checkout needs the withdrawal consent wording for service lines; our `/kasse` page carries the order button.
- `WeroProvider` stays unconfigured until `docs/wero-adapter.md` is done.
- Call the API with `apiFetch`. Toast methods must exist (`tests/islandToasts.test.ts`). Never mount a `ToastHost`.
- Migrations: shop prefix, unsigned integer FKs. Stay in the `0.6.x` line; versions via the release workflow.
- Going live is `POST /shop/products/{id}/publish`, gated by `Support\ProductReadiness`; a save never publishes.

## Topic files

| File | Read before |
|---|---|
| [docs/agents/architecture.md](docs/agents/architecture.md) | Changing products, prices, labels, indexing, routes or site keys |
| [docs/agents/amazon-sync.md](docs/agents/amazon-sync.md) | Touching the PA-API client, `SyncTicker`, `OfferSync` or timestamps |
| [docs/agents/checkout.md](docs/agents/checkout.md) | Touching checkout, baskets, shipping, payment providers, webhooks or invoices |
| [docs/agents/backend.md](docs/agents/backend.md) | Touching PHP, migrations or `php/docs/api.php` |
| [docs/agents/frontend-conventions.md](docs/agents/frontend-conventions.md) | Touching any island, page or widget markup |
| [docs/agents/testing.md](docs/agents/testing.md) | Writing or changing tests |
| [docs/agents/release.md](docs/agents/release.md) | Releasing or changing CI |

Payment adapter design: [docs/wero-adapter.md](docs/wero-adapter.md).
Workspace rules: `../CLAUDE.md`. Cross-repo state: `../MIGRATION-STATUS.md`.
