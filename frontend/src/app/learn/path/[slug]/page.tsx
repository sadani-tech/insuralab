import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { PathItemStatus, PathProgress } from "@/components/learning/path-progress";
import { Badge } from "@/components/ui/badge";
import { Breadcrumb } from "@/components/ui/breadcrumb";
import { Card } from "@/components/ui/card";
import { Container, PageHeader, Section } from "@/components/ui/container";
import { ApiError, getLearningPath } from "@/lib/api";

export const revalidate = 300;

async function loadPath(slug: string) {
  try {
    return await getLearningPath(slug);
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) notFound();
    throw error;
  }
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  try {
    const path = await getLearningPath(slug);
    return { title: `Jalur Belajar ${path.title}`, description: path.description ?? undefined };
  } catch {
    return { title: "Jalur Belajar" };
  }
}

export default async function LearningPathPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const path = await loadPath(slug);
  return (
    <>
      <PageHeader
        eyebrow="Jalur Belajar"
        title={path.title}
        lead={path.description ?? "Ikuti materi dan quiz dalam urutan yang disarankan."}
      >
        <Breadcrumb
          items={[
            { label: "Home", href: "/" },
            { label: "Learn", href: "/learn" },
            { label: "Jalur Belajar", href: "/learn/path" },
            { label: path.title },
          ]}
        />
        <div className="mt-4 flex flex-wrap gap-2">
          <Badge tone="neutral">{path.level ?? "Semua tingkat"}</Badge>
          <Badge tone="neutral">{path.lessons_count ?? 0} materi</Badge>
          <Badge tone="neutral">{path.quizzes_count ?? 0} quiz</Badge>
          {path.estimated_duration_minutes ? (
            <Badge tone="neutral">± {path.estimated_duration_minutes} menit</Badge>
          ) : null}
        </div>
      </PageHeader>
      <Section>
        <Container className="max-w-4xl">
          <Card className="mb-8">
            <PathProgress path={path} />
          </Card>
          <ol className="grid gap-4" aria-label={`Urutan ${path.title}`}>
            {path.items.map((item) => {
              const target = item.article ?? item.quiz;
              if (!target) return null;
              const href = item.article
                ? `/learn/${item.article.slug}?path=${path.slug}`
                : `/quiz/${item.quiz!.slug}?path=${path.slug}`;
              return (
                <li key={`${item.type}-${target.slug}`}>
                  <Link href={href} className="group block">
                    <Card className="group-hover:border-border-strong flex items-start gap-4 transition-colors sm:gap-6">
                      <span className="text-muted-foreground font-mono text-sm">
                        {String(item.position).padStart(2, "0")}
                      </span>
                      <span className="min-w-0 grow">
                        <span className="text-muted-foreground text-xs uppercase">
                          {item.type === "article" ? "Artikel" : "Quiz"}
                        </span>
                        <span className="mt-1 block font-semibold">{target.title}</span>
                        {item.note ? (
                          <span className="text-muted-foreground mt-1 block text-sm">
                            {item.note}
                          </span>
                        ) : null}
                      </span>
                      <PathItemStatus item={item} />
                    </Card>
                  </Link>
                </li>
              );
            })}
          </ol>
          <p className="text-muted-foreground mt-8 text-sm">
            Tidak ada materi yang dikunci. Kamu bebas membuka topik mana pun sesuai kebutuhan.
          </p>
        </Container>
      </Section>
    </>
  );
}
