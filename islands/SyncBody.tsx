import { useCallback, useEffect, useState } from "react";

import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { Skeleton } from "@tracht-digital-solutions/tds-shared/components";
import { toast } from "@tracht-digital-solutions/tds-shared/toast";

interface Status {
  queue: Record<string, number>;
  revoked: boolean;
  configured: boolean;
  lastRun: { finished_at: string | null; items_ok: number; items_failed: number; error: string | null } | null;
}

/**
 * The offer-sync widget.
 *
 * It exists for one state that has no other symptom: **Amazon has withdrawn
 * API access.** That happens when qualifying sales stop, and nothing about it
 * looks broken — the shop keeps working, the links keep working, prices simply
 * stop appearing as each quote passes its 24-hour life. Without this widget
 * the first sign would be someone noticing, weeks later, that the catalogue
 * has no prices.
 *
 * So a revoked queue is rendered as a persistent in-flow alert, not a toast: a
 * toast disappears while it is being read, and this is a standing condition
 * somebody has to act on.
 */
export default function SyncBody() {
  const [status, setStatus] = useState<Status | null>(null);
  const [failed, setFailed] = useState(false);
  const [busy, setBusy] = useState(false);

  const load = useCallback(async () => {
    try {
      const res = await apiFetch("/shop/sync/status");
      if (!res.ok) throw new Error(String(res.status));
      setStatus((await res.json()) as Status);
      setFailed(false);
    } catch {
      setFailed(true);
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  const run = async () => {
    setBusy(true);
    try {
      const res = await apiFetch("/shop/sync/enqueue", { method: "POST" });
      if (!res.ok) throw new Error(String(res.status));
      const result = (await res.json()) as { ok: number; failed: number; stopped: string | null };
      if (result.stopped === "revoked") {
        toast.error("Amazon lehnt den Zugang weiterhin ab — Zugangsdaten und Partnerprogramm prüfen.");
      } else {
        toast.success(`${result.ok} Angebote aktualisiert, ${result.failed} fehlgeschlagen.`);
      }
      await load();
    } catch (err) {
      toast.error(`Abgleich fehlgeschlagen (${err instanceof Error ? err.message : "unbekannt"}).`);
    } finally {
      setBusy(false);
    }
  };

  if (failed) return <p className="tds-widget__metric">—</p>;
  if (!status) return <Skeleton width="6ch" height="1.75rem" />;

  if (!status.configured) {
    return (
      <p>
        Nicht konfiguriert. Zugangsdaten unter <strong>Einstellungen → TDShop</strong>.
      </p>
    );
  }

  const pending = (status.queue.pending ?? 0) + (status.queue.error ?? 0);

  return (
    <>
      {status.revoked ? (
        <div className="tds-alert tds-alert--danger">
          Amazon hat den API-Zugang entzogen. Der Katalog läuft weiter, aber
          Preise verschwinden nach 24 Stunden aus der Anzeige. Prüfen, ob das
          Partnerprogramm noch aktiv ist, dann hier erneut anstoßen.
        </div>
      ) : null}

      <p className="tds-widget__metric">{pending}</p>
      <ul className="tds-list">
        <li className="tds-list__row">
          <span>Wartend</span>
          <span>{pending}</span>
        </li>
        <li className="tds-list__row">
          <span>Zuletzt aktualisiert</span>
          <span>{status.lastRun?.items_ok ?? 0}</span>
        </li>
      </ul>

      <div className="tds-toolbar">
        <button type="button" className="btn btn-ghost" onClick={() => void run()} disabled={busy} aria-busy={busy}>
          Jetzt abgleichen
        </button>
      </div>
    </>
  );
}
