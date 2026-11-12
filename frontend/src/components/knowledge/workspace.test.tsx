import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { KnowledgeWorkspace } from "./workspace";
import { KnowledgeExplorer } from "./explorer";
import { matchesQuery, type Library } from "@/lib/knowledge";

vi.mock("next/navigation", () => ({
  usePathname: () => "/",
  useSearchParams: () => new URLSearchParams(window.location.search),
}));

beforeEach(() => window.history.replaceState(null, "", "/"));

// Browser tests cover the real renderers; jsdom covers editorial interaction here.
vi.mock("./graph-3d", () => ({ default: () => <div>Graph canvas</div> }));
vi.mock("./neural-view", () => ({ default: () => <div>Neural canvas</div> }));

const library: Library = {
  nodes: [
    {
      id: 1,
      slug: "asuransi",
      title: "Asuransi",
      summary: "Pahami kontrak pertanggungan.",
      body: "Penjelasan.",
      group: "dasar",
      aliases: ["insurance"],
      reference_status: "needs_review",
      reviewed_at: null,
      kind: "concept",
    },
  ],
  edges: [],
  entries: [
    {
      slug: "cashless",
      kind: "faq",
      title: "Apakah cashless tanpa batas?",
      summary: "Cashless tetap mengikuti syarat polis.",
      body: "Baca ketentuan jaringan dan batas manfaat pada polis.",
      steps: [],
      keywords: ["cashless"],
      terms: ["asuransi"],
      passage_id: 1,
    },
  ],
  passages: [
    {
      id: 1,
      title: "Catatan sumber",
      publisher: "InsuraLab",
      url: null,
      version: "Draf editorial",
      locator: "Bagian manfaat",
      body: "Konteks bacaan.",
    },
  ],
  ai: { available: false, mode: "curated" },
};

describe("knowledge without AI", () => {
  it("renders persisted answers and keeps them usable when AI is unavailable", async () => {
    render(<KnowledgeWorkspace section="answers" library={library} />);
    await userEvent.click(screen.getByText("Apakah cashless tanpa batas?"));
    expect(screen.getByText("Baca ketentuan jaringan dan batas manfaat pada polis.")).toBeVisible();
    expect(screen.getByRole("link", { name: /Catatan sumber/ })).toHaveAttribute(
      "href",
      "/sumber?source=1",
    );
    await userEvent.click(screen.getByRole("button", { name: "Tentang bantuan AI" }));
    expect(screen.getByRole("status")).toHaveTextContent("Bantuan AI belum tersedia");
    expect(screen.getByText("Apakah cashless tanpa batas?")).toBeInTheDocument();
  });

  it("renders concepts and equivalent list controls without a WebGL dependency", () => {
    render(<KnowledgeExplorer library={library} />);
    expect(screen.getByRole("button", { name: "Neural View" })).toHaveAttribute(
      "aria-pressed",
      "true",
    );
    expect(screen.getByRole("button", { name: "Graph View" })).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Daftar" })).toBeInTheDocument();
    expect(
      screen.getByRole("textbox", { name: "Apa yang ingin Anda pahami?" }),
    ).toBeInTheDocument();
    expect(screen.getByText(/Materi pengantar masih memerlukan tinjauan ahli/)).toBeInTheDocument();
  });

  it("matches aliases and multiple words without treating unrelated content as an answer", () => {
    expect(matchesQuery(" insurance ", "Asuransi", ["insurance"])).toBe(true);
    expect(matchesQuery("cashless polis", "Cashless tetap mengikuti polis")).toBe(true);
    expect(matchesQuery("cashless", "Pengajuan asuransi")).toBe(false);
  });

  it("opens persisted related answers and evidence without a model or network call", async () => {
    window.history.replaceState(null, "", "/?view=graph&ask=apakah+cashless+tanpa+batas");
    const fetch = vi.spyOn(globalThis, "fetch");
    render(<KnowledgeExplorer library={library} />);
    await userEvent.click(screen.getByText("Apakah cashless tanpa batas?"));
    expect(screen.getByText("Baca ketentuan jaringan dan batas manfaat pada polis.")).toBeVisible();
    expect(screen.getByRole("link", { name: /Lihat sumber/ })).toHaveAttribute(
      "href",
      "/sumber?source=1",
    );
    await userEvent.click(screen.getByRole("button", { name: "Neural View" }));
    expect(window.location.search).toContain("ask=apakah+cashless+tanpa+batas");
    expect(window.location.search).toContain("view=neural");
    expect(fetch).not.toHaveBeenCalled();
  });

  it("does not fabricate an answer to an unrelated or empty-content question", () => {
    window.history.replaceState(null, "", "/?ask=zzzz-nonexistent");
    const { container } = render(<KnowledgeExplorer library={library} />);
    expect(screen.getByText(/Belum ada materi yang cocok/)).toBeVisible();
    expect(container.querySelector(".hpt-answer")).toBeNull();
  });
});
