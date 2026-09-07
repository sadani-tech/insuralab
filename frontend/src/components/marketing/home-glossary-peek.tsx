"use client";

import Link from "next/link";
import { useGlossaryPeek } from "@/lib/api/hooks";
import { Skeleton } from "@/components/ui/skeleton";

/**
 * Small client-rendered preview that pulls live glossary terms through TanStack
 * Query — the one interactive API integration on the homepage. Everything else
 * on this page is a Server Component.
 */
export function HomeGlossaryPeek() {
  const { data, isLoading, isError } = useGlossaryPeek(4);

  if (isError) {
    return (
      <p className="text-muted-foreground text-sm">
        The glossary is available once the content service is running.
      </p>
    );
  }

  if (isLoading || !data) {
    return (
      <div className="flex flex-col gap-3">
        {Array.from({ length: 4 }).map((_, i) => (
          <Skeleton key={i} className="h-10 w-full" />
        ))}
      </div>
    );
  }

  return (
    <dl className="flex flex-col gap-3">
      {data.data.map((term) => (
        <div key={term.id} className="border-border border-b pb-3 last:border-b-0 last:pb-0">
          <dt className="text-sm font-semibold">{term.term}</dt>
          <dd className="text-muted-foreground text-sm">{term.short_definition}</dd>
        </div>
      ))}
      <Link
        href="/glossary"
        className="text-primary text-sm font-medium underline-offset-4 hover:underline"
      >
        Open the glossary →
      </Link>
    </dl>
  );
}
