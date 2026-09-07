import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import { Container } from "@/components/ui/container";
import { Card } from "@/components/ui/card";
import { Breadcrumb } from "@/components/ui/breadcrumb";
import { LevelBadge } from "@/components/domain/level-badge";
import { ProviderBadge } from "@/components/domain/provider-badge";
import { ReferenceStatusBadge } from "@/components/domain/reference-status-badge";
import { LastReviewed } from "@/components/domain/last-reviewed";
import { SourceList } from "@/components/domain/source-list";
import { EducationalDisclaimer } from "@/components/domain/educational-disclaimer";
import { TrustNotice } from "@/components/domain/trust-notice";
import { KnowledgeCard } from "@/components/domain/knowledge-card";
import { GlossaryProvider, type GlossaryMap } from "@/components/learn/glossary-context";
import { MarkdownContent } from "@/components/learn/markdown-content";
import { ArticleToc } from "@/components/learn/article-toc";
import { ArticleCompletion } from "@/components/learning/article-completion";
import { ApiError, getArticle, getLearningPath } from "@/lib/api";
import { extractToc } from "@/lib/markdown";

export const revalidate = 300;

async function loadArticle(slug: string) {
  try {
    return await getArticle(slug);
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
    const article = await getArticle(slug);
    return {
      title: article.title,
      description: article.excerpt ?? undefined,
    };
  } catch {
    return { title: "Artikel" };
  }
}

export default async function ArticlePage({
  params,
  searchParams,
}: {
  params: Promise<{ slug: string }>;
  searchParams: Promise<{ path?: string }>;
}) {
  const [{ slug }, query] = await Promise.all([params, searchParams]);
  const article = await loadArticle(slug);

  const learningPath = query.path ? await getLearningPath(query.path).catch(() => null) : null;
  const currentPosition = learningPath?.items.findIndex(
    (item) => item.article?.slug === article.slug,
  );
  const nextItem =
    currentPosition !== undefined && currentPosition >= 0
      ? learningPath?.items[currentPosition + 1]
      : undefined;
  const nextTarget = nextItem?.article ?? nextItem?.quiz;
  const next =
    nextTarget && learningPath
      ? {
          title: nextTarget.title,
          href: nextItem?.article
            ? `/learn/${nextItem.article.slug}?path=${learningPath.slug}`
            : `/quiz/${nextItem!.quiz!.slug}?path=${learningPath.slug}`,
        }
      : undefined;

  const toc = extractToc(article.body);
  const glossaryMap: GlossaryMap = Object.fromEntries(
    article.glossary_terms.map((t) => [
      t.slug,
      { term: t.term, short_definition: t.short_definition },
    ]),
  );

  return (
    <article className="pb-16">
      <div className="border-border bg-surface-muted/40 border-b">
        <Container className="flex flex-col gap-4 py-8">
          <Breadcrumb
            items={[
              { label: "Home", href: "/" },
              { label: "Learn", href: "/learn" },
              ...(learningPath
                ? [
                    {
                      label: learningPath.title,
                      href: `/learn/path/${learningPath.slug}`,
                    },
                  ]
                : []),
              ...(article.category
                ? [
                    {
                      label: article.category.name,
                      href: `/learn?category=${article.category.slug}`,
                    },
                  ]
                : []),
              { label: article.title },
            ]}
          />
          <h1 className="max-w-3xl text-3xl font-semibold sm:text-4xl">{article.title}</h1>
          {article.excerpt ? (
            <p className="text-muted-foreground max-w-2xl text-lg">{article.excerpt}</p>
          ) : null}
          <div className="text-muted-foreground flex flex-wrap items-center gap-2 text-sm">
            <LevelBadge level={article.level} />
            {article.knowledge_type === "provider_specific" ? (
              <>
                <ProviderBadge name={article.provider?.name ?? "Provider"} />
                <ReferenceStatusBadge status={article.trust.reference_status} />
              </>
            ) : (
              <ProviderBadge name={null} />
            )}
            <span aria-hidden="true">·</span>
            <span>{article.estimated_reading_minutes} menit baca</span>
            {article.trust.last_reviewed_at ? (
              <>
                <span aria-hidden="true">·</span>
                <LastReviewed date={article.trust.last_reviewed_at} />
              </>
            ) : null}
          </div>
        </Container>
      </div>

      <Container className="grid gap-10 py-10 lg:grid-cols-[minmax(0,1fr)_16rem]">
        <div className="flex min-w-0 flex-col gap-8">
          <TrustNotice
            isProviderSpecific={article.knowledge_type === "provider_specific"}
            referenceStatus={article.trust.reference_status}
            providerName={article.provider?.name}
          />

          <ArticleToc entries={toc} variant="mobile" />

          <div className="reading-measure">
            <GlossaryProvider terms={glossaryMap}>
              <MarkdownContent markdown={article.body} />
            </GlossaryProvider>
          </div>

          {article.key_takeaways.length ? (
            <Card className="reading-measure bg-surface-muted/50">
              <h2 className="text-base font-semibold">Poin Penting</h2>
              <ul className="mt-3 flex flex-col gap-2 text-sm">
                {article.key_takeaways.map((point, i) => (
                  <li key={i} className="flex gap-2">
                    <span aria-hidden="true" className="text-primary">
                      &bull;
                    </span>
                    <span>{point}</span>
                  </li>
                ))}
              </ul>
            </Card>
          ) : null}

          {article.sources.length ? (
            <div className="reading-measure">
              <h2 className="mb-3 text-base font-semibold">Sumber &amp; Referensi</h2>
              <SourceList sources={article.sources} />
            </div>
          ) : null}

          {article.glossary_terms.length ? (
            <div className="reading-measure">
              <h2 className="mb-2 text-base font-semibold">Istilah dalam artikel ini</h2>
              <div className="flex flex-wrap gap-2 text-sm">
                {article.glossary_terms.map((t) => (
                  <Link
                    key={t.slug}
                    href={`/glossary/${t.slug}`}
                    className="border-border hover:border-border-strong rounded-full border px-3 py-1"
                  >
                    {t.term}
                  </Link>
                ))}
              </div>
            </div>
          ) : null}

          <EducationalDisclaimer
            className="reading-measure"
            variant={article.knowledge_type === "provider_specific" ? "provider" : "default"}
          />

          <ArticleCompletion articleSlug={article.slug} pathSlug={learningPath?.slug} next={next} />

          {article.related.length ? (
            <div>
              <h2 className="mb-4 text-lg font-semibold">Pelajari Selanjutnya</h2>
              <div className="grid gap-4 sm:grid-cols-2">
                {article.related.map((rel) => (
                  <KnowledgeCard key={rel.id} article={rel} href={`/learn/${rel.slug}`} />
                ))}
              </div>
            </div>
          ) : null}
        </div>

        <aside className="hidden lg:block">
          <ArticleToc entries={toc} variant="desktop" />
        </aside>
      </Container>
    </article>
  );
}
