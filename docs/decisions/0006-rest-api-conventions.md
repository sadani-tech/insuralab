# ADR 0006 — Public REST API conventions (v1)

- Status: Accepted
- Date: 2026-09-01

## Context

Phase 3 exposes the domain to the Next.js frontend. The brief and ARCHITECTURE §3
require a versioned, read-only, unauthenticated API that never leaks Laravel
internals or unpublished content, and that carries the source/review trust model
into every provider-specific payload.

## Decision

1. **Versioned path prefix.** All public endpoints live under `/api/v1`, named
   `api.v1.*`. A breaking change ships as `/api/v2`; `/api/v1` keeps working.

2. **Read-only, unauthenticated, throttled.** Only `GET`. No public auth. The
   existing `api` throttle (60/min per IP) applies. Writes stay in Filament.

3. **Resource classes, never models.** Every response goes through an
   `App\Http\Resources\Api\V1` class. Two shapes per rich entity:
   a **`*SummaryResource`** for list endpoints (no body, no heavy relations) and a
   **`*Resource`** for detail (full body, `trust{}`, `sources[]`, timestamps).
   Small fixed entities (category, protection category, tag, source) have one
   resource.

4. **Consistent envelope.** Laravel resource collections' native
   `data` / `links` / `meta` for paginated lists; `{ "data": {...} }` for single
   resources; a bare `{ "data": [...] }` for small non-paginated lists; and
   `GET /health` is an un-enveloped system response.

5. **Slugs are the public identifier.** Detail routes are `.../{slug}` resolved by
   an explicit `where('slug', …)->firstOrFail()` on a `public()`/`published()`
   scope, so unpublished content 404s by slug too. IDs remain in payloads for
   client-side keying only. Slug columns already carry unique constraints.

6. **Query validation via Form Requests.** `page` ≥ 1; `per_page` 1–100 (hard cap,
   no "return everything"); filter enums validated against the PHP backed enums;
   filter slugs validated with `exists`. Invalid input is `422`, not a silent
   empty result. Pagination limits live on a plain `Pagination` class because PHP
   8.1 traits cannot declare constants.

7. **Trust metadata is always structural.** Provider-specific articles, all
   products, providers, and provider-specific glossary terms expose a `trust`
   object (`reference_status`, `reference_period`, `last_reviewed_at`) and, on
   detail endpoints, `sources[]` including `notes` (where inconsistencies are
   recorded). `historical` products are still returned — they are deliberate
   educational history — but always badged.

8. **Controlled JSONB exposure.** `products.metadata` is never returned wholesale.
   `ProductResource::PUBLIC_METADATA_KEYS` is an explicit allow-list
   (`payout_model`, `tiers`, `reduces_base_sum_assured`, `variants`, `is_paydi`);
   anything else added to that column stays server-side.

9. **Quiz/assessment definitions only.** No `is_correct`, no option explanations,
   no assessment option `weight` / `protection_category_key` in any payload.
   Scoring and evaluation are later phases and happen server-side.

10. **JSON errors for `api/*`.** The exception handler forces JSON for any
    `api/*` path regardless of `Accept`, and renders `NotFoundHttpException` as a
    generic `{"message":"Resource not found."}` that never names a model class.

11. **CORS is environment-driven.** `config/cors.php` reads
    `CORS_ALLOWED_ORIGINS` (comma-separated) or falls back to `FRONTEND_URL`.
    No wildcard origin.

12. **No API caching layer yet.** `Cache-Control` / response caching is deferred
    until there is evidence it is needed (ARCHITECTURE §6.3 spirit).

13. **No generated OpenAPI.** `docs/API.md` is the hand-written contract; the
    project has no automated OpenAPI tooling and the brief says not to add it.

## Consequences

- The frontend codes against `docs/API.md` + the Zod schemas in `src/lib/api`,
  not against Eloquent.
- Adding an endpoint = route + thin controller + request + resource + Pest test.
- The summary/detail split doubles the resource count for rich entities; accepted
  for payload control and to keep list responses small.
- 42 Pest feature tests pin the shapes and the publication rules.
