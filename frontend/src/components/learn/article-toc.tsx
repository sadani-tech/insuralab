"use client";

import { useEffect, useState } from "react";
import { cn } from "@/lib/utils";
import type { TocEntry } from "@/lib/markdown";

/**
 * Table of contents from an article's H2/H3 headings. Rendered twice by the
 * page: `variant="mobile"` (a collapsible "Daftar Isi" in the flow) and
 * `variant="desktop"` (sticky in the sidebar). Only shows when there are enough
 * headings to be useful.
 */
export function ArticleToc({
  entries,
  variant,
}: {
  entries: TocEntry[];
  variant: "mobile" | "desktop";
}) {
  const [activeId, setActiveId] = useState<string | null>(null);
  const [open, setOpen] = useState(false);

  useEffect(() => {
    if (variant !== "desktop") return;
    const headings = entries
      .map((e) => document.getElementById(e.id))
      .filter((el): el is HTMLElement => Boolean(el));
    if (!headings.length) return;

    const observer = new IntersectionObserver(
      (obsEntries) => {
        const visible = obsEntries
          .filter((e) => e.isIntersecting)
          .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
        if (visible[0]?.target.id) setActiveId(visible[0].target.id);
      },
      { rootMargin: "-80px 0px -70% 0px" },
    );
    headings.forEach((h) => observer.observe(h));
    return () => observer.disconnect();
  }, [entries, variant]);

  if (entries.length < 3) return null;

  const list = (
    <ul className="flex flex-col gap-1.5 text-sm">
      {entries.map((e) => (
        <li key={e.id} className={cn(e.depth === 3 && "ml-3")}>
          <a
            href={`#${e.id}`}
            onClick={() => setOpen(false)}
            className={cn(
              "hover:text-foreground block py-0.5 transition-colors",
              activeId === e.id ? "text-foreground font-medium" : "text-muted-foreground",
            )}
          >
            {e.text}
          </a>
        </li>
      ))}
    </ul>
  );

  if (variant === "mobile") {
    return (
      <details
        className="border-border bg-surface rounded-[var(--radius)] border p-4 lg:hidden"
        open={open}
        onToggle={(e) => setOpen((e.target as HTMLDetailsElement).open)}
      >
        <summary className="cursor-pointer text-sm font-semibold">Daftar Isi</summary>
        <div className="mt-3">{list}</div>
      </details>
    );
  }

  return (
    <nav aria-label="Daftar isi" className="sticky top-24">
      <p className="text-muted-foreground mb-3 text-xs font-semibold tracking-wide uppercase">
        Daftar Isi
      </p>
      {list}
    </nav>
  );
}
