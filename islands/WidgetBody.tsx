import { useEffect, useState } from "react";

import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { Skeleton } from "@tracht-digital-solutions/tds-shared/components";

interface Summary {
  published: number;
  drafts: number;
  unwritten: number;
  offers: number;
  stalePrices: number;
}

/**
 * The TDShop dashboard widget.
 *
 * Leads with the published count, but the two numbers underneath are the ones
 * worth a glance:
 *
 * - `unwritten` — products that render but stay out of the search index for
 *   want of their own assessment. That is the number deciding whether the
 *   catalogue reads as a resource or as a link farm, and it is invisible
 *   everywhere else.
 * - `stalePrices` — offers whose quote has aged past 24 hours and is therefore
 *   no longer shown. A rising number here means the offer sync has stopped,
 *   which otherwise has no symptom at all: the pages keep working, they just
 *   quietly stop showing prices.
 *
 * `apiFetch`, never a relative `fetch`: a relative call hits the host's SPA
 * fallback, gets 200 with an HTML body, and renders a calm empty state.
 */
export default function WidgetBody() {
  const [data, setData] = useState<Summary | null>(null);
  const [failed, setFailed] = useState(false);

  useEffect(() => {
    let alive = true;
    void (async () => {
      try {
        const res = await apiFetch("/shop/summary");
        if (!res.ok) throw new Error(String(res.status));
        const json = (await res.json()) as Summary;
        if (alive) setData(json);
      } catch {
        // A widget that cannot load says so. Rendering a zero would be a
        // number the reader has no reason to doubt.
        if (alive) setFailed(true);
      }
    })();
    return () => {
      alive = false;
    };
  }, []);

  if (failed) {
    return <p className="tds-widget__metric">—</p>;
  }

  return (
    <>
      <p className="tds-widget__metric" aria-busy={data === null}>
        {data ? data.published : <Skeleton width="3ch" height="1.75rem" />}
      </p>
      {data ? (
        <ul className="tds-list">
          <li className="tds-list__row">
            <span>Ohne eigenen Text</span>
            <span>{data.unwritten}</span>
          </li>
          <li className="tds-list__row">
            <span>Preise veraltet</span>
            <span>{data.stalePrices}</span>
          </li>
        </ul>
      ) : null}
    </>
  );
}
