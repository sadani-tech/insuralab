# LEARNING_SYSTEM.md — Quiz and Learning Journey

Status: Phase 8 implementation.

## Learning philosophy

InsuraLab follows: **understand → reflect → test understanding → review an explanation → continue learning**. Quiz scores are a compact self-check, not points, a leaderboard, a credential, or proof of expertise. Paths guide order but never lock content.

## Quiz system

The MVP supports single-choice questions. Six seeded quizzes cover fundamentals, life protection, sharia, PAYDI, insurance operations, and ethics/compliance. Questions test concepts and misconceptions rather than obscure facts. Every option has an educational explanation, including incorrect options using calm language such as “Belum tepat.”

Stable question and option keys form the public submission contract. `GET /api/v1/quizzes/{slug}` returns prompts, options, ordering, and educational metadata, but never returns `is_correct` or explanations. The browser submits only `{question, option}` pairs to `POST /api/v1/quizzes/{slug}/submit`.

Laravel's `SubmitQuiz` action validates:

- the quiz is published;
- each question belongs to that quiz;
- each option belongs to its submitted question;
- no question appears twice;
- every required question is answered.

Laravel then computes the score and returns per-question correctness, the correct option, explanations, related general lessons, and de-duplicated review topics for missed concepts. The client never submits a score, correctness flag, or points.

## Learning paths

`learning_paths` contain ordered relational `learning_path_items`. An item is either an `ARTICLE` or `QUIZ` and refers to exactly one corresponding record. This deliberately avoids a generic workflow engine and JSON arrays of slugs. Public path responses omit unpublished targets and preserve editorial ordering.

The seeded paths are:

1. Dasar-Dasar Asuransi
2. Memahami Proteksi Pribadi
3. Memahami Produk Modern
4. Asuransi Syariah
5. Operasi & Etika Asuransi

The 2D learning map groups these concepts into Foundation, Protection, and Advanced sections. It is semantic HTML/CSS and remains vertical on mobile. No prerequisite hard-lock exists.

## Anonymous local progress

The frontend stores a versioned document under `insuralab.learning.v1` in `localStorage`. It contains only:

- completed article slugs;
- completed quiz slugs with one latest percentage and completion timestamp;
- the last visited learning-path slug.

Opening an article does not complete it. The visitor must use **Tandai Selesai**. A quiz completes only after a successful server submission. **Reset Progress** removes the whole namespaced record. “Lanjutkan Belajar” deterministically selects the first unfinished item in the last visited path.

The record must never contain assessment answers, financial values, identity, medical details, provider preferences, or product preferences. Assessment results remain separately session-scoped as documented in `ASSESSMENT_METHODOLOGY.md`.

## Recommendation rules

“What should I learn next?” is deterministic:

1. Within a path, select its first unfinished ordered item.
2. After a quiz, use missed questions' relational `relatedArticles`, de-duplicated in question/article order.
3. After the Phase 7 assessment, use the assessment dimension's related general learning path, then its general article topics.
4. Outside those contexts, existing curated article relationships remain the fallback.

No AI, provider ranking, product matching, or personalized financial recommendation is used.

## Administration

Filament manages quiz metadata, duration, questions, stable keys, options, correctness, explanations, related general articles, and ordering. It also manages learning paths and their ordered article/quiz items using searchable relationship selectors, so editors do not type raw foreign IDs. Assessment dimensions can select a published learning path.

## Privacy and security

Quiz scoring is authoritative only on the backend. Correctness metadata is withheld until submission. Public-user authentication and a progress backend are intentionally absent. Local progress is educational and low sensitivity; visitors can inspect or clear it at any time.

## Future account migration (documentation only)

Article, quiz, question, option, and path slugs are stable identities. A future account feature can read the local v1 record after explicit consent, map each slug to its server record, and merge completion using set union. For quiz state, it can retain the better or latest result according to a documented policy. Successful upload should not silently remove local data; the visitor should be told what was merged and offered a clear-device option. Unknown or retired slugs should be ignored safely. No account or synchronization API is implemented in Phase 8.

