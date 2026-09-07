import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import * as api from "@/lib/api";
import type { AssessmentDefinition, AssessmentResult as AssessmentResultDto } from "@/lib/api";
import { AssessmentRunner, ASSESSMENT_RESULT_KEY } from "./assessment-runner";
import { AssessmentResult } from "./assessment-result";

const assessment: AssessmentDefinition = {
  id: 1,
  slug: "protection-needs",
  title: "Protection Needs",
  methodology_version: "1.0",
  description: "Refleksi edukatif",
  intro: "Jawab pertanyaan",
  disclaimer: "Bukan rekomendasi produk.",
  questions: [
    {
      id: 1,
      key: "dependents",
      prompt: "Apakah ada tanggungan?",
      help_text: null,
      type: "single",
      is_required: true,
      condition: null,
      options: [
        { id: 1, key: "none", label: "Tidak" },
        { id: 2, key: "one", label: "Ya" },
      ],
    },
    {
      id: 2,
      key: "dependent_health",
      prompt: "Apakah tanggungan memiliki proteksi kesehatan?",
      help_text: null,
      type: "single",
      is_required: true,
      condition: { question: "dependents", options: ["one"] },
      options: [
        { id: 3, key: "no", label: "Belum" },
        { id: 4, key: "yes", label: "Ya" },
      ],
    },
    {
      id: 3,
      key: "understanding",
      prompt: "Apakah kamu memahami polis?",
      help_text: null,
      type: "single",
      is_required: true,
      condition: null,
      options: [
        { id: 5, key: "no", label: "Belum memahami" },
        { id: 6, key: "yes", label: "Memahami" },
      ],
    },
  ],
};

const result: AssessmentResultDto = {
  assessment: "protection-needs",
  methodology_version: "1.0",
  disclaimer: "Assessment ini bersifat edukatif dan bukan rekomendasi produk.",
  dimensions: [
    {
      key: "life",
      name: "Proteksi Jiwa",
      relevance: "highly_relevant",
      relevance_label: "Sangat Relevan",
      description: "Dampak finansial pada tanggungan.",
      reasons: ["Kamu memiliki tanggungan."],
      learning_topics: [
        {
          title: "Asuransi Jiwa",
          slug: "asuransi-jiwa",
          excerpt: "Konsep umum.",
          url: "/learn/asuransi-jiwa",
        },
      ],
      learning_path: {
        title: "Memahami Proteksi Pribadi",
        slug: "memahami-proteksi-pribadi",
        url: "/learn/path/memahami-proteksi-pribadi",
      },
    },
    {
      key: "health",
      name: "Proteksi Kesehatan",
      relevance: "foundational",
      relevance_label: "Fondasi Pembelajaran",
      description: "Biaya medis.",
      reasons: ["Topik tetap tersedia sebagai fondasi."],
      learning_topics: [],
    },
  ],
  additional_learning: {
    title: "Sudah punya perlindungan?",
    description: "Pahami polis yang ada.",
    topics: [
      {
        title: "Polis Asuransi",
        slug: "polis-asuransi",
        excerpt: null,
        url: "/learn/polis-asuransi",
      },
    ],
  },
};

beforeEach(() => sessionStorage.clear());

describe("Assessment runner", () => {
  it("renders semantic radios and blocks forward navigation until required selection", async () => {
    const user = userEvent.setup();
    render(<AssessmentRunner assessment={assessment} />);

    expect(screen.getByRole("group", { name: "Apakah ada tanggungan?" })).toBeInTheDocument();
    await user.click(screen.getByRole("button", { name: "Lanjut" }));
    expect(screen.getByRole("alert")).toHaveTextContent("Pilih satu jawaban");
  });

  it("skips and restores a conditional question while keeping accurate progress and back navigation", async () => {
    const user = userEvent.setup();
    render(<AssessmentRunner assessment={assessment} />);

    await user.click(screen.getByRole("radio", { name: "Tidak" }));
    await user.click(screen.getByRole("button", { name: "Lanjut" }));
    expect(screen.getByText("Pertanyaan 2 dari 2")).toBeInTheDocument();
    expect(screen.getByRole("group", { name: "Apakah kamu memahami polis?" })).toBeInTheDocument();

    await user.click(screen.getByRole("button", { name: "Kembali" }));
    await user.click(screen.getByRole("radio", { name: "Ya" }));
    await user.click(screen.getByRole("button", { name: "Lanjut" }));
    expect(screen.getByText("Pertanyaan 2 dari 3")).toBeInTheDocument();
    expect(
      screen.getByRole("group", { name: "Apakah tanggungan memiliki proteksi kesehatan?" }),
    ).toBeInTheDocument();
  });

  it("submits applicable answers, shows loading behavior, and stores only the result in session storage", async () => {
    const singleQuestion = { ...assessment, questions: [assessment.questions[0]!] };
    let resolveResult!: (value: AssessmentResultDto) => void;
    const deferred = new Promise<AssessmentResultDto>((resolve) => {
      resolveResult = resolve;
    });
    const evaluate = vi.spyOn(api, "evaluateProtectionNeedsAssessment").mockReturnValue(deferred);
    const user = userEvent.setup();
    render(<AssessmentRunner assessment={singleQuestion} />);

    await user.click(screen.getByRole("radio", { name: "Ya" }));
    await user.click(screen.getByRole("button", { name: "Lihat Hasil" }));

    expect(screen.getByRole("button", { name: "Menyusun peta pembelajaran…" })).toBeDisabled();
    resolveResult(result);

    await waitFor(() =>
      expect(evaluate).toHaveBeenCalledWith("1.0", [{ question: "dependents", option: "one" }]),
    );
    await waitFor(() =>
      expect(JSON.parse(sessionStorage.getItem(ASSESSMENT_RESULT_KEY) ?? "null")).toEqual(result),
    );
    expect(sessionStorage.getItem(ASSESSMENT_RESULT_KEY)).not.toContain("dependents");
  });

  it("shows a useful retry message when evaluation fails", async () => {
    vi.spyOn(api, "evaluateProtectionNeedsAssessment").mockRejectedValue(new Error("offline"));
    const user = userEvent.setup();
    render(
      <AssessmentRunner assessment={{ ...assessment, questions: [assessment.questions[0]!] }} />,
    );
    await user.click(screen.getByRole("radio", { name: "Ya" }));
    await user.click(screen.getByRole("button", { name: "Lihat Hasil" }));
    expect(await screen.findByRole("alert")).toHaveTextContent("Hasil belum dapat dibuat");
  });
});

describe("Assessment result", () => {
  it("renders ordered dimensions, reasons, general learning links, disclaimer, and restart", async () => {
    sessionStorage.setItem(ASSESSMENT_RESULT_KEY, JSON.stringify(result));
    render(<AssessmentResult />);

    expect(
      await screen.findByRole("heading", { name: "Peta relevansi pembelajaran" }),
    ).toBeInTheDocument();
    expect(screen.getAllByText("Proteksi Jiwa")[0]).toBeInTheDocument();
    expect(screen.getByText("Kamu memiliki tanggungan.")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Asuransi Jiwa" })).toHaveAttribute(
      "href",
      "/learn/asuransi-jiwa",
    );
    expect(
      screen.getByRole("link", { name: "Mulai Jalur Memahami Proteksi Pribadi" }),
    ).toHaveAttribute("href", "/learn/path/memahami-proteksi-pribadi");
    expect(screen.getByText("Sudah punya perlindungan?")).toBeInTheDocument();
    expect(screen.getByText(/bukan rekomendasi produk/i)).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Ulangi Assessment" })).toBeInTheDocument();
  });

  it("handles missing or malformed session results without exposing answers in a URL", async () => {
    sessionStorage.setItem(ASSESSMENT_RESULT_KEY, "not-json");
    render(<AssessmentResult />);
    expect(await screen.findByText("Hasil assessment tidak tersedia")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Mulai Assessment" })).toHaveAttribute(
      "href",
      "/assessment/protection-needs",
    );
  });
});
