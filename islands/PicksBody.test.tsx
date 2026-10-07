// @vitest-environment jsdom
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { cleanup, render, waitFor } from "@testing-library/react";
import { primeRuntimeConfig } from "@tracht-digital-solutions/tds-shared/api";
import PicksBody from "./PicksBody";

/**
 * The portal dashboard's product placement. Empty is a normal state and must
 * leave NOTHING behind — not even the card and "Empfehlungen" heading that
 * `widgets/PicksWidget.astro` draws around this island, which every portal
 * dashboard showed empty until 2026-10-07.
 */

function slot() {
  document.body.innerHTML = `<section class="widget-slot"><article class="tds-widget"><h3>Empfehlungen</h3><div id="mount"></div></article></section>`;
  return {
    section: document.querySelector<HTMLElement>(".widget-slot")!,
    mount: document.getElementById("mount")!,
  };
}

beforeEach(() => primeRuntimeConfig(null));
afterEach(() => {
  cleanup();
  vi.unstubAllGlobals();
});

describe("PicksBody", () => {
  it("hides its dashboard slot when the placement is empty", async () => {
    vi.stubGlobal("fetch", vi.fn(async () => new Response(JSON.stringify({ key: "panel-dashboard", products: [] }), { status: 200 })));
    const { section, mount } = slot();
    render(<PicksBody />, { container: mount });
    await waitFor(() => expect(section.hidden).toBe(true));
  });

  it("hides it too when the shop cannot be reached", async () => {
    vi.stubGlobal("fetch", vi.fn(async () => { throw new TypeError("Failed to fetch"); }));
    const { section, mount } = slot();
    render(<PicksBody />, { container: mount });
    await waitFor(() => expect(section.hidden).toBe(true));
  });
});
