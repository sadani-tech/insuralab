import { render, screen } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import * as api from "@/lib/api";
import QuizPage from "./page";

afterEach(() => vi.restoreAllMocks());

describe("Quiz landing", () => {
  it("explains the learning purpose and lists honest quiz metadata", async () => {
    vi.spyOn(api, "getQuizzes").mockResolvedValue([
      {
        id: 1,
        title: "Dasar-Dasar Asuransi",
        slug: "dasar-dasar-asuransi",
        description: "Uji konsep fondasi.",
        level: "beginner",
        estimated_duration_minutes: 8,
        category: { name: "Fondasi", slug: "fondasi" },
        questions_count: 7,
        related_learning_paths: [{ title: "Dasar-Dasar Asuransi", slug: "dasar" }],
      },
    ]);

    render(await QuizPage());
    expect(screen.getByRole("heading", { name: "Uji Pemahamanmu" })).toBeInTheDocument();
    expect(screen.getByText(/bukan sekadar mencari jawaban benar/i)).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /Dasar-Dasar Asuransi/i })).toHaveAttribute(
      "href",
      "/quiz/dasar-dasar-asuransi",
    );
    expect(screen.getByText("7 pertanyaan")).toBeInTheDocument();
    expect(screen.getByText("8 menit")).toBeInTheDocument();
  });
});
