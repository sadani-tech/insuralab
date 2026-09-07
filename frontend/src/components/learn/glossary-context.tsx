"use client";

import { createContext, useContext, type ReactNode } from "react";

/** slug -> short definition, from an article's curated `glossary_terms`. */
export type GlossaryMap = Record<string, { term: string; short_definition: string }>;

const GlossaryContext = createContext<GlossaryMap>({});

export function GlossaryProvider({ terms, children }: { terms: GlossaryMap; children: ReactNode }) {
  return <GlossaryContext.Provider value={terms}>{children}</GlossaryContext.Provider>;
}

export function useGlossaryEntry(slug: string) {
  return useContext(GlossaryContext)[slug];
}
