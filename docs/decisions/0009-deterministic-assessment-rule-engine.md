# ADR 0009 — Deterministic, configurable assessment rule engine

- Status: Accepted
- Date: 2026-09-05

## Context

Phase 7 translates anonymous answers about financial responsibilities into useful learning topics. One answer may affect several dimensions, dimensions have different maximums, one question is conditional, and every result needs an editorial explanation. The Phase 2 single `weight + protection_category_key` placeholder cannot represent this cleanly.

The feature must remain educational, provider-neutral, inspectable, and reproducible. It must not drift into underwriting, pseudo-scientific risk scoring, or product recommendations.

## Decision

1. Laravel owns evaluation through `EvaluateProtectionNeedsAssessment`; React collects answers and renders the DTO.
2. Stable semantic keys identify assessments, questions, options, and dimensions.
3. `assessment_dimensions` defines the five educational categories per assessment.
4. `assessment_option_impacts` maps one option to zero or more dimensions with a bounded 0–5 weight, reason code, and editorial reason.
5. Conditional applicability is structured as one prior question key plus allowed option keys. No arbitrary executable rule is stored.
6. Scores are normalized by maximum possible impact from applicable questions before mapping to three calm tiers. Numeric scores stay internal.
7. Ordered article pivots connect dimensions and options only to public general articles. Missing content is omitted safely.
8. Evaluation is stateless. No answer-level record is persisted. The browser holds only the result in session storage.
9. `methodology_version` is returned with the definition and required during evaluation; stale submissions are rejected.
10. Results contain dimensions, tiers, reasons, and general learning links—never provider or product recommendation fields.

Rejected alternatives: hardcoded React scoring; arbitrary executable PHP/JSON rules; AI/ML recommendations; and persisting detailed anonymous answers.

## Consequences

- Editors can change wording, order, impacts, reasons, article mappings, and publication state through Filament, with bounded weights and relational constraints.
- The schema is more explicit than the Phase 2 placeholder, but every relationship is auditable.
- Methodology changes can alter results; version increments make that visible, though historical replay is not built.
- Session-only results cannot be shared or restored on another device. This is a deliberate privacy trade-off.
