"use client";

import { useMemo, useSyncExternalStore } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Badge } from "@/components/ui/badge";
import { Button, ButtonLink } from "@/components/ui/button";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { Callout } from "@/components/ui/callout";
import { assessmentResultSchema, type AssessmentResult as AssessmentResultDto } from "@/lib/api";
import { ASSESSMENT_RESULT_KEY } from "./assessment-runner";

const DISPLAY_STRENGTH = {
  foundational: 34,
  worth_exploring: 64,
  highly_relevant: 92,
} as const;

export function AssessmentResult() {
  const router = useRouter();
  const stored = useSyncExternalStore<string | null | undefined>(
    () => () => undefined,
    () => sessionStorage.getItem(ASSESSMENT_RESULT_KEY),
    () => undefined,
  );
  const result = useMemo<AssessmentResultDto | null | undefined>(() => {
    if (stored === undefined) return undefined;
    if (!stored) return null;
    try {
      const parsed = assessmentResultSchema.safeParse(JSON.parse(stored));
      return parsed.success ? parsed.data : null;
    } catch {
      return null;
    }
  }, [stored]);

  if (result === undefined) {
    return <p className="text-muted-foreground py-16 text-center">Memuat peta pembelajaran…</p>;
  }

  if (result === null) {
    return (
      <Card className="mx-auto max-w-xl text-center">
        <CardTitle>Hasil assessment tidak tersedia</CardTitle>
        <CardDescription>
          Hasil hanya disimpan selama sesi browser ini. Mulai assessment kembali untuk membuat peta
          pembelajaran baru.
        </CardDescription>
        <ButtonLink href="/assessment/protection-needs" className="mt-5">
          Mulai Assessment
        </ButtonLink>
      </Card>
    );
  }

  function restart() {
    sessionStorage.removeItem(ASSESSMENT_RESULT_KEY);
    router.push("/assessment/protection-needs");
  }

  return (
    <div className="flex flex-col gap-10">
      <section aria-labelledby="protection-map-title">
        <h2 id="protection-map-title" className="text-xl font-semibold">
          Peta relevansi pembelajaran
        </h2>
        <p className="text-muted-foreground mt-2 max-w-2xl text-sm">
          Semua topik tetap dapat dipelajari. Panjang bar hanya membantu membandingkan tingkat
          relevansi edukatif dan bukan skor risiko atau ukuran kecukupan proteksi.
        </p>
        <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
          {result.dimensions.map((dimension) => (
            <Card key={dimension.key} className="p-4">
              <p className="text-sm font-semibold">{dimension.name}</p>
              <div className="bg-surface-muted mt-4 h-2 overflow-hidden rounded-full" aria-hidden>
                <div
                  className="bg-accent h-full rounded-full"
                  style={{ width: `${DISPLAY_STRENGTH[dimension.relevance]}%` }}
                />
              </div>
              <p className="text-accent mt-2 text-xs font-semibold">{dimension.relevance_label}</p>
            </Card>
          ))}
        </div>
      </section>

      <section className="grid gap-5" aria-label="Rincian topik proteksi">
        {result.dimensions.map((dimension, index) => (
          <Card key={dimension.key} className="sm:p-8">
            <div className="flex flex-wrap items-start justify-between gap-3">
              <div>
                <p className="text-muted-foreground font-mono text-xs">TOPIK {index + 1}</p>
                <CardTitle className="mt-1 text-xl">{dimension.name}</CardTitle>
              </div>
              <Badge tone={dimension.relevance === "highly_relevant" ? "primary" : "neutral"}>
                {dimension.relevance_label}
              </Badge>
            </div>
            <CardDescription className="mt-4 max-w-3xl">{dimension.description}</CardDescription>

            <div className="mt-6 grid gap-6 md:grid-cols-2">
              <div>
                <h3 className="text-sm font-semibold">Mengapa topik ini muncul?</h3>
                <ul className="text-muted-foreground mt-3 grid gap-2 text-sm">
                  {dimension.reasons.map((reason) => (
                    <li key={reason} className="flex gap-2">
                      <span aria-hidden>—</span>
                      <span>{reason}</span>
                    </li>
                  ))}
                </ul>
              </div>
              <div>
                <h3 className="text-sm font-semibold">Apa yang dapat dipelajari?</h3>
                {dimension.learning_topics.length ? (
                  <ul className="mt-3 grid gap-2 text-sm">
                    {dimension.learning_topics.map((topic) => (
                      <li key={topic.slug}>
                        <Link
                          className="text-primary font-medium underline-offset-4 hover:underline"
                          href={topic.url}
                        >
                          {topic.title}
                        </Link>
                      </li>
                    ))}
                  </ul>
                ) : (
                  <p className="text-muted-foreground mt-3 text-sm">
                    Materi untuk topik ini sedang disiapkan.
                  </p>
                )}
              </div>
            </div>

            {(dimension.learning_path ?? dimension.learning_topics[0]) ? (
              <ButtonLink
                href={dimension.learning_path?.url ?? dimension.learning_topics[0]!.url}
                className="mt-6"
              >
                {dimension.learning_path
                  ? `Mulai Jalur ${dimension.learning_path.title}`
                  : "Pelajari Topik Ini"}
              </ButtonLink>
            ) : null}
          </Card>
        ))}
      </section>

      {result.additional_learning ? (
        <Card className="border-accent/40 bg-info-surface sm:p-8">
          <CardTitle className="text-xl">{result.additional_learning.title}</CardTitle>
          <CardDescription className="mt-3 max-w-3xl">
            {result.additional_learning.description}
          </CardDescription>
          <div className="mt-5 flex flex-wrap gap-3">
            {result.additional_learning.topics.map((topic) => (
              <ButtonLink key={topic.slug} href={topic.url} variant="secondary" size="sm">
                {topic.title}
              </ButtonLink>
            ))}
          </div>
        </Card>
      ) : null}

      <Callout tone="note">
        <p>{result.disclaimer}</p>
        <p className="mt-2">
          Memiliki lebih banyak asuransi tidak otomatis lebih baik. Perlindungan yang sudah
          ada—termasuk BPJS atau manfaat perusahaan—perlu dipahami bersama kebutuhan,
          keterjangkauan, kemungkinan tumpang tindih, dan keberlanjutannya.
        </p>
      </Callout>

      <div className="flex flex-col gap-3 sm:flex-row">
        <Button type="button" onClick={restart}>
          Ulangi Assessment
        </Button>
        <ButtonLink href="/assessment/protection-needs/methodology" variant="secondary">
          Pelajari Metodologi
        </ButtonLink>
      </div>
    </div>
  );
}
