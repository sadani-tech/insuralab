# ADR 0004 — Source, reference-period, and review model

- Status: Accepted
- Date: 2026-08-31

## Context

Insurance information changes: benefit limits, entry ages, cost-of-rider structures, commission rates, agency qualification thresholds. The Allianz Basic Training material InsuraLab draws on is explicitly *not a substitute for official documents* and contains figures tied to specific periods, plus at least one internally inconsistent slide ("Option 3": text says 2 direct BE, diagram shows 5). If InsuraLab presents any of this as timeless fact, it breaks its own trust promise.

## Decision

**1. Reusable `sources`.** A `sources` row: `title`, `publisher`, `url?`, `published_at?`, `accessed_at?`, `reference_period?`, `notes?`. One source (e.g. the Basic Training PDF) is referenced by many entities.

**2. Polymorphic `sourceables`.** A `sourceables` pivot (`source_id`, `sourceable_type`, `sourceable_id`, optional `note`) attaches any number of sources to `articles`, `providers`, `products`, `glossary_terms` — and other entities later — without schema changes.

**3. Review metadata on volatile entities.** `providers`, `products`, and `articles` carry `last_reviewed_at` (date). `providers` and `products` also carry `reference_period` (free text) and `reference_status`:

| `reference_status` | Meaning | Rendering rule |
|---|---|---|
| `current` | Re-verified against a current official document | Shown normally |
| `historical` | Tied to a past period / superseded scheme | Shown, but badged; never presented as current |
| `needs_review` | Not yet verified, or the source is inconsistent/ambiguous | Badged; the uncertainty is shown, not hidden |

**4. Inconsistencies stay visible.** When source material conflicts, both readings are written into the content body and `sources.notes`, and `reference_status = needs_review`. Editors do not silently pick one.

**5. Filament guardrails.** Provider-specific article/product forms surface these fields prominently and block `published` status for provider content that lacks review metadata.

**6. Historical content is kept, not deleted.** It is useful for teaching insurance history; it is simply never rendered as current product fact.

## Consequences

- Every provider claim in the product is traceable to a dated source with a review state.
- The frontend (Phase 6) must implement distinct visual treatments for `historical` and `needs_review`.
- A Phase 13 runbook is needed for periodically re-reviewing and re-dating content.
- Slightly more editorial overhead per provider item; this is the point.
