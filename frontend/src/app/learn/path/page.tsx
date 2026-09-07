import type { Metadata } from "next";
import Link from "next/link";
import { ResetLearningProgress } from "@/components/learning/path-progress";
import { Badge } from "@/components/ui/badge";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { Container, PageHeader, Section, SectionHeader } from "@/components/ui/container";
import { EmptyState } from "@/components/ui/empty-state";
import { getLearningPaths } from "@/lib/api";

export const metadata: Metadata = {
  title: "Jalur Belajar",
  description:
    "Jalur belajar terstruktur untuk memahami konsep asuransi secara bertahap tanpa mengunci materi.",
};

export const revalidate = 300;

const MAP = [
  { title: "Fondasi", topics: "Risiko · Asuransi · Polis · Klaim" },
  { title: "Proteksi", topics: "Jiwa · Kesehatan · Penyakit Kritis · Disabilitas · Kecelakaan" },
  { title: "Lanjutan", topics: "PAYDI · Syariah · Underwriting · Operasional · Kepatuhan" },
];

export default async function LearningPathIndexPage() {
  const paths = await getLearningPaths().catch(() => []);
  return (
    <>
      <PageHeader
        eyebrow="Learning Journey"
        title="Jalur Belajar InsuraLab"
        lead="Gunakan urutan sebagai panduan, bukan batasan. Semua artikel dan quiz tetap dapat dibuka kapan saja."
      >
        <ResetLearningProgress />
      </PageHeader>
      <Section className="pb-0">
        <Container>
          <SectionHeader
            eyebrow="Peta 2D"
            title="Dari fondasi menuju pemahaman lanjutan"
            lead="Hubungan topik ditampilkan vertikal agar tetap mudah dibaca di layar kecil."
          />
          <ol className="mx-auto mt-8 grid max-w-3xl gap-3">
            {MAP.map((group, index) => (
              <li key={group.title} className="relative">
                <Card className="text-center">
                  <p className="text-accent font-mono text-xs">
                    {String(index + 1).padStart(2, "0")}
                  </p>
                  <CardTitle className="mt-2 text-xl uppercase">{group.title}</CardTitle>
                  <CardDescription>{group.topics}</CardDescription>
                </Card>
                {index < MAP.length - 1 ? (
                  <div className="text-muted-foreground py-2 text-center" aria-hidden="true">
                    ↓
                  </div>
                ) : null}
              </li>
            ))}
          </ol>
        </Container>
      </Section>
      <Section>
        <Container>
          <SectionHeader eyebrow="Pilih Jalur" title="Belajar dalam urutan yang koheren" />
          {paths.length ? (
            <div className="mt-8 grid gap-5 sm:grid-cols-2">
              {paths.map((path) => (
                <Link key={path.slug} href={`/learn/path/${path.slug}`} className="group">
                  <Card className="group-hover:border-border-strong h-full transition-colors">
                    <Badge tone="neutral">{path.level ?? "Semua tingkat"}</Badge>
                    <CardTitle className="mt-4 text-xl">{path.title}</CardTitle>
                    <CardDescription>{path.description}</CardDescription>
                    <p className="text-muted-foreground mt-5 text-sm">
                      {path.lessons_count ?? 0} materi · {path.quizzes_count ?? 0} quiz
                      {path.estimated_duration_minutes
                        ? ` · ${path.estimated_duration_minutes} menit`
                        : ""}
                    </p>
                  </Card>
                </Link>
              ))}
            </div>
          ) : (
            <EmptyState
              className="mt-8"
              title="Jalur belajar belum tersedia"
              description="Jalankan backend dan seeder untuk memuat jalur belajar."
            />
          )}
        </Container>
      </Section>
    </>
  );
}
