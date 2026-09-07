import { cn } from "@/lib/utils";

export type Source = {
  id: number;
  title: string;
  publisher?: string | null;
  url?: string | null;
  reference_period?: string | null;
  notes?: string | null;
};

/**
 * Renders the citations behind a piece of provider-specific content, including
 * the `notes` field where source inconsistencies are recorded — visibly.
 */
export function SourceList({ sources, className }: { sources: Source[]; className?: string }) {
  if (!sources.length) return null;

  return (
    <div className={cn("flex flex-col gap-3", className)}>
      <h3 className="text-muted-foreground text-xs font-semibold tracking-wide uppercase">
        Sources
      </h3>
      <ul className="flex flex-col gap-3">
        {sources.map((source) => (
          <li key={source.id} className="border-border border-l-2 pl-3 text-sm">
            <p className="font-medium">
              {source.url ? (
                <a href={source.url} className="underline underline-offset-2">
                  {source.title}
                </a>
              ) : (
                source.title
              )}
            </p>
            {source.publisher ? (
              <p className="text-muted-foreground text-xs">{source.publisher}</p>
            ) : null}
            {source.reference_period ? (
              <p className="text-muted-foreground text-xs">
                Reference period: {source.reference_period}
              </p>
            ) : null}
            {source.notes ? (
              <p className="text-muted-foreground mt-1 text-xs whitespace-pre-line italic">
                {source.notes}
              </p>
            ) : null}
          </li>
        ))}
      </ul>
    </div>
  );
}
