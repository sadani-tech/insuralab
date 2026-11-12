# InsuraLab revamp
Date: 12 November 2026. Supersedes the separate-project proposal and archived phase order.

## Product decisions
Revamp the existing repository. Laravel/Filament own editorial data, publication, and domain rules; Next.js owns the public experience. PostgreSQL remains the datastore. No NestJS migration or replacement application.

The primary journey is explore → concept → meaningful reading connection → evidence → guide/FAQ. AI is optional and disabled by default. The independent public library and future private agent workspace have separate boundaries.

## Implemented
- New protective teal/navy identity, warm surfaces, compact navigation, an immersive two-column explorer with question pane, responsive detail panels, and consistent source/guide readers.
- Canonical explorer at /; /explore redirects while retaining query parameters.
- URL state: q, ask, group, kind, view, node, entry, source. Native history supports back/forward.
- Neural View is the default: a procedural particle cortex with selectable real concept markers on a dark canvas. Graph View is a light, spatial 3D network of actual nodes and curated edges. An equivalent keyboard-accessible list is the WebGL fallback. This follows the HPT two-mode reference after direct visual comparison; old view=2d and view=3d URLs map to Graph View.
- Keyword/alias search, persisted editorial FAQ and guides, session-page checklist state, source passages and version context.
- Glossary remains the canonical concept identity. Explorer label/summary are editorial presentation fields on that identity; the original detailed prose is retained.
- Additive migration, idempotent corpus seed, Filament resources for entries/passages/relations, and source rights management.
- Public source revocation suppresses dependent content; the explorer and assist response bypass shared caches.
- Optional explanation contract with a disabled adapter, bounded public retrieval, curated fallback on failure, and citation-ID validation. No external model is configured.
- /agent is a public availability screen. Its operational API is denied to everyone until scoped membership policies are implemented.

## Scope kept for the next implementation
Actual CRM, memberships, prospect/customer records, notifications, insurer integrations, document uploads, AI vendor adapter and conversation persistence. No real customer information has been imported.

## Content and validation
See CONTENT_COVERAGE.md, AGENT_OPERATIONS_BLUEPRINT.md and QA_REPORT.md. UI screenshots are saved in docs/screenshots. Archived documents remain in docs/old-docs as history; their old execution status is not authoritative.

## Deployment boundary
Implementation and verification were local. The user subsequently authorized committing and pushing the branch. Deployment, provider communications, billing integration, and redistribution of source PDFs remain outside this task.
