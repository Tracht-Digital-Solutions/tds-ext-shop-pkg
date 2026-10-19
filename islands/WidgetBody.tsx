import { SummaryMetric } from "@tracht-digital-solutions/tds-shared/components";

interface Summary {
  published: number;
  drafts: number;
  unwritten: number;
  offers: number;
  stalePrices: number;
}

/**
 * Shop widget body. Leads with the published count; the two numbers under it
 * are the ones that need work (no own text yet, stale prices). A failure reads
 * `—` — a zero would be a number the reader has no reason to doubt.
 */
export default function WidgetBody() {
  return (
    <SummaryMetric<Summary> endpoint="/shop/summary" value={(d) => d.published}>
      {(d) => (
        <ul className="tds-list">
          <li className="tds-list__row">
            <span>Ohne eigenen Text</span>
            <span>{d.unwritten}</span>
          </li>
          <li className="tds-list__row">
            <span>Preise veraltet</span>
            <span>{d.stalePrices}</span>
          </li>
        </ul>
      )}
    </SummaryMetric>
  );
}
