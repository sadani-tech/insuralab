# API additions: knowledge explorer
Legacy routes remain under /api/v1. Public exposure now requires allowed source rights for source-dependent content.

## GET /api/v1/knowledge
Returns {data: {nodes, edges, entries, passages, ai, limits}} with Cache-Control: no-store. q is an optional literal keyword/phrase up to 160 characters, filtering editorial entries. Invalid query shapes return 422.

Nodes retain canonical entity IDs in a documented projection namespace. The browser receives no file paths, operational records, keys or raw source notes. kind supports concept, risk, protection, process, provider, product, source. Negative IDs are reserved projections; glossary IDs are positive.

Edges identify two nodes and a readable label/explanation plus a source passage ID. Passages include locator, version, first-party prose/paraphrase, publisher and a safe HTTPS source URL if available. All publication/rights filters are applied on the server.

Bounds: core terms 80, extra domain/source nodes 40, 240 edges, 80 entries and 100 passages. The public snapshot always stays bounded; it is not a bulk source-file export.

## POST /api/v1/knowledge/assist
Input: question (required string, at most 500 characters), use_ai (optional boolean, false by default). Throttled to 12/min in addition to the normal API rate limit.

Returns mode=curated, candidate entries/passages, and explanation=null by default. Candidates are explicitly related readings, not a generated answer. A provider can only run after server enablement, opt-in, availability and non-empty public retrieval. Timeouts, bad citation IDs, or missing providers retain the candidate materials. No question/conversation is saved.

KNOWLEDGE_AI_ENABLED=false by default. Setting it to true alone does not configure a provider. KnowledgeExplainer is currently bound to DisabledKnowledgeExplainer; a real vendor adapter and credential/budget configuration remain future work.

## GET /api/v1/agent/workspace
Sanctum authentication required; always returns 403 after authentication while operations are inactive. Public /agent is only a feature-availability page.

## Revocation
Rights are unreviewed by default; public, restricted and personal are explicit classifications. Dependent old articles/products/providers/glossary entries with nonpublic sources are withheld without deleting stored data. Source withdrawal hides passage/FAQ/relation/graph data immediately on the next API request.
