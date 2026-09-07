import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import { Container } from "@/components/ui/container";
import { Breadcrumb } from "@/components/ui/breadcrumb";
import { Badge } from "@/components/ui/badge";
import { ReferenceStatusBadge } from "@/components/domain/reference-status-badge";
import { LastReviewed } from "@/components/domain/last-reviewed";
import { SourceList } from "@/components/domain/source-list";
import { KnowledgeCard } from "@/components/domain/knowledge-card";
import { ApiError, getGlossaryTerm } from "@/lib/api";

export const revalidate = 300;

async function loadTerm(slug: string) {
  try {
    return await getGlossaryTerm(slug);
  } catch (err) {
    if (err instanceof ApiError && err.status === 404) notFound();
    throw err;
  }
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  try {
    const term = await getGlossaryTerm(slug);
    return { title: `${term.term} — Glosarium`, description: term.short_definition };
  } catch {
    return { title: "Glosarium" };
  }
}

export default async function GlossaryTermPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const term = await loadTerm(slug);

  return (
    <Container className="flex max-w-3xl flex-col gap-8 py-10">
      <Breadcrumb
        items={[
          { label: "Home", href: "/" },
          { label: "Glosarium", href: "/glossary" },
          { label: term.term },
        ]}
      />

      <header className="flex flex-col gap-3">
        <div className="flex flex-wrap items-center gap-2">
          <Badge tone="neutral">{term.category}</Badge>
          {term.trust.is_provider_specific ? <Badge tone="primary">provider-spesifik</Badge> : null}
          <ReferenceStatusBadge status={term.trust.reference_status} />
        </div>
        <h1 className="text-3xl font-semibold sm:text-4xl">{term.term}</h1>
        <p className="text-muted-foreground text-lg">{term.short_definition}</p>
      </header>

      <div className="text-foreground/90 leading-[1.75]">
        <p>{term.definition}</p>
      </div>

      {term.trust.last_reviewed_at ? (
        <p className="text-muted-foreground text-sm">
          <LastReviewed date={term.trust.last_reviewed_at} />
        </p>
      ) : null}

      {term.related_terms.length ? (
        <section className="flex flex-col gap-2">
          <h2 className="text-base font-semibold">Konsep terkait</h2>
          <div className="flex flex-wrap gap-2 text-sm">
            {term.related_terms.map((rt) => (
              <Link
                key={rt.slug}
                href={`/glossary/${rt.slug}`}
                className="border-border hover:border-border-strong rounded-full border px-3 py-1"
              >
                {rt.term}
              </Link>
            ))}
          </div>
        </section>
      ) : null}

      {term.related_articles.length ? (
        <section className="flex flex-col gap-3">
          <h2 className="text-base font-semibold">Pelajari di artikel</h2>
          <div className="grid gap-4 sm:grid-cols-2">
            {term.related_articles.map((a) => (
              <KnowledgeCard key={a.id} article={a} href={`/learn/${a.slug}`} />
            ))}
          </div>
        </section>
      ) : null}

      {term.sources.length ? (
        <section>
          <h2 className="mb-3 text-base font-semibold">Sumber &amp; Referensi</h2>
          <SourceList sources={term.sources} />
        </section>
      ) : null}
    </Container>
  );
}
