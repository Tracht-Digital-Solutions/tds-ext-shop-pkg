import { useEffect, useState } from "react";

import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { ProductCard, SkeletonText } from "@tracht-digital-solutions/tds-shared/components";
import {
  emptyPlacement,
  type ShopOffer,
  type ShopPlacement,
  type ShopProductRef,
} from "@tracht-digital-solutions/tds-shared/schemas";

const PLACEMENT_KEY = "panel-dashboard";

/**
 * Product placement in the customer portal dashboard.
 *
 * Renders the shared `ProductCard`, which carries the advertising label and the
 * price-freshness rule itself — this island decides nothing about either, and
 * that is deliberate: three surfaces render this card, and a rule re-decided
 * per surface is a rule that eventually differs on one of them.
 *
 * ### Empty is a normal state here, not an error
 *
 * A slot with nothing in it renders NOTHING — no heading, no empty-state card,
 * no "no recommendations yet". This is advertising inside somebody's working
 * dashboard; an empty box explaining that there is no advertising is worse than
 * the absence it describes. The widget therefore collapses to null, and the
 * dashboard closes over the gap.
 */
export default function PicksBody() {
  const [placement, setPlacement] = useState<ShopPlacement | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let alive = true;
    void (async () => {
      try {
        const res = await apiFetch(`/content/shop/placement/${PLACEMENT_KEY}?lang=de`);
        if (!res.ok) throw new Error(String(res.status));
        const json = (await res.json()) as ShopPlacement;
        if (alive) setPlacement(json);
      } catch {
        // Degrade to the empty slot rather than surfacing a failure. A
        // customer cannot act on the shop being unreachable, and an error in
        // an advertising box reads as the portal itself being broken.
        if (alive) setPlacement(emptyPlacement(PLACEMENT_KEY));
      } finally {
        if (alive) setLoading(false);
      }
    })();
    return () => {
      alive = false;
    };
  }, []);

  if (loading) {
    return <SkeletonText lines={2} />;
  }
  if (!placement || placement.products.length === 0) {
    return null;
  }

  // `offer.url` already points at the shop's `/go/{id}` redirect — the API
  // builds it, so the partner tag stays in one place and no consumer has to
  // assemble a link. Only the attribution query is added here, since only this
  // island knows which surface and slot the click came from.
  const attribute = (url: string, lang: string): string =>
    `${url}?source=customer&placement=${PLACEMENT_KEY}&lang=${lang}`;

  return (
    <div className="tds-product-strip">
      {placement.products.map((product: ShopProductRef) => (
        <ProductCard
          key={product.slug}
          product={{
            ...product,
            offers: product.offers.map((o: ShopOffer) => ({
              ...o,
              url: attribute(o.url, product.lang),
            })),
          }}
          variant="card"
          lang={product.lang}
          affiliateLabel={placement.label}
        />
      ))}
    </div>
  );
}
