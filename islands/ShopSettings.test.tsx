// @vitest-environment jsdom
import { afterEach, describe, expect, it, vi } from "vitest";
import { cleanup, fireEvent, render, screen, waitFor } from "@testing-library/react";
import ShopSettings from "./ShopSettings";

const mocks = vi.hoisted(() => ({
  apiFetch: vi.fn(),
  toast: { success: vi.fn(), warning: vi.fn(), danger: vi.fn() },
}));
vi.mock("@tracht-digital-solutions/tds-shared/api", () => ({ apiFetch: mocks.apiFetch }));
vi.mock("@tracht-digital-solutions/tds-shared/toast", () => ({ toast: mocks.toast }));
vi.mock("@tracht-digital-solutions/tds-shared/components", () => ({ Spinner: () => <span>Loading</span> }));
afterEach(() => { cleanup(); vi.resetAllMocks(); });

async function openSettings() {
  mocks.apiFetch.mockResolvedValueOnce(new Response(JSON.stringify({ settings: [
    { key: "cache_url", secret: false, value: "https://shop.example.test" },
    { key: "cache_token", secret: true, configured: true, last4: "1234" },
  ] })));
  render(<ShopSettings />);
  return screen.findByRole("button", { name: "Seiten-Cache neu aufbauen" });
}

describe("shop cache settings", () => {
  it("does not claim a successful rebuild when the token is missing", async () => {
    const button = await openSettings();
    mocks.apiFetch.mockResolvedValueOnce(new Response(JSON.stringify({ cached: false, cache_status: "not_configured" })));
    fireEvent.click(button);
    await waitFor(() => expect(mocks.toast.warning).toHaveBeenCalled());
    expect(mocks.toast.success).not.toHaveBeenCalled();
    expect(mocks.apiFetch).toHaveBeenLastCalledWith("/shop/cache/rebuild", { method: "POST" });
  });

  it("shows a successful rebuild only when the API confirms it", async () => {
    await openSettings();
    expect((screen.getByLabelText(/Cache-Token/) as HTMLInputElement).value).toBe("");
    expect((screen.getByLabelText("Shop-Adresse") as HTMLInputElement).value).toBe("https://shop.example.test");
    mocks.apiFetch.mockResolvedValueOnce(new Response(JSON.stringify({ cached: true, cache_status: "refreshed" })));
    fireEvent.click(screen.getByRole("button", { name: "Seiten-Cache neu aufbauen" }));
    await waitFor(() => expect(mocks.toast.success).toHaveBeenCalledWith("Öffentlicher Shop aktualisiert."));
    expect(mocks.toast.warning).not.toHaveBeenCalled();
  });
});
