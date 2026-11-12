# API.md — InsuraLab Public REST API (v1)

Status: **Phases 3–8 built.** This document describes the public API as
implemented. It is the contract the Next.js frontend consumes; the frontend
holds no authoritative domain logic (ARCHITECTURE §2).

Phase 5 additions (ADR-0008): `GET /learning-paths` + `/learning-paths/{slug}`;
`articles/{slug}` now also returns `key_takeaways`, `glossary_terms` (curated),
and `related`; `glossary/{slug}` now also returns `related_articles`; the
glossary list carries `articles_count`.

Phase 6 additions: `providers/{slug}` now also returns `metadata` (allow-list:
`entities`, `business_areas`, `distribution`) and `articles[]` (its
provider-specific articles); `products/{slug}` now also returns
`related_articles[]` (concept-first) and `related_terms[]`.

Phase 7 additions (ADR-0009): `GET /assessments/protection-needs` returns the
versioned public questionnaire; `POST /assessments/protection-needs/evaluate`
performs stateless, deterministic educational evaluation. Scores and rule
weights stay private; the result contains calm relevance tiers, editorial
reasons, and general learning links only.

---

## 1. Base URL & versioning

| Environment | Base URL |
|---|---|
| Local dev  | `http://localhost:8000/api/v1` |

- The API is **versioned in the path**: `/api/v1/...`. A breaking change ships as
  `/api/v2` while `/api/v1` keeps working.
- Public content endpoints are **read-only** and unauthenticated. The Phase 7
  evaluation endpoint accepts a `POST`, but performs no write and persists no
  answer data. Content writes remain admin-only through Filament (`/admin`).
- Transport is JSON over HTTP. Send `Accept: application/json`. Error responses
  are JSON regardless of the header for any `/api/*` path.

## 2. Response format

### Single resource

```json
{ "data": { "...": "..." } }
```

### Collection (paginated)

Laravel resource collections produce `data` + `links` + `meta`:

```json
{
  "data": [ { "...": "..." } ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": {
    "current_page": 1, "from": 1, "last_page": 12, "path": "...",
    "per_page": 2, "to": 2, "total": 23,
    "links": [ { "url": "...", "label": "1", "active": true } ]
  }
}
```

### Collection (non-paginated)

Small fixed lists (`categories`, `protection-categories`, `quizzes`,
`assessments`) return `{ "data": [ ... ] }` with no `meta`/`links`.

### Health

`GET /api/v1/health` is a system endpoint and is **not** wrapped in `data`:

```json
{ "status": "ok", "service": "insuralab-api", "api_version": "v1", "time": "2026-09-01T06:16:09+00:00" }
```

## 3. Pagination

| Param | Rule | Default |
|---|---|---|
| `page` | integer ≥ 1 | 1 |
| `per_page` | integer, 1–100 | 15 |

`per_page` over **100** is rejected with `422` — there is no "return everything"
mode. Filter params are echoed back into the pagination `links`.

## 4. Errors

| Status | Body |
|---|---|
| `404` | `{ "message": "Resource not found." }` — unknown slug, or content that is not public |
| `422` | `{ "message": "<first error>", "errors": { "<field>": ["..."] } }` — invalid query params (bad enum, `per_page` too large, unknown `category` slug, …) |
| `429` | `{ "message": "Too Many Requests" }` — throttle (60 req/min per IP) |

The `404` body is deliberately generic and never names a model class.

## 5. Publication rules (what the API will and won't return)

- **Articles:** only `status = published` **and** `published_at <= now`. Drafts,
  archived, and future-dated articles are unreachable — by list and by slug.
- **Products / providers:** only `status = published` / `status = active`.
- **Quizzes / assessments:** only `status = published`.
- **Historical content is still returned** where it is deliberate educational
  history (e.g. a `historical` product), but the payload always carries the
  trust block so the frontend can badge it. Historical items are never silently
  presented as current (ADR-0004).

## 6. Trust metadata

One of InsuraLab's differentiators. Provider-specific articles, all products,
providers, and provider-specific glossary terms carry a `trust` object:

```json
"trust": {
  "reference_status": "needs_review",          // current | historical | needs_review | null
  "reference_period": "Allianz Basic Training compilation, August 2026",
  "last_reviewed_at": "2026-08-18"
}
```

and a `sources[]` array (on detail endpoints) where each entry includes
`title`, `publisher`, `url`, `published_at`, `reference_period`, and `notes`
— `notes` is where source inconsistencies are recorded, visibly.

General knowledge (`provider_id = null`) may have `reference_status: "current"`
or `null` and needs no reference period.

## 7. Endpoints

### System

#### `GET /health`
Liveness only. No environment or infrastructure detail.

---

### Articles

#### `GET /articles`
Paginated list of published articles (general + provider-specific), newest first,
featured first.

| Query param | Type | Notes |
|---|---|---|
| `page`, `per_page` | int | see §3 |
| `category` | string | category **slug**; `422` if unknown |
| `provider` | string | provider **slug**; `422` if unknown |
| `level` | enum | `beginner` \| `intermediate` \| `advanced` |
| `featured` | boolean | `true`/`false`/`1`/`0` |
| `knowledge_type` | enum | `general` \| `provider_specific` |

Item shape (`ArticleSummaryResource`): `id, title, slug, excerpt, level,
estimated_reading_minutes, is_featured, knowledge_type, category {id,name,slug},
provider {id,name,slug,country_of_origin,last_reviewed_at} | null,
reference_status, last_reviewed_at, published_at`.

Example:

```
GET /api/v1/articles?level=beginner&category=fundamentals&per_page=2
```

#### `GET /articles/{slug}`
Full article (`ArticleResource`): summary fields **plus** `body` (Markdown),
`key_takeaways[]` ("Poin Penting" — plain strings), `tags[]`, `glossary_terms[]`
(curated `{term, slug, short_definition}` — the terms explicitly linked to this
article, used for the in-article `:istilah[…]` tooltips), `related[]`
(deterministic — same category, then shared tags, then same knowledge type;
never random, never provider-crossing), `trust{}`, `sources[]`, `created_at`,
`updated_at`. `404` for unknown / non-public slug.

The `body` Markdown uses GFM plus a small set of directives the frontend renders
as callouts: `:::definisi` `:::penting` `:::contoh` `:::kesalahpahaman`
`:::catatan-provider` `:::referensi-historis` `:::perlu-verifikasi`, and the
inline `:istilah[teks]{slug=…}` glossary reference. Directives are authored
explicitly in content — the API never auto-parses prose.

---

### Categories

#### `GET /categories`
Non-paginated. `id, name, slug, description, sort_order, articles_count`
(published articles only). Ordered by `sort_order`.

---

### Protection categories

#### `GET /protection-categories`
Non-paginated. The six protection concepts: `id, key, name, slug,
short_description, description, icon, sort_order`.

---

### Glossary

#### `GET /glossary`
Paginated, ordered by term.

| Query param | Type | Notes |
|---|---|---|
| `page`, `per_page` | int | see §3 |
| `letter` | string | single A–Z letter, case-insensitive (`?letter=a`) |
| `category` | string | `general` \| `sharia` \| `product` \| `process` \| `compliance` \| `agency` |
| `q` | string | substring match on term / short definition / definition |

Item shape (`GlossaryTermSummaryResource`): `id, term, slug, short_definition,
category, letter, is_provider_specific, reference_status`.

#### `GET /glossary/{slug}`
Full term (`GlossaryTermResource`): `+ definition, provider, trust{},
related_terms[] ({term,slug,short_definition}), related_articles[]
(published `ArticleSummaryResource`s that teach this term), sources[]`.

---

### Learning paths

#### `GET /learning-paths`
Non-paginated. Curated ordered journeys through published articles and quizzes.
Item shape: `id, title, slug, description, level, estimated_duration_minutes,
lessons_count, quizzes_count`. Ordered by `sort_order`.

#### `GET /learning-paths/{slug}`
`+ items[]`: ordered `{ position, type, note, article, quiz }`. `type` is
`article` or `quiz`; exactly one matching target is non-null. Only published
targets appear and positions are renumbered so gaps never show. No user progress
is stored by the API. `404` for an unknown or draft path.

---

### Providers

#### `GET /providers`
Paginated (active providers only). Item shape: `id, name, slug,
country_of_origin, last_reviewed_at, products_count, articles_count`.

#### `GET /providers/{slug}`
Full profile (`ProviderResource`): `+ description, presence_in_indonesia,
website_url, status, metadata (allow-list: entities, business_areas,
distribution), counts {products, articles}, articles[] (the provider-specific
published articles, as summaries), sources[]`.
**Products are never nested** — fetch `/products?provider={slug}`.

---

### Products

#### `GET /products`
Paginated (published only), ordered by name.

| Query param | Type | Notes |
|---|---|---|
| `page`, `per_page` | int | see §3 |
| `provider` | string | provider slug |
| `category` | string | product-category slug |
| `protection` | string | protection-category **key** (`life`, `health`, `critical_illness`, `disability`, `accident`, `income`) |
| `insurance_scheme` | enum | `conventional` \| `sharia` |
| `product_structure` | enum | `traditional` \| `paydi` \| `other` |
| `reference_status` | enum | `current` \| `historical` \| `needs_review` |

Item shape (`ProductSummaryResource`): `id, name, slug, short_description,
insurance_scheme, product_structure, provider, product_category,
protection_categories[], reference_status, reference_period, last_reviewed_at`.

#### `GET /products/{slug}`
Full product (`ProductResource`): `+ description, metadata (controlled public
keys only — see below), related_articles[] (concept-first: the GENERAL learning
articles to understand before this product — published `ArticleSummaryResource`s),
related_terms[] ({term, slug, short_definition}), trust{}, sources[], created_at,
updated_at`.

**Controlled `metadata`.** The JSONB `metadata` column is never returned
wholesale. Only this allow-list is exposed:
`payout_model, tiers, reduces_base_sum_assured, variants, is_paydi`.
Anything else stored there stays server-side.

---

### Quiz definitions

#### `GET /quizzes` · `GET /quizzes/{slug}`
Published definitions only. Summary shape includes `id, title, slug,
description, level, estimated_duration_minutes, category, questions_count,
related_learning_paths[]`. Detail adds ordered `questions[] { id, key, prompt,
type, options[] { id, key, label } }`.

**The payload never contains `is_correct`, correct-answer identifiers, or option
explanations.**

#### `POST /quizzes/{slug}/submit`

Request:

```json
{
  "answers": [
    { "question": "risk-transfer", "option": "risk-transfer" }
  ]
}
```

Laravel rejects an unpublished/unknown quiz with `404`, and duplicate answers,
unknown questions, invalid/cross-question options, or missing required answers
with `422`. It ignores no client score because no score field is accepted.

Response `data` contains `quiz`, `score {correct,total,percentage}`,
`results[] {question,prompt,correct,selected_option,selected_option_label,
correct_option,correct_option_label,explanation,concept_explanation,
related_lessons[]}`, and de-duplicated `review_topics[]` for missed concepts.

---

### Protection Needs Assessment

#### `GET /assessments` · `GET /assessments/{slug}`

Published definitions only. For the canonical `protection-needs` definition,
the detail shape is `id, title, slug, methodology_version, description, intro,
disclaimer, questions[] { id, key, prompt, help_text, type, is_required,
condition { question, options[] } | null, options[] { id, key, label } }`.

Internal weights, reason codes, dimension mappings, and `rules` are not exposed.

#### `POST /assessments/protection-needs/evaluate`

Stateless evaluation owned by Laravel. Request:

```json
{
  "methodology_version": "1.0",
  "answers": [
    { "question": "dependents", "option": "two_to_three" },
    { "question": "income_role", "option": "primary" }
  ]
}
```

The endpoint rejects stale versions, duplicate questions, unknown question or
option keys, options belonging to another question, missing required answers,
and answers to inapplicable conditional questions with `422`.

Response:

```json
{
  "data": {
    "assessment": "protection-needs",
    "methodology_version": "1.0",
    "dimensions": [
      {
        "key": "life",
        "name": "Proteksi Jiwa",
        "relevance": "highly_relevant",
        "relevance_label": "Sangat Relevan",
        "description": "...",
        "reasons": ["Kamu menunjukkan bahwa ada orang lain yang bergantung pada penghasilanmu."],
        "learning_topics": [
          { "title": "Asuransi Jiwa", "slug": "asuransi-jiwa", "excerpt": "...", "url": "/learn/asuransi-jiwa" }
        ],
        "learning_path": {
          "title": "Memahami Proteksi Pribadi",
          "slug": "memahami-proteksi-pribadi",
          "url": "/learn/path/memahami-proteksi-pribadi"
        }
      }
    ],
    "additional_learning": {
      "title": "Sudah punya perlindungan?",
      "description": "...",
      "topics": []
    },
    "disclaimer": "..."
  }
}
```

`additional_learning` is omitted when no selected option maps to additional
policy-reading content. Raw/normalized scores are internal. Products and
providers are absent by contract. No assessment response is stored.

## 8. Performance notes

- List endpoints eager-load their nested relations (`category`, `provider`,
  `productCategory`, `protectionCategories`) — no N+1.
- Counts (`articles_count`, `products_count`, `questions_count`) use
  `withCount`, not loaded collections.
- No response caching layer yet (ADR / ROADMAP: add only with evidence).

## 9. CORS

`config/cors.php` allows only the configured frontend origin(s):
`CORS_ALLOWED_ORIGINS` (comma-separated) or `FRONTEND_URL`. No wildcard origin.
Applies to `api/*`, `sanctum/csrf-cookie`, `login`, `logout`.

## 10. Conventions summary

- Public navigation uses **slugs**, never sequential IDs. IDs are included in
  payloads for client-side keying only.
- Every slug column has a unique constraint (migrations).
- Enum values are validated against the PHP backed enums; an invalid value is a
  `422`, not a silent empty result.
