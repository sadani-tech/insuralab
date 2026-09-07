import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it } from "vitest";
import { ArticleCompletion } from "./article-completion";
import { ContinueLearning } from "./continue-learning";
import {
  LearningProgressProvider,
  LEARNING_PROGRESS_KEY,
  useLearningProgress,
} from "./learning-progress";
import type { LearningPathDetail } from "@/lib/api";

const article = {
  id: 1,
  title: "Apa Itu Risiko?",
  slug: "apa-itu-risiko",
  excerpt: null,
  level: "beginner" as const,
  estimated_reading_minutes: 3,
  is_featured: false,
  knowledge_type: "general" as const,
  category: null,
  provider: null,
  published_at: null,
  reference_status: null,
  last_reviewed_at: null,
};
const path: LearningPathDetail = {
  id: 1,
  title: "Dasar-Dasar Asuransi",
  slug: "dasar-dasar-asuransi",
  description: null,
  level: "beginner",
  estimated_duration_minutes: 20,
  lessons_count: 1,
  quizzes_count: 0,
  items: [{ position: 1, type: "article", note: null, article, quiz: null }],
};

function Controls() {
  const { markQuizComplete, visitPath, resetProgress } = useLearningProgress();
  return (
    <>
      <button onClick={() => visitPath(path.slug)}>visit</button>
      <button onClick={() => markQuizComplete("quiz-dasar", 80)}>quiz</button>
      <button onClick={resetProgress}>reset</button>
    </>
  );
}

beforeEach(() => localStorage.clear());

describe("anonymous learning progress", () => {
  it("stores only namespaced educational progress and can reset it", async () => {
    const user = userEvent.setup();
    render(
      <LearningProgressProvider>
        <ArticleCompletion articleSlug={article.slug} />
        <Controls />
      </LearningProgressProvider>,
    );
    await user.click(await screen.findByRole("button", { name: "Tandai Selesai" }));
    await user.click(screen.getByRole("button", { name: "visit" }));
    await user.click(screen.getByRole("button", { name: "quiz" }));
    const stored = JSON.parse(localStorage.getItem(LEARNING_PROGRESS_KEY) ?? "null");
    expect(stored.completedArticles).toEqual([article.slug]);
    expect(stored.completedQuizzes["quiz-dasar"].percentage).toBe(80);
    expect(stored.lastVisitedPath).toBe(path.slug);
    expect(JSON.stringify(stored)).not.toMatch(/assessment|income|provider/i);
    await user.click(screen.getByRole("button", { name: "reset" }));
    expect(localStorage.getItem(LEARNING_PROGRESS_KEY)).toBeNull();
  });

  it("shows a deterministic next item for Continue Learning", async () => {
    localStorage.setItem(
      LEARNING_PROGRESS_KEY,
      JSON.stringify({ completedArticles: [], completedQuizzes: {}, lastVisitedPath: path.slug }),
    );
    render(
      <LearningProgressProvider>
        <ContinueLearning paths={[path]} />
      </LearningProgressProvider>,
    );
    expect(await screen.findByRole("heading", { name: path.title })).toBeInTheDocument();
    expect(screen.getByText(`Berikutnya: ${article.title}`)).toBeInTheDocument();
    expect(screen.getByRole("link", { name: "Lanjutkan" })).toHaveAttribute(
      "href",
      `/learn/${article.slug}?path=${path.slug}`,
    );
    await waitFor(() =>
      expect(screen.getByRole("button", { name: "Reset Progress" })).toBeInTheDocument(),
    );
  });
});
