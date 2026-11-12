# Architecture after revamp
## Boundaries
Next.js App Router → Laravel REST /api/v1 → PostgreSQL. Filament runs inside Laravel at /admin. The browser never holds a database credential or model key.

GlossaryTerm is the existing concept identity. Explorer publication and editorial display metadata extend it. KnowledgeRelation links two terms to immutable, versioned SourcePassage evidence. KnowledgeEntry stores FAQ/guide prose, keywords, ordered steps, and explicit term relations. Source.access_level separates usage rights from accuracy/review status.

KnowledgeLibrary returns a bounded public snapshot: concepts plus public source/provider/product projections. Negative numeric IDs namespace non-glossary projections; they do not create duplicate domain records. Source/provider/product details come from their canonical database records. Production expansions should move to explicit typed IDs before identifiers approach namespace boundaries.

Public queries require allowed source rights and publication. WithPublicEvidence hides legacy records depending on sources whose rights are not public, preserving stored data for editorial review. Public resource collections also filter source metadata. An existing URL can now return 404 until its source rights are approved.

KnowledgeAnswers ranks persisted editorial entries using words. It passes only public evidence to the KnowledgeExplainer contract, only after explicit opt-in and server enablement. DisabledKnowledgeExplainer is the default; outages and invalid citation IDs return the curated results. There is no actual vendor adapter, semantic index, or saved conversation.

## Web
LibraryPage fetches a validated snapshot server-side with no-store and a timeout. The client explores that bounded dataset without additional model calls. Rendering text uses React escaping. WebGL is lazy loaded; source text is not HTML. The accessible list remains available when WebGL fails. KnowledgeExplorer keeps Neural View (procedural Three.js cortex illustration with actual concept markers) and Graph View (bounded react-force-graph-3d network) beside a persistent curated-question pane. Visual particles are decoration, never fabricated knowledge records. Question results use stored titles/keywords and explicit editorial concept links; weak matches are shown as related choices. Graph positions do not express similarity or product quality. Both views dispose WebGL resources, support camera controls and respect reduced motion.

Entry/source readers use passage IDs. Referenced passage text/version cannot be edited in place; create a new passage and explicitly relink an entry for a new version. Revocation remains allowed. No shared public cache stores withdrawn content. Already-open pages should be refreshed after an editorial change.

## Agent boundary
/agent contains only a public feature-availability explanation. /api/v1/agent/workspace requires Sanctum and then returns 403, including for administrators. No operational tables or customer data exist yet. See AGENT_OPERATIONS_BLUEPRINT.md for required membership, policies, and tenant isolation before enabling CRUD.
