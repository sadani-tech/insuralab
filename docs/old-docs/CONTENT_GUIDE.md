# CONTENT_GUIDE.md — InsuraLab

How educational content is written for InsuraLab. Applies to articles, glossary terms, provider/product descriptions, quiz explanations, and assessment copy.

---

## 1. Voice & tone

| Do | Don't |
|---|---|
| Clear, neutral, educational | Salesy, urgent, fear-driven |
| Consumer-friendly, plain language | Jargon-heavy, corporate |
| Non-aggressive, patient | "Buy now", "limited time", "agents standing by" |
| Honest about uncertainty | Overstate certainty to sound authoritative |
| Respectful of the reader's intelligence and their budget | Condescending or pushy |

InsuraLab explains; it does not persuade toward a purchase. If a sentence only makes sense as a nudge to buy, cut it.

---

## 2. Jargon rule

- Avoid unnecessary insurance jargon.
- When a term is genuinely needed, **explain it on first use** and link it to its `glossary_term`.
- Prefer the everyday word: "payout" before "indemnity", "the person covered" before "the insured" (then introduce the precise term once).
- Indonesian terms (SPAJ, UP, RIPLAY, tabarru') are defined on first use and kept in the glossary.

---

## 3. Content hierarchy (article template)

Every explanatory article should move through these beats, in this order:

1. **Concept** — what it is, in one or two plain sentences.
2. **Example** — a concrete, everyday scenario (use rounded, clearly hypothetical numbers).
3. **Common misconception** — the thing people usually get wrong, stated and corrected.
4. **Things to check** — what a reader should look at in a real policy / illustration / their own situation.
5. **Source** — where provider-specific claims come from, with reference period and review status.

General-knowledge articles may keep section 5 short ("general insurance principle"); provider-specific articles must complete it fully.

---

## 4. General vs provider-specific — always distinguish

Two phrasings, never blurred:

- **General:** "Insurance generally works like…", "A critical-illness benefit is typically a lump sum…"
- **Provider/product:** "This provider's training material states…", "Per the [reference], as of [period]…"

Rules:
- A provider-specific claim never appears without attribution.
- Never write a provider's historical training figure as a present-tense fact ("Flexi CI covers 168 conditions" → **no**; "The training material (Aug 2026 compilation) lists 168 conditions for the Platinum tier; verify against the current RIPLAY" → **yes**).
- If general and provider content sit in the same article, they are visually and grammatically separated.

---

## 5. Provider content requirements

Every provider-specific article/record must carry:

| Field | Example |
|---|---|
| **Source** | "Allianz Basic Training – Dokumentasi Terstruktur" (linked `sources` row) |
| **Reference period** | "Compilation dated August 2026" |
| **Last reviewed date** | `last_reviewed_at` — when an editor last checked it |
| **Reference status** | `current` / `historical` / `needs_review` |

- Default new provider content to `needs_review` until an editor verifies it.
- Numeric tables (benefit limits, ages, commission rates, qualification thresholds) are `needs_review` unless re-verified against a current official document.
- Period-bound schemes (old commission tables, generation qualification tied to 2021–2022) are `historical`.

---

## 6. Handling uncertain or conflicting sources

- If the source is internally inconsistent (e.g. Basic Training "Option 3": text says 2 direct BE, diagram shows 5), **show the conflict**. Write both readings, then: *"This inconsistency is unresolved and must be verified against the official compensation guide."*
- Never pick one interpretation silently to make the copy cleaner.
- Put the same note in `sources.notes` so it surfaces in the CMS.

---

## 7. Numbers & money

- Use `Rp` with grouping: `Rp180,000,000` or `Rp180 juta` in Indonesian copy — be consistent within a piece.
- Label every illustrative figure as hypothetical: "Suppose someone earns Rp180,000,000 a year…"
- Never present HLV or any formula output as "how much your life is worth" — it estimates **economic loss to dependants**.
- Round example numbers. Precision implies authority the example doesn't have.

---

## 8. What content must never do

- Recommend a specific product to buy ("you should get Allianz Flexi CI").
- Give personalized financial, legal, or tax advice.
- Imply a claim outcome is guaranteed.
- Present agency/compensation material as a recruitment pitch or an earnings promise.
- Use countdown timers, false scarcity, or manufactured urgency.
- State a dynamic figure without a date and a review status.

---

## 9. Accessibility & structure (writing-side)

- Short paragraphs, one idea each. Front-load the point.
- Descriptive headings (a reader scanning headings should get the gist).
- Meaningful link text — never "click here".
- Define acronyms once, then use them.
- Tables for comparisons; prose for reasoning.

---

## 10. Glossary term template

- **term** — the canonical term (Title Case for names, lower case for concepts).
- **short_definition** — one sentence, tooltip-length, no jargon.
- **definition** — 2–5 sentences; may include one short example and a "not to be confused with" note.
- **category** — e.g. `general`, `sharia`, `product`, `process`, `compliance`, `agency`.
- **related** — link 2–4 related terms.

---

## 10b. Article Markdown directives (Phase 5+)

Article bodies are Markdown (GFM) plus a fixed set of block directives that the
frontend renders as educational callouts. Author them by hand — the app never
auto-generates them.

| Directive | Use |
|---|---|
| `:::definisi` | The concept in one or two plain sentences. |
| `:::penting` | A caveat the reader must not miss. |
| `:::contoh` | A concrete, clearly hypothetical scenario. |
| `:::kesalahpahaman` | "Kesalahpahaman umum" — the wrong belief, then the correction. |
| `:::catatan-provider` | Marks provider-specific content; pairs with review metadata. |
| `:::referensi-historis` | Period-bound material, kept for history, never "current". |
| `:::perlu-verifikasi` | Provider figure not yet re-checked against an official document. |

Inline glossary reference: `:istilah[teks]{slug=underwriting}` — renders a tooltip
with the term's short definition and a link to `/glossary/underwriting`. Only add
one where it genuinely helps a first-time reader, and list the same slugs in the
seeder's `glossary` array so the link is stored, not guessed.

Structured "Poin Penting" go in the article's `key_takeaways` (a JSON list), not
in the body.

## 11. Reading level & length

- Target a general adult reader with no insurance background.
- Explanatory article: ~400–900 words. Overview/hub article: shorter.
- `estimated_reading_minutes` ≈ words ÷ 200, rounded up.
- `level`: `beginner` (no prerequisites), `intermediate` (assumes the beginner set), `advanced` (agency/compensation, sharia fund mechanics, PAYDI cost structure).

## 12. Assessment copy

- Ask about financial impact and learning interest, never medical diagnosis, actuarial risk, or underwriting eligibility.
- Prefer broad ranges; never request exact salary, balance, debt, identity, contact, employer, or policy data.
- “Tidak tahu” indicates an opportunity for foundational learning and must not receive the maximum impact.
- Reasons describe what the visitor told us without diagnosing adequacy: “penghasilanmu berperan penting,” not “kamu underinsured.”
- BPJS, employer benefits, private coverage, and overlapping coverage are acknowledged neutrally. Encourage understanding benefits, limits, networks, exclusions, claims, and sustainability.
- Use `Fondasi Pembelajaran`, `Layak Dieksplorasi`, and `Sangat Relevan`. Never use danger, severe, at risk, deficit, or insurance/risk score.
- Every result links to general concepts first. Provider and product names do not belong in assessment reasons or primary recommendations.

## 13. Quiz questions and explanations

- Test understanding and useful distinctions, not obscure memorization, trick wording, or exact provider figures.
- A question must have one unambiguously best answer. Rewrite it if two options could reasonably be correct.
- Every option needs an explanation. Begin correct feedback with “Tepat.” and incorrect feedback with “Belum tepat.”; never shame the learner.
- Explain the concept, not merely that a different option was correct. Attach one or more general articles when a misunderstanding has a useful review destination.
- Provider-specific quiz content, if added later, must name the provider, reference period, and review status. Historical commission rates are not suitable prominent quiz material.
- PAYDI questions must reinforce investment fluctuation, charges/COI, withdrawal effects, and policy sustainability. Never imply a premium holiday is free insurance.
- Operations questions must distinguish agent assistance from insurer authority: agents do not decide underwriting or final claim approval.
- Scores describe current understanding only. Never describe a passing score as certification, expertise, or purchase readiness.

## 14. Learning paths

- Paths provide a suggested sequence; they never make an article inaccessible.
- Use stable relational article/quiz selections and intentional ordering. Do not paste arrays of slugs into prose or metadata.
- Titles and notes should explain why a step follows the previous one.
- Article completion is explicit. Do not claim completion because a page was opened or scrolled.
- “Next” recommendations must point to a named lesson or quiz, not generic “read more” text.
