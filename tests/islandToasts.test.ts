import { readdirSync, readFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { join } from "node:path";
import { describe, expect, it } from "vitest";
import { toast } from "@tracht-digital-solutions/tds-shared/toast";

/**
 * Nothing type-checks this package's islands: `tsconfig.json` and `tsup` cover
 * `src/`, and the product build compiles `islands/` with esbuild, which strips
 * types without checking them. That is how nine `toast.error(...)` calls
 * shipped. tds-shared's toast has `success`, `info`, `warning` and `danger`, so
 * every one of those failure paths threw a TypeError instead of telling the
 * operator what went wrong.
 *
 * The allowed names come from the installed library, not from a list here.
 */
const ISLANDS = fileURLToPath(new URL("../islands/", import.meta.url));

function islandSources(): Array<{ file: string; source: string }> {
  return readdirSync(ISLANDS, { recursive: true })
    .map(String)
    .filter((file) => file.endsWith(".tsx") && !file.endsWith(".test.tsx"))
    .map((file) => ({ file, source: readFileSync(join(ISLANDS, file), "utf8") }));
}

describe("island toasts", () => {
  it("only call methods the shared toast actually has", () => {
    const calls = islandSources().flatMap(({ file, source }) =>
      [...source.matchAll(/\btoast\.(\w+)\s*\(/g)].map((match) => ({ file, method: match[1] ?? "" })),
    );

    expect(calls.length).toBeGreaterThan(0);
    expect(calls.filter(({ method }) => typeof Reflect.get(toast, method) !== "function")).toEqual([]);
  });
});
