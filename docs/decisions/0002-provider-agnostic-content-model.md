# ADR 0002 — Provider-agnostic content model

- Status: Accepted
- Date: 2026-08-31

## Context

InsuraLab's mission is to help people understand insurance *before* choosing. Allianz is the first provider case study, but the platform must never become "an Allianz site". At the same time, general insurance knowledge ("what is critical illness insurance?") must stay clearly distinct from provider-specific training references ("how Allianz Flexi CI works per the Aug 2026 Basic Training compilation").

## Decision

1. **Providers are data, not schema.** There is a `providers` table. There is **no** `allianz_*` table, model, config key, or hardcoded identifier anywhere. Allianz (and its three Indonesian entities) are rows.
2. **Knowledge carries a nullable `provider_id`.** On `articles` (and any future knowledge-bearing entity):
   - `provider_id IS NULL` → general insurance knowledge.
   - `provider_id = <id>` → provider-specific educational content.
3. **Products always belong to a provider** (`products.provider_id` non-null) and to a `product_category`, and relate to protection concepts via a pivot (`product_protection_category`).
4. **No provider is privileged in the UI or API.** Layouts, routes, and payloads treat "the provider" as a variable.
5. **Extensible provider/product attributes** that don't warrant columns use a controlled JSONB `metadata` field — never EAV, never for anything searchable/filterable.

## Consequences

- Adding a second provider is a data task, not a migration.
- The general/provider split is enforceable in queries, the API, Filament forms, and the frontend.
- Slightly more join complexity than a single-provider schema; accepted as the core value proposition.
- Editorial process must decide `provider_id` for every article; the Filament form makes this explicit.
