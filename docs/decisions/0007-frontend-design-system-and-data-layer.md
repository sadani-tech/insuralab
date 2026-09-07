# ADR 0007 — Frontend design system and data layer (Phase 4)

- Status: Accepted
- Date: 2026-09-01

## Context

Phase 4 builds the public app shell: visual identity, tokens, navigation,
reusable components, the route skeleton, and a typed client for the Phase 3 API.
ROADMAP §4 lists shadcn/ui, TanStack Query, RHF, Zod, and Motion as the libraries
to install; CLAUDE §17 requires dependency restraint. The detailed brief also
says not to install a large menu library and to use Zod "only where it adds
meaningful safety".

## Decision

### Design tokens & theming
- A single token set in `src/app/globals.css`: surfaces, text, one interactive
  accent (indigo — deliberately **not** Allianz corporate blue), muted semantic
  colours, radius scale. Exposed to Tailwind v4 via `@theme inline`.
- **Dark mode by OS preference only** (`prefers-color-scheme`), plus
  `:root[data-theme]` hooks for a future toggle. No toggle is shipped now —
  reading quality first, and the brief says a theme control only ships with a
  real theme system.
- `prefers-reduced-motion` disables animation globally in the base layer.

### Component approach
- shadcn/ui is followed as a **pattern**, not a dependency: `class-variance-authority`
  + `clsx` + `tailwind-merge` and hand-written primitives (`Button`, `Badge`,
  `Card`, `Callout`, `Container`/`Section`, `Skeleton`, `EmptyState`). This keeps
  Radix and its transitive weight out until a component genuinely needs it, and
  satisfies "don't install a large menu library" — the mobile menu is a
  hand-rolled accessible disclosure.

### Data layer
- `src/lib/api/`: a `fetch` wrapper that validates every response at the boundary
  with a **Zod** schema (`apiGet(path, schema)`), typed DTOs inferred from those
  schemas (not mirrored from Eloquent), and per-endpoint fetchers.
- **Server Components fetch directly.** Content pages are RSC; the API base URL
  and short `revalidate` windows live in the fetcher.
- **TanStack Query is wired** (`Providers` in the root layout) and used for the
  one interactive client path on the homepage (glossary peek). It is ready for
  the interactive list/search views in Phase 5+.
- Fetchers that feed a page are wrapped so a backend outage renders an
  `EmptyState`, never a build/render failure.

### Deferred on purpose
- **React Hook Form** is **not** installed. There are no forms until the
  assessment/quiz runners (Phase 7/8). Installing it now would be an unused
  dependency (CLAUDE §17). It will be added in the phase that has a form.
- **shadcn/ui CLI / Radix**, `@tailwindcss/typography` (article prose is Phase 5),
  Three.js and friends (Phase 9).

### Testing
- **Vitest + Testing Library** (jsdom). Component and boundary tests only:
  navigation render, mobile-menu toggle + Escape, static content pages, homepage
  render under API failure, `ReferenceStatusBadge` behaviour, and the API client
  (parse success, error mapping, schema mismatch, network failure).
- **No Playwright / E2E** here — ROADMAP already places E2E in Phase 13, and the
  brief says not to spend more time on E2E infrastructure than on the app.

## Consequences

- Bundle stays small; every dependency is used.
- A second reviewer can see the general/provider trust model reflected in the UI
  components, not just the API.
- When Phase 5 adds interactive lists, the Query client and fetchers are already
  in place; when Phase 7 adds a form, RHF is added then.
- Lighthouse a11y ≥ 95 (ROADMAP §4 DoD) was **not** machine-verified in this
  execution — the accessibility fundamentals are implemented and unit-tested, and
  the formal audit remains a Phase 12 gate.
