# InsuraLab project guidance
The November 2026 user-authorized revamp replaces the archived phase order.

## Product and stack
Public insurance knowledge workspace plus a future private agent workspace. Keep Laravel/Filament for domain/editorial/API, Next.js/TypeScript for web, and PostgreSQL. Work in this repository and preserve unrelated work.

## Core rules
- Core graph/search/glossary/FAQ/guides/readers work without AI or embeddings.
- Professional, protective UI: warm surfaces, navy text, restrained teal, readable hierarchy, accessible controls, responsive detail panels.
- Glossary is the concept identity. Explorer display metadata is a curated presentation of that identity.
- Source rights default to unreviewed. Never expose internal, personal or unapproved material through content, graph, search, citation, storage, caches or AI.
- Keep general explanations separate from dated provider/product studies. No fake current data, human review dates, earnings promises, or guaranteed underwriting/claims.
- AI is opt-in and disabled by default. The current adapter is intentionally unavailable; disclose implementation boundaries.
- Agent operations are not enabled until real membership and per-record server policies exist. Admin access to editorial CMS does not imply access to customer information.
- Preserve data. Use additive migrations/idempotent imports. Never reset an active database. Lifecycle/reset tests run only on verified insuralab_testing.
- Read frontend/AGENTS.md and installed Next.js docs before changes. Keep controllers thin and domain authority in Laravel.

## Current references
Read docs/REVAMP_PLAN.md, PRODUCT_VISION.md, ARCHITECTURE.md, CONTENT_GUIDE.md, CONTENT_COVERAGE.md, API.md, ROADMAP.md, AGENT_OPERATIONS_BLUEPRINT.md and decisions/0011-knowledge-first-revamp.md. Old implementation notes are archived in docs/old-docs; their phase restrictions do not supersede the new user brief.

## Verification
Run meaningful focused tests first, then affected full tests/lint/typecheck/build and format checks. Validate actual browser behavior and screenshots on desktop/mobile. Report blocked checks honestly. Do not push, deploy, transmit prospect information, or publish reference documents under the local revamp scope.
