# ADR 0008 — Learn content model and the Markdown pipeline

- Status: Accepted
- Date: 2026-09-01

## Context

Phase 5 turns the seeded knowledge into a real reading experience: a Learn hub,
article detail pages with high-quality typography, a glossary with inline
tooltips, and a curated "Start Here" sequence. This needs (a) a Markdown
rendering stack, (b) a mechanism for in-article glossary references that is
*explicit*, not an auto-linker, and (c) a few small backend relations.

## Decision

### Backend additions (small, additive — no breaking change)

1. **`articles.key_takeaways`** — a JSON column holding the "Poin Penting" list.
   Takeaways are data, authored per article, never hardcoded in React.
2. **`glossary_term_article` pivot** — an **explicit** many-to-many link between a
   glossary term and the articles that teach it. Powers "related learning" on the
   glossary detail page and the curated `:istilah[…]` tooltips. InsuraLab never
   scans article prose for glossary matches.
3. **`GET /learning-paths` + `/learning-paths/{slug}`** — curated ordered journeys
   through published articles (the existing `learning_paths` tables). Returns
   ordered `{ position, note, article }`; unpublished articles are dropped and
   positions renumbered. No user progress is stored.
4. `articles/{slug}` gained `key_takeaways`, `glossary_terms`, and `related`
   (deterministic: same category → shared tags → same knowledge type, never
   random, never provider-crossing). `glossary/{slug}` gained `related_articles`;
   the glossary list gained `articles_count`.

### Content

- The Learn corpus is authored in **Bahasa Indonesia** as the primary language,
  with the standard English term in parentheses (CONTENT_GUIDE §11). ~26 general
  articles cover the Phase 5 topic list; the 3 Allianz articles stay
  provider-specific with `needs_review` / `historical` status.
- Article bodies are Markdown with a small **directive vocabulary** rendered as
  callouts: `:::definisi` `:::penting` `:::contoh` `:::kesalahpahaman`
  `:::catatan-provider` `:::referensi-historis` `:::perlu-verifikasi`, plus the
  inline `:istilah[teks]{slug=…}` glossary reference. All directives are authored
  by hand.

### Frontend Markdown stack

- `react-markdown` + `remark-gfm` (tables, lists, links) + `remark-directive`.
- Two tiny local remark plugins (`src/lib/markdown.ts`): one converts the known
  directive names into plain element names (`callout`, `glossaryterm`) that
  `react-markdown`'s `components` map renders as `<Callout>` / `<GlossaryTooltip>`;
  the other adds slug `id`s to `h2`/`h3` for the table of contents.
- The **table of contents** is extracted from the raw Markdown by regex (same
  `github-slugger` algorithm as the heading ids) and only shown when there are
  ≥ 3 headings. Sticky on desktop, a `<details>` "Daftar Isi" on mobile.
- **Glossary tooltips** open on hover, keyboard focus, or tap; the short
  definition comes from the article payload's `glossary_terms` via React context
  (no per-term fetch), with a "Pelajari lebih lanjut" link to `/glossary/{slug}`.
- Article reading column is constrained to `.reading-measure` (~68ch).

### Deferred / not done

- No user reading progress, no accounts (later phase).
- No `@tailwindcss/typography` — the directive + component approach covers the
  authored content; a generic prose plugin isn't needed.
- Global search is **not** built — Learn has only the API-backed category/level
  filters and the glossary's debounced term search.

## Consequences

- Adding an article = a Markdown string + `key_takeaways` + `glossary` slugs in
  the seeder; the reader picks up callouts and tooltips automatically.
- The general-vs-provider and current/historical/needs-review distinctions are
  visible in the reader (trust notices) and in the glossary, matching the API
  guarantees.
- `docs/API.md` and `docs/CONTENT_GUIDE.md` document the directive vocabulary.
