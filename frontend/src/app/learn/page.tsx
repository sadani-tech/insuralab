import type { Metadata } from "next";
import Link from "next/link";
import { ContinueLearning } from "@/components/learning/continue-learning";
import { KnowledgeCard } from "@/components/domain/knowledge-card";
import { Badge } from "@/components/ui/badge";
import { ButtonLink } from "@/components/ui/button";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { Container, PageHeader, Section, SectionHeader } from "@/components/ui/container";
import { EmptyState } from "@/components/ui/empty-state";
import { getArticles, getCategories, getLearningPath, getLearningPaths } from "@/lib/api";

export const metadata: Metadata = {
  title: "Learn",
  description: "Belajar asuransi melalui artikel, jalur terstruktur, dan quiz pemahaman InsuraLab.",
};

export const revalidate = 300;

const LEVELS = [
  { key: "beginner", label: "Beginner", blurb: "Untuk yang baru pertama kali belajar asuransi." },
  {
    key: "intermediate",
    label: "Intermediate",
    blurb: "Untuk memahami proteksi dan polis lebih dalam.",
  },
  {
    key: "advanced",
    label: "Advanced",
    blurb: "Topik lanjutan: underwriting, kepatuhan, dan distribusi.",
  },
];

async function safe<T>(promise: Promise<T>, fallback: T): Promise<T> {
  try {
    return await promise;
  } catch {
    return fallback;
  }
}

export default async function LearnPage({
  searchParams,
}: {
  searchParams: Promise<{ level?: string; category?: string }>;
}) {
  const params = await searchParams;
  const level = LEVELS.some((item) => item.key === params.level) ? params.level : undefined;
  const category = params.category;
  const [paths, categories, list] = await Promise.all([
    safe(getLearningPaths(), []),
    safe(getCategories(), []),
    safe(getArticles({ per_page: 24, level, category, knowledge_type: "general" }), {
      data: [],
      meta: { total: 0 } as never,
    }),
  ]);
  const pathDetails = await Promise.all(
    paths.map((path) => safe(getLearningPath(path.slug), null)),
  );
  const availablePathDetails = pathDetails.filter((path) => path !== null);
  const filtering = Boolean(level || category);

  return (
    <>
      <PageHeader
        eyebrow="InsuraLab Learn"
        title="Belajar Asuransi dari Dasarnya"
        lead="Pahami konsep, refleksikan kaitannya, uji pemahaman, lalu lanjutkan melalui jalur belajar yang koheren."
      >
        <div className="mt-2 flex flex-wrap gap-3">
          <ButtonLink href="/learn/path/dasar-dasar-asuransi">Mulai dari Dasar</ButtonLink>
          <ButtonLink href="/learn/path" variant="secondary">
            Lihat Semua Jalur
          </ButtonLink>
        </div>
      </PageHeader>

      <Section className="pb-0">
        <Container>
          <ContinueLearning paths={availablePathDetails} />
        </Container>
      </Section>

      {paths.length ? (
        <Section className="pb-0">
          <Container>
            <SectionHeader
              eyebrow="Jalur Belajar"
              title="Urutan yang membantu, tanpa mengunci materi"
            />
            <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {paths.slice(0, 5).map((path) => (
                <Link key={path.slug} href={`/learn/path/${path.slug}`} className="group">
                  <Card className="group-hover:border-border-strong h-full transition-colors">
                    <Badge tone="neutral">{path.level ?? "Semua tingkat"}</Badge>
                    <CardTitle className="mt-4">{path.title}</CardTitle>
                    <CardDescription>{path.description}</CardDescription>
                    <p className="text-muted-foreground mt-4 text-xs">
                      {path.lessons_count ?? 0} materi · {path.quizzes_count ?? 0} quiz
                    </p>
                  </Card>
                </Link>
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      <Section className="pb-0">
        <Container>
          <SectionHeader eyebrow="Tingkat" title="Pilih titik masuk sesuai pengalamanmu" />
          <div className="mt-8 grid gap-4 sm:grid-cols-3">
            {LEVELS.map((item) => (
              <Link key={item.key} href={`/learn?level=${item.key}`}>
                <Card className="hover:border-border-strong h-full transition-colors">
                  <Badge tone="neutral">{item.label}</Badge>
                  <CardDescription className="mt-2">{item.blurb}</CardDescription>
                </Card>
              </Link>
            ))}
          </div>
        </Container>
      </Section>

      {categories.length ? (
        <Section className="bg-surface-muted/40">
          <Container>
            <SectionHeader eyebrow="Jelajahi per Topik" title="Kelompok pengetahuan" />
            <div className="mt-6 flex flex-wrap gap-2">
              <Link href="/learn">
                <Badge tone={category ? "neutral" : "primary"}>Semua</Badge>
              </Link>
              {categories.map((item) => (
                <Link key={item.id} href={`/learn?category=${item.slug}`}>
                  <Badge tone={category === item.slug ? "primary" : "neutral"}>
                    {item.name}
                    {typeof item.articles_count === "number" ? (
                      <span className="text-muted-foreground ml-1">{item.articles_count}</span>
                    ) : null}
                  </Badge>
                </Link>
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      <Section id="articles">
        <Container>
          <div className="flex flex-wrap items-center justify-between gap-4">
            <SectionHeader
              eyebrow="Artikel"
              title={filtering ? "Hasil terfilter" : "Semua artikel"}
            />
            <div className="flex flex-wrap gap-2">
              <FilterChip label="Semua" href="/learn" active={!level} />
              {LEVELS.map((item) => (
                <FilterChip
                  key={item.key}
                  label={item.label}
                  href={`/learn?level=${item.key}${category ? `&category=${category}` : ""}`}
                  active={level === item.key}
                />
              ))}
            </div>
          </div>
          {list.data.length ? (
            <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {list.data.map((article) => (
                <KnowledgeCard key={article.id} article={article} href={`/learn/${article.slug}`} />
              ))}
            </div>
          ) : (
            <EmptyState
              className="mt-8"
              title={filtering ? "Tidak ada artikel untuk filter ini" : "Belum ada artikel"}
              description={
                filtering
                  ? "Coba pilih tingkat atau topik lain."
                  : "Jalankan backend InsuraLab untuk melihat konten."
              }
            />
          )}
        </Container>
      </Section>
    </>
  );
}

function FilterChip({ label, href, active }: { label: string; href: string; active: boolean }) {
  return (
    <Link href={href}>
      <Badge tone={active ? "primary" : "neutral"}>{label}</Badge>
    </Link>
  );
}
