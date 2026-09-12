import { useCallback, useEffect, useState } from "react";

import { apiFetch } from "@tracht-digital-solutions/tds-shared/api";
import { Spinner } from "@tracht-digital-solutions/tds-shared/components";
import { resolveChipVariant } from "@tracht-digital-solutions/tds-shared/design";
import { toast } from "@tracht-digital-solutions/tds-shared/toast";

interface Placement {
  id: number;
  key: string;
  label: string;
  surface: "blog" | "panel" | "shop";
  strategy: "manual" | "category" | "tag" | "auto";
  selector: string | null;
  heading_de: string | null;
  heading_en: string | null;
  max_items: number;
  active: number | boolean;
}

const SURFACE_LABEL: Record<Placement["surface"], string> = {
  blog: "Journal",
  panel: "Kundenportal",
  shop: "TDShop",
};

const STRATEGY_LABEL: Record<Placement["strategy"], string> = {
  manual: "Von Hand bestückt",
  category: "Nach Kategorie",
  tag: "Nach Schlagwort",
  auto: "Automatisch (neueste)",
};

/**
 * The advertising slots.
 *
 * The keys are seeded rather than created here, and that is on purpose: the
 * consuming code names them in markup — the journal asks for
 * `blog-article-end`, the portal widget for `panel-dashboard`. A slot invented
 * in this screen would have nowhere to appear, and a missing one is not an
 * error anywhere (the endpoint answers with an empty slot), so the feature
 * would look like it works and show nothing. Editing, not creating, is
 * therefore the whole job here.
 */
export default function PlacementManager() {
  const [placements, setPlacements] = useState<Placement[] | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [savingKey, setSavingKey] = useState<string | null>(null);

  const load = useCallback(async () => {
    try {
      const res = await apiFetch("/shop/placements");
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = (await res.json()) as { placements: Placement[] };
      setPlacements(json.placements);
      setError(null);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Unbekannter Fehler");
      setPlacements([]);
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  const patch = async (placement: Placement, fields: Partial<Placement>) => {
    setSavingKey(placement.key);
    try {
      const res = await apiFetch(`/shop/placements/${placement.key}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(fields),
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      toast.success(`„${placement.label}" gespeichert.`);
      await load();
    } catch (err) {
      toast.danger(
        `Speichern fehlgeschlagen (${err instanceof Error ? err.message : "unbekannt"}).`,
      );
    } finally {
      setSavingKey(null);
    }
  };

  if (placements === null) {
    return <Spinner />;
  }

  return (
    <>
      {error ? (
        <div className="tds-alert tds-alert--danger">
          Platzierungen konnten nicht geladen werden: {error}
        </div>
      ) : null}

      <div className="tds-alert tds-alert--info">
        Jede Platzierung wird als <strong>Anzeige</strong> gekennzeichnet. Die
        Kennzeichnung wird von der API mitgeliefert und lässt sich hier nicht
        abschalten — sie ist eine Rechtspflicht, keine Einstellung.
      </div>

      {placements.length === 0 ? (
        <p className="tds-empty">Keine Platzierungen vorhanden.</p>
      ) : (
        <ul className="tds-list">
          {placements.map((placement) => (
            <li className="tds-list__row" key={placement.key}>
              <div>
                <strong>{placement.label}</strong>
                <br />
                <code>{placement.key}</code>{" "}
                <span className={`chip ${resolveChipVariant("neutral")}`}>
                  {SURFACE_LABEL[placement.surface]}
                </span>{" "}
                <span
                  className={`chip ${resolveChipVariant(placement.active ? "success" : "neutral")}`}
                >
                  {placement.active ? "Aktiv" : "Aus"}
                </span>
              </div>

              <label>
                Auswahl
                <select className="field-boxed"
                  value={placement.strategy}
                  disabled={savingKey === placement.key}
                  onChange={(e) =>
                    void patch(placement, { strategy: e.target.value as Placement["strategy"] })
                  }
                >
                  {(Object.keys(STRATEGY_LABEL) as Placement["strategy"][]).map((s) => (
                    <option value={s} key={s}>
                      {STRATEGY_LABEL[s]}
                    </option>
                  ))}
                </select>
              </label>

              <label>
                Anzahl
                <input className="field-boxed"
                  type="number"
                  min={1}
                  max={12}
                  defaultValue={placement.max_items}
                  disabled={savingKey === placement.key}
                  onBlur={(e) => {
                    const next = Number(e.target.value);
                    if (next !== placement.max_items) {
                      void patch(placement, { max_items: next });
                    }
                  }}
                />
              </label>

              <button
                type="button"
                className="btn btn-ghost"
                disabled={savingKey === placement.key}
                aria-busy={savingKey === placement.key}
                onClick={() => void patch(placement, { active: placement.active ? 0 : 1 })}
              >
                {placement.active ? "Ausschalten" : "Einschalten"}
              </button>
            </li>
          ))}
        </ul>
      )}
    </>
  );
}
