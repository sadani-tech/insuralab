"use client";

import { Button, ButtonLink } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { useLearningProgress } from "./learning-progress";

export function ArticleCompletion({
  articleSlug,
  pathSlug,
  next,
}: {
  articleSlug: string;
  pathSlug?: string;
  next?: { href: string; title: string };
}) {
  const { progress, markArticleComplete } = useLearningProgress();
  const complete = progress.completedArticles.includes(articleSlug);

  return (
    <Card className="reading-measure border-primary/30">
      <h2 className="text-lg font-semibold">Sudah memahami materi ini?</h2>
      <p className="text-muted-foreground mt-2 text-sm">
        Tandai secara eksplisit setelah kamu selesai membaca. Status hanya disimpan di browser ini.
      </p>
      <div className="mt-5 flex flex-col gap-3 sm:flex-row">
        <Button type="button" onClick={() => markArticleComplete(articleSlug)} disabled={complete}>
          {complete ? "Materi Selesai" : "Tandai Selesai"}
        </Button>
        {next ? (
          <ButtonLink href={next.href} variant="secondary">
            Lanjut: {next.title}
          </ButtonLink>
        ) : pathSlug ? (
          <ButtonLink href={`/learn/path/${pathSlug}`} variant="secondary">
            Kembali ke Jalur
          </ButtonLink>
        ) : null}
      </div>
    </Card>
  );
}
