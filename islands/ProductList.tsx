import { useCallback, useEffect, useMemo, useState } from "react";

import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { ConfirmDialog, Spinner } from "@tracht-digital-solutions/tds-shared/components";
import { Presence } from "@tracht-digital-solutions/tds-shared/motion/react";
import { resolveChipVariant } from "@tracht-digital-solutions/tds-shared/design";
import { toast } from "@tracht-digital-solutions/tds-shared/toast";

import PairEditor, { parsePairs, type Pair } from "./PairEditor.tsx";

type Lang = "de" | "en";

interface Translation {
  slug: string;
  title: string;
  teaser: string;
  metaDescription: string | null;
  metaTitle?: string | null;
  summary?: string | null;
  hasBody?: boolean;
}

interface Problem {
  code: string;
  message: string;
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
  /** Absent on an API older than the publish action. */
  problems?: Problem[];
  ready?: boolean;
}

/** The raw editor read (`GET /shop/products/{id}`): DB rows, snake_case. */
interface RawTranslation {
  body?: string | null;
  body_format?: string | null;
  meta_title?: string | null;
  summary?: string | null;
  facts?: string | null;
  faq?: string | null;
}

type Filter = "all" | "ready" | "blocked" | "published";

const FILTER_LABEL: Record<Filter, string> = {
  all: "Alle",
  ready: "Bereit zur Freigabe",
  blocked: "Unvollständig",
  published: "Freigegeben",
};

const EDITORIAL_LABEL: Record<Product["editorialStatus"], string> = {
  none: "Kein Text",
  stub: "Notiz",
  published: "Eigener Text",
};

const STATUS_LABEL: Record<Product["status"], string> = {
  draft: "Entwurf",
  published: "Freigegeben",
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
  editorialStatus: "none" as Product["editorialStatus"],
  metaDescription: "",
  metaTitle: "",
  summary: "",
  body: "",
  facts: [] as Pair[],
  faq: [] as Pair[],
};

const TITLE_MAX = 65;

/**
 * The TDShop catalogue screen.
 *
 * Three things about the presentation are deliberate rather than decorative:
 *
 * 1. **Going live is a button, not a select.** "Freigeben" asks the server
 *    whether the product is complete (text, meta data in both languages,
 *    cover, category name, price) and refuses otherwise; the reasons stand in
 *    the row. Seeded products arrive complete, so releasing them is one click
 *    each — or one for a whole selection.
 * 2. **`editorialStatus` is a column, not a detail.** A product with no
 *    assessment of its own renders on the site but stays out of the search
 *    index.
 * 3. **A product is listed once with its languages beside it**, not once per
 *    language. The two share a price and an ASIN; showing them as two rows
 *    would invite editing them as two products.
 */
export default function ProductList() {
  const [products, setProducts] = useState<Product[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [form, setForm] = useState({ ...EMPTY_FORM });
  const [editingId, setEditingId] = useState<number | null>(null);
  // The editor is closed by default: with a prepared catalogue the usual task
  // is releasing, not typing, so the list with "Freigeben" comes first.
  const [creating, setCreating] = useState(false);
  const [loadingEditor, setLoadingEditor] = useState(false);
  const [saving, setSaving] = useState(false);
  const [pendingDelete, setPendingDelete] = useState<Product | null>(null);
  const [deleting, setDeleting] = useState(false);
  const [filter, setFilter] = useState<Filter>("all");
  const [selected, setSelected] = useState<Set<number>>(new Set());
  const [publishing, setPublishing] = useState<number | "bulk" | null>(null);
  // Suggestions for the category field, so an existing category is picked
  // rather than retyped as a near-duplicate ("netzwerk" / "netzwerke").
  const [categorySlugs, setCategorySlugs] = useState<string[]>([]);

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

  // Re-read whenever the catalogue changes: saving a product may have created
  // a category. Failures are silent on purpose — these are suggestions, and
  // the field works without them.
  useEffect(() => {
    void (async () => {
      try {
        const res = await apiFetch("/shop/categories");
        if (!res.ok) return;
        const json = (await res.json()) as { categories: { slug: string }[] };
        setCategorySlugs(json.categories.map((c) => c.slug));
      } catch {
        // suggestions only
      }
    })();
  }, [products]);

  const counts = useMemo(() => {
    const all = products ?? [];
    return {
      all: all.length,
      ready: all.filter((p) => p.status !== "published" && p.ready === true).length,
      blocked: all.filter((p) => p.status !== "published" && p.ready === false).length,
      published: all.filter((p) => p.status === "published").length,
    } satisfies Record<Filter, number>;
  }, [products]);

  const visible = useMemo(() => {
    const all = products ?? [];
    switch (filter) {
      case "ready":
        return all.filter((p) => p.status !== "published" && p.ready === true);
      case "blocked":
        return all.filter((p) => p.status !== "published" && p.ready === false);
      case "published":
        return all.filter((p) => p.status === "published");
      default:
        return all;
    }
  }, [products, filter]);

  const releasable = visible.filter((p) => p.status !== "published" && p.ready === true);

  const save = async (event: React.FormEvent) => {
    event.preventDefault();
    setSaving(true);
    try {
      const res = await apiFetch(
        editingId === null ? "/shop/products" : `/shop/products/${editingId}`,
        {
          method: editingId === null ? "POST" : "PUT",
          headers: { "Content-Type": "application/json" },
          // The body goes along in markdown, the only format the shop renders.
          body: JSON.stringify({ ...form, bodyFormat: "markdown" }),
        },
      );
      const json = (await res.json().catch(() => ({}))) as { error?: string };
      if (!res.ok) {
        // Carry the server's own message: "Slug ungültig" is actionable,
        // "Speichern fehlgeschlagen" is not.
        toast.danger(json.error ?? `Speichern fehlgeschlagen (HTTP ${res.status})`);
        return;
      }
      toast.success(editingId === null ? "Produkt angelegt." : "Produkt gespeichert.");
      setForm({ ...EMPTY_FORM });
      setEditingId(null);
      setCreating(false);
      await load();
    } catch {
      toast.danger("Speichern fehlgeschlagen — keine Verbindung zur API.");
    } finally {
      setSaving(false);
    }
  };

  /**
   * Open a product in the form. The list carries no body, facts or FAQ, so the
   * editor reads the full product first — saving a form without them would
   * leave those fields untouched on the server, but the editor should show
   * what it is editing.
   */
  const edit = async (product: Product, lang: Lang) => {
    const translation = product.translations[lang];
    setEditingId(product.id);
    setCreating(false);
    // The form opens above the list; bring it into view.
    requestAnimationFrame(() => document.getElementById("shop-product-editor")?.scrollIntoView({ block: "start" }));
    const base = {
      ...EMPTY_FORM,
      lang,
      slug: translation?.slug ?? "",
      title: translation?.title ?? "",
      teaser: translation?.teaser ?? "",
      metaDescription: translation?.metaDescription ?? "",
      category: product.category,
      tags: product.tags.join(", "),
      brand: product.brand ?? "",
      kind: product.kind,
      editorialStatus: product.editorialStatus,
    };
    setForm(base);
    if (!translation) return;
    setLoadingEditor(true);
    try {
      const res = await apiFetch(`/shop/products/${product.id}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = (await res.json()) as { translations?: Partial<Record<Lang, RawTranslation>> };
      const raw = json.translations?.[lang] ?? {};
      setForm({
        ...base,
        body: raw.body ?? "",
        metaTitle: raw.meta_title ?? "",
        summary: raw.summary ?? "",
        facts: parsePairs(raw.facts, ["label", "value"]),
        faq: parsePairs(raw.faq, ["q", "a"]),
      });
    } catch (err) {
      toast.danger(`Produkttext konnte nicht geladen werden (${err instanceof Error ? err.message : "unbekannt"}).`);
    } finally {
      setLoadingEditor(false);
    }
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
      toast.danger(`Löschen fehlgeschlagen (${err instanceof Error ? err.message : "unbekannt"}).`);
    } finally {
      setDeleting(false);
      setPendingDelete(null);
    }
  };

  const setLive = async (product: Product, live: boolean) => {
    setPublishing(product.id);
    try {
      const res = await apiFetch(`/shop/products/${product.id}/${live ? "publish" : "unpublish"}`, { method: "POST" });
      const json = (await res.json().catch(() => ({}))) as { error?: string; problems?: Problem[] };
      if (!res.ok) {
        const reasons = json.problems?.map((p) => p.message).join(" ") ?? "";
        toast.danger(`${json.error ?? "Freigabe fehlgeschlagen"} (HTTP ${res.status}) ${reasons}`.trim());
        return;
      }
      toast.success(live ? "Produkt freigegeben." : "Produkt zurückgezogen.");
      await load();
    } catch {
      toast.danger("Keine Verbindung zur API.");
    } finally {
      setPublishing(null);
    }
  };

  const publishSelected = async () => {
    const ids = [...selected];
    if (ids.length === 0) return;
    setPublishing("bulk");
    try {
      const res = await apiFetch("/shop/products/publish", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ids }),
      });
      const json = (await res.json().catch(() => ({}))) as {
        error?: string;
        published?: number[];
        refused?: Record<string, Problem[]>;
      };
      if (!res.ok) {
        toast.danger(json.error ?? `Freigabe fehlgeschlagen (HTTP ${res.status})`);
        return;
      }
      const refused = Object.keys(json.refused ?? {}).length;
      const published = json.published?.length ?? 0;
      if (refused > 0) {
        toast.warning(`${published} freigegeben, ${refused} noch unvollständig — Gründe stehen in der Liste.`);
      } else {
        toast.success(`${published} Produkte freigegeben.`);
      }
      setSelected(new Set());
      await load();
    } catch {
      toast.danger("Freigabe fehlgeschlagen — keine Verbindung zur API.");
    } finally {
      setPublishing(null);
    }
  };

  const toggle = (id: number) =>
    setSelected((current) => {
      const next = new Set(current);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });

  if (products === null) {
    return <Spinner />;
  }

  const pageTitle = form.metaTitle.trim() || form.title;
  const editorOpen = creating || editingId !== null;
  const closeEditor = () => {
    setEditingId(null);
    setCreating(false);
    setForm({ ...EMPTY_FORM });
  };

  return (
    <>
      {error ? (
        <div className="tds-alert tds-alert--danger">
          Produkte konnten nicht geladen werden: {error}
        </div>
      ) : null}

      {/* "Bearbeiten" in the table below swaps what this form edits. A
          cross-fade makes that visible. Keyed by product, NOT by language:
          the language select lives in this form, and a re-keyed form would
          take the focus off it on every change. */}
      {!editorOpen ? (
        <div className="tds-toolbar">
          <button type="button" className="btn btn-ghost" onClick={() => setCreating(true)}>
            + Neues Produkt
          </button>
        </div>
      ) : null}

      {editorOpen ? (
      <Presence view={editingId === null ? "new" : `product-${editingId}`}>
        <form id="shop-product-editor" className="tds-card" onSubmit={save} aria-busy={loadingEditor}>
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
            Kurz gesagt
            <textarea className="field-boxed"
              value={form.summary}
              onChange={(e) => setForm({ ...form, summary: e.target.value })}
              rows={3}
              maxLength={400}
            />
            {/* The answer paragraph at the top of the page — what a search
                assistant quotes. It answers; the teaser sells. */}
            <small>2–3 Sätze, die die Frage „Was bekomme ich?“ vollständig beantworten.</small>
          </label>

          <div className="tds-field-row">
            <label>
              Meta-Titel
              <input className="field-boxed"
                value={form.metaTitle}
                onChange={(e) => setForm({ ...form, metaTitle: e.target.value })}
                maxLength={70}
                placeholder={form.title}
              />
              <small>
                Seitentitel: {pageTitle.length} Zeichen
                {pageTitle.length > TITLE_MAX ? ` — höchstens ${TITLE_MAX}, bitte kürzen.` : "."}
              </small>
            </label>
            <label>
              Meta-Description
              <input className="field-boxed"
                value={form.metaDescription}
                onChange={(e) => setForm({ ...form, metaDescription: e.target.value })}
                maxLength={300}
              />
              {/* 80–160 is the range that survives a search result intact. The
                  publish action enforces it, so the hint here matches the rule. */}
              <small>{form.metaDescription.length} Zeichen — nötig sind 80 bis 160.</small>
            </label>
          </div>

          <label>
            Produkttext (Markdown)
            <textarea className="field-boxed"
              value={form.body}
              onChange={(e) => setForm({ ...form, body: e.target.value })}
              rows={12}
            />
          </label>

          <PairEditor
            legend="Fakten (Dauer, Umfang, Ergebnis …)"
            rows={form.facts}
            onChange={(facts) => setForm({ ...form, facts })}
            keys={["label", "value"]}
            labels={["Bezeichnung", "Wert"]}
          />

          <PairEditor
            legend="Häufige Fragen"
            rows={form.faq}
            onChange={(faq) => setForm({ ...form, faq })}
            keys={["q", "a"]}
            labels={["Frage", "Antwort"]}
            long
          />

          <div className="tds-field-row">
            <label>
              Kategorie
              {/* A slug, because it is part of the shop's address — the server
                  refuses anything else. The readable German and English names
                  live under "Kategorien" below the catalogue. */}
              <input className="field-boxed"
                value={form.category}
                onChange={(e) => setForm({ ...form, category: e.target.value })}
                list="shop-category-slugs"
                pattern="[a-z0-9\-]{2,60}"
                title="2–60 Kleinbuchstaben, Ziffern und Bindestriche"
              />
              <datalist id="shop-category-slugs">
                {categorySlugs.map((slug) => (
                  <option key={slug} value={slug} />
                ))}
              </datalist>
              <small>Slug, z. B. netzwerk. Den lesbaren Namen pflegen Sie unter „Kategorien“.</small>
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
            <button type="submit" className="btn btn-primary" disabled={saving || loadingEditor} aria-busy={saving}>
              {editingId === null ? "Anlegen" : "Speichern"}
            </button>
            <button type="button" className="btn btn-ghost" onClick={closeEditor}>
              Abbrechen
            </button>
          </div>
        </form>
      </Presence>
      ) : null}

      <div className="tds-toolbar" role="group" aria-label="Produkte filtern">
        {(Object.keys(FILTER_LABEL) as Filter[]).map((key) => (
          <button
            key={key}
            type="button"
            className={filter === key ? "btn btn-primary" : "btn btn-ghost"}
            aria-pressed={filter === key}
            onClick={() => setFilter(key)}
          >
            {FILTER_LABEL[key]} ({counts[key]})
          </button>
        ))}
      </div>

      {releasable.length > 0 ? (
        <div className="tds-toolbar">
          <button
            type="button"
            className="btn btn-ghost"
            onClick={() => setSelected(new Set(releasable.map((p) => p.id)))}
          >
            Alle bereiten auswählen ({releasable.length})
          </button>
          <button
            type="button"
            className="btn btn-accent"
            disabled={selected.size === 0 || publishing !== null}
            aria-busy={publishing === "bulk"}
            onClick={() => void publishSelected()}
          >
            Ausgewählte freigeben ({selected.size})
          </button>
        </div>
      ) : null}

      {visible.length === 0 ? (
        <p className="tds-empty">
          {products.length === 0 ? "Noch keine Produkte im Katalog." : "Keine Produkte in dieser Ansicht."}
        </p>
      ) : (
        <table className="tds-table">
          <thead>
            <tr>
              <th>
                <span className="sr-only">Auswahl</span>
              </th>
              <th>Titel</th>
              <th>Kategorie</th>
              <th>Status</th>
              <th>Bereitschaft</th>
              <th>Redaktion</th>
              <th>Sprachen</th>
              <th />
            </tr>
          </thead>
          <tbody>
            {visible.map((product) => {
              const title = product.translations.de?.title ?? product.translations.en?.title ?? "—";
              const live = product.status === "published";
              const busy = publishing === product.id;
              return (
                <tr key={product.id}>
                  <td>
                    {!live && product.ready ? (
                      <input
                        type="checkbox"
                        aria-label={`${title} auswählen`}
                        checked={selected.has(product.id)}
                        onChange={() => toggle(product.id)}
                      />
                    ) : null}
                  </td>
                  <td>{title}</td>
                  <td>{product.category}</td>
                  <td>
                    {/* Never interpolate a chip variant — an unknown value
                        renders an unstyled chip rather than failing. */}
                    <span className={`chip ${resolveChipVariant(live ? "success" : "neutral")}`}>
                      {STATUS_LABEL[product.status]}
                    </span>
                  </td>
                  <td>
                    {product.ready === undefined ? (
                      "—"
                    ) : product.ready ? (
                      <span className={`chip ${resolveChipVariant("success")}`}>Vollständig</span>
                    ) : (
                      <details>
                        <summary>
                          <span className={`chip ${resolveChipVariant("warning")}`}>
                            {product.problems?.length ?? 0} offen
                          </span>
                        </summary>
                        <ul className="marginalia">
                          {product.problems?.map((p) => (
                            <li key={p.code}>{p.message}</li>
                          ))}
                        </ul>
                      </details>
                    )}
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
                          onClick={() => void edit(product, lang)}
                        >
                          {lang.toUpperCase()}
                        </button>
                      ) : (
                        <button
                          key={lang}
                          type="button"
                          className="btn btn-ghost"
                          onClick={() => {
                            void edit(product, lang);
                            setForm((f) => ({ ...f, lang, slug: "", title: "", teaser: "" }));
                          }}
                        >
                          + {lang.toUpperCase()}
                        </button>
                      ),
                    )}
                  </td>
                  <td>
                    <div className="tds-toolbar">
                      {live ? (
                        <button
                          type="button"
                          className="btn btn-ghost"
                          disabled={busy}
                          aria-busy={busy}
                          onClick={() => void setLive(product, false)}
                        >
                          Zurückziehen
                        </button>
                      ) : (
                        <button
                          type="button"
                          className="btn btn-primary"
                          disabled={busy || product.ready === false}
                          aria-busy={busy}
                          title={product.ready === false ? "Erst vervollständigen — siehe Bereitschaft." : undefined}
                          onClick={() => void setLive(product, true)}
                        >
                          Freigeben
                        </button>
                      )}
                      <button
                        type="button"
                        className="btn btn-ghost"
                        onClick={() => setPendingDelete(product)}
                      >
                        Löschen
                      </button>
                    </div>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      )}

      {/* A controlled dialog, never window.confirm(): the native one blocks
          the whole browser and cannot show a busy state. */}
      <ConfirmDialog
        open={pendingDelete !== null}
        title="Produkt löschen?"
        message={
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
