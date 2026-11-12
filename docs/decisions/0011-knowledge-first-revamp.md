# ADR 0011: Knowledge-first in-place revamp
Date: 12 November 2026. Status: accepted by the user-provided revamp brief.

## Decision
Keep the repository and Laravel/Filament + Next.js + PostgreSQL architecture. Replace the public information architecture and visual system. Core education uses persisted editorial material and keyword search; optional AI is disabled by default. Use glossary identities for concepts, versioned source passages as evidence, and explicit editorial reading relations.

Use the existing relational database for this bounded graph. Lazy-load react-force-graph-3d, keep a readable progressive SVG 2D view and a keyboard list, and default to 2D. Avoid extra AI/vector/graph infrastructure for the core journey.

Source usage rights default to unreviewed and differ from accuracy review status. Revocation hides dependent public content, including legacy training-derived records, while retaining the database. Cited passage text is immutable; new text gets a new version/key.

The future agent workspace is private, manual-first, and separate from the public knowledge graph. The current operational endpoint remains deny-by-default.

## Consequences
Some existing product/provider/article URLs are withheld until their source rights are reviewed. Existing APIs and stored content are retained. Local files are not redistributed or bulk-imported. Future AI and operational features must respect the same publication and access boundary.
