// @vitest-environment jsdom
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { cleanup, fireEvent, render, screen, waitFor } from "@testing-library/react";
import { primeRuntimeConfig } from "@tracht-digital-solutions/tds-shared/api";
import { TOAST_EVENT } from "@tracht-digital-solutions/tds-shared/toast";
import ShopSettings from "./ShopSettings";

/**
 * The settings load and save through tds-shared `useRuntimeSettings`, which
 * calls its own copy of `apiFetch` — so the network is stubbed at `fetch`, not
 * by mocking the api module, and toasts are read off the shared toast bus.
 */
type Reply = { path: RegExp; method?: string; body: unknown; status?: number };
let replies: Reply[] = [];
let calls: Array<{ path: string; method: string }> = [];
let toasts: Array<{ variant: string; message: string }> = [];
const onToast = (e: Event) => toasts.push((e as CustomEvent).detail);

beforeEach(() => {
  primeRuntimeConfig(null);
  replies = [];
  calls = [];
  toasts = [];
  window.addEventListener(TOAST_EVENT, onToast);
  vi.stubGlobal(
    "fetch",
    vi.fn(async (url: string, init?: RequestInit) => {
      const path = String(url).replace(/^https?:\/\/[^/]+/, "");
      const method = init?.method ?? "GET";
      calls.push({ path, method });
      const reply = replies.find((r) => r.path.test(path) && (r.method ?? "GET") === method);
      return new Response(JSON.stringify(reply?.body ?? {}), { status: reply?.status ?? 200 });
    }),
  );
});

afterEach(() => {
  window.removeEventListener(TOAST_EVENT, onToast);
  cleanup();
});

async function openSettings() {
  replies.push({
    path: /\/admin\/settings\/shop$/,
    body: {
      settings: [
        { key: "cache_url", secret: false, value: "https://shop.example.test" },
        { key: "cache_token", secret: true, configured: true, last4: "1234" },
      ],
    },
  });
  render(<ShopSettings />);
  return screen.findByRole("button", { name: "Seiten-Cache neu aufbauen" });
}

describe("shop cache settings", () => {
  it("does not claim a successful rebuild when the token is missing", async () => {
    const button = await openSettings();
    replies.push({ path: /\/shop\/cache\/rebuild$/, method: "POST", body: { cached: false, cache_status: "not_configured" } });
    fireEvent.click(button);
    await waitFor(() => expect(toasts.some((t) => t.variant === "warning")).toBe(true));
    expect(toasts.some((t) => t.variant === "success")).toBe(false);
    expect(calls.at(-1)).toEqual({ path: "/shop/cache/rebuild", method: "POST" });
  });

  it("shows a successful rebuild only when the API confirms it", async () => {
    await openSettings();
    expect((screen.getByLabelText(/Cache-Token/) as HTMLInputElement).value).toBe("");
    // The stored values reach the fields in the effect after the load renders.
    await waitFor(() => expect((screen.getByLabelText("Shop-Adresse") as HTMLInputElement).value).toBe("https://shop.example.test"));
    expect(screen.getByText(/konfiguriert \(…1234\)/)).toBeTruthy();
    replies.push({ path: /\/shop\/cache\/rebuild$/, method: "POST", body: { cached: true, cache_status: "refreshed" } });
    fireEvent.click(screen.getByRole("button", { name: "Seiten-Cache neu aufbauen" }));
    await waitFor(() => expect(toasts).toContainEqual(expect.objectContaining({ variant: "success", message: "Öffentlicher Shop aktualisiert." })));
    expect(toasts.some((t) => t.variant === "warning")).toBe(false);
  });
});
