"use client";

import { keepPreviousData, useQuery } from "@tanstack/react-query";
import { getGlossary, getHealth } from "./endpoints";

/** Client-side glossary fetch, used for small interactive previews. */
export function useGlossaryPeek(limit = 4) {
  return useQuery({
    queryKey: ["glossary", "peek", limit],
    queryFn: () => getGlossary({ per_page: limit }),
  });
}

export type GlossaryQuery = { letter?: string; category?: string; q?: string; page?: number };

/** The interactive glossary browser query (alphabet + category + debounced search). */
export function useGlossaryBrowser(query: GlossaryQuery) {
  return useQuery({
    queryKey: ["glossary", "browse", query],
    queryFn: () => getGlossary({ per_page: 60, ...query }),
    placeholderData: keepPreviousData,
  });
}

export function useApiHealth() {
  return useQuery({ queryKey: ["health"], queryFn: ({ signal }) => getHealth(signal) });
}
