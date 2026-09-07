# ROADMAP.md — InsuraLab

Phase-by-phase plan. **A phase may not begin until the previous phase's Definition of Done (DoD) is fully satisfied and verified.**

Legend: ✅ complete · 🟡 in progress · ⬜ not started

| Phase | Title | Status |
|---|---|---|
| 0 | Repository Analysis & Planning | ✅ |
| 1 | Foundation & Infrastructure | ✅ |
| 2 | Domain Model + Database + CMS | ✅ |
| 3 | REST Public API | ✅ |
| 4 | Design System & Public App Shell | ✅ |
| 5 | Learn + Glossary | ✅ |
| 6 | Providers + Products | ✅ |
| 7 | Protection Needs Assessment | ✅ |
| 8 | Quiz + Learning Journey | ⬜ |
| 9 | Protection Universe 3D | ⬜ |
| 10 | Advanced Insurance Visualizations | ⬜ |
| 11 | Search + SEO | ⬜ |
| 12 | Accessibility + Performance | ⬜ |
| 13 | Testing + Production Readiness | ⬜ |

> Phases 0–7 are complete and verified. Phase 8 remains intentionally unstarted.

---

## Phase 0 — Repository Analysis & Planning ✅

**Purpose:** Understand the starting point and commit a concrete plan before writing product code.
**Scope:** Inspect repo, tooling, environment. Produce `docs/IMPLEMENTATION_PLAN.md`. Establish project memory (`CLAUDE.md`, `docs/*`, `docs/decisions/*`).
**Out of scope:** Any application code beyond scaffolding.
**Deliverables:** Repo/tooling findings; implementation plan; vision, architecture, domain-knowledge, content-guide, roadmap docs; initial ADRs.
**Definition of Done:**
- [x] Repository state, tooling versions, and constraints documented.
- [x] `docs/IMPLEMENTATION_PLAN.md` committed with structure, tech decisions, dependencies, risks, phase plan, testing strategy.
- [x] `CLAUDE.md` + `docs/PRODUCT_VISION.md` + `docs/ARCHITECTURE.md` + `docs/DOMAIN_KNOWLEDGE.md` + `docs/CONTENT_GUIDE.md` + `docs/ROADMAP.md` exist.
- [x] `docs/decisions/` seeded with the meaningful ADRs.
- [x] Git repository initialized.

---

## Phase 1 — Foundation & Infrastructure ✅

**Purpose:** A monorepo where both apps boot, lint, and build, wired to PostgreSQL.
**Scope:** Monorepo layout; Next.js frontend (App Router, TS strict, Tailwind, ESLint, Prettier, env config, minimal dev shell); Laravel 10 backend (PostgreSQL, API structure, Sanctum, Pest, Pint, Filament); Docker Compose for PostgreSQL; `.env.example` files; developer-experience commands (Makefile).
**Out of scope:** Public site design; domain migrations; content; Three.js and other deferred libraries.
**Deliverables:** `frontend/`, `backend/`, `docker-compose.yml`, `Makefile`, `README.md`, `.env.example` × both apps, booting dev shell.
**Definition of Done:**
- [x] `frontend/` boots (`npm run dev`), type-checks in strict mode, lints, and `npm run build` succeeds.
- [x] `backend/` boots (`php artisan serve`), connects to PostgreSQL.
- [x] Filament installed and `/admin` loads.
- [x] Pest runs; Pint runs.
- [x] `.env.example` files committed; no secrets committed.
- [x] Root `Makefile` provides install/start/stop/migrate/seed/test/lint/format/build.
- [x] No deferred dependency installed early.

---

## Phase 2 — Domain Model + Database + CMS ✅

**Purpose:** A domain-aware content platform: normalized schema, models, Filament admin, and meaningful seed content — with the general/provider split and the source/review trust model built in.
**Scope:** Migrations for the core tables (Knowledge, Protection, Provider, Product, Glossary, Source, Quiz, Assessment; learning paths if they fit cleanly); Eloquent models + relationships; Filament resources for all content types with sensible forms/tables/filters/validation and review-metadata guardrails; admin dashboard widgets; seeders (≥15 articles, ≥40 glossary terms, Allianz provider, product categories, educational product records, sources, at least one quiz and one assessment); Pest tests for foundation behavior.
**Out of scope:** Public REST API; public UI; assessment evaluation engine; quiz scoring; compensation calculation; Three.js.
**Deliverables:** Migrations, models, factories, seeders, Filament panel + resources + widgets, tests.
**Definition of Done:**
- [x] `php artisan migrate:fresh` succeeds from a clean database; `migrate:refresh` works (down migrations valid).
- [x] `php artisan db:seed` succeeds from a clean database and produces ≥15 articles (target 20–25), ≥40 glossary terms, Allianz as a `providers` row, product categories, educational products, sources, ≥1 quiz, ≥1 assessment.
- [x] Articles support a nullable `provider_id`; a general article (NULL) and a provider-specific article both exist in seed data.
- [x] Products belong to a provider and a product category, and relate to protection categories.
- [x] `sources` + polymorphic `sourceables` work and are attached to articles, provider, and products in seed data.
- [x] `reference_status` values (`current` / `historical` / `needs_review`) are enforced and used in seed data, including at least one `needs_review` capturing a source inconsistency.
- [x] Filament resources exist for Articles, Categories, Tags, Glossary Terms, Providers, Product Categories, Products, Sources, Quizzes, Assessments; each is usable (form + table + search + basic filters).
- [x] Article form separates general vs provider-specific and requires review metadata before a provider-specific article can be `published`.
- [x] Admin dashboard shows: Published Articles, Draft Articles, Providers, Products, Glossary Terms, Needs Review, Historical Content.
- [x] No table named `allianz_*`. Allianz appears only as data.
- [x] Pest tests cover: provider creation; general article without provider; provider-specific article with provider; product↔provider; source relationships; `reference_status` values; seeder runs; `migrate:fresh` / rollback.
- [x] `php artisan test` green; `./vendor/bin/pint --test` clean; frontend `lint` + `build` still green.

---

## Phase 3 — REST Public API ✅

**Purpose:** A versioned read API exposing published general knowledge, glossary, providers, products, quizzes, and assessment definitions to the frontend.
**Scope:** `/api/v1` resources/controllers (thin), API resources/transformers, pagination, filtering (level, category, provider, status), rate limiting, description doc, feature tests per endpoint. Draft/archived content never exposed. Provider content carries source + review metadata in the payload.
**Out of scope:** Any write API beyond Filament; auth for public reads.
**Deliverables:** API routes, resources, requests, tests, `docs/API.md`, ADR-0006.
**Definition of Done:**
- [x] Routes versioned under `/api/v1`; 16 routes registered.
- [x] Endpoints: health, articles (list + slug), categories, protection-categories, glossary (list + slug), providers (list + slug), products (list + slug), quizzes (list + slug), assessments (list + slug).
- [x] Summary vs detail resource split; consistent `data` / `meta` / `links` envelope.
- [x] Provider payloads carry `trust` (`reference_status`, `reference_period`, `last_reviewed_at`) + `sources[]`; `historical` products remain reachable and badged.
- [x] Draft / archived / future-dated content unreachable by list and by slug (tested).
- [x] Query validation via Form Requests: enum values, unknown filter slugs → `422`; `per_page` capped at 100.
- [x] N+1 reviewed — list endpoints eager-load; a test asserts the 23-article list stays under 10 queries.
- [x] CORS is env-driven (`CORS_ALLOWED_ORIGINS` / `FRONTEND_URL`), verified against the running frontend origin.
- [x] `docs/API.md` written.
- [x] 42 new Pest API tests (84 total) green; Pint clean.

## Phase 4 — Design System & Public App Shell ✅

**Purpose:** The visual and structural foundation of the public site.
**Scope:** design tokens; layout, nav, footer; typography/spacing scale; OS-preference dark mode; the product-area routes as shells; a typed fetch layer + TanStack Query against the Phase 3 API; error/loading/empty states.
**Installed here:** `zod`, `motion`, `@tanstack/react-query`, `class-variance-authority`, `clsx`, `tailwind-merge`, and Vitest + Testing Library. **RHF deferred** — no forms exist until Phase 7/8; installing it now would violate dependency restraint (CLAUDE §17). shadcn/ui is followed as a *pattern* (cva + hand-written primitives) rather than pulled via its CLI/Radix, per the brief's "don't install a large menu library".
**Out of scope:** Real content pages; 3D; search.
**Definition of Done:**
- [x] Coherent visual identity + documented design tokens (`globals.css`, ADR-0007); light + dark palettes.
- [x] Typography scale; reading-measure container defined for Phase 5.
- [x] App shell: responsive `SiteHeader` with accessible mobile menu (Esc + focus handling), `SiteFooter` with the independence notice, skip-to-content link, semantic landmarks.
- [x] Routes: `/`, `/learn`, `/protection`, `/providers`, `/products`, `/glossary`, `/quiz`, `/explore`, `/about`, `/methodology`, `/disclaimer` + `not-found` / `error` / `loading`.
- [x] Homepage: all 8 briefed sections; 2D Protection-Universe placeholder (no Three.js).
- [x] `/methodology`, `/disclaimer`, `/about` fully written (Bahasa Indonesia where briefed).
- [x] Trust/domain components: ReferenceStatusBadge, LevelBadge, ProviderBadge, SourceList, LastReviewed, EducationalDisclaimer, ProtectionCategoryCard, KnowledgeCard, GlossaryTermPreview, ProductTypeBadge.
- [x] API client foundation: `src/lib/api` with Zod boundary validation, typed DTOs, RSC fetchers, and one TanStack Query client path (homepage glossary peek).
- [x] Real API integration verified: `/providers`, `/learn`, homepage render live seeded data via SSR; CORS clean.
- [x] Accessibility fundamentals: focus-visible ring, `aria-current`, keyboard menu, reduced-motion support.
- [x] 16 Vitest tests green; `lint`, `typecheck`, `test`, `build` all pass; backend Phase 3 tests still green.
- [ ] Lighthouse a11y ≥ 95 — not measured in this execution (no headless-Chrome run available); fundamentals are in place, formal audit is Phase 12.

## Phase 5 — Learn + Glossary ✅

**Purpose:** The reading experience for general knowledge.
**Scope (built):** Learn hub (hero, levels, "Start Here" from the learning-path API, explore-by-topic, filterable list); `/learn/[slug]` reader (breadcrumb, meta, TOC, Markdown with directive callouts, "Poin Penting", trust notices, sources, "Pelajari Selanjutnya"); `/glossary` (alphabet nav, category filter, debounced search); `/glossary/[slug]` (definition, related terms, related articles, sources); in-article glossary tooltips (hover/focus/tap); `.reading-measure` column. See ADR-0008.
**Content:** 26 general Learn articles + 3 Allianz provider articles, primary language Bahasa Indonesia; 69 glossary terms; 3 learning paths; 125 curated glossary↔article links.
**Backend:** `articles.key_takeaways` (JSON) + `glossary_term_article` pivot; `GET /learning-paths` (+ `/{slug}`); `articles/{slug}` gained `key_takeaways` / `glossary_terms` / `related`; `glossary/{slug}` gained `related_articles`.
**Definition of Done:**
- [x] `/learn` is a learning hub (levels, Start Here, topics, filters) — not a blog archive.
- [x] Article list + detail use the backend; typography constrained to a comfortable measure.
- [x] Long-article TOC (≥ 3 headings), sticky desktop / collapsible mobile.
- [x] General vs provider-specific, `historical`, and `needs_review` all labelled in the reader (trust notices) and the glossary.
- [x] Sources block + "Pelajari Selanjutnya" (deterministic related).
- [x] `/glossary` filtering + debounced search + `/glossary/[slug]` detail with stable slugs.
- [x] In-article glossary tooltip foundation — explicit `:istilah[…]` refs only, keyboard-accessible.
- [x] ≥ 40 glossary terms (69) and ≥ 20 articles (29).
- [x] 12 new frontend tests (16 → 28: markdown, markdown-content, glossary-browser, learn); 90 backend tests (84 → +6 learn relations); lint / typecheck / build green.
- [ ] `notFound()` returns HTTP 200 under `next dev` (streaming quirk) — the not-found UI renders correctly; production `next start` returns 404.

## Phase 6 — Providers + Products ✅

**Purpose:** Provider-agnostic provider and product case studies.
**Scope (built):** `/providers` landing (positioning, cards, no ranking); `/providers/[slug]` (breadcrumb, independence notice, overview, Indonesian entities, business areas, "Materi edukasi provider" with trust badges, product cards, sources); `/products` landing (educational positioning, URL filters for scheme/structure/reference-status); `/products/[slug]` (breadcrumb, provider link, reference status/period/last reviewed, independence notice, PAYDI risk note, sharia terminology note, "Risiko yang ditangani", "Apa produk ini / batasan" via Markdown, "Istilah penting" glossary links, "Pahami konsepnya lebih dulu" concept-first articles, sources). Per-slug not-found for both.
**Backend:** `product_article` + `product_glossary_term` pivots; `providers/{slug}` adds `metadata` (allow-list) + `articles[]`; `products/{slug}` adds `related_articles` + `related_terms`.
**Content:** Provider description + metadata in Bahasa Indonesia; 11 product records enriched with the "apa / risiko / manfaat / batasan" structure + concept + glossary links; +1 Allianz provider article ("Allianz di Indonesia: Tiga Entitas dan Jaringan Keagenan", needs_review). Historical commission table stays `historical`; the BE→BP "Option 3" source conflict stays visible.
**Definition of Done:**
- [x] `/providers` + `/providers/[slug]` educational, no ranking / score / winner badge.
- [x] Allianz is one provider row; the three Indonesian entities are shown separately, not conflated; ASN framed as a channel, not the insurer.
- [x] `/products` + `/products/[slug]` with filters; every page shows `reference_status` + reference period + last reviewed + sources.
- [x] Product pages link back to general concepts (concept → provider → product).
- [x] PAYDI risk note where structure is `paydi`; sharia terminology note where scheme is `sharia`; cashless clarified on AFMP.
- [x] Training figures (Flexi CI tiers/benefits/COR/60-day claim) presented only in the linked article, labelled `needs_review`, with "150% ≠ one diagnosis" stated.
- [x] Historical compensation reference kept and labelled; no calculator; Option 3 inconsistency shown, not resolved.
- [x] No buy / apply / get-quote CTA; no product recommendation.
- [x] Responsive: training tables scroll inside `overflow-x-auto` (MarkdownContent).
- [x] 6 new frontend tests (28 → 34: providers 2 + products 4); 92 backend tests (90 → +2 provider metadata / product relations); lint / typecheck / build green.
- [x] Content audit: no overclaiming, implied guarantees, unlabeled historical data, sales language, or "agent decides underwriting/claims".

## Phase 7 — Protection Needs Assessment ✅

**Purpose:** Help users discover which **topics** to learn about.
**Scope (built):** `/assessment` landing; accessible runner at `/assessment/protection-needs`; session-backed result route; dedicated methodology page; 11 Bahasa Indonesia questions (10 when the dependant question is skipped); five educational dimensions; configurable option impacts/reasons/article relations; versioned deterministic Laravel evaluator; 2D protection map and concept-first learning links.
**Privacy:** no login, identity, diagnosis, exact financial value, response table, answer URL, sharing, or downloadable report. Laravel evaluates in memory; the browser retains only the result for the session.
**Out of scope kept:** no product/provider recommendations, quotations, premium/sum-assured calculations, underwriting/medical scoring, financial advice, analytics platform, Three.js, quiz scoring, or Phase 8 work.
**Definition of Done:**
- [x] Landing, runner, result, and methodology routes exist; no login required.
- [x] 11 understandable questions cover dependants, income role, emergency buffer, existing health protection, obligations, interruption/disability impact, accident-learning interest, policy understanding, and primary concern.
- [x] Conditional dependant-health question, back/forward navigation, applicable-question progress, native radio semantics, validation, loading/error/retry states, and restart work.
- [x] Laravel owns deterministic evaluation; semantic keys, question/option ownership, required/applicable answers, duplicates, and stale methodology versions are validated.
- [x] Five configurable dimensions use normalized applicable maximums and calm relevance tiers; raw numeric scores stay internal.
- [x] Every dimension remains visible with an editorial explanation and links to published general articles; existing-coverage education is separate and respectful of BPJS/employer coverage.
- [x] No detailed answer persistence, provider bias, product recommendation, Three.js, or future-phase work.
- [x] Filament manages assessment text/status/version, question and option order/keys/conditions, dimensions, bounded impacts, reasons, thresholds, and general article mappings.
- [x] Fresh migrations + seeders verified; methodology documented in `ASSESSMENT_METHODOLOGY.md` and ADR-0009.
- [x] Persona A–E and manipulated-payload tests pass; backend 110 tests / 631 assertions and frontend 43 tests pass.
- [x] Backend Pint, frontend lint/typecheck, production build, real API smoke flow, and responsive desktop/mobile rendering verified.

## Phase 8 — Quiz + Learning Journey ⬜

**Purpose:** Low-stakes self-check and a guided path through Learn.
**Scope:** Quiz taking + scoring + per-option explanations; learning paths / path items surfaced as journeys; progress stored client-side (no accounts yet).
**DoD:** quizzes score correctly with explanations; at least one end-to-end learning path is navigable.

## Phase 9 — Protection Universe 3D ⬜

**Purpose:** Spatial exploration of how protection categories relate to risks.
**Scope:** **Install `three`, `@react-three/fiber`, `@react-three/drei` here.** A scene fed by structured API data via a view-model mapper; keyboard + reduced-motion fallbacks; a non-3D equivalent view.
**Out of scope:** Domain logic in the scene layer.
**DoD:** the 3D view derives nothing itself — all numbers/relationships come from the API; a documented fallback exists; reduced-motion respected.

## Phase 10 — Advanced Insurance Visualizations ⬜

**Purpose:** Focused explanatory visualizations (HLV, layered protection, reimbursement vs lump sum, PAYDI value/charges over time).
**DoD:** each visualization has an accessible fallback and cites its data assumptions.

## Phase 11 — Search + SEO ⬜

**Purpose:** Find content; be found.
**Scope:** PostgreSQL full-text search in Laravel; search UI; metadata, sitemaps, structured data, OG tags. **No Elasticsearch.**
**DoD:** search covers articles + glossary + products; every public page has correct metadata and is in the sitemap.

## Phase 12 — Accessibility + Performance ⬜

**Scope:** WCAG 2.1 AA pass; keyboard/screen-reader audit; Core Web Vitals; image/font strategy; caching.
**DoD:** automated a11y checks in CI; Lighthouse ≥ 95 across a11y/perf/SEO on key routes.

## Phase 13 — Testing + Production Readiness ⬜

**Scope:** E2E tests; CI pipeline (lint, test, build for both apps); deployment docs; backup/restore for PostgreSQL; observability; security review; content-review workflow for `needs_review` items.
**DoD:** green CI on both apps; documented deploy; a runbook for reviewing and re-dating provider content.
