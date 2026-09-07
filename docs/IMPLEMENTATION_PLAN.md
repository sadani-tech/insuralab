# IMPLEMENTATION_PLAN.md — InsuraLab (Phases 0–2)

## 1. Current repository state (as found)

| Item | Finding |
|---|---|
| Git | **Not a repository.** Initialized during Phase 0 (`main` branch). |
| Existing files | `CLAUDE.md` (stub), `docs/ROADMAP.md` (phase names only), `docs/DOMAIN_KNOWLEDGE.md` (ASCII diagram only), `docs/Allianz_Basic_Training_Lengkap.pdf` (26-page source material), `.gitignore` (was ignoring `CLAUDE.md` — fixed). |
| Frontend | None. |
| Backend | None. |
| Database code | None. |
| CI / Docker config | None in repo. Host has Docker 29 + Compose v5; a `postgres:latest` (PG 18) container runs on host port 5433, plus unrelated redis/mysql containers. |
| Package managers | Composer 2.10.2; npm 11.16, pnpm 11.17, yarn 1.22. |
| PHP | **8.1.34** (host). No 8.2/8.3 available. |
| Node | 24.18.0. |
| psql | Not on PATH; use the Docker container. |

**Nothing useful was overwritten.** The stub docs were expanded; the PDF and `.gitignore` intent were preserved.

## 2. Key constraint and its resolution

Host PHP is 8.1; the brief wants PHP 8.3 + latest Laravel/Filament. Per the decision taken, the backend is **pinned to Laravel 10.x + Filament 3.x** running natively on PHP 8.1, and the database **reuses the existing PostgreSQL 18 container** (`127.0.0.1:5433`, DB `insuralab`). See **ADR 0005**. Follow-up: upgrade to PHP 8.3 + Laravel 11/12 before Phase 3 / any deployment.

## 3. Proposed implementation structure

```
/
├── frontend/          Next.js 16, App Router, TS strict, Tailwind v4, ESLint, Prettier
├── backend/           Laravel 10.50, Filament 3, Sanctum, Pest, Pint, PDO pgsql
│   ├── app/Models/            Eloquent models (one per core entity)
│   ├── app/Filament/          Resources + Widgets
│   ├── database/migrations/   ~22 normalized migrations
│   ├── database/factories/    Factories for tested entities
│   └── database/seeders/      Domain seeders (content, glossary, provider, products, quiz, assessment)
├── docs/              Project memory (+ decisions/)
├── docker-compose.yml PostgreSQL (reuse note + optional self-contained service, profile `full`)
├── Makefile           install / start / stop / migrate / seed / test / lint / format / build
├── README.md
└── CLAUDE.md
```

## 4. Technology decisions

| Concern | Decision | Rationale / ADR |
|---|---|---|
| Repo shape | Monorepo, directory split, Makefile (no Nx/Turbo) | ADR 0001 |
| Provider modelling | `providers` as data; nullable `provider_id` on knowledge; no `allianz_*` | ADR 0002 |
| Domain layer + CMS | Laravel + Filament 3 + Sanctum + Pest + Pint | ADR 0003 |
| Trust model | Reusable `sources` + polymorphic `sourceables` + `reference_status` + `last_reviewed_at` | ADR 0004 |
| Runtime pin | Laravel 10 / Filament 3 on PHP 8.1; PG 18 on :5433 | ADR 0005 |
| DB | PostgreSQL only. JSONB for optional metadata; real columns for anything filterable. No EAV. | ARCHITECTURE §4 |
| Frontend libs | Next + TS + Tailwind now. shadcn/ui, RHF, Zod, TanStack Query, Motion → Phase 4. Three.js → Phase 9. | Dependency restraint (CLAUDE §17) |

## 5. Dependencies

**Frontend (installed):** `next@16`, `react@19`, `react-dom@19`, `typescript`, `tailwindcss@4`, `@tailwindcss/postcss`, `eslint@9`, `eslint-config-next`, `prettier`, `prettier-plugin-tailwindcss`.
**Frontend (deferred):** shadcn/ui, `react-hook-form`, `zod`, `@tanstack/react-query`, `motion`, `three`, `@react-three/fiber`, `@react-three/drei`.

**Backend (installed):** `laravel/framework@10`, `laravel/sanctum`, `laravel/tinker`, `filament/filament@3`, `pestphp/pest` + `pestphp/pest-plugin-laravel`, `laravel/pint`.
**Backend (not added):** Redis, Horizon, Scout/Elasticsearch, Octane, any queue transport, any of the excluded infra (MySQL, Mongo, Kafka, RabbitMQ, k8s).

## 6. Data model (Phase 2 target)

Tables: `users` (+`is_admin`); `categories`; `articles` (nullable `category_id`, nullable `provider_id`, `title`, `slug`, `excerpt`, `body`, `level` enum, `status` enum, `is_featured`, `estimated_reading_minutes`, `published_at`, `last_reviewed_at`, timestamps); `tags` + `article_tag`; `glossary_terms` (+ self-referential `glossary_term_related`); `providers` (`name`, `slug`, `description`, `country_of_origin?`, `presence_in_indonesia?`, `website_url?`, `status`, `last_reviewed_at`, timestamps); `product_categories`; `products` (`provider_id`, `product_category_id`, `name`, `slug`, `short_description`, `description`, `insurance_scheme` enum, `product_structure` enum, `reference_status` enum, `reference_period`, `last_reviewed_at`, `status`, `metadata` jsonb, timestamps); `protection_categories` + `product_protection_category`; `sources` + `sourceables` (polymorphic); `quizzes` → `quiz_questions` → `quiz_options`; `assessments` → `assessment_questions` → `assessment_options`; `learning_paths` + `learning_path_items`.

Enums: `article.level` = beginner|intermediate|advanced · `article.status` = draft|published|archived · `provider.status` = active|inactive · `product.status` = draft|published|archived · `product.insurance_scheme` = conventional|sharia · `product.product_structure` = traditional|paydi|other · `*.reference_status` = current|historical|needs_review.

## 7. Filament plan

Resources: Article, Category, Tag, GlossaryTerm, Provider, ProductCategory, Product, Source, Quiz (with question/option relation managers), Assessment (with question/option relation managers).
Guardrails: Article form has a "Knowledge type" toggle (General / Provider-specific); choosing Provider-specific reveals + requires `provider_id`, `last_reviewed_at`, and a source; validation prevents `status = published` on provider-specific articles missing review metadata. Product form foregrounds `reference_status`, `reference_period`, `last_reviewed_at`.
Widgets (dashboard): `StatsOverview` with Published Articles, Draft Articles, Providers, Products, Glossary Terms, Needs Review (products+articles), Historical Content.

## 8. Seed-content plan

- **Categories:** Fundamentals, Life, Health, Critical Illness, Disability & Accident, Sharia, Unit Link / PAYDI, Policy & Process, Ethics & Conduct, Agency & Compensation.
- **Articles (target 22, ≥15 required):** the list in the brief — What is Risk; What is Insurance; How Insurance Works; Life Insurance; Human Life Value; Health Insurance; Critical Illness; Disability (TPD); Accident Protection; Insurance Policy; Premium; Claims; Underwriting; Sharia Insurance; PAYDI / Unit Link; Rider; Reimbursement vs Lump Sum; Insurance Business Process; Insurance Agent Role; Insurance Ethics — all `provider_id = NULL`. Plus ≥2 provider-specific: "How Allianz Flexi CI is described in training material" (`needs_review`), "Allianz agency structure: BE, BP, and generations" (`needs_review`), and one `historical` ("Allianz PAYDI base-commission table (historical training reference)").
- **Glossary:** ≥40 terms from DOMAIN_KNOWLEDGE Appendix A.
- **Providers:** Allianz (as one provider row; description notes the three Indonesian entities and ASN as distribution).
- **Product categories:** Medical / Hospitalisation, Critical Illness, Critical Illness Rider, Life (Unit Link), Term Life Rider, Disability Rider, Accident Rider, Payor Rider, Sharia Life.
- **Products (educational records):** Allianz Flexi Medical Plan, Flexi CI, CI Plus, CI Accelerated, TPD, ADDB, Term Life, Payor Protection, Payor Benefit — each with `reference_status` per DOMAIN_KNOWLEDGE ("How this maps to the data model"), a source row → the Basic Training PDF, and protection-category links.
- **Sources:** one row for the Basic Training PDF (`reference_period = "Compilation dated August 2026"`, `notes` recording the Option 3 / overriding-slide inconsistencies).
- **Quiz:** "Insurance fundamentals self-check" — 5 single-choice questions, every option with `is_correct` + `explanation`.
- **Assessment:** "Protection Needs Assessment" definition — 5 questions with weighted options mapped to protection categories (no engine, data only).
- **Learning path:** "Start here: understanding protection" linking the beginner articles in order.

## 9. Risks

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Laravel 10 EOL / unpatched advisories | Certain | Med (local only) | ADR 0005; documented; upgrade before Phase 3/deploy; no real data. |
| Filament 3 / PHP 8.1 friction with a future package | Med | Med | Keep deferred libs deferred; re-evaluate at each phase; runtime upgrade path documented. |
| Shared PostgreSQL container (not project-owned) | Med | Low | Dedicated `insuralab` DB + role; `docker-compose.yml` ships a self-contained alternative (`--profile full`). |
| Windows path / line-ending issues | Med | Low | `git config core.autocrlf false`; `.gitattributes` if needed; Pint + Prettier normalize. |
| Seeding volume vs. quality | Med | Med | Article bodies follow CONTENT_GUIDE hierarchy; provider numbers carry `needs_review`; no fabricated current facts. |
| Scope creep into Phase 3+ | Med | High | CLAUDE.md execution state; ROADMAP DoD gates; no public pages/API/3D in this execution. |

## 10. Testing strategy (Phases 0–2)

- **Framework:** Pest (backend). Frontend has no unit tests yet (shell only) — covered by `lint` + `build` + `tsc`.
- **Foundation feature/unit tests (required):**
  1. Provider can be created with valid attributes.
  2. An article can exist with `provider_id = NULL` (general knowledge).
  3. An article can be created referencing a provider (provider-specific).
  4. A product belongs to a provider and a product category.
  5. `sources` attach polymorphically to articles, providers, and products (`sourceables`).
  6. `reference_status` accepts only `current` / `historical` / `needs_review`.
  7. The database seeder runs to completion and produces the required minimums (counts asserted).
  8. `migrate:fresh` succeeds; a rollback (`migrate:refresh`) succeeds (down methods valid).
  9. Filament panel route `/admin` requires authentication (redirects guests).
- **Verification gate (run before declaring Phase 2 done):** `php artisan test`; `./vendor/bin/pint --test`; `php artisan migrate:fresh --seed` on the clean `insuralab` DB; `php artisan about` / `route:list` to confirm Filament boots; frontend `npm run lint` and `npm run build`; `npx tsc --noEmit`.

## 11. Phase plan (this execution)

1. **Phase 0** — inspection + this plan + project-memory docs + ADRs + `git init`. ✅
2. **Phase 1** — monorepo files (`docker-compose.yml`, `Makefile`, `README.md`, `.env.example` × 2), frontend config (TS strict, Prettier, env, minimal shell), backend config (`.env` pgsql, Sanctum, Pest, Pint, Filament install + panel + admin user).
3. **Phase 2** — migrations → models + factories → Filament resources + widgets → seeders → Pest tests → full verification gate.
4. **Stop.** Report. Do not start Phase 3.
