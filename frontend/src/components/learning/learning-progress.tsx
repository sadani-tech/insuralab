"use client";

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from "react";

export const LEARNING_PROGRESS_KEY = "insuralab.learning.v1";

export type LearningProgress = {
  completedArticles: string[];
  completedQuizzes: Record<string, { percentage: number; completedAt: string }>;
  lastVisitedPath: string | null;
};

const EMPTY_PROGRESS: LearningProgress = {
  completedArticles: [],
  completedQuizzes: {},
  lastVisitedPath: null,
};

type LearningProgressContextValue = {
  progress: LearningProgress;
  hydrated: boolean;
  markArticleComplete: (slug: string) => void;
  markQuizComplete: (slug: string, percentage: number) => void;
  visitPath: (slug: string) => void;
  resetProgress: () => void;
};

const LearningProgressContext = createContext<LearningProgressContextValue | null>(null);

function readProgress(): LearningProgress {
  try {
    const value = JSON.parse(localStorage.getItem(LEARNING_PROGRESS_KEY) ?? "null") as unknown;
    if (!value || typeof value !== "object") return EMPTY_PROGRESS;

    const candidate = value as Partial<LearningProgress>;
    const completedArticles = Array.isArray(candidate.completedArticles)
      ? candidate.completedArticles.filter((item): item is string => typeof item === "string")
      : [];
    const completedQuizzes = Object.fromEntries(
      Object.entries(candidate.completedQuizzes ?? {}).filter(
        ([slug, result]) =>
          Boolean(slug) &&
          result !== null &&
          typeof result === "object" &&
          typeof result.percentage === "number" &&
          typeof result.completedAt === "string",
      ),
    ) as LearningProgress["completedQuizzes"];

    return {
      completedArticles: [...new Set(completedArticles)],
      completedQuizzes,
      lastVisitedPath:
        typeof candidate.lastVisitedPath === "string" ? candidate.lastVisitedPath : null,
    };
  } catch {
    return EMPTY_PROGRESS;
  }
}

export function LearningProgressProvider({ children }: { children: ReactNode }) {
  const [progress, setProgress] = useState<LearningProgress>(EMPTY_PROGRESS);
  const [hydrated, setHydrated] = useState(false);

  useEffect(() => {
    let active = true;
    queueMicrotask(() => {
      if (!active) return;
      setProgress(readProgress());
      setHydrated(true);
    });
    return () => {
      active = false;
    };
  }, []);

  const update = useCallback((change: (current: LearningProgress) => LearningProgress) => {
    setProgress((current) => {
      const next = change(current);
      localStorage.setItem(LEARNING_PROGRESS_KEY, JSON.stringify(next));
      return next;
    });
  }, []);

  const markArticleComplete = useCallback(
    (slug: string) =>
      update((current) => ({
        ...current,
        completedArticles: current.completedArticles.includes(slug)
          ? current.completedArticles
          : [...current.completedArticles, slug],
      })),
    [update],
  );
  const markQuizComplete = useCallback(
    (slug: string, percentage: number) =>
      update((current) => ({
        ...current,
        completedQuizzes: {
          ...current.completedQuizzes,
          [slug]: { percentage, completedAt: new Date().toISOString() },
        },
      })),
    [update],
  );
  const visitPath = useCallback(
    (slug: string) => update((current) => ({ ...current, lastVisitedPath: slug })),
    [update],
  );
  const resetProgress = useCallback(() => {
    localStorage.removeItem(LEARNING_PROGRESS_KEY);
    setProgress(EMPTY_PROGRESS);
  }, []);

  const value = useMemo<LearningProgressContextValue>(
    () => ({
      progress,
      hydrated,
      markArticleComplete,
      markQuizComplete,
      visitPath,
      resetProgress,
    }),
    [hydrated, markArticleComplete, markQuizComplete, progress, resetProgress, visitPath],
  );

  return (
    <LearningProgressContext.Provider value={value}>{children}</LearningProgressContext.Provider>
  );
}

export function useLearningProgress() {
  const value = useContext(LearningProgressContext);
  if (!value) throw new Error("useLearningProgress must be used within LearningProgressProvider");
  return value;
}
