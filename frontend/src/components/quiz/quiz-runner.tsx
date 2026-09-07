"use client";

import { useState } from "react";
import Link from "next/link";
import { useLearningProgress } from "@/components/learning/learning-progress";
import { Badge } from "@/components/ui/badge";
import { Button, ButtonLink } from "@/components/ui/button";
import { Callout } from "@/components/ui/callout";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { submitQuiz, type QuizDetail, type QuizResult } from "@/lib/api";

function scoreLabel(percentage: number) {
  if (percentage < 50) return "Perlu Review";
  if (percentage < 80) return "Pemahaman Berkembang";
  return "Pemahaman Baik";
}

export function QuizRunner({ quiz, pathSlug }: { quiz: QuizDetail; pathSlug?: string }) {
  const [started, setStarted] = useState(false);
  const [current, setCurrent] = useState(0);
  const [answers, setAnswers] = useState<Record<string, string>>({});
  const [result, setResult] = useState<QuizResult | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const { markQuizComplete } = useLearningProgress();

  function retry() {
    setStarted(true);
    setCurrent(0);
    setAnswers({});
    setResult(null);
    setError(null);
  }

  async function finish() {
    setSubmitting(true);
    setError(null);
    try {
      const submitted = await submitQuiz(
        quiz.slug,
        quiz.questions.map((question) => ({
          question: question.key,
          option: answers[question.key]!,
        })),
      );
      setResult(submitted);
      markQuizComplete(quiz.slug, submitted.score.percentage);
      window.scrollTo({ top: 0, behavior: "smooth" });
    } catch {
      setError("Jawaban belum dapat diperiksa. Periksa koneksi lalu coba lagi.");
    } finally {
      setSubmitting(false);
    }
  }

  if (!started) {
    return (
      <Card className="mx-auto max-w-2xl sm:p-8">
        <Badge tone="neutral">{quiz.level ?? "Semua tingkat"}</Badge>
        <CardTitle className="mt-4 text-2xl">{quiz.title}</CardTitle>
        <CardDescription className="mt-3 text-base">
          {quiz.description ?? "Cek pemahamanmu dan pelajari penjelasan untuk setiap jawaban."}
        </CardDescription>
        <dl className="text-muted-foreground mt-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
          <div>
            <dt>Pertanyaan</dt>
            <dd className="text-foreground font-semibold">{quiz.questions.length}</dd>
          </div>
          <div>
            <dt>Durasi</dt>
            <dd className="text-foreground font-semibold">
              {quiz.estimated_duration_minutes
                ? `${quiz.estimated_duration_minutes} menit`
                : "Fleksibel"}
            </dd>
          </div>
          <div>
            <dt>Format</dt>
            <dd className="text-foreground font-semibold">Pilihan tunggal</dd>
          </div>
        </dl>
        <Callout tone="note" className="mt-6">
          Tidak ada batas waktu atau batas percobaan. Jawaban dinilai oleh server setelah semua
          pertanyaan selesai.
        </Callout>
        <Button type="button" className="mt-6 w-full sm:w-auto" onClick={() => setStarted(true)}>
          Mulai Quiz
        </Button>
      </Card>
    );
  }

  if (result) {
    const missed = result.results.filter((item) => !item.correct).length;
    return (
      <div className="mx-auto flex max-w-3xl flex-col gap-8" aria-live="polite">
        <Card className="border-primary/30 text-center sm:p-10">
          <p className="text-muted-foreground text-sm">Pemahamanmu</p>
          <p className="mt-2 text-5xl font-semibold">{result.score.percentage}%</p>
          <h2 className="mt-3 text-xl font-semibold">{scoreLabel(result.score.percentage)}</h2>
          <p className="text-muted-foreground mt-2">
            {result.score.correct} dari {result.score.total} konsep dipahami
            {missed ? `; ${missed} dapat dipelajari kembali.` : "."}
          </p>
          <p className="text-muted-foreground mt-3 text-xs">
            Hasil ini adalah refleksi belajar, bukan sertifikasi profesional.
          </p>
        </Card>

        {result.review_topics.length ? (
          <Card className="bg-info-surface">
            <CardTitle>Topik yang bisa dipelajari kembali</CardTitle>
            <div className="mt-4 flex flex-wrap gap-2">
              {result.review_topics.map((topic) => (
                <ButtonLink
                  key={topic.slug}
                  href={`${topic.url}${pathSlug ? `?path=${pathSlug}` : ""}`}
                  variant="secondary"
                  size="sm"
                >
                  {topic.title}
                </ButtonLink>
              ))}
            </div>
          </Card>
        ) : null}

        <section aria-labelledby="review-answers-title">
          <h2 id="review-answers-title" className="text-2xl font-semibold">
            Review Jawaban
          </h2>
          <ol className="mt-5 grid gap-4">
            {result.results.map((item, index) => (
              <li key={item.question}>
                <Card className={item.correct ? "border-success/40" : "border-warning/50"}>
                  <p className="text-muted-foreground font-mono text-xs">PERTANYAAN {index + 1}</p>
                  <h3 className="mt-2 font-semibold">{item.prompt}</h3>
                  <p
                    className={`mt-4 font-semibold ${item.correct ? "text-success" : "text-warning"}`}
                  >
                    {item.correct ? "Tepat." : "Belum tepat."}
                  </p>
                  <dl className="mt-3 grid gap-2 text-sm">
                    <div>
                      <dt className="text-muted-foreground">Jawabanmu</dt>
                      <dd>{item.selected_option_label}</dd>
                    </div>
                    {!item.correct ? (
                      <div>
                        <dt className="text-muted-foreground">Jawaban yang tepat</dt>
                        <dd>{item.correct_option_label}</dd>
                      </div>
                    ) : null}
                  </dl>
                  <p className="border-border mt-4 border-t pt-4 text-sm">{item.explanation}</p>
                  {item.related_lessons.length ? (
                    <p className="text-muted-foreground mt-4 text-sm">
                      Pelajari:{" "}
                      {item.related_lessons.map((lesson, lessonIndex) => (
                        <span key={lesson.slug}>
                          {lessonIndex ? ", " : ""}
                          <Link
                            className="text-primary underline-offset-4 hover:underline"
                            href={`${lesson.url}${pathSlug ? `?path=${pathSlug}` : ""}`}
                          >
                            {lesson.title}
                          </Link>
                        </span>
                      ))}
                    </p>
                  ) : null}
                </Card>
              </li>
            ))}
          </ol>
        </section>

        <div className="flex flex-col gap-3 sm:flex-row">
          <Button type="button" onClick={retry}>
            Ulangi Quiz
          </Button>
          {pathSlug ? (
            <ButtonLink href={`/learn/path/${pathSlug}`} variant="secondary">
              Lanjutkan Jalur Belajar
            </ButtonLink>
          ) : (
            <ButtonLink href="/learn/path" variant="secondary">
              Lihat Jalur Belajar
            </ButtonLink>
          )}
        </div>
      </div>
    );
  }

  const question = quiz.questions[current]!;
  const selection = answers[question.key];
  const lastQuestion = current === quiz.questions.length - 1;

  return (
    <div className="mx-auto max-w-2xl">
      <div className="mb-5 flex items-center justify-between gap-3 text-sm">
        <span className="font-medium">
          Pertanyaan {current + 1} dari {quiz.questions.length}
        </span>
        <span className="text-muted-foreground">
          {Math.round(((current + 1) / quiz.questions.length) * 100)}%
        </span>
      </div>
      <div className="bg-surface-muted mb-8 h-2 overflow-hidden rounded-full" aria-hidden="true">
        <div
          className="bg-primary h-full"
          style={{ width: `${((current + 1) / quiz.questions.length) * 100}%` }}
        />
      </div>
      <Card className="sm:p-8">
        <fieldset>
          <legend className="text-xl font-semibold sm:text-2xl">{question.prompt}</legend>
          <div className="mt-6 grid gap-3">
            {question.options.map((option) => (
              <label
                key={option.key}
                className="border-border has-[:checked]:border-primary has-[:checked]:bg-info-surface flex cursor-pointer items-start gap-3 rounded-[var(--radius-sm)] border p-4 transition-colors"
              >
                <input
                  type="radio"
                  name={question.key}
                  value={option.key}
                  checked={selection === option.key}
                  onChange={() =>
                    setAnswers((currentAnswers) => ({
                      ...currentAnswers,
                      [question.key]: option.key,
                    }))
                  }
                  className="mt-1 size-4 accent-[var(--color-primary)]"
                />
                <span>{option.label}</span>
              </label>
            ))}
          </div>
        </fieldset>
        {error ? (
          <p role="alert" className="text-danger mt-5 text-sm">
            {error}
          </p>
        ) : null}
        <div className="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
          <Button
            type="button"
            variant="secondary"
            onClick={() => setCurrent((value) => value - 1)}
            disabled={current === 0}
          >
            Kembali
          </Button>
          <Button
            type="button"
            onClick={lastQuestion ? finish : () => setCurrent((value) => value + 1)}
            disabled={!selection || submitting}
          >
            {submitting ? "Memeriksa jawaban…" : lastQuestion ? "Lihat Hasil" : "Lanjut"}
          </Button>
        </div>
      </Card>
    </div>
  );
}
