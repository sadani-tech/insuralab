import { Badge } from "@/components/ui/badge";
import type { GlossaryTermSummary } from "@/lib/api/schemas";

export function GlossaryTermPreview({ term }: { term: GlossaryTermSummary }) {
  return (
    <div className="border-border border-b py-4 last:border-b-0">
      <div className="flex flex-wrap items-baseline gap-2">
        <h3 className="font-semibold">{term.term}</h3>
        <Badge tone="neutral">{term.category}</Badge>
        {term.is_provider_specific ? <Badge tone="primary">provider-specific</Badge> : null}
      </div>
      <p className="text-muted-foreground mt-1 text-sm">{term.short_definition}</p>
    </div>
  );
}
