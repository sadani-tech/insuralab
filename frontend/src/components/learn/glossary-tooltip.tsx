"use client";

import { useId, useState, type ReactNode } from "react";
import Link from "next/link";
import { useGlossaryEntry } from "./glossary-context";

/**
 * Inline glossary reference. Explicit — only rendered where an article author
 * wrote `:istilah[…]{slug=…}`. Opens on hover, focus, or tap; closes on blur,
 * mouse-leave, or Escape. Works on touch (the trigger is a real button).
 */
export function GlossaryTooltip({ slug, children }: { slug: string; children: ReactNode }) {
  const [open, setOpen] = useState(false);
  const entry = useGlossaryEntry(slug);
  const panelId = useId();

  if (!slug) return <>{children}</>;

  return (
    <span
      className="relative inline-block"
      onMouseEnter={() => setOpen(true)}
      onMouseLeave={() => setOpen(false)}
    >
      <button
        type="button"
        aria-expanded={open}
        aria-describedby={open ? panelId : undefined}
        onClick={() => setOpen((v) => !v)}
        onFocus={() => setOpen(true)}
        onBlur={() => setOpen(false)}
        onKeyDown={(e) => e.key === "Escape" && setOpen(false)}
        className="decoration-primary/50 hover:decoration-primary cursor-help border-none bg-transparent p-0 font-[inherit] text-[inherit] underline decoration-dotted underline-offset-2"
      >
        {children}
      </button>

      {open ? (
        <span
          id={panelId}
          role="tooltip"
          className="border-border bg-surface text-foreground absolute bottom-full left-1/2 z-30 mb-2 block w-64 -translate-x-1/2 rounded-[var(--radius-sm)] border p-3 text-left text-sm shadow-lg"
        >
          {entry ? (
            <>
              <span className="block font-semibold">{entry.term}</span>
              <span className="text-muted-foreground mt-1 block">{entry.short_definition}</span>
            </>
          ) : (
            <span className="text-muted-foreground block">Lihat definisi di glosarium.</span>
          )}
          <Link
            href={`/glossary/${slug}`}
            className="text-primary mt-2 block text-xs font-medium underline-offset-4 hover:underline"
          >
            Pelajari lebih lanjut →
          </Link>
        </span>
      ) : null}
    </span>
  );
}
