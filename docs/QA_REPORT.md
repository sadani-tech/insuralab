# Revamp verification report

Date: 12 November 2026. Branch: feat/knowledge-explorer. Local verification completed. The user subsequently authorized commit and branch push; no deployment.

## Results

| Check | Result |
| --- | --- |
| Backend full Pest suite | 130 tests passed, 887 assertions |
| Backend formatting | Pint passed across 238 files |
| Frontend full Vitest suite | 54 tests passed across 17 files |
| Frontend ESLint and TypeScript | Passed |
| Frontend full Prettier check | Passed |
| Production build | Passed with Next.js 16.4.6 |
| Production browser suite | 18 tests passed on desktop and mobile emulation (Neural/Graph revision) |
| Git whitespace check | Passed |
| npm production audit | No reported production dependency vulnerabilities |

Backend coverage includes source revocation, unknown rights, draft evidence, source-link sanitization, bounded expanded corpora, hidden related terms, idempotent imports, immutable referenced passages, inactive agent authorization, new CMS authorization/rendering, and optional-provider failure/citation fallback. Requests to external services are prohibited in the knowledge tests.

The test application now refuses to start outside the isolated insuralab_testing database before RefreshDatabase can run. The full suite covers fresh migrations, rollback/lifecycle and seeding there. The development database received additive migrations and the new knowledge seed; it was not reset.

Frontend/browser coverage includes search, direct URL state, browser back navigation, concept selection, relation evidence, FAQ expansion, no results, checklist state, source readers, inactive agent availability, reduced motion, real WebGL 3D, simulated missing WebGL with list fallback, node-type filtering and resetting filters. Core navigation runs with no AI adapter or credentials. Additional browser coverage verifies both distinct visual modes, retained question context, missing WebGL in each renderer, and stationary canvas frames after initialization under reduced-motion preferences. Direct pointer clicks on the Neural and Graph canvases were also manually verified to open real concept details; screenshots are neural-node-panel-desktop.png and graph-node-panel-desktop.png. Backend results above are retained from the initial revamp; this visual revision changed frontend code and documents only.

## Visual review

Reviewed actual desktop/mobile screenshots of the revised immersive explorer: default Neural View, the distinct light Graph View, question results and concept/source panels. Readers, editorial answers, guide checklist, and agent availability retain the earlier verified design. Enlarged the spatial graph and labels after screenshot review, increased reading sizes, and preserved the question/topic/filter context between modes. Source/guide readers continue to use the shared design tokens.

The native Computer Use/browser host was unavailable, so read-only reference inspection and local application tests used Playwright with installed Edge. Both HPT Neural View and Graph View were inspected visually on desktop; its mobile Neural View was also inspected. Reference screenshots are reference-hpt-neural.png, reference-hpt-graph.png and reference-hpt-mobile.png. The implementation follows the two-mode structure and canvas treatment while retaining InsuraLab branding and curated knowledge behavior.

Current screenshots: docs/screenshots/explorer-desktop.png and explorer-mobile.png (default Neural View), graph-view-desktop.png and graph-view-mobile.png, neural-view-desktop.png and neural-view-mobile.png (question context), explorer-question-desktop.png and explorer-question-mobile.png, plus the source-reader, answers, guide-reader and agent desktop/mobile images. Earlier graph-3d-* screenshots record the superseded presentation and are not current UI.

## Performance

Revised production browser sample, 49 actual nodes and 87 editorial connections. Initial readiness now waits for the default Neural View first rendered frame (rather than switching immediately to the list). Graph switch readiness waits for its settled canvas:

| Surface | Viewport | Initial Neural View ready | Graph switch ready | Browser frame cadence |
| --- | --- | --- | --- | --- |
| Desktop | 1440 x 1100 | 2145 ms | 500 ms | 61 frames/s |
| Mobile emulation | 390 x 664 | 2327 ms | 622 ms | 60 frames/s |

Both revised Neural View samples met the initial 3000 ms interactive target. Frame cadence is measured using requestAnimationFrame while the 3D canvas is present; it is a browser responsiveness indicator, not an independent GPU/renderer FPS benchmark. Mobile is emulated, not a physical-device profile. Raw observations are in docs/screenshots/performance-desktop.json and performance-mobile.json.

A test expands the database corpus with 130 synthetic concepts and verifies the bounded public snapshot. A larger real-world graph GPU benchmark remains follow-up work; no performance claim is made for unlimited graphs.

## Dependency and integration limits

- Updated Next.js and matching ESLint config from 16.3.3 to 16.4.6 and applied compatible dependency fixes. Production npm audit reports zero findings. The full audit still reports seven moderate findings in transitive development lint tooling; they are recorded rather than hidden with a forced incompatible override.
- No real AI vendor is configured. The disabled adapter, editorial retrieval, opt-in boundary, invalid citation fallback and simulated provider failure are tested; live answer quality and external-provider billing/latency are not claimed as verified.
- The complete reference folder was not imported. See CONTENT_COVERAGE.md for the actual sample, editorial material and gaps. Independent expert/content review is still required.
- Agent operations remain inactive. The blueprint, public availability screen and denied operational endpoint prepare the next implementation without collecting customer data.
- No production backup/restore drill, deployment, physical-device GPU benchmark, or formal accessibility certification is claimed by this local revamp.

## Preview

The local Next.js preview (port 3000) and Laravel server (port 8000) were stopped at the user's request before committing. Use npm start from repository root to resume the standard development workflow. No deployment was performed.

Existing user changes that moved old documents into docs/old-docs were retained. Current product/architecture/roadmap/API documents were rewritten for the authorized revamp. Project CLAUDE.md now follows the new brief and is no longer excluded by the repository ignore rule.
