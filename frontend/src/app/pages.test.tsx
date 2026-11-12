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
      screen.getByRole("heading", { name: /Cara kami mengelola pengetahuan/i }),
    ).toBeInTheDocument();
    expect(screen.getByText("Pengetahuan umum")).toBeInTheDocument();
    expect(screen.getByText("Studi provider dan produk")).toBeInTheDocument();
    // all three reference states are explained
    expect(screen.getByText("Perlu ditinjau:")).toBeInTheDocument();
    expect(screen.getByText("Historis:")).toBeInTheDocument();
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
      screen.getByRole("heading", { name: /Pengetahuan untuk perlindungan/i }),
    ).toBeInTheDocument();
  });
});

describe("home page", () => {
  afterEach(() => vi.unstubAllGlobals());
  it("provides an honest retry screen when the knowledge API is unavailable", async () => {
    vi.stubGlobal(
      "fetch",
      vi.fn(async () => {
        throw new Error("connection refused");
      }),
    );
    const { default: HomePage } = await import("./page");
    render(await HomePage());
    expect(screen.getByRole("heading", { name: "Materi belum dapat dimuat." })).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Muat ulang" })).toHaveAttribute("href", "/");
  });
});
