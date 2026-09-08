import { useCallback, useEffect, useState } from "react";

import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { Spinner } from "@tracht-digital-solutions/tds-shared/components";
import { resolveChipVariant } from "@tracht-digital-solutions/tds-shared/design";
import { toast } from "@tracht-digital-solutions/tds-shared/toast";

interface Order {
  id: number;
  order_no: string;
  email: string;
  name: string | null;
  status: "pending" | "paid" | "refunded" | "failed";
  gross_cents: number;
  net_cents: number;
  tax_cents: number;
  currency: string;
  country: string;
  items: string | null;
  fulfilled_at: string | null;
  withdrawal_consent_at: string | null;
  withdrawal_consent_text: string | null;
  created_at: string;
}

const STATUS_LABEL: Record<Order["status"], string> = {
  pending: "Offen",
  paid: "Bezahlt",
  refunded: "Erstattet",
  failed: "Fehlgeschlagen",
};

const euro = (cents: number, currency: string) => {
  try {
    return new Intl.NumberFormat("de-DE", { style: "currency", currency }).format(cents / 100);
  } catch {
    return `${(cents / 100).toFixed(2)} ${currency}`;
  }
};

const date = (iso: string | null) => {
  if (!iso) return "—";
  const t = Date.parse(iso.replace(" ", "T") + (iso.endsWith("Z") ? "" : "Z"));
  return Number.isNaN(t) ? "—" : new Intl.DateTimeFormat("de-DE", { dateStyle: "short", timeStyle: "short" }).format(t);
};

/**
 * Orders for TDS's own digital service packages.
 *
 * Two things this screen shows that are not decoration:
 *
 * 1. **Paid but not yet delivered** is the working queue. These are services,
 *    not downloads — nothing is delivered automatically, so an order sits here
 *    until somebody does the work and says so. That is why "Als erbracht
 *    markieren" exists and why the widget counts `open` rather than `paid`.
 * 2. **The withdrawal confirmation is shown with its wording**, not as a tick.
 *    § 356 Abs. 4 BGB makes the sentence the customer agreed to the thing that
 *    has to be provable, and that sentence changes over the years — so each
 *    order carries its own copy, and this is where you read it back.
 */
export default function OrderList() {
  const [orders, setOrders] = useState<Order[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [busyId, setBusyId] = useState<number | null>(null);
  const [expanded, setExpanded] = useState<number | null>(null);

  const load = useCallback(async () => {
    try {
      const res = await apiFetch("/shop/orders");
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = (await res.json()) as { orders: Order[] };
      setOrders(json.orders);
      setError(null);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Unbekannter Fehler");
      setOrders([]);
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  const fulfil = async (order: Order) => {
    setBusyId(order.id);
    try {
      const res = await apiFetch(`/shop/orders/${order.id}/fulfil`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({}),
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      toast.success(`${order.order_no} als erbracht markiert.`);
      await load();
    } catch (err) {
      toast.error(`Fehlgeschlagen (${err instanceof Error ? err.message : "unbekannt"}).`);
    } finally {
      setBusyId(null);
    }
  };

  if (orders === null) return <Spinner />;

  const open = orders.filter((o) => o.status === "paid" && !o.fulfilled_at).length;

  return (
    <>
      {error ? (
        <div className="tds-alert tds-alert--danger">
          Bestellungen konnten nicht geladen werden: {error}
        </div>
      ) : null}

      {open > 0 ? (
        <div className="tds-alert tds-alert--warning">
          {open} bezahlte {open === 1 ? "Bestellung wartet" : "Bestellungen warten"} auf
          Erbringung. Diese Produkte sind Leistungen — es wird nichts automatisch
          ausgeliefert.
        </div>
      ) : null}

      {orders.length === 0 ? (
        <p className="tds-empty">Noch keine Bestellungen.</p>
      ) : (
        <table className="tds-table">
          <thead>
            <tr>
              <th>Nummer</th>
              <th>Datum</th>
              <th>Kunde</th>
              <th>Leistung</th>
              <th>Brutto</th>
              <th>Status</th>
              <th />
            </tr>
          </thead>
          <tbody>
            {orders.flatMap((order) => [
              <tr key={order.id}>
                <td>
                  <button
                    type="button"
                    className="btn btn-ghost"
                    onClick={() => setExpanded(expanded === order.id ? null : order.id)}
                    aria-expanded={expanded === order.id}
                  >
                    {order.order_no}
                  </button>
                </td>
                <td>{date(order.created_at)}</td>
                <td>
                  {order.name ?? "—"}
                  <br />
                  <small>{order.email}</small>
                </td>
                <td>{order.items ?? "—"}</td>
                <td>{euro(order.gross_cents, order.currency)}</td>
                <td>
                  <span
                    className={`chip ${resolveChipVariant(
                      order.status === "paid" ? "success" : order.status === "refunded" ? "warning" : "neutral",
                    )}`}
                  >
                    {STATUS_LABEL[order.status]}
                  </span>
                  {order.fulfilled_at ? (
                    <>
                      {" "}
                      <span className={`chip ${resolveChipVariant("info")}`}>Erbracht</span>
                    </>
                  ) : null}
                </td>
                <td>
                  {order.status === "paid" && !order.fulfilled_at ? (
                    <button
                      type="button"
                      className="btn btn-primary"
                      disabled={busyId === order.id}
                      aria-busy={busyId === order.id}
                      onClick={() => void fulfil(order)}
                    >
                      Als erbracht markieren
                    </button>
                  ) : null}
                </td>
              </tr>,

              /* The detail lives in its OWN full-width row, not inside the
                 number cell.
                 Below 40rem `.tds-table` becomes its own horizontal scroll
                 container, and a column is as wide as its widest cell — so the
                 full withdrawal wording sitting in the first cell would
                 stretch that column to thousands of pixels and turn the whole
                 table into a horizontal drag. A `colSpan` row belongs to no
                 column and therefore widens none. */
              expanded === order.id ? (
                <tr key={`${order.id}-detail`}>
                  <td colSpan={7}>
                    <small>
                      Widerruf bestätigt: {date(order.withdrawal_consent_at)}
                      <br />
                      „{order.withdrawal_consent_text ?? "—"}"
                      <br />
                      Netto {euro(order.net_cents, order.currency)} · USt{" "}
                      {euro(order.tax_cents, order.currency)} · {order.country}
                    </small>
                  </td>
                </tr>
              ) : null,
            ])}
          </tbody>
        </table>
      )}
    </>
  );
}
