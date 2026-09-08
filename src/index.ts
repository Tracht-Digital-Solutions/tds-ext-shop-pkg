import { defineExtension } from "@tracht-digital-solutions/tds-frontend-contract";

/**
 * TDShop — the panel half of `shop.tracht-digital.de`.
 *
 * Maintains a catalogue of digitalisation and technology products: affiliate
 * offers (Amazon via the Product Advertising API, plus other partner
 * programmes) and TDS's own digital service packages. The same catalogue feeds
 * product placements in the journal and the customer portal, which is why this
 * one extension is composed into BOTH products.
 *
 * ### Scope of this version
 *
 * Complete: the catalogue, the placements that embed it in the journal and the
 * portal, the click counter, the Amazon offer sync, and the checkout for TDS's
 * own digital service packages.
 *
 * ### Why `shop-picks` carries no permission
 *
 * Every other widget here is staff-facing and gated. `shop-picks` is the
 * product placement in the customer portal's dashboard — it is shown TO
 * customers, so gating it on a `shop:*` right would hide it from exactly the
 * audience it exists for. A widget without `permission` is visible to any
 * authenticated user, which is the intent. It reads a public placement
 * endpoint and never touches the admin routes.
 */
export default defineExtension({
  id: "shop",
  name: "TDShop",
  version: "0.1.0",
  permissions: [
    { id: "shop:read", label: "Shop ansehen", group: "shop" },
    { id: "shop:write", label: "Produkte und Platzierungen bearbeiten", group: "shop" },
    { id: "shop:orders", label: "Bestellungen verwalten", group: "shop" },
    { id: "shop:sync", label: "Angebotsabgleich steuern", group: "shop" },
  ],
  nav: [
    {
      id: "shop",
      label: "TDShop",
      href: "/shop",
      icon: "shopping-bag",
      group: "content",
      order: 20,
      permission: "shop:read",
    },
  ],
  widgets: [
    {
      id: "shop-summary",
      title: "TDShop",
      island: "@tracht-digital-solutions/tds-ext-shop/widgets/Widget.astro",
      size: "sm",
      permission: "shop:read",
      dataEndpoint: "/shop/summary",
      order: 45,
    },
    {
      id: "shop-sync",
      title: "Angebotsabgleich",
      island: "@tracht-digital-solutions/tds-ext-shop/widgets/SyncWidget.astro",
      size: "sm",
      permission: "shop:sync",
      dataEndpoint: "/shop/sync/status",
      order: 46,
    },
    {
      // Deliberately ungated — see the class doc above.
      id: "shop-picks",
      title: "Empfehlungen",
      island: "@tracht-digital-solutions/tds-ext-shop/widgets/PicksWidget.astro",
      size: "md",
      order: 90,
    },
  ],
  settings: [
    {
      id: "shop",
      label: "TDShop",
      island: "@tracht-digital-solutions/tds-ext-shop/islands/Settings.astro",
      order: 55,
    },
  ],
  routes: [
    {
      pattern: "/shop",
      entrypoint: "@tracht-digital-solutions/tds-ext-shop/pages/Index.astro",
      permission: "shop:read",
    },
    {
      pattern: "/shop/platzierungen",
      entrypoint: "@tracht-digital-solutions/tds-ext-shop/pages/Placements.astro",
      permission: "shop:write",
    },
    {
      pattern: "/shop/bestellungen",
      entrypoint: "@tracht-digital-solutions/tds-ext-shop/pages/Orders.astro",
      permission: "shop:orders",
    },
  ],
  i18n: {
    de: {
      "shop.title": "TDShop",
      "shop.products": "Produkte",
      "shop.placements": "Platzierungen",
      "shop.orders": "Bestellungen",
      "shop.sync": "Angebotsabgleich",
      "shop.picks": "Empfehlungen",
    },
    en: {
      "shop.title": "TDShop",
      "shop.products": "Products",
      "shop.placements": "Placements",
      "shop.orders": "Orders",
      "shop.sync": "Offer sync",
      "shop.picks": "Recommendations",
    },
  },
});
