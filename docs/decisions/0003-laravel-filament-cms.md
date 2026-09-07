# ADR 0003 — Laravel + Filament as the domain layer and CMS

- Status: Accepted
- Date: 2026-08-31

## Context

InsuraLab needs: a place for domain/business logic, a REST API for the Next.js frontend (Phase 3+), and a content-administration UI for a domain-aware model (articles with general/provider split, products with review metadata, a polymorphic source system, quizzes, assessments). This is not a blog; it is a structured content platform. We want to avoid building a bespoke admin UI.

## Decision

- **Laravel** owns all domain and business logic and the REST boundary. Controllers stay thin; logic lives in models, actions, and services.
- **Filament 3** is the content-administration system, mounted at `/admin` inside the same Laravel app (not a separate service).
- **Laravel Sanctum** provides admin/session authentication for the panel.
- **PostgreSQL** is the database (see ADR 0003's sibling constraints and ARCHITECTURE.md).
- **Pest** for backend tests; **Laravel Pint** for formatting.

Rejected alternatives: a headless CMS (Strapi/Directus/Sanity) — would split the domain model out of the code that enforces its invariants, and duplicate the general/provider and source/review logic. A hand-rolled admin — needless cost. Nova — paid, less suited to the bespoke resources.

## Consequences

- One codebase enforces schema, API, and admin rules consistently.
- Filament resources become the editorial guardrails (e.g. requiring review metadata before publishing provider content).
- We are coupled to Filament's release cadence and its Livewire/Alpine stack for admin. Acceptable — admin is internal.
- The public frontend never touches Filament; it consumes the REST API only.
