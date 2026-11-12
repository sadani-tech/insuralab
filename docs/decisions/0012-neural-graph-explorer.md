# 0012 — Distinct Neural View and Graph View

Date: 12 November 2026. Status: implemented locally.

The user clarified that the initial card-based 2D/3D map did not follow the HPT reference closely enough. Direct visual inspection of https://hpt.muhammadiyah.or.id/3d established the defining structure: a large left canvas with a Neural/Graph switch and a persistent right question/library pane. Neural View uses a dark particle cortex; Graph View uses a light spatial network. This decision supersedes the initial default-2D presentation in ADR 0011.

InsuraLab now follows that layout and those two visual modes while retaining its own teal/navy identity and knowledge-first behavior. Neural View uses locally generated Three.js geometry. Small surface particles are decorative; larger clickable markers map to real public concept records. No anatomical accuracy, AI processing, hidden knowledge, or invented reasoning steps are claimed. Graph View uses only actual published snapshot nodes and editorial edges. A deterministic volumetric layout keeps all nodes bounded and does not assert similarity, importance, or need for a product.

Question input searches persisted FAQ/guide titles and keywords. Results are clearly offered as related editorial choices, never synthesized answers. Explicit concept links from those entries highlight related markers. Selecting a concept exposes its evidence and relationships in the right pane (a closable bottom sheet on mobile). No login or LLM is required for public knowledge.

Canonical view values are neural, graph, and list. Previous 2d/3d values remain supported as Graph View aliases. Questions use ask; concept keyword search uses q. Switching visual mode retains question, selected concept, and topic. WebGL initialization or context loss opens an equivalent list. Both renderers have zoom, rotation, reset and pause controls, honor reduced motion, and clean up resources on unmount. The source/guide readers and future private agent boundary retain their existing scope.

The native browser host was unavailable during this comparison. Read-only reference inspection and local functional/visual tests used Playwright with installed Edge. Safe reference and implementation screenshots are retained in docs/screenshots. See QA_REPORT.md for actual checks and limitations.
