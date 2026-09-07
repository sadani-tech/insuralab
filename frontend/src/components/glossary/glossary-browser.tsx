"use client";

import { useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { cn } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { EmptyState } from "@/components/ui/empty-state";
import { useGlossaryBrowser } from "@/lib/api/hooks";

const ALPHABET = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");

const CATEGORIES = [
  { key: "", label: "Semua kategori" },
  { key: "general", label: "Umum" },
  { key: "process", label: "Proses" },
  { key: "product", label: "Produk" },
  { key: "sharia", label: "Syariah" },
  { key: "compliance", label: "Kepatuhan" },
  { key: "agency", label: "Keagenan" },
  { key: "life", label: "Jiwa" },
];

function useDebounced<T>(value: T, ms = 250): T {
  const [debounced, setDebounced] = useState(value);
  useEffect(() => {
    const t = setTimeout(() => setDebounced(value), ms);
    return () => clearTimeout(t);
  }, [value, ms]);
  return debounced;
}

export function GlossaryBrowser() {
  const [letter, setLetter] = useState<string | null>(null);
  const [category, setCategory] = useState("");
  const [rawQuery, setRawQuery] = useState("");
  const q = useDebounced(rawQuery.trim());

  // A free-text search overrides the letter filter.
  const query = useMemo(
    () => ({
      letter: q ? undefined : (letter ?? undefined),
      category: category || undefined,
      q: q || undefined,
    }),
    [letter, category, q],
  );

  const { data, isLoading, isError, isPlaceholderData } = useGlossaryBrowser(query);
  const terms = data?.data ?? [];

  return (
    <div className="flex flex-col gap-6">
      <div className="flex flex-col gap-4">
        <label className="flex flex-col gap-1.5">
          <span className="text-sm font-medium">Cari istilah</span>
          <input
            type="search"
            value={rawQuery}
            onChange={(e) => setRawQuery(e.target.value)}
            placeholder="mis. underwriting, premi, tabarru"
            className="border-border-strong bg-surface focus-visible:outline-ring h-11 rounded-[var(--radius-sm)] border px-3.5 text-sm focus-visible:outline-2 focus-visible:outline-offset-2"
          />
        </label>

        <div className="flex flex-wrap gap-2">
          {CATEGORIES.map((c) => (
            <button
              key={c.key}
              type="button"
              onClick={() => setCategory(c.key)}
              className="rounded-full"
              aria-pressed={category === c.key}
            >
              <Badge tone={category === c.key ? "primary" : "neutral"}>{c.label}</Badge>
            </button>
          ))}
        </div>

        <nav aria-label="Navigasi abjad" className="flex flex-wrap gap-1">
          <button
            type="button"
            onClick={() => setLetter(null)}
            className={cn(
              "rounded-md px-2 py-1 text-sm font-medium",
              !letter
                ? "bg-primary text-primary-foreground"
                : "text-muted-foreground hover:bg-surface-muted",
            )}
          >
            Semua
          </button>
          {ALPHABET.map((l) => (
            <button
              key={l}
              type="button"
              onClick={() => setLetter(l === letter ? null : l)}
              disabled={Boolean(q)}
              className={cn(
                "w-7 rounded-md py-1 text-sm font-medium disabled:opacity-40",
                letter === l
                  ? "bg-primary text-primary-foreground"
                  : "text-muted-foreground hover:bg-surface-muted",
              )}
            >
              {l}
            </button>
          ))}
        </nav>
      </div>

      {isError ? (
        <EmptyState
          title="Glosarium belum tersedia"
          description="Jalankan backend InsuraLab untuk menampilkan istilah."
        />
      ) : isLoading ? (
        <div className="flex flex-col gap-3">
          {Array.from({ length: 8 }).map((_, i) => (
            <Skeleton key={i} className="h-16 w-full" />
          ))}
        </div>
      ) : terms.length === 0 ? (
        <EmptyState
          title="Tidak ada istilah yang cocok"
          description="Coba kata kunci atau filter lain."
        />
      ) : (
        <ul
          className={cn(
            "border-border bg-surface divide-border divide-y rounded-[var(--radius)] border",
            isPlaceholderData && "opacity-60",
          )}
        >
          {terms.map((term) => (
            <li key={term.id}>
              <Link
                href={`/glossary/${term.slug}`}
                className="hover:bg-surface-muted/60 flex flex-col gap-1 px-5 py-4"
              >
                <span className="flex flex-wrap items-baseline gap-2">
                  <span className="font-semibold">{term.term}</span>
                  <Badge tone="neutral">{term.category}</Badge>
                  {term.is_provider_specific ? (
                    <Badge tone="primary">provider-spesifik</Badge>
                  ) : null}
                  {typeof term.articles_count === "number" && term.articles_count > 0 ? (
                    <span className="text-muted-foreground text-xs">
                      {term.articles_count} artikel terkait
                    </span>
                  ) : null}
                </span>
                <span className="text-muted-foreground text-sm">{term.short_definition}</span>
              </Link>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
