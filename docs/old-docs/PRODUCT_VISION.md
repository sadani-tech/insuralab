# PRODUCT_VISION.md — InsuraLab

## Product name

**InsuraLab**

## Primary message

> **"Understand insurance before you buy it."**

## Indonesian communication direction

> "Asuransi tidak harus rumit.
> Pahami risiko, kenali proteksi, dan pelajari cara kerjanya sebelum mengambil keputusan."

## Mission

Help people understand financial risks and how insurance protection works, **without aggressively directing them toward purchasing a particular product**.

InsuraLab builds trust through understanding. The measure of success is not a click on "Buy now" — it is a user who can explain, in their own words, what risk they are worried about and how a category of protection responds to it.

---

## Primary audiences

1. **People who know almost nothing about insurance.** They need plain language, concrete examples, and permission to ask "basic" questions.
2. **People considering buying insurance.** They need a framework for thinking about needs before they are in a sales conversation.
3. **People who already own insurance but want to understand their policy.** They need vocabulary, process maps, and "things to check".
4. **Insurance learners.** Students, new entrants, career changers.
5. **Insurance agents who want structured foundational knowledge.** They need an organised, source-aware reference — including the professional-conduct material.
6. **Developers / recruiters evaluating this software-engineering portfolio.** They need a codebase that demonstrates domain modelling, architectural judgement, testing discipline, and restraint.

---

## Core product areas

| Area           | Purpose                                                                                  |
|----------------|------------------------------------------------------------------------------------------|
| **LEARN**      | Structured educational articles: general concepts first, provider case studies second.  |
| **PROTECTION** | Protection categories (life, health, critical illness, disability, accident, income).    |
| **PROVIDERS**  | Provider-agnostic profiles. Allianz is the first case study, not the platform identity.  |
| **PRODUCTS**   | Educational product records, source- and date-aware. Never "buy this".                   |
| **GLOSSARY**   | Plain-language definitions of insurance terms, cross-linked to articles.                 |
| **QUIZ**       | Low-stakes self-check on concepts. Explanations on every option.                         |
| **ASSESS**     | *Protection Needs Assessment* — helps users discover which **topics** to learn about. Never "buy Allianz Product X". |
| **EXPLORE**    | Visual / interactive exploration of how protection relates to risk (Three.js later).    |

## Future section concepts (NOT separate brands or apps)

- **InsuraLab Learn** — the structured curriculum experience.
- **InsuraLab Pedia** — the reference/glossary experience.
- **InsuraLab Explore** — guided interactive exploration.
- **InsuraLab Verse** — the 3D "protection universe" visualization.

These are **sections/concepts inside the single InsuraLab product**. Do not spin up separate brands, repositories, or applications for them.

---

## Product philosophy — the user journey

The intended flow:

```
RISK
  ↓
UNDERSTANDING
  ↓
PROTECTION CATEGORY
  ↓
INSURANCE CONCEPT
  ↓
PROVIDER
  ↓
PRODUCT
```

**Not:**

```
PRODUCT
  ↓
BUY NOW
```

Every product area, navigation choice, and content template should reinforce the first flow. If a feature only makes sense in the second flow, it does not belong in InsuraLab.

---

## What InsuraLab is not

- Not a price-comparison or lead-generation site.
- Not a robo-advisor. It does not output "you should buy X".
- Not an agency recruitment funnel. Agency/compensation material exists only as **advanced knowledge content**.
- Not a source of legal, tax, or personalized financial advice.
- Not a live quote engine.

---

## Trust model (why this product can be believed)

1. **Separation of knowledge types.** General insurance knowledge is clearly distinct from provider-specific training references.
2. **Source awareness.** Provider/product content shows where it came from, the reference period, when it was last reviewed, and whether it is `current`, `historical`, or `needs_review`.
3. **Visible uncertainty.** When source material is inconsistent or outdated, InsuraLab says so rather than papering over it.
4. **No pressure.** No countdown timers, no "agents standing by", no dark patterns.

---

## Portfolio intent

InsuraLab is also a demonstration of engineering practice: a domain-aware content platform (not a blog CMS), a clean REST boundary between Laravel and Next.js, a source/versioning model for volatile domain data, tests around the parts that matter, and disciplined phase-by-phase delivery.
