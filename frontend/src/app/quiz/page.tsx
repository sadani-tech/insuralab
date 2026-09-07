import type { Metadata } from "next";
import Link from "next/link";
import { Badge } from "@/components/ui/badge";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { Container, PageHeader, Section } from "@/components/ui/container";
import { EmptyState } from "@/components/ui/empty-state";
import { getQuizzes } from "@/lib/api";

export const metadata: Metadata = {
  title: "Uji Pemahamanmu",
  description:
    "Quiz edukatif InsuraLab untuk mengecek pemahaman konsep asuransi dengan penjelasan pada setiap jawaban.",
};

export const revalidate = 300;

export default async function QuizPage() {
  const quizzes = await getQuizzes().catch(() => []);

  return (
    <>
      <PageHeader
        eyebrow="Quiz InsuraLab"
        title="Uji Pemahamanmu"
        lead="Quiz InsuraLab dirancang untuk membantu mengecek pemahaman, bukan sekadar mencari jawaban benar."
      />
      <Section>
        <Container>
          {quizzes.length ? (
            <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
              {quizzes.map((quiz) => (
                <Link key={quiz.slug} href={`/quiz/${quiz.slug}`} className="group">
                  <Card className="group-hover:border-border-strong flex h-full flex-col transition-colors">
                    <div className="flex flex-wrap gap-2">
                      <Badge tone="neutral">{quiz.category?.name ?? "Konsep umum"}</Badge>
                      <Badge tone="neutral">{quiz.level ?? "Semua tingkat"}</Badge>
                    </div>
                    <CardTitle className="mt-4 text-xl">{quiz.title}</CardTitle>
                    <CardDescription className="grow">{quiz.description}</CardDescription>
                    <dl className="text-muted-foreground mt-5 flex flex-wrap gap-x-5 gap-y-2 text-xs">
                      <div>
                        <dt className="sr-only">Jumlah pertanyaan</dt>
                        <dd>{quiz.questions_count ?? 0} pertanyaan</dd>
                      </div>
                      <div>
                        <dt className="sr-only">Estimasi durasi</dt>
                        <dd>
                          {quiz.estimated_duration_minutes
                            ? `${quiz.estimated_duration_minutes} menit`
                            : "Durasi fleksibel"}
                        </dd>
                      </div>
                    </dl>
                    {quiz.related_learning_paths[0] ? (
                      <p className="text-primary mt-3 text-sm">
                        Jalur: {quiz.related_learning_paths[0].title}
                      </p>
                    ) : null}
                  </Card>
                </Link>
              ))}
            </div>
          ) : (
            <EmptyState
              title="Quiz belum tersedia"
              description="Jalankan backend dan seeder InsuraLab untuk memuat quiz."
            />
          )}
        </Container>
      </Section>
    </>
  );
}
