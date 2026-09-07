import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { afterEach, describe, expect, it, vi } from "vitest";
import { GlossaryBrowser } from "./glossary-browser";
import * as endpoints from "@/lib/api/endpoints";

function renderBrowser() {
  const client = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return render(
    <QueryClientProvider client={client}>
      <GlossaryBrowser />
    </QueryClientProvider>,
  );
}

const term = {
  id: 1,
  term: "Underwriting",
  slug: "underwriting",
  short_definition: "Penilaian risiko calon tertanggung.",
  category: "process",
  letter: "U",
  is_provider_specific: false,
  reference_status: "current" as const,
  articles_count: 2,
};

afterEach(() => vi.restoreAllMocks());

describe("GlossaryBrowser", () => {
  it("renders terms from the API with their category and related-article count", async () => {
    vi.spyOn(endpoints, "getGlossary").mockResolvedValue({
      data: [term],
      meta: { total: 1 } as never,
    });

    renderBrowser();

    expect(await screen.findByRole("link", { name: /Underwriting/ })).toHaveAttribute(
      "href",
      "/glossary/underwriting",
    );
    expect(screen.getByText(/2 artikel terkait/)).toBeInTheDocument();
  });

  it("debounces the search box into the query", async () => {
    const user = userEvent.setup();
    const spy = vi.spyOn(endpoints, "getGlossary").mockResolvedValue({
      data: [term],
      meta: { total: 1 } as never,
    });

    renderBrowser();
    await screen.findByRole("link", { name: /Underwriting/ });

    await user.type(screen.getByRole("searchbox"), "under");

    await waitFor(() => {
      expect(spy).toHaveBeenCalledWith(expect.objectContaining({ q: "under" }));
    });
  });

  it("shows an empty state when the API errors", async () => {
    vi.spyOn(endpoints, "getGlossary").mockRejectedValue(new Error("down"));
    renderBrowser();
    expect(await screen.findByText(/Glosarium belum tersedia/i)).toBeInTheDocument();
  });
});
