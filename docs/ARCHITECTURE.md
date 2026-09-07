# ARCHITECTURE.md — InsuraLab

Status: Phases 0–8 built. This document describes the system as designed and as currently built, and names the parts that are deliberately deferred.

---

## 1. High-level shape

```
┌─────────────────────────┐        HTTPS / JSON (REST)        ┌──────────────────────────────┐
│  frontend/  (Next.js 16) │  ───────────────────────────────▶ │  backend/  (Laravel 10)      │
│  App Router, TS strict   │                                   │                              │
│  Public web experience   │  ◀─────────────────────────────── │  • Domain / business logic   │
│                          │        JSON responses             │  • REST API (Phase 3+)       │
└─────────────────────────┘                                    │  • Filament CMS (/admin)     │
                                                               │  • Sanctum auth (admin)      │
                                                               └───────────────┬──────────────┘
                                                                               │ PDO (pgsql)
                                                                               ▼
                                                               ┌──────────────────────────────┐
                                                               │  PostgreSQL 18 (Docker)      │
                                                               │  DB: insuralab @ :5433       │
                                                               └──────────────────────────────┘
```

- **Two deployables, one repository** (monorepo). See ADR-0001.
- The frontend never talks to PostgreSQL. All domain data crosses the **REST boundary**.
- Filament is an admin UI mounted inside the Laravel app at `/admin`; it is not a separate service.

---

## 2. Responsibilities

### frontend/ — Next.js
- Owns the **public web experience**: routing, layout, design system, accessibility, SEO, interactive visualizations.
- Consumes the Laravel REST API. Holds **no** authoritative domain logic — at most presentation-level derivation.
- Built through Phase 8: design tokens and app shell; Learn/article/glossary; provider/product case studies; the Protection Needs Assessment; server-scored quiz pages; ordered learning paths; and anonymous local learning progress. `/protection` and `/explore` remain later-phase surfaces.

### backend/ — Laravel
- Owns **all domain and business logic**: entities, relationships, validation, invariants, content lifecycle, scoring rules (later).
- Exposes a **REST API** for the frontend (Phase 3+). Controllers stay thin; logic lives in models, actions, and services.
- Hosts the **Filament CMS** for content administration.
- `php artisan` is the single entry point for migrations, seeders, and tests.

### PostgreSQL
- The **single source of truth** for domain data.
- Runs in Docker. Development reuses an existing container, connecting to `127.0.0.1:5433`, database `insuralab`.
- `docker-compose.yml` also defines a self-contained `insuralab-postgres` service (profile `full`) so the repo is portable for other developers/recruiters.

---

## 3. The REST boundary

- Transport: JSON over HTTP, versioned under `/api/v1/...` (built in Phase 3 — see **ADR-0006** and `docs/API.md`).
- Read endpoints for public content are unauthenticated and throttled (60/min per IP). No response caching layer yet.
- Write access is **admin-only**, through Filament, authenticated with **Laravel Sanctum** (session guard for the admin panel).
- The frontend receives **structured domain data** — never HTML fragments of business meaning, never raw DB rows. Every response goes through an `App\Http\Resources\Api\V1` class (summary shape for lists, detail shape for single resources).
- Publication is enforced at the boundary: `Article::public()` (published + `published_at <= now`), `Product::published()`, `Provider::public()` (active). Unpublished content is unreachable by list **and** by slug.
- Provider-specific payloads always carry a `trust` object (`reference_status`, `reference_period`, `last_reviewed_at`) and `sources[]`; `products.metadata` is exposed only through an allow-list.
- CORS is configured to allow the frontend origin(s) only (`config/cors.php`, driven by `CORS_ALLOWED_ORIGINS` / `FRONTEND_URL`).

### 3.1 Frontend consumption (Phase 4 — ADR-0007)

- `frontend/src/lib/api/` is the typed client: a `fetch` wrapper that validates each response against a **Zod** schema at the boundary, DTO types inferred from those schemas, and per-endpoint fetchers.
- Content pages are **React Server Components** that call the fetchers directly. **TanStack Query** powers the interactive client views (homepage glossary peek, the glossary browser's debounced search).
- Article Markdown is rendered with `react-markdown` + `remark-gfm` + `remark-directive`; two local remark plugins map InsuraLab's `:::callout` directives and `:istilah[…]` glossary refs to components (ADR-0008). Directives are authored by hand — prose is never auto-parsed.
- The frontend holds **no** authoritative domain logic — presentation-level derivation only.

---

## 4. Domain model overview

Domains (bounded areas within one Laravel app — **not** microservices):

| Domain                | Core tables                                                            |
|-----------------------|----------------------------------------------------------------------|
| Knowledge             | `categories`, `articles`, `tags`, `article_tag`                       |
| Protection            | `protection_categories`, `product_protection_category` (pivot)        |
| Provider              | `providers`                                                          |
| Product               | `product_categories`, `products`                                     |
| Glossary              | `glossary_terms`, `glossary_term_related` (self-referential)          |
| Source (trust)        | `sources`, `sourceables` (polymorphic pivot)                         |
| Quiz                  | `quizzes`, `quiz_questions`, `quiz_options`, `quiz_question_article` |
| Assessment            | `assessments`, `assessment_questions`, `assessment_options`          |
| Learning              | `learning_paths`, `learning_path_items`                              |
| Distribution (future) | *knowledge/content only for now — no tables beyond provider notes*    |

Full column-level detail lives in the migrations and in `docs/DOMAIN_KNOWLEDGE.md`. Key rules:

### 4.1 General vs provider-specific knowledge
- `articles.provider_id` is **nullable**.
  - `NULL` → general insurance knowledge ("What is critical illness insurance?").
  - non-null → provider-specific educational content ("How Allianz Flexi CI works per a training reference").
- The same nullable-provider pattern is available to other knowledge-bearing entities as they are added.

### 4.2 Sources & review metadata (the trust model)
- `sources` are **reusable records**: `title`, `publisher`, `url?`, `published_at?`, `accessed_at?`, `reference_period?`, `notes?`.
- `sourceables` is a **polymorphic** pivot linking a source to any number of entities (articles, providers, products, glossary terms, and more later).
- Provider- and product-bearing rows carry review metadata:
  - `last_reviewed_at` (date)
  - `reference_period` (free text, e.g. "Allianz Basic Training 2023 deck")
  - `reference_status` enum: `current` | `historical` | `needs_review`
- Articles additionally carry `last_reviewed_at` and a `status` lifecycle (`draft` | `published` | `archived`).
- **Historical content stays usable** for teaching insurance history, but must never be rendered as current product fact. The frontend is expected to badge `historical` / `needs_review` content distinctly (Phase 6).

### 4.3 Products ↔ protection categories
- A product belongs to exactly one `product_category` (e.g. "Health", "Critical Illness Rider", "Term Life").
- A product relates to one or more **protection concepts** (`life`, `health`, `critical illness`, `disability`, `accident`) via `product_protection_category`.
- Provider-specific optional attributes that do not deserve columns use a **controlled JSONB `metadata`** field. JSONB is for optional/extensible metadata only — anything searchable or filterable gets a real column. No EAV.

### 4.4 Assessment (Phase 7)
- `assessments` → `assessment_questions` → `assessment_options`; stable semantic keys form the public submission contract.
- `assessment_dimensions` defines five educational dimensions. `assessment_option_impacts` lets one option affect several dimensions with a bounded weight and editorial reason.
- Ordered article pivots point only to published general learning content.
- `EvaluateProtectionNeedsAssessment` validates ownership/applicability, normalizes against each applicable maximum, assigns a calm tier, and returns explanations plus learning links.
- Evaluation is stateless: there are no response/answer tables. The frontend keeps only the result in `sessionStorage`; answers never enter the URL.
- `methodology_version` prevents a stale definition from being evaluated silently. See ADR-0009 and `ASSESSMENT_METHODOLOGY.md`.

### 4.5 Quiz and learning journeys (Phase 8)
- `quizzes` → `quiz_questions` (single-choice now, extensible later) → `quiz_options`.
- Stable question/option keys form the submission contract. Every option records `is_correct` and an educational `explanation`, but both remain server-side until submission.
- `SubmitQuiz` validates ownership, duplicates, and required answers, computes the score, and derives relational review lessons. The client cannot submit or influence score fields.
- `quiz_question_article` maps a misconception to ordered published general lessons.
- `learning_paths` contain ordered `learning_path_items`; each item points to exactly one article or quiz, enforced by a database check constraint.
- Public learning progress has no backend table. The frontend stores only educational slugs, a latest quiz result, and last visited path under `insuralab.learning.v1`. See ADR-0010 and `LEARNING_SYSTEM.md`.

---

## 5. Content administration (Filament)

- Filament panel at `/admin`, guarded by Sanctum/session auth (`users` table, `is_admin` flag).
- Resources: Articles, Categories, Tags, Glossary Terms, Providers, Product Categories, Products, Sources, Quizzes, Learning Paths, Assessments.
- Admin UX guardrails:
  - Article form separates **General** vs **Provider-specific**; choosing a provider reveals and *requires* review metadata (reference period, last reviewed, reference status) before `published`.
  - Provider/Product forms surface `last_reviewed_at`, `reference_period`, `reference_status` prominently.
  - Quiz forms manage semantic keys, explanations, correctness, ordered related general articles, and duration. Learning Path forms use searchable article/quiz relationships instead of raw IDs.
- Dashboard widgets: Published Articles, Draft Articles, Providers, Products, Glossary Terms, **Needs Review**, **Historical Content**.

---

## 6. Future architecture (documented, NOT built in Phase 0–2)

### 6.1 Protection Needs Assessment (built in Phase 7)
- Public routes: `/assessment`, `/assessment/protection-needs`, a dedicated methodology page, and a session-backed result page.
- API routes: definition and stateless evaluation under `/api/v1/assessments/protection-needs`.
- Output is protection learning topics only. Provider/product recommendations remain forbidden.
- In Phase 8, each dimension can additionally point to a published general learning path. The evaluator still returns no provider or product recommendation.

### 6.2 Three.js "Protection Universe" (Phases 9–10)
- Lives entirely in `frontend/`. Packages (`three`, `@react-three/fiber`, `@react-three/drei`) are installed **only** at that phase.
- **Three.js does not own insurance domain logic.** It is a rendering layer.
- Data flow: Laravel REST → typed DTOs in Next.js → a view-model mapper → scene graph. The 3D layer receives already-structured, already-validated domain data (protection categories, relationships, weights) and is responsible only for spatial layout, interaction, and animation.
- If the visualization needs a number or a relationship, that number/relationship is computed in Laravel and delivered over the API — not derived in the shader/scene code.

### 6.3 Search & SEO (Phase 11)
- Start with PostgreSQL full-text search (`tsvector`) inside Laravel. No Elasticsearch. Revisit only with evidence.

---

## 7. Explicitly excluded (see CLAUDE.md §17, ADR-0003)

MySQL, MongoDB, Elasticsearch, Kafka, RabbitMQ, Kubernetes, and Redis (not needed at this phase). No microservices. No GraphQL. No premature message bus.

---

## 8. Environments & configuration

- `backend/.env` — app URL, `DB_CONNECTION=pgsql`, host `127.0.0.1`, port `5433`, database `insuralab`; `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`.
- `frontend/.env.local` — `NEXT_PUBLIC_API_BASE_URL` (default `http://localhost:8000/api`), `NEXT_PUBLIC_SITE_URL`.
- `.env.example` files are committed; real `.env` files are git-ignored. No secrets in the repo.
- Developer commands are in the root `Makefile` (`make install`, `make start`, `make migrate`, `make seed`, `make test`, `make lint`, `make format`, `make build`).
