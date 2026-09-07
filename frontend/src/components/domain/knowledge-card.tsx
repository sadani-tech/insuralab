import Link from "next/link";
import { Card } from "@/components/ui/card";
import { LevelBadge } from "./level-badge";
import { ProviderBadge } from "./provider-badge";
import { ReferenceStatusBadge } from "./reference-status-badge";
import type { ArticleSummary } from "@/lib/api/schemas";

/**
 * Compact preview of an article. Provider-specific items always show their
 * provider and trust state alongside the general/provider distinction.
 */
export function KnowledgeCard({ article, href }: { article: ArticleSummary; href?: string }) {
  const body = (
    <Card as="article" className="hover:border-border-strong h-full transition-colors">
      <div className="flex flex-wrap items-center gap-2">
        <LevelBadge level={article.level} />
        {article.knowledge_type === "provider_specific" ? (
          <>
            <ProviderBadge name={article.provider?.name ?? "Provider"} />
            <ReferenceStatusBadge status={article.reference_status} />
          </>
        ) : (
          <ProviderBadge name={null} />
        )}
      </div>
      <h3 className="mt-3 text-base font-semibold">{article.title}</h3>
      {article.excerpt ? (
        <p className="text-muted-foreground mt-1.5 line-clamp-3 text-sm">{article.excerpt}</p>
      ) : null}
      <p className="text-muted-foreground mt-3 text-xs">
        {article.estimated_reading_minutes} min read
      </p>
    </Card>
  );

  return href ? (
    <Link href={href} className="block focus-visible:outline-none">
      {body}
    </Link>
  ) : (
    body
  );
}
