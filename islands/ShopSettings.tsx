import { useEffect, useState } from "react";

import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { secretHint, useRuntimeSettings } from "@tracht-digital-solutions/tds-shared/admin";
import { Alert, Spinner } from "@tracht-digital-solutions/tds-shared/components";
import { toast } from "@tracht-digital-solutions/tds-shared/toast";

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
  const settings = useRuntimeSettings("shop");
  const [rebuilding, setRebuilding] = useState(false);
  const [cacheUrl, setCacheUrl] = useState("https://shop.tracht-digital.de");
  const [cacheToken, setCacheToken] = useState("");

  const [accessInput, setAccessInput] = useState("");
  const [secretInput, setSecretInput] = useState("");
  const [partnerTag, setPartnerTag] = useState("");
  const [marketplace, setMarketplace] = useState("www.amazon.de");

  const [stripeKeyInput, setStripeKeyInput] = useState("");
  const [stripeHookInput, setStripeHookInput] = useState("");

  const [ppClientId, setPpClientId] = useState("");
  const [ppSecretInput, setPpSecretInput] = useState("");
  const [ppWebhookId, setPpWebhookId] = useState("");
  const [ppSandbox, setPpSandbox] = useState(false);

  const [weroPsp, setWeroPsp] = useState("");
  const [weroKeyInput, setWeroKeyInput] = useState("");
  const [weroHookInput, setWeroHookInput] = useState("");

  const [shipFlat, setShipFlat] = useState("");
  const [shipFreeFrom, setShipFreeFrom] = useState("");

  useEffect(() => {
    const get = (key: string) => settings.get(key)?.value;
    setCacheUrl(get("cache_url") ?? "https://shop.tracht-digital.de");
    setPartnerTag(get("amazon_partner_tag") ?? "");
    setMarketplace(get("amazon_marketplace") ?? "www.amazon.de");
    setPpClientId(get("paypal_client_id") ?? "");
    setPpWebhookId(get("paypal_webhook_id") ?? "");
    setPpSandbox((get("paypal_sandbox") ?? "") !== "");
    setWeroPsp(get("wero_psp") ?? "");
    setShipFlat(get("shipping_flat_cents") ?? "");
    setShipFreeFrom(get("shipping_free_from_cents") ?? "");
  }, [settings.settings]);

  const save = async () => {
    const saved = await settings.save([
      { key: "cache_url", value: cacheUrl.trim() },
      { key: "cache_token", secret: true, value: cacheToken.trim() },
      // A blank secret is NOT an instruction to clear it — the core keeps the
      // stored value. That is what lets this form be saved without ever
      // holding the real key.
      { key: "amazon_access_key", secret: true, value: accessInput.trim() },
      { key: "amazon_secret_key", secret: true, value: secretInput.trim() },
      { key: "amazon_partner_tag", value: partnerTag.trim() },
      { key: "amazon_marketplace", value: marketplace.trim() },
      { key: "stripe_secret_key", secret: true, value: stripeKeyInput.trim() },
      { key: "stripe_webhook_secret", secret: true, value: stripeHookInput.trim() },
      { key: "paypal_client_id", value: ppClientId.trim() },
      { key: "paypal_secret", secret: true, value: ppSecretInput.trim() },
      { key: "paypal_webhook_id", value: ppWebhookId.trim() },
      // Any non-empty value means sandbox; empty means live. A boolean would
      // be tidier, but the settings store holds strings and "false" is a
      // non-empty string — which is how a sandbox flag becomes a live one.
      { key: "paypal_sandbox", value: ppSandbox ? "1" : "" },
      { key: "wero_psp", value: weroPsp.trim() },
      { key: "wero_api_key", secret: true, value: weroKeyInput.trim() },
      { key: "wero_webhook_secret", secret: true, value: weroHookInput.trim() },
      // Cents, as a string, because that is what the settings store holds —
      // and cents rather than euros because every price in this package is an
      // integer number of them. A euro field here would be the one place a
      // float gets into the arithmetic.
      { key: "shipping_flat_cents", value: shipFlat.trim() },
      { key: "shipping_free_from_cents", value: shipFreeFrom.trim() },
    ]);
    if (!saved) return;
    setAccessInput("");
    setCacheToken("");
    setSecretInput("");
    setStripeKeyInput("");
    setStripeHookInput("");
    setPpSecretInput("");
    setWeroKeyInput("");
    setWeroHookInput("");
  };

  if (!settings.loaded) return <Spinner />;

  return (
    <>
      <Alert>{settings.error}</Alert>

      <h3>Öffentlichen Shop aktualisieren</h3>
      <p>Freigaben aktualisieren den Seiten-Cache automatisch. Das Token muss mit TDS_CACHE_TOKEN auf dem Shop-Host übereinstimmen. Angaben zuerst speichern.</p>
      <div className="tds-field-row">
        <label>Shop-Adresse
          <input className="field-boxed" type="url" value={cacheUrl} onChange={(e) => setCacheUrl(e.target.value)} />
        </label>
        <label>Cache-Token <em>({secretHint(settings.get("cache_token"))})</em>
          <input className="field-boxed" type="password" autoComplete="off" value={cacheToken} onChange={(e) => setCacheToken(e.target.value)} placeholder="leer lassen = unverändert" />
        </label>
      </div>
      <button className="btn btn-ghost" type="button" disabled={settings.busy || rebuilding} aria-busy={rebuilding} onClick={async () => {
        setRebuilding(true);
        try {
          const res = await apiFetch("/shop/cache/rebuild", { method: "POST" });
          const report = await res.json() as { cached?: boolean; cache_status?: string };
          if (res.ok && report.cached) toast.success("Öffentlicher Shop aktualisiert.");
          else toast.warning(report.cache_status === "not_configured" ? "Bitte Shop-Adresse und Cache-Token konfigurieren und speichern." : "Seiten-Cache konnte nicht vollständig aktualisiert werden. Bitte Verbindung und Token prüfen.");
        } catch {
          toast.danger("Keine Verbindung zur API.");
        } finally {
          setRebuilding(false);
        }
      }}>Seiten-Cache neu aufbauen</button>

      <Alert tone="info">
        Ohne diese drei Angaben ist der Angebotsabgleich aus. Der Katalog
        funktioniert trotzdem — Affiliate-Links sind nicht die API. Nur Preise
        verschwinden dann nach 24 Stunden von selbst aus der Anzeige, weil sie
        laut Lizenz nicht länger gezeigt werden dürfen.
      </Alert>

      <div className="tds-field-row">
        <label>
          Access Key <em>({secretHint(settings.get("amazon_access_key"))})</em>
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
          Secret Key <em>({secretHint(settings.get("amazon_secret_key"))})</em>
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
      <Alert tone="info">
        Für den Verkauf eigener Leistungen. Der Secret Key ist nur nötig, wenn
        der Shop ein eigenes Stripe-Konto nutzen soll — sonst gilt das zentrale
        Konto unter Einstellungen → Zahlungen (Stripe). Das Webhook-Secret ist
        kein Nice-to-have: fehlt es, weist der Webhook <strong>jede</strong>{" "}
        Anfrage ab, statt irgendeinen POST als Zahlung zu akzeptieren.
      </Alert>

      <div className="tds-field-row">
        <label>
          Secret Key, optional <em>({secretHint(settings.get("stripe_secret_key"))})</em>
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
          Webhook-Secret <em>({secretHint(settings.get("stripe_webhook_secret"))})</em>
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

      <h3>PayPal</h3>
      <Alert tone="info">
        Direkt über Orders v2, nicht über einen zweiten Dienstleister. Die
        Webhook-ID gehört zu den Zugangsdaten und ist keine Formalität: ohne sie
        lässt sich ein Ereignis nicht prüfen, und eine Zahlungsart, die eine
        Zahlung starten, aber nie bestätigen kann, wird deshalb gar nicht erst
        angeboten. Webhook-Adresse:{" "}
        <code>/shop/payment/paypal/webhook</code>
      </Alert>

      <div className="tds-field-row">
        <label>
          Client-ID
          <input
            className="field-boxed"
            value={ppClientId}
            onChange={(e) => setPpClientId(e.target.value)}
            placeholder="leer = PayPal aus"
          />
        </label>
        <label>
          Secret <em>({secretHint(settings.get("paypal_secret"))})</em>
          <input
            className="field-boxed"
            type="password"
            autoComplete="off"
            value={ppSecretInput}
            onChange={(e) => setPpSecretInput(e.target.value)}
            placeholder="leer lassen = unverändert"
          />
        </label>
      </div>

      <div className="tds-field-row">
        <label>
          Webhook-ID
          <input
            className="field-boxed"
            value={ppWebhookId}
            onChange={(e) => setPpWebhookId(e.target.value)}
            placeholder="WH-…"
          />
        </label>
        <label className="tds-toggle-row">
          <input
            type="checkbox"
            checked={ppSandbox}
            onChange={(e) => setPpSandbox(e.target.checked)}
          />
          Sandbox statt Live
        </label>
      </div>

      <h3>Wero</h3>
      <Alert tone="warning">
        Noch nicht angeschlossen. Wero läuft über einen Zahlungsdienstleister,
        und solange hier keiner eingetragen ist, erscheint die Zahlungsart in
        der Kasse überhaupt nicht — sie lässt sich also nicht versehentlich
        anbieten. Was zum Fertigstellen fehlt, steht in{" "}
        <code>docs/wero-adapter.md</code>.
      </Alert>

      <div className="tds-field-row">
        <label>
          Dienstleister
          <input
            className="field-boxed"
            value={weroPsp}
            onChange={(e) => setWeroPsp(e.target.value)}
            placeholder="leer = Wero aus"
          />
        </label>
        <label>
          API-Schlüssel <em>({secretHint(settings.get("wero_api_key"))})</em>
          <input
            className="field-boxed"
            type="password"
            autoComplete="off"
            value={weroKeyInput}
            onChange={(e) => setWeroKeyInput(e.target.value)}
            placeholder="leer lassen = unverändert"
          />
        </label>
      </div>

      <div className="tds-field-row">
        <label>
          Webhook-Secret <em>({secretHint(settings.get("wero_webhook_secret"))})</em>
          <input
            className="field-boxed"
            type="password"
            autoComplete="off"
            value={weroHookInput}
            onChange={(e) => setWeroHookInput(e.target.value)}
            placeholder="leer lassen = unverändert"
          />
        </label>
      </div>

      <h3>Versand</h3>
      <Alert tone="info">
        Gilt nur für Bestellungen mit körperlicher Ware; ein Warenkorb aus
        reinen Leistungen wird nie mit Versand belastet und fragt auch keine
        Lieferanschrift ab. Beträge in <strong>Cent</strong>, netto — die Steuer
        kommt oben drauf, und zwar mit dem Satz der gelieferten Ware, bei
        gemischten Sätzen anteilig aufgeteilt (Abschn. 3.10 UStAE). 0 als
        Pauschale heißt versandkostenfrei.
      </Alert>

      <div className="tds-field-row">
        <label>
          Pauschale (Cent, netto)
          <input
            className="field-boxed"
            inputMode="numeric"
            value={shipFlat}
            onChange={(e) => setShipFlat(e.target.value)}
            placeholder="495"
          />
        </label>
        <label>
          Versandfrei ab (Cent, netto)
          <input
            className="field-boxed"
            inputMode="numeric"
            value={shipFreeFrom}
            onChange={(e) => setShipFreeFrom(e.target.value)}
            placeholder="0 = keine Grenze"
          />
        </label>
      </div>

      <div className="tds-toolbar">
        <button type="button" className="btn btn-primary" onClick={() => void save()} disabled={settings.busy} aria-busy={settings.busy}>
          Speichern
        </button>
      </div>
    </>
  );
}
