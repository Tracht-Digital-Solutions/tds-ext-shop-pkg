/**
 * Edits a short list of two-field rows: FAQ (`q`/`a`) on products and
 * categories, facts (`label`/`value`) on products.
 *
 * Rows missing either side are dropped by the server (`PairList::clean()`), so
 * a half-filled row here is not an error — it just does not survive the save.
 * The cap matches the server's `PairList::MAX_ITEMS`.
 */
export type Pair = Record<string, string>;

const MAX_ITEMS = 12;

interface Props {
  legend: string;
  rows: Pair[];
  onChange: (rows: Pair[]) => void;
  keys: [string, string];
  labels: [string, string];
  /** Render the second field as a textarea (FAQ answers run to sentences). */
  long?: boolean;
}

export default function PairEditor({ legend, rows, onChange, keys, labels, long = false }: Props) {
  const [k, v] = keys;
  const update = (index: number, patch: Pair) =>
    onChange(rows.map((row, i) => (i === index ? { ...row, ...patch } : row)));

  return (
    <fieldset className="tds-stack tds-stack--tight">
      <legend>{legend}</legend>
      {rows.map((row, index) => (
        <div className="tds-row" key={index}>
          <label>
            {labels[0]}
            <input
              className="field-boxed"
              value={row[k] ?? ""}
              maxLength={300}
              onChange={(e) => update(index, { [k]: e.target.value })}
            />
          </label>
          <label>
            {labels[1]}
            {long ? (
              <textarea
                className="field-boxed"
                rows={2}
                value={row[v] ?? ""}
                maxLength={2000}
                onChange={(e) => update(index, { [v]: e.target.value })}
              />
            ) : (
              <input
                className="field-boxed"
                value={row[v] ?? ""}
                maxLength={2000}
                onChange={(e) => update(index, { [v]: e.target.value })}
              />
            )}
          </label>
          <button
            type="button"
            className="btn btn-ghost"
            aria-label={`${labels[0]} ${index + 1} entfernen`}
            onClick={() => onChange(rows.filter((_, i) => i !== index))}
          >
            Entfernen
          </button>
        </div>
      ))}
      {rows.length < MAX_ITEMS ? (
        <div className="tds-toolbar">
          <button type="button" className="btn btn-ghost" onClick={() => onChange([...rows, { [k]: "", [v]: "" }])}>
            + {labels[0]}
          </button>
        </div>
      ) : null}
    </fieldset>
  );
}

/** Parse a stored JSON column (the editor's raw read) into rows; anything else is empty. */
export function parsePairs(raw: unknown, keys: [string, string]): Pair[] {
  let value = raw;
  if (typeof raw === "string") {
    try {
      value = JSON.parse(raw);
    } catch {
      return [];
    }
  }
  if (!Array.isArray(value)) return [];
  return value
    .filter((row): row is Record<string, unknown> => typeof row === "object" && row !== null)
    .map((row) => ({ [keys[0]]: String(row[keys[0]] ?? ""), [keys[1]]: String(row[keys[1]] ?? "") }));
}
