import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import * as api from "@/lib/api";
import {
  LearningProgressProvider,
  LEARNING_PROGRESS_KEY,
} from "@/components/learning/learning-progress";
import { QuizRunner } from "./quiz-runner";

const quiz: api.QuizDetail = {
  id: 1,
  title: "Dasar-Dasar Asuransi",
  slug: "dasar-asuransi",
  description: "Cek konsep dasar.",
  level: "beginner",
  estimated_duration_minutes: 5,
  category: { name: "Fondasi", slug: "fondasi" },
  questions_count: 2,
  related_learning_paths: [{ title: "Dasar", slug: "dasar" }],
  questions: [
    {
      id: 1,
      key: "risk",
      prompt: "Apa itu risiko?",
      type: "single",
      options: [
        { id: 1, key: "uncertainty", label: "Ketidakpastian" },
        { id: 2, key: "profit", label: "Keuntungan pasti" },
      ],
    },
    {
      id: 2,
      key: "transfer",
      prompt: "Konsep asuransi konvensional?",
      type: "single",
      options: [
        { id: 3, key: "sharing", label: "Risk sharing" },
        { id: 4, key: "transfer", label: "Risk transfer" },
      ],
    },
  ],
};

beforeEach(() => {
  localStorage.clear();
  vi.restoreAllMocks();
  vi.stubGlobal("scrollTo", vi.fn());
});

describe("QuizRunner", () => {
  it("starts, selects answers, submits to the backend, reviews feedback, stores completion, and retries", async () => {
    vi.spyOn(api, "submitQuiz").mockResolvedValue({
      quiz: { slug: quiz.slug, title: quiz.title },
      score: { correct: 1, total: 2, percentage: 50 },
      results: [
        {
          question: "risk",
          prompt: "Apa itu risiko?",
          correct: true,
          selected_option: "uncertainty",
          selected_option_label: "Ketidakpastian",
          correct_option: "uncertainty",
          correct_option_label: "Ketidakpastian",
          explanation: "Tepat. Risiko berkaitan dengan ketidakpastian.",
          concept_explanation: "Risiko bukan keuntungan pasti.",
          related_lessons: [],
        },
        {
          question: "transfer",
          prompt: "Konsep asuransi konvensional?",
          correct: false,
          selected_option: "sharing",
          selected_option_label: "Risk sharing",
          correct_option: "transfer",
          correct_option_label: "Risk transfer",
          explanation: "Belum tepat. Konsepnya adalah pengalihan dampak finansial.",
          concept_explanation: null,
          related_lessons: [
            {
              title: "Apa Itu Asuransi?",
              slug: "apa-itu-asuransi",
              excerpt: null,
              url: "/learn/apa-itu-asuransi",
            },
          ],
        },
      ],
      review_topics: [
        {
          title: "Apa Itu Asuransi?",
          slug: "apa-itu-asuransi",
          excerpt: null,
          url: "/learn/apa-itu-asuransi",
        },
      ],
    });
    const user = userEvent.setup();
    render(
      <LearningProgressProvider>
        <QuizRunner quiz={quiz} pathSlug="dasar" />
      </LearningProgressProvider>,
    );
    await user.click(screen.getByRole("button", { name: "Mulai Quiz" }));
    expect(screen.getByRole("group", { name: "Apa itu risiko?" })).toBeInTheDocument();
    await user.click(screen.getByRole("radio", { name: "Ketidakpastian" }));
    await user.click(screen.getByRole("button", { name: "Lanjut" }));
    await user.click(screen.getByRole("radio", { name: "Risk sharing" }));
    await user.click(screen.getByRole("button", { name: "Lihat Hasil" }));
    await waitFor(() =>
      expect(api.submitQuiz).toHaveBeenCalledWith(quiz.slug, [
        { question: "risk", option: "uncertainty" },
        { question: "transfer", option: "sharing" },
      ]),
    );
    expect(await screen.findByText("Pemahaman Berkembang")).toBeInTheDocument();
    expect(screen.getByText("Tepat.")).toBeInTheDocument();
    expect(screen.getByText("Belum tepat.")).toBeInTheDocument();
    expect(screen.getByText(/pengalihan dampak finansial/)).toBeInTheDocument();
    expect(screen.getAllByRole("link", { name: "Apa Itu Asuransi?" })).toHaveLength(2);
    expect(screen.getAllByRole("link", { name: "Apa Itu Asuransi?" })[0]).toHaveAttribute(
      "href",
      "/learn/apa-itu-asuransi?path=dasar",
    );
    expect(
      JSON.parse(localStorage.getItem(LEARNING_PROGRESS_KEY) ?? "null").completedQuizzes[quiz.slug]
        .percentage,
    ).toBe(50);
    await user.click(screen.getByRole("button", { name: "Ulangi Quiz" }));
    expect(screen.getByRole("group", { name: "Apa itu risiko?" })).toBeInTheDocument();
    expect(screen.getByRole("radio", { name: "Ketidakpastian" })).not.toBeChecked();
  });
});
