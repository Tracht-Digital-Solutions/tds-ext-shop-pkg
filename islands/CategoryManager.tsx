import { useCallback, useEffect, useState } from "react";

import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { Spinner } from "@tracht-digital-solutions/tds-shared/components";
import { toast } from "@tracht-digital-solutions/tds-shared/toast";

interface Category {
  slug: string;
  nameDe: string | null;
  nameEn: string | null;
  products: number;
}

interface Draft {
  nameDe: string;
  nameEn: string;
}

/** Mirrors `CategoryName::fromSlug()` — what the shop shows when no name is set. */
const fromSlug = (slug: string): string => {
  const words = slug.replace(/-/g, " ").trim();
  return words.charAt(0).toUpperCase() + words.slice(1);
};

/**
 * Category names in German and English.
 *
 * A category comes into being by typing its slug on a product, so this screen
 * never creates one — it lists what the catalogue uses and names it. Before it
 * existed the English shop showed German category names, because the slug was
 * the only thing anybody had ever written down.
 *
 * The placeholder in each field is what the shop shows while the field is
 * empty, so an unnamed category is visible as exactly that rather than as a
 * blank.
 */
export default function CategoryManager() {
  const [categories, setCategories] = useState<Category[] | null>(null);
  const [drafts, setDrafts] = useState<Record<string, Draft>>({});
  const [error, setError] = useState<string | null>(null);
  const [saving, setSaving] = useState<string | null>(null);

  const load = useCallback(async () => {
    try {
      const res = await apiFetch("/shop/categories");
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = (await res.json()) as { categories: Category[] };
      setCategories(json.categories);
      setDrafts(
        Object.fromEntries(
          json.categories.map((c) => [c.slug, { nameDe: c.nameDe ?? "", nameEn: c.nameEn ?? "" }]),
        ),
      );
      setError(null);
    } catch (err) {
      // In-flow, not a toast: a list that failed to load is a condition to act
      // on, and a toast disappears while it is being read.
      setError(err instanceof Error ? err.message : "Unbekannter Fehler");
      setCategories([]);
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  const setDraft = (slug: string, patch: Partial<Draft>) =>
    setDrafts((all) => ({ ...all, [slug]: { ...(all[slug] ?? { nameDe: "", nameEn: "" }), ...patch } }));

  const save = async (slug: string) => {
    const draft = drafts[slug];
    if (!draft) return;
    setSaving(slug);
    try {
      const res = await apiFetch(`/shop/categories/${slug}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(draft),
      });
      const json = (await res.json().catch(() => ({}))) as { error?: string };
      if (!res.ok) {
        toast.danger(json.error ?? `Speichern fehlgeschlagen (HTTP ${res.status})`);
        return;
      }
      toast.success(`Namen für „${slug}“ gespeichert.`);
      await load();
    } catch {
      toast.danger("Speichern fehlgeschlagen — keine Verbindung zur API.");
    } finally {
      setSaving(null);
    }
  };

  if (categories === null) {
    return <Spinner />;
  }

  return (
    <section className="tds-card">
      <h2>Kategorien</h2>
      <p>
        Der Slug steht in der Adresse des Shops. Die Namen sind, was Besucher lesen. Ohne englischen
        Namen zeigt die englische Seite den deutschen, ohne beide den Slug mit großem Anfangsbuchstaben.
      </p>

      {error ? (
        <div className="tds-alert tds-alert--danger">Kategorien konnten nicht geladen werden: {error}</div>
      ) : null}

      {categories.length === 0 ? (
        <p className="tds-empty">Noch keine Kategorien. Sie entstehen mit dem ersten Produkt.</p>
      ) : (
        <table className="tds-table">
          <thead>
            <tr>
              <th>Slug</th>
              <th>Name Deutsch</th>
              <th>Name Englisch</th>
              <th>Produkte</th>
              <th />
            </tr>
          </thead>
          <tbody>
            {categories.map((category) => {
              const draft = drafts[category.slug] ?? { nameDe: "", nameEn: "" };
              const busy = saving === category.slug;
              return (
                <tr key={category.slug}>
                  <th scope="row">{category.slug}</th>
                  <td>
                    <input
                      className="field-boxed"
                      aria-label={`Deutscher Name für ${category.slug}`}
                      value={draft.nameDe}
                      maxLength={80}
                      placeholder={fromSlug(category.slug)}
                      onChange={(e) => setDraft(category.slug, { nameDe: e.target.value })}
                    />
                  </td>
                  <td>
                    <input
                      className="field-boxed"
                      aria-label={`Englischer Name für ${category.slug}`}
                      value={draft.nameEn}
                      maxLength={80}
                      placeholder={draft.nameDe.trim() || fromSlug(category.slug)}
                      onChange={(e) => setDraft(category.slug, { nameEn: e.target.value })}
                    />
                  </td>
                  <td>{category.products}</td>
                  <td>
                    <button
                      type="button"
                      className="btn btn-ghost"
                      disabled={busy}
                      aria-busy={busy}
                      onClick={() => void save(category.slug)}
                    >
                      Speichern
                    </button>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      )}
    </section>
  );
}
