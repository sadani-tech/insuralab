import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import * as api from "@/lib/api";
import type { AssessmentDefinition } from "@/lib/api";
import AssessmentLandingPage from "./page";
import AssessmentMethodologyPage from "./protection-needs/methodology/page";

const definition: AssessmentDefinition = {
  id: 1,
  slug: "protection-needs",
  title: "Kenali Topik Proteksi",
  methodology_version: "1.0",
  description: "Refleksi edukatif.",
  intro: "Jawab beberapa pertanyaan sederhana mengenai kondisi finansial.",
  disclaimer: "Bukan rekomendasi produk.",
  questions: [],
};

describe("Assessment pages", () => {
  it("renders the landing promise, duration, privacy, and start actions", async () => {
    vi.spyOn(api, "getProtectionNeedsAssessment").mockResolvedValue(definition);
    render(await AssessmentLandingPage());

    expect(
      screen.getByRole("heading", { name: "Kenali Topik Proteksi yang Relevan untuk Dipelajari" }),
    ).toBeInTheDocument();
    expect(screen.getByText("Sekitar 2–4 menit")).toBeInTheDocument();
    expect(screen.getByText("Tanpa login dan identitas")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Mulai Assessment" })).toHaveAttribute(
      "href",
      "/assessment/protection-needs",
    );
    expect(screen.getByRole("link", { name: "Bagaimana Assessment Ini Bekerja?" })).toHaveAttribute(
      "href",
      "/assessment/protection-needs/methodology",
    );
  });

  it("renders a useful unavailable state", async () => {
    vi.spyOn(api, "getProtectionNeedsAssessment").mockRejectedValue(new Error("offline"));
    render(await AssessmentLandingPage());
    expect(screen.getByText("Assessment belum dapat dimuat")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Coba Lagi" })).toHaveAttribute("href", "/assessment");
  });

  it("documents deterministic provider-neutral non-goals", () => {
    render(<AssessmentMethodologyPage />);
    expect(screen.getByText(/deterministik, dapat dijelaskan/i)).toBeInTheDocument();
    expect(screen.getByText(/Tidak menggunakan AI/i)).toBeInTheDocument();
    expect(screen.getByText(/Tidak merekomendasikan produk, provider/i)).toBeInTheDocument();
  });
});
