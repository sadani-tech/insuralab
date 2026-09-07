import { render, screen } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import * as api from "@/lib/api";
import { LearningProgressProvider } from "@/components/learning/learning-progress";
import LearningPathIndexPage from "./page";
import LearningPathPage from "./[slug]/page";

const article = {
  id: 1,
  title: "Apa Itu Risiko?",
  slug: "apa-itu-risiko",
  excerpt: null,
  level: "beginner" as const,
  estimated_reading_minutes: 4,
  is_featured: false,
  knowledge_type: "general" as const,
  category: null,
  provider: null,
  published_at: null,
  reference_status: null,
  last_reviewed_at: null,
};
const quiz = {
  id: 2,
  title: "Quiz Dasar",
  slug: "quiz-dasar",
  description: null,
  level: "beginner" as const,
  estimated_duration_minutes: 5,
  category: null,
  questions_count: 5,
  related_learning_paths: [],
};
const path: api.LearningPathDetail = {
  id: 1,
  title: "Dasar-Dasar Asuransi",
  slug: "dasar-dasar-asuransi",
  description: "Mulai dari risiko.",
  level: "beginner",
  estimated_duration_minutes: 30,
  lessons_count: 1,
  quizzes_count: 1,
  items: [
    { position: 1, type: "article", note: "Fondasi", article, quiz: null },
    { position: 2, type: "quiz", note: null, article: null, quiz },
  ],
};

afterEach(() => vi.restoreAllMocks());

describe("Learning paths", () => {
  it("lists paths and renders the mobile-safe educational map", async () => {
    vi.spyOn(api, "getLearningPaths").mockResolvedValue([path]);
    render(<LearningProgressProvider>{await LearningPathIndexPage()}</LearningProgressProvider>);
    expect(screen.getByRole("heading", { name: "Jalur Belajar InsuraLab" })).toBeInTheDocument();
    expect(screen.getByText("Fondasi")).toBeInTheDocument();
    expect(screen.getByText("Proteksi")).toBeInTheDocument();
    expect(screen.getByText("Lanjutan")).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /Dasar-Dasar Asuransi/i })).toHaveAttribute(
      "href",
      "/learn/path/dasar-dasar-asuransi",
    );
  });

  it("renders article and quiz items in API order without locking either", async () => {
    vi.spyOn(api, "getLearningPath").mockResolvedValue(path);
    render(
      <LearningProgressProvider>
        {await LearningPathPage({ params: Promise.resolve({ slug: path.slug }) })}
      </LearningProgressProvider>,
    );
    expect(screen.getByRole("list", { name: `Urutan ${path.title}` })).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /Apa Itu Risiko/i })).toHaveAttribute(
      "href",
      `/learn/${article.slug}?path=${path.slug}`,
    );
    expect(screen.getByRole("link", { name: /Quiz Dasar/i })).toHaveAttribute(
      "href",
      `/quiz/${quiz.slug}?path=${path.slug}`,
    );
    expect(screen.getByText(/Tidak ada materi yang dikunci/)).toBeInTheDocument();
  });
});
