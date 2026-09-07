import { useCallback, useEffect, useState } from "react";

import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { ConfirmDialog, Spinner } from "@tracht-digital-solutions/tds-shared/components";
import { resolveChipVariant } from "@tracht-digital-solutions/tds-shared/design";
import { toast } from "@tracht-digital-solutions/tds-shared/toast";

type Lang = "de" | "en";

interface Translation {
  slug: string;
  title: string;
  teaser: string;
  metaDescription: string | null;
}

interface Product {
  id: number;
  kind: "affiliate" | "digital";
  status: "draft" | "published" | "archived";
  editorialStatus: "none" | "stub" | "published";
  category: string;
  tags: string[];
  brand: string | null;
  publishedAt: string | null;
  translations: Partial<Record<Lang, Translation>>;
}

const EDITORIAL_LABEL: Record<Product["editorialStatus"], string> = {
  none: "Kein Text",
  stub: "Notiz",
  published: "Eigener Text",
};

const STATUS_LABEL: Record<Product["status"], string> = {
  draft: "Entwurf",
  published: "Veröffentlicht",
  archived: "Archiviert",
};

const EMPTY_FORM = {
  lang: "de" as Lang,
  slug: "",
  title: "",
  teaser: "",
  category: "allgemein",
  tags: "",
  brand: "",
  kind: "affiliate" as Product["kind"],
  status: "draft" as Product["status"],
  editorialStatus: "none" as Product["editorialStatus"],
  metaDescription: "",
};

/**
 * The TDShop catalogue screen.
 *
 * Two things about the presentation are deliberate rather than decorative:
 *
 * 1. **`editorialStatus` is a column, not a detail.** A product with no
 *    assessment of its own renders on the site but stays out of the search
 *    index — so "Kein Text" is the difference between a catalogue entry that
 *    works and one that merely exists. Burying it in the editor would make the
 *    single most consequential field the least visible one.
 * 2. **A product is listed once with its languages beside it**, not once per
 *    language. The two share a price and an ASIN; showing them as two rows
 *    would invite editing them as two products, which is exactly the drift the
 *    schema was shaped to prevent.
 */
export default function ProductList() {
  const [products, setProducts] = useState<Product[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [form, setForm] = useState({ ...EMPTY_FORM });
  const [editingId, setEditingId] = useState<number | null>(null);
  const [saving, setSaving] = useState(false);
  const [pendingDelete, setPendingDelete] = useState<Product | null>(null);
  const [deleting, setDeleting] = useState(false);

  const load = useCallback(async () => {
    try {
      const res = await apiFetch("/shop/products");
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = (await res.json()) as { products: Product[] };
      setProducts(json.products);
      setError(null);
    } catch (err) {
      // An in-flow alert, not a toast: this is a persistent condition the
      // operator has to act on, and a toast disappears while they read it.
      setError(err instanceof Error ? err.message : "Unbekannter Fehler");
      setProducts([]);
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  const save = async (event: React.FormEvent) => {
    event.preventDefault();
    setSaving(true);
    try {
      const res = await apiFetch(
        editingId === null ? "/shop/products" : `/shop/products/${editingId}`,
        {
          method: editingId === null ? "POST" : "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(form),
        },
      );
      const json = (await res.json().catch(() => ({}))) as { error?: string };
      if (!res.ok) {
        // Carry the server's own message: "Slug ungültig" is actionable,
        // "Speichern fehlgeschlagen" is not.
        toast.error(json.error ?? `Speichern fehlgeschlagen (HTTP ${res.status})`);
        return;
      }
      toast.success(editingId === null ? "Produkt angelegt." : "Produkt gespeichert.");
      setForm({ ...EMPTY_FORM });
      setEditingId(null);
      await load();
    } catch {
      toast.error("Speichern fehlgeschlagen — keine Verbindung zur API.");
    } finally {
      setSaving(false);
    }
  };

  const edit = (product: Product, lang: Lang) => {
    const translation = product.translations[lang];
    setEditingId(product.id);
    setForm({
      lang,
      slug: translation?.slug ?? "",
      title: translation?.title ?? "",
      teaser: translation?.teaser ?? "",
      metaDescription: translation?.metaDescription ?? "",
      category: product.category,
      tags: product.tags.join(", "),
      brand: product.brand ?? "",
      kind: product.kind,
      status: product.status,
      editorialStatus: product.editorialStatus,
    });
  };

  const remove = async () => {
    if (!pendingDelete) return;
    setDeleting(true);
    try {
      const res = await apiFetch(`/shop/products/${pendingDelete.id}`, { method: "DELETE" });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      toast.success("Produkt gelöscht.");
      await load();
    } catch (err) {
      toast.error(`Löschen fehlgeschlagen (${err instanceof Error ? err.message : "unbekannt"}).`);
    } finally {
      setDeleting(false);
      setPendingDelete(null);
    }
  };

  if (products === null) {
    return <Spinner />;
  }

  return (
    <>
      {error ? (
        <div className="tds-alert tds-alert--danger">
          Produkte konnten nicht geladen werden: {error}
        </div>
      ) : null}

      <form className="tds-card" onSubmit={save}>
        <h2>{editingId === null ? "Neues Produkt" : `Produkt #${editingId} bearbeiten`}</h2>

        <div className="tds-field-row">
          <label>
            Sprache
            <select className="field-boxed"
              value={form.lang}
              onChange={(e) => setForm({ ...form, lang: e.target.value as Lang })}
            >
              <option value="de">Deutsch</option>
              <option value="en">Englisch</option>
            </select>
          </label>
          <label>
            Slug
            <input className="field-boxed"
              value={form.slug}
              onChange={(e) => setForm({ ...form, slug: e.target.value })}
              placeholder="fritzbox-7590-ax"
              required
            />
          </label>
        </div>

        <div className="tds-field-row">
          <label>
            Titel
            <input className="field-boxed"
              value={form.title}
              onChange={(e) => setForm({ ...form, title: e.target.value })}
              required
            />
          </label>
          <label>
            Marke
            <input className="field-boxed" value={form.brand} onChange={(e) => setForm({ ...form, brand: e.target.value })} />
          </label>
        </div>

        <label>
          Kurzbeschreibung
          <textarea className="field-boxed"
            value={form.teaser}
            onChange={(e) => setForm({ ...form, teaser: e.target.value })}
            rows={2}
          />
        </label>

        <label>
          Meta-Description
          <input className="field-boxed"
            value={form.metaDescription}
            onChange={(e) => setForm({ ...form, metaDescription: e.target.value })}
            maxLength={300}
          />
          {/* 80–160 is the range that survives a search result intact; the
              budget is checked by the shop site's own test, so the hint here
              is guidance rather than a second, drifting rule. */}
          <small>{form.metaDescription.length} Zeichen — 80 bis 160 ist die nützliche Spanne.</small>
        </label>

        <div className="tds-field-row">
          <label>
            Kategorie
            <input className="field-boxed"
              value={form.category}
              onChange={(e) => setForm({ ...form, category: e.target.value })}
            />
          </label>
          <label>
            Schlagwörter
            <input className="field-boxed"
              value={form.tags}
              onChange={(e) => setForm({ ...form, tags: e.target.value })}
              placeholder="nas, backup, homeoffice"
            />
          </label>
        </div>

        <div className="tds-field-row">
          <label>
            Art
            <select className="field-boxed"
              value={form.kind}
              onChange={(e) => setForm({ ...form, kind: e.target.value as Product["kind"] })}
            >
              <option value="affiliate">Affiliate</option>
              <option value="digital">Eigenes digitales Produkt</option>
            </select>
          </label>
          <label>
            Status
            <select className="field-boxed"
              value={form.status}
              onChange={(e) => setForm({ ...form, status: e.target.value as Product["status"] })}
            >
              <option value="draft">Entwurf</option>
              <option value="published">Veröffentlicht</option>
              <option value="archived">Archiviert</option>
            </select>
          </label>
          <label>
            Redaktion
            <select className="field-boxed"
              value={form.editorialStatus}
              onChange={(e) =>
                setForm({ ...form, editorialStatus: e.target.value as Product["editorialStatus"] })
              }
            >
              <option value="none">Kein eigener Text</option>
              <option value="stub">Notiz</option>
              <option value="published">Eigener Text — wird indexiert</option>
            </select>
          </label>
        </div>

        <div className="tds-toolbar">
          <button type="submit" className="btn btn-primary" disabled={saving} aria-busy={saving}>
            {editingId === null ? "Anlegen" : "Speichern"}
          </button>
          {editingId !== null ? (
            <button
              type="button"
              className="btn btn-ghost"
              onClick={() => {
                setEditingId(null);
                setForm({ ...EMPTY_FORM });
              }}
            >
              Abbrechen
            </button>
          ) : null}
        </div>
      </form>

      {products.length === 0 ? (
        <p className="tds-empty">Noch keine Produkte im Katalog.</p>
      ) : (
        <table className="tds-table">
          <thead>
            <tr>
              <th>Titel</th>
              <th>Kategorie</th>
              <th>Status</th>
              <th>Redaktion</th>
              <th>Sprachen</th>
              <th />
            </tr>
          </thead>
          <tbody>
            {products.map((product) => (
              <tr key={product.id}>
                <td>{product.translations.de?.title ?? product.translations.en?.title ?? "—"}</td>
                <td>{product.category}</td>
                <td>
                  {/* Never interpolate a chip variant — an unknown value
                      renders an unstyled chip rather than failing. */}
                  <span className={`chip ${resolveChipVariant(
                    product.status === "published" ? "success" : "neutral",
                  )}`}>
                    {STATUS_LABEL[product.status]}
                  </span>
                </td>
                <td>
                  <span className={`chip ${resolveChipVariant(
                    product.editorialStatus === "published" ? "success" : "warning",
                  )}`}>
                    {EDITORIAL_LABEL[product.editorialStatus]}
                  </span>
                </td>
                <td>
                  {(["de", "en"] as Lang[]).map((lang) =>
                    product.translations[lang] ? (
                      <button
                        key={lang}
                        type="button"
                        className="btn btn-ghost"
                        onClick={() => edit(product, lang)}
                      >
                        {lang.toUpperCase()}
                      </button>
                    ) : (
                      <button
                        key={lang}
                        type="button"
                        className="btn btn-ghost"
                        onClick={() => {
                          edit(product, lang);
                          setForm((f) => ({ ...f, lang, slug: "", title: "", teaser: "" }));
                        }}
                      >
                        + {lang.toUpperCase()}
                      </button>
                    ),
                  )}
                </td>
                <td>
                  <button
                    type="button"
                    className="btn btn-ghost"
                    onClick={() => setPendingDelete(product)}
                  >
                    Löschen
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {/* A controlled dialog, never window.confirm(): the native one blocks
          the whole browser and cannot show a busy state. */}
      <ConfirmDialog
        open={pendingDelete !== null}
        title="Produkt löschen?"
        description={
          pendingDelete
            ? `„${pendingDelete.translations.de?.title ?? pendingDelete.id}" wird mit allen Übersetzungen, Angeboten und Platzierungseinträgen entfernt.`
            : ""
        }
        confirmLabel="Löschen"
        busy={deleting}
        onConfirm={() => void remove()}
        onCancel={() => setPendingDelete(null)}
      />
    </>
  );
}
