"use client";

import { useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { evaluateProtectionNeedsAssessment, type AssessmentDefinition } from "@/lib/api";
import { cn } from "@/lib/utils";

export const ASSESSMENT_RESULT_KEY = "insuralab:protection-needs-result";

export function AssessmentRunner({ assessment }: { assessment: AssessmentDefinition }) {
  const router = useRouter();
  const [answers, setAnswers] = useState<Record<string, string>>({});
  const [index, setIndex] = useState(0);
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const questions = useMemo(
    () =>
      assessment.questions.filter(
        (question) =>
          !question.condition ||
          question.condition.options.includes(answers[question.condition.question] ?? ""),
      ),
    [answers, assessment.questions],
  );
  const safeIndex = Math.min(index, questions.length - 1);
  const question = questions[safeIndex]!;

  if (!question) {
    return <p>Assessment belum memiliki pertanyaan yang dapat ditampilkan.</p>;
  }

  const selected = answers[question.key];
  const isLast = safeIndex === questions.length - 1;

  function choose(option: string) {
    setAnswers((current) => ({ ...current, [question.key]: option }));
    setError(null);
  }

  async function continueAssessment() {
    if (question.is_required && !selected) {
      setError("Pilih satu jawaban sebelum melanjutkan.");
      return;
    }

    if (!isLast) {
      setIndex(safeIndex + 1);
      setError(null);
      return;
    }

    setSubmitting(true);
    setError(null);
    try {
      const applicableKeys = new Set(questions.map((item) => item.key));
      const payload = Object.entries(answers)
        .filter(([key]) => applicableKeys.has(key))
        .map(([questionKey, option]) => ({ question: questionKey, option }));
      const result = await evaluateProtectionNeedsAssessment(
        assessment.methodology_version,
        payload,
      );
      sessionStorage.setItem(ASSESSMENT_RESULT_KEY, JSON.stringify(result));
      router.push("/assessment/protection-needs/result");
    } catch {
      setError(
        "Hasil belum dapat dibuat. Periksa koneksi lalu coba lagi. Jika assessment baru diperbarui, muat ulang halaman ini.",
      );
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="mx-auto w-full max-w-3xl min-w-0">
      <div className="mb-8" aria-label={`Pertanyaan ${safeIndex + 1} dari ${questions.length}`}>
        <div className="mb-2 flex items-center justify-between gap-4 text-sm">
          <span className="font-medium">
            Pertanyaan {safeIndex + 1} dari {questions.length}
          </span>
          <span className="text-muted-foreground">Versi {assessment.methodology_version}</span>
        </div>
        <div
          className="bg-surface-muted h-2 overflow-hidden rounded-full"
          role="progressbar"
          aria-valuemin={1}
          aria-valuemax={questions.length}
          aria-valuenow={safeIndex + 1}
        >
          <div
            className="bg-primary h-full rounded-full transition-[width] duration-300"
            style={{ width: `${((safeIndex + 1) / questions.length) * 100}%` }}
          />
        </div>
      </div>

      <Card className="min-w-0 p-5 sm:p-8">
        <fieldset
          className="min-w-0"
          aria-describedby={question.help_text ? `${question.key}-help` : undefined}
        >
          <legend className="text-xl font-semibold sm:text-2xl">{question.prompt}</legend>
          {question.help_text ? (
            <p id={`${question.key}-help`} className="text-muted-foreground mt-3 text-sm">
              {question.help_text}
            </p>
          ) : null}

          <div className="mt-6 grid gap-3">
            {question.options.map((option) => {
              const checked = selected === option.key;
              return (
                <label
                  key={option.key}
                  className={cn(
                    "border-border bg-background flex min-h-14 cursor-pointer items-center gap-4 rounded-[var(--radius)] border px-4 py-3 transition-colors",
                    "hover:border-border-strong has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-[var(--color-ring)]",
                    checked && "border-primary bg-info-surface",
                  )}
                >
                  <input
                    type="radio"
                    name={question.key}
                    value={option.key}
                    checked={checked}
                    onChange={() => choose(option.key)}
                    className="accent-primary h-5 w-5 shrink-0"
                  />
                  <span className="min-w-0 font-medium break-words">{option.label}</span>
                </label>
              );
            })}
          </div>
        </fieldset>

        {error ? (
          <p className="text-danger mt-4 text-sm" role="alert">
            {error}
          </p>
        ) : null}

        <div className="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
          <Button
            type="button"
            variant="secondary"
            disabled={safeIndex === 0 || submitting}
            onClick={() => {
              setIndex(Math.max(0, safeIndex - 1));
              setError(null);
            }}
          >
            Kembali
          </Button>
          <Button type="button" disabled={submitting} onClick={continueAssessment}>
            {submitting ? "Menyusun peta pembelajaran…" : isLast ? "Lihat Hasil" : "Lanjut"}
          </Button>
        </div>
      </Card>

      <p className="text-muted-foreground mt-5 text-center text-xs">
        Jawaban hanya dikirim untuk evaluasi saat ini dan tidak disimpan sebagai profil pengguna.
      </p>
    </div>
  );
}
