"use client";

import { ButtonLink } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import type { LearningPathDetail } from "@/lib/api";
import { ResetLearningProgress } from "./path-progress";
import { useLearningProgress } from "./learning-progress";

export function ContinueLearning({ paths }: { paths: LearningPathDetail[] }) {
  const { progress, hydrated } = useLearningProgress();
  if (!hydrated || !progress.lastVisitedPath) return null;

  const path = paths.find((candidate) => candidate.slug === progress.lastVisitedPath);
  if (!path) return null;
  const next = path.items.find((item) => {
    if (item.article) return !progress.completedArticles.includes(item.article.slug);
    if (item.quiz) return !(item.quiz.slug in progress.completedQuizzes);
    return false;
  });
  const target = next?.article ?? next?.quiz;
  const href = next?.article
    ? `/learn/${next.article.slug}?path=${path.slug}`
    : next?.quiz
      ? `/quiz/${next.quiz.slug}?path=${path.slug}`
      : `/learn/path/${path.slug}`;

  return (
    <Card className="border-primary/30 bg-info-surface">
      <p className="text-accent font-mono text-xs font-semibold tracking-wider uppercase">
        Lanjutkan Belajar
      </p>
      <h2 className="mt-2 text-xl font-semibold">{path.title}</h2>
      <p className="text-muted-foreground mt-2 text-sm">
        {target ? `Berikutnya: ${target.title}` : "Semua langkah pada jalur ini telah selesai."}
      </p>
      <div className="mt-5 flex flex-wrap items-center gap-3">
        <ButtonLink href={href}>{target ? "Lanjutkan" : "Lihat Jalur"}</ButtonLink>
        <ResetLearningProgress />
      </div>
    </Card>
  );
}
