import { useEffect, useState } from "react";

import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { Spinner } from "@tracht-digital-solutions/tds-shared/components";
import { toast } from "@tracht-digital-solutions/tds-shared/toast";

interface Masked {
  key: string;
  secret: boolean;
  configured?: boolean;
  last4?: string | null;
  value?: string;
}

const NS = "/admin/settings/shop";

/**
 * TDShop settings — the Amazon Product Advertising credentials.
 *
 * Persisted in the core's runtime settings store (admin-only), so they live in
 * the database rather than in `.env` and can be changed without a deploy.
 * Secrets come back **masked** (`configured` + `last4`) and a blank secret on
 * save keeps the stored value — the raw key never round-trips to the browser.
 *
 * The module reads them DB-first with an env fallback, and treats all three of
 * key/secret/tag as one unit: with any of them missing the sync is simply off,
 * rather than half-configured and failing every call with a signature error
 * that reads like a code bug.
 */
export default function ShopSettings() {
  const [loaded, setLoaded] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  const [accessState, setAccessState] = useState<Masked | null>(null);
  const [secretState, setSecretState] = useState<Masked | null>(null);
  const [accessInput, setAccessInput] = useState("");
  const [secretInput, setSecretInput] = useState("");
  const [partnerTag, setPartnerTag] = useState("");
  const [marketplace, setMarketplace] = useState("www.amazon.de");

  const [stripeKeyState, setStripeKeyState] = useState<Masked | null>(null);
  const [stripeHookState, setStripeHookState] = useState<Masked | null>(null);
  const [stripeKeyInput, setStripeKeyInput] = useState("");
  const [stripeHookInput, setStripeHookInput] = useState("");

  const load = async () => {
    try {
      const res = await apiFetch(NS);
      if (!res.ok) {
        setError(
          res.status === 401 || res.status === 403
            ? "Nur für Administratoren."
            : `Einstellungen konnten nicht geladen werden (HTTP ${res.status}).`,
        );
        setLoaded(true);
        return;
      }
      const data = (await res.json()) as { settings?: Masked[] };
      const map = new Map((data.settings ?? []).map((s) => [s.key, s]));
      setAccessState(map.get("amazon_access_key") ?? null);
      setSecretState(map.get("amazon_secret_key") ?? null);
      setPartnerTag(map.get("amazon_partner_tag")?.value ?? "");
      setMarketplace(map.get("amazon_marketplace")?.value ?? "www.amazon.de");
      setStripeKeyState(map.get("stripe_secret_key") ?? null);
      setStripeHookState(map.get("stripe_webhook_secret") ?? null);
      setError(null);
    } catch {
      setError("Keine Verbindung zur API.");
    } finally {
      setLoaded(true);
    }
  };

  useEffect(() => {
    void load();
  }, []);

  const save = async () => {
    setBusy(true);
    try {
      const settings: Masked[] = [
        // A blank secret is NOT an instruction to clear it — the core keeps the
        // stored value. That is what lets this form be saved without ever
        // holding the real key.
        { key: "amazon_access_key", secret: true, value: accessInput.trim() },
        { key: "amazon_secret_key", secret: true, value: secretInput.trim() },
        { key: "amazon_partner_tag", secret: false, value: partnerTag.trim() },
        { key: "amazon_marketplace", secret: false, value: marketplace.trim() },
        { key: "stripe_secret_key", secret: true, value: stripeKeyInput.trim() },
        { key: "stripe_webhook_secret", secret: true, value: stripeHookInput.trim() },
      ];
      const res = await apiFetch(NS, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ settings }),
      });
      if (!res.ok) {
        toast.error(`Speichern fehlgeschlagen (HTTP ${res.status}).`);
        return;
      }
      setAccessInput("");
      setSecretInput("");
      setStripeKeyInput("");
      setStripeHookInput("");
      toast.success("Gespeichert.");
      await load();
    } catch {
      toast.error("Speichern fehlgeschlagen — keine Verbindung zur API.");
    } finally {
      setBusy(false);
    }
  };

  const hint = (s: Masked | null) =>
    s?.configured ? `konfiguriert (…${s.last4 ?? "????"})` : "nicht konfiguriert";

  if (!loaded) return <Spinner />;

  return (
    <>
      {error ? <div className="tds-alert tds-alert--danger">{error}</div> : null}

      <div className="tds-alert tds-alert--info">
        Ohne diese drei Angaben ist der Angebotsabgleich aus. Der Katalog
        funktioniert trotzdem — Affiliate-Links sind nicht die API. Nur Preise
        verschwinden dann nach 24 Stunden von selbst aus der Anzeige, weil sie
        laut Lizenz nicht länger gezeigt werden dürfen.
      </div>

      <div className="tds-field-row">
        <label>
          Access Key <em>({hint(accessState)})</em>
          <input
            className="field-boxed"
            type="password"
            autoComplete="off"
            value={accessInput}
            onChange={(e) => setAccessInput(e.target.value)}
            placeholder="leer lassen = unverändert"
          />
        </label>
        <label>
          Secret Key <em>({hint(secretState)})</em>
          <input
            className="field-boxed"
            type="password"
            autoComplete="off"
            value={secretInput}
            onChange={(e) => setSecretInput(e.target.value)}
            placeholder="leer lassen = unverändert"
          />
        </label>
      </div>

      <div className="tds-field-row">
        <label>
          Partner-Tag
          <input
            className="field-boxed"
            value={partnerTag}
            onChange={(e) => setPartnerTag(e.target.value)}
            placeholder="tds-21"
          />
        </label>
        <label>
          Marktplatz
          <input
            className="field-boxed"
            value={marketplace}
            onChange={(e) => setMarketplace(e.target.value)}
          />
        </label>
      </div>

      <h3>Stripe</h3>
      <div className="tds-alert tds-alert--info">
        Für den Verkauf eigener Leistungen. Ohne diese beiden Schlüssel ist der
        Kauf aus — der Katalog bleibt davon unberührt. Das Webhook-Secret ist
        kein Nice-to-have: fehlt es, weist der Webhook <strong>jede</strong>{" "}
        Anfrage ab, statt irgendeinen POST als Zahlung zu akzeptieren.
      </div>

      <div className="tds-field-row">
        <label>
          Secret Key <em>({hint(stripeKeyState)})</em>
          <input
            className="field-boxed"
            type="password"
            autoComplete="off"
            value={stripeKeyInput}
            onChange={(e) => setStripeKeyInput(e.target.value)}
            placeholder="leer lassen = unverändert"
          />
        </label>
        <label>
          Webhook-Secret <em>({hint(stripeHookState)})</em>
          <input
            className="field-boxed"
            type="password"
            autoComplete="off"
            value={stripeHookInput}
            onChange={(e) => setStripeHookInput(e.target.value)}
            placeholder="whsec_…"
          />
        </label>
      </div>

      <div className="tds-toolbar">
        <button type="button" className="btn btn-primary" onClick={() => void save()} disabled={busy} aria-busy={busy}>
          Speichern
        </button>
      </div>
    </>
  );
}
