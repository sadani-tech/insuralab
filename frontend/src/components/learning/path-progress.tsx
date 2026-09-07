"use client";

import { useEffect } from "react";
import { Button } from "@/components/ui/button";
import type { LearningPathDetail } from "@/lib/api";
import { useLearningProgress } from "./learning-progress";

function itemCompleted(
  item: LearningPathDetail["items"][number],
  completedArticles: string[],
  completedQuizzes: Record<string, unknown>,
) {
  if (item.type === "article" && item.article) return completedArticles.includes(item.article.slug);
  if (item.type === "quiz" && item.quiz) return item.quiz.slug in completedQuizzes;
  return false;
}

export function PathProgress({ path }: { path: LearningPathDetail }) {
  const { progress, hydrated, visitPath } = useLearningProgress();

  useEffect(() => visitPath(path.slug), [path.slug, visitPath]);

  if (!hydrated) return <p className="text-muted-foreground text-sm">Memuat progress…</p>;

  const completed = path.items.filter((item) =>
    itemCompleted(item, progress.completedArticles, progress.completedQuizzes),
  ).length;

  return (
    <div aria-label={`${completed} dari ${path.items.length} langkah selesai`}>
      <div className="flex items-center justify-between gap-4 text-sm">
        <span className="font-medium">Progress di browser ini</span>
        <span className="text-muted-foreground">
          {completed}/{path.items.length} selesai
        </span>
      </div>
      <div className="bg-surface-muted mt-2 h-2 overflow-hidden rounded-full" aria-hidden="true">
        <div
          className="bg-primary h-full"
          style={{ width: `${path.items.length ? (completed / path.items.length) * 100 : 0}%` }}
        />
      </div>
    </div>
  );
}

export function ResetLearningProgress() {
  const { hydrated, progress, resetProgress } = useLearningProgress();
  const hasProgress =
    progress.completedArticles.length > 0 ||
    Object.keys(progress.completedQuizzes).length > 0 ||
    Boolean(progress.lastVisitedPath);
  if (!hydrated || !hasProgress) return null;

  return (
    <Button type="button" variant="ghost" size="sm" onClick={resetProgress}>
      Reset Progress
    </Button>
  );
}

export function PathItemStatus({ item }: { item: LearningPathDetail["items"][number] }) {
  const { progress, hydrated } = useLearningProgress();
  if (!hydrated) return <span className="text-muted-foreground text-xs">Belum dimulai</span>;
  const complete = itemCompleted(item, progress.completedArticles, progress.completedQuizzes);
  return (
    <span
      className={complete ? "text-success text-xs font-semibold" : "text-muted-foreground text-xs"}
    >
      {complete ? "Selesai ✓" : "Belum selesai"}
    </span>
  );
}
