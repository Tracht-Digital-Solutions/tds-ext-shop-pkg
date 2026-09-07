// @vitest-environment jsdom
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { cleanup, render, screen, waitFor } from "@testing-library/react";

import { primeRuntimeConfig } from "@tracht-digital-solutions/tds-shared/api";

import WidgetBody from "./WidgetBody";

/**
 * The TDShop dashboard widget.
 *
 * `apiFetch` probes the runtime config before its first request, so every test
 * primes it — otherwise the assertions race a config fetch that never resolves.
 */

const SUMMARY = {
  published: 12,
  drafts: 3,
  unwritten: 7,
  offers: 20,
  stalePrices: 4,
};

function stubFetch(handler: (url: string) => { ok: boolean; status: number; body: unknown }) {
  const seen: string[] = [];
  vi.stubGlobal("fetch", async (input: RequestInfo | URL) => {
    const url = String(input);
    seen.push(url);
    const { ok, status, body } = handler(url);
    return { ok, status, json: async () => body } as Response;
  });
  return seen;
}

beforeEach(() => {
  primeRuntimeConfig(null);
});

afterEach(() => {
  cleanup();
  vi.unstubAllGlobals();
});

describe("the shop widget", () => {
  it("shows the published count once loaded", async () => {
    stubFetch(() => ({ ok: true, status: 200, body: SUMMARY }));
    render(<WidgetBody />);
    expect(await screen.findByText("12")).toBeTruthy();
  });

  it("leaves its loading state behind", async () => {
    stubFetch(() => ({ ok: true, status: 200, body: SUMMARY }));
    render(<WidgetBody />);
    await screen.findByText("12");
    await waitFor(() => expect(document.querySelector('[aria-busy="true"]')).toBeNull());
  });

  it("surfaces the two numbers that have no other symptom", async () => {
    // `unwritten` decides whether the catalogue is indexed at all, and a rising
    // `stalePrices` is the ONLY sign that the offer sync has stopped — the
    // pages keep working, they just quietly stop showing prices.
    stubFetch(() => ({ ok: true, status: 200, body: SUMMARY }));
    render(<WidgetBody />);
    expect(await screen.findByText("7")).toBeTruthy();
    expect(screen.getByText("4")).toBeTruthy();
  });

  it("reads its endpoint through apiFetch, never a relative path", async () => {
    // A relative fetch reaches the host's SPA fallback, gets 200 with an HTML
    // body, and renders a calm empty state — the outage that produced this rule.
    const seen = stubFetch(() => ({ ok: true, status: 200, body: SUMMARY }));
    render(<WidgetBody />);
    await screen.findByText("12");
    const call = seen.find((u) => u.includes("/shop/summary"));
    expect(call).toBeDefined();
    expect(call).toMatch(/^https?:\/\//);
  });

  it("shows a dash rather than a zero when the request fails", async () => {
    // A zero is a number the reader has no reason to doubt. "No catalogue" and
    // "could not ask" must not look the same.
    stubFetch(() => ({ ok: false, status: 500, body: {} }));
    render(<WidgetBody />);
    expect(await screen.findByText("—")).toBeTruthy();
    expect(screen.queryByText("0")).toBeNull();
  });
});
