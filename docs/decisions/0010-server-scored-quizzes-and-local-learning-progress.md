# ADR 0010 — Server-scored quizzes and local anonymous learning progress

- Status: Accepted
- Date: 2026-09-07

## Context

Phase 8 needs quiz feedback and coherent learning journeys without introducing public accounts. Correct answers cannot be exposed with the quiz definition, and article/quiz relationships need to remain editable and stable.

## Decision

1. Laravel evaluates complete quiz submissions through a dedicated action. GET definitions omit correctness and explanations; POST results reveal them only after validation and scoring.
2. Semantic question and option keys are stable submission identifiers. The backend validates quiz, question, option ownership, duplicates, and completeness.
3. Quiz-to-article and learning-path-to-article/quiz connections are relational. Learning path items use a small `ARTICLE | QUIZ` discriminator with a database check constraint.
4. Anonymous progress stays in browser `localStorage` under `insuralab.learning.v1`. It stores only completed educational slugs, one local quiz result per slug, and the last path.
5. Articles require an explicit completion action. Paths provide deterministic guidance but do not enforce prerequisites.
6. Phase 7 assessment dimensions may link to one published general learning path; this does not change assessment evaluation into product recommendation.

Rejected alternatives: client-side scoring, answer metadata in GET responses, a public progress backend, account authentication, opaque JSON slug arrays, hard prerequisites, AI recommendations, and a generic workflow engine.

## Consequences

- Correctness remains authoritative and tamper-resistant at the REST boundary.
- Editors can curate paths and remediation lessons through Filament.
- Progress is device/browser-specific and can be cleared, which is an intentional no-account trade-off.
- Stable slugs allow a later consent-based account migration without changing content identities.

