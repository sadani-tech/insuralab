# InsuraLab
**Pengetahuan untuk perlindungan.** A source-aware insurance learning workspace with a knowledge map, curated answers, practical guides and a future private agent area.

The November 2026 revamp keeps Laravel/Filament, Next.js, TypeScript and PostgreSQL in this repository. No AI key, embedding service or model quota is required to use the core experience.

## Run locally
1. Use the existing PostgreSQL at :5433, or the dedicated Docker Compose profile at :5434. Configure backend/.env and frontend/.env.local from the examples. Do not overwrite populated settings.
2. Install dependencies with npm run install:all when needed.
3. From backend, run php artisan migrate, then php artisan db:seed --class=KnowledgeSeeder. This adds the new library without wiping or reseeding the existing database.
4. Run npm start from the repository root. Open http://localhost:3000 . CMS: http://localhost:8000/admin .

The optional standalone database command is docker compose --profile full up -d. Its port differs from the default; confirm configuration before using it.

## Product
- / — canonical explorer with Neural View (particle cortex), Graph View (3D network), accessible list, curated question search and source-backed reading connections.
- /panduan — stored guides with page-session checklists.
- /tanya — editorial FAQ and keyword search. Optional AI is unavailable until a real adapter is configured.
- /sumber — evidence passages with source/version context.
- /agent — feature-availability notice. No customer records or active CRM.
- Existing Learn/Glossary routes remain. Source-dependent records are withheld if publication rights are unknown/restricted.

## Editorial workflow
Source rights and review status are separate. Use the CMS to manage concepts, source rights, passages, relations and FAQ/guides. For changed cited text create a new passage key/version. Restricted source files remain outside public assets/Git.

The reference corpus at E:\\Kerja\\Insurance was inventoried and sampled, not bulk-ingested. New first-party material explicitly awaits independent expert review. See docs/CONTENT_COVERAGE.md.

## Verification
Frontend: npm run lint, npm run typecheck, npm test, npm run build.
Backend: php artisan test and php vendor/bin/pint --test from backend.
Browser: with both apps running, npm run test:e2e from frontend. Windows defaults to installed Edge; other hosts use Playwright Chromium. Override PLAYWRIGHT_CHANNEL if necessary. Synthetic test inputs only.

Tests use insuralab_testing, never the development database. Database resets are for a verified isolated test database only.

## Current documentation
Start with docs/REVAMP_PLAN.md, docs/ARCHITECTURE.md, docs/CONTENT_COVERAGE.md, docs/AGENT_OPERATIONS_BLUEPRINT.md and docs/QA_REPORT.md. Original documentation stays in docs/old-docs as history. No push or deployment is part of this local revamp.
