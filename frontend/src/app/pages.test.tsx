import { render, screen } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import MethodologyPage from "./methodology/page";
import DisclaimerPage from "./disclaimer/page";
import AboutPage from "./about/page";

vi.mock("@/components/marketing/home-glossary-peek", () => ({
  HomeGlossaryPeek: () => <div data-testid="glossary-peek" />,
}));

describe("static content pages", () => {
  it("renders the methodology page with the general/provider split", () => {
    render(<MethodologyPage />);
    expect(
      screen.getByRole("heading", { name: /How InsuraLab organises what it teaches/i }),
    ).toBeInTheDocument();
    expect(screen.getByText("General knowledge")).toBeInTheDocument();
    expect(screen.getByText("Provider-specific knowledge")).toBeInTheDocument();
    // all three reference states are explained
    expect(screen.getByText("Needs review")).toBeInTheDocument();
    expect(screen.getByText("Historical")).toBeInTheDocument();
  });

  it("renders the disclaimer page in Bahasa Indonesia", () => {
    render(<DisclaimerPage />);
    expect(
      screen.getByRole("heading", { name: /Cara membaca konten InsuraLab/i }),
    ).toBeInTheDocument();
    expect(screen.getByText(/bukan nasihat keuangan/i)).toBeInTheDocument();
  });

  it("renders the about page", () => {
    render(<AboutPage />);
    expect(
      screen.getByRole("heading", { name: /Understand insurance before you buy it/i }),
    ).toBeInTheDocument();
  });
});

describe("home page", () => {
  afterEach(() => vi.unstubAllGlobals());

  it("renders the hero and key sections even when the API is unreachable", async () => {
    vi.stubGlobal(
      "fetch",
      vi.fn(async () => {
        throw new Error("connection refused");
      }),
    );

    const { default: HomePage } = await import("./page");
    render(await HomePage());

    expect(
      screen.getByRole("heading", { name: /Pahami asuransi sebelum memilih proteksi/i }),
    ).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Mulai Belajar" })).toHaveAttribute("href", "/learn");
    expect(screen.getByRole("link", { name: "Kenali Jenis Proteksi" })).toHaveAttribute(
      "href",
      "/protection",
    );
    // protection categories fall back to the static concept list
    expect(screen.getByText("Critical Illness")).toBeInTheDocument();
    // final educational CTA, not a sales CTA
    expect(screen.getByRole("heading", { name: "Mulai dari dasar." })).toBeInTheDocument();
  });
});
