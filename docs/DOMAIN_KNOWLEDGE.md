# DOMAIN_KNOWLEDGE.md — InsuraLab

This document is the accumulated insurance knowledge that InsuraLab is being built around at the project's current stage. It is **not** a curriculum list — it is the reference the data model, the seed content, and future editorial work all draw from.

Primary source for the Allianz case-study material: **`docs/Allianz_Basic_Training_Lengkap.pdf`** ("Allianz Basic Training – Dokumentasi Terstruktur", compilation dated **August 2026**, 26 pages, 6 modules + appendix). Page references below (`[p.N]`) point into that file.

---

## 0. Trust labels — how to read this document

Every non-trivial statement in InsuraLab content carries one of these classifications. They map directly to `articles.provider_id`, `reference_status`, and the `sources` system.

| Label | Meaning | Data handling |
|---|---|---|
| **GENERAL CONCEPT** | Insurance principle that is broadly true across providers and time. | `provider_id = NULL`. No reference period required. |
| **PROVIDER-SPECIFIC TRAINING EXAMPLE** | A figure, feature, or rule taken from a specific provider's training material. Illustrative, not authoritative. | `provider_id = <id>`, `reference_period` set, `reference_status = current` only if re-verified. |
| **HISTORICAL REFERENCE** | Provider material explicitly tied to a past period / superseded scheme. | `reference_status = historical`. Never rendered as current. |
| **DYNAMIC RULE** | A rule that changes over time by nature (benefit limits, ages, COR structure, commission rates, qualification thresholds). | `reference_status = needs_review` until checked against current official docs. |
| **NEEDS VERIFICATION** | Source material is internally inconsistent or ambiguous. | `reference_status = needs_review`; the inconsistency is shown, not resolved. |

**Golden rule:** never silently transform a HISTORICAL REFERENCE or DYNAMIC RULE into a current provider guarantee. The Basic Training document itself states it is *"Bukan pengganti dokumen resmi"* — not a substitute for official documents; real transactions must use the current policy, RIPLAY, illustration, underwriting guide, SOP, agency terms, compensation plan, and prevailing regulation. [p.2]

---

## MODULE 1 — Insurance & Allianz introduction

### 1.1 Allianz as a global group — GENERAL CONCEPT + PROVIDER-SPECIFIC

Allianz is a global financial-services group operating in risk protection, insurance, and financial services. For an Indonesian audience, the crucial understanding is **which legal entity issues the policy**, because the entity named on the contract is the party that carries the risk. [p.3]

### 1.2 The three Indonesian entities must not be conflated — PROVIDER-SPECIFIC

| Entity | Focus (per training) |
|---|---|
| **Allianz Life Indonesia** | Life, health, and individual/family financial-protection solutions. |
| **Allianz Utama Indonesia** | General insurance — motor, property, business, travel, and other non-life risks. |
| **Allianz Life Syariah Indonesia** | Life protection and financial-risk management on sharia principles. |

> In InsuraLab data, these are **three separate `providers` rows** (or one provider with an entity attribute) — never merged into a single "Allianz". A product's issuing entity determines who bears the contractual risk.

### 1.3 Solution portfolio — PROVIDER-SPECIFIC TRAINING EXAMPLE [p.3]

| Category | General purpose | Examples named in the material |
|---|---|---|
| Life | Family financial continuity and legacy | SmartLink Protection Life, Allianz PASTI, LegacyPro |
| Health | Reduce impact of hospitalisation / medical costs | Allianz Flexi Medical Plan, Individual Health |
| Sharia | Protection on mutual-help principles and sharia fund management | AlliSya Protection Life, Allisya Aman, Legacy Max Syariah |
| Rider | Add protection layers to a base policy | Flexi CI, TPD, ADDB, Term Life, Payor |
| General | Protect non-life assets and activities | Motor, home, business, travel |
| Pension | Support retirement-income planning | Pension programs per entity/product |

### 1.4 Allianz Star Network (ASN) — PROVIDER-SPECIFIC

ASN is the **agency distribution and business-development channel** of Allianz Life. In the training it is the context for the marketer career path, the BE/BP structure, unit / agency / group agency, and the compensation model. [p.3]

**Key principle:** the insurance company (policy issuer) is distinct from the distribution network. ASN is an agency channel; the policy is still issued and administered by the insurance-company entity named in the contract. [p.4] → This is exactly why InsuraLab's model is **provider-agnostic**: distribution ≠ underwriter.

### 1.5 What an insurance agent may and may not do — GENERAL CONCEPT [p.4, p.11, p.17]

**An agent MAY:** perform needs analysis and identify the protection gap; explain products (benefits, costs, risks, exclusions, coverage term, customer obligations) in a balanced way; assist the application; help ensure application-data quality; deliver and explain policy information; assist servicing; guide the customer during claims.

**An agent does NOT:** make final underwriting decisions; make final claim decisions. Those sit with the company, per its authority and the policy.

**Core professional values:** honesty (no overstating benefits, no hiding risks), competence (product/process/authority limits), customer focus (recommendation starts from need and affordability), compliance (code of conduct, regulation), responsibility (present across the whole policy lifecycle, not just at closing). [p.4]

### 1.6 Insurance is a trust-based business — GENERAL CONCEPT [p.4]

The customer buys a promise of protection that may not be used for many years. Business quality is therefore measured not by policies issued but by whether the product **fits the need, is understood, is sustainably maintained, and works per the contract when the risk occurs**.

> Journey framing from the material: **Need → Trust → Policy → Service → Claim experience.** This is the same spine as InsuraLab's `RISK → UNDERSTANDING → PROTECTION → CONCEPT → PROVIDER → PRODUCT`.

---

## MODULE 2 — Insurance fundamentals

### 2.1 Risk and the financial impact of risk — GENERAL CONCEPT [p.5]

Insurance transfers or shares the **financial impact** of uncertain risk. Major personal risks:

| Risk | Financial impact | Protection goal |
|---|---|---|
| Death | Household income stops; fixed obligations continue | Income-replacement fund for the family |
| Critical illness | Medical costs, work interruption, recovery needs | Lump-sum benefit to support cash flow / income replacement |
| Medical expenses | Savings/assets eroded by hospital bills | Reduce the burden of eligible treatment costs |
| Total permanent disability | Lives longer but earning capacity is gone | Fund for living costs and income continuity |
| Accident | Death or loss of bodily function | Additional accident benefit per policy |

### 2.2 Life-insurance roles — GENERAL CONCEPT [p.6]

- **Policyholder** — owns the contract, exercises its rights/obligations.
- **Insured** — the person whose risk is covered.
- **Beneficiary** — the party entitled to the benefit per the policy.
- **Premium** — contractual payment to obtain protection.
- **Sum assured (UP, Uang Pertanggungan)** — the benefit value per policy terms.

### 2.3 Human Life Value (HLV) — GENERAL CONCEPT (simplified) + NEEDS CONTEXT [p.5]

HLV is a **simple starting-point** approach to estimating a person's economic value to their family.

> **Training simplified formula:** annual income × remaining productive years.
> **Training example:** Rp180,000,000 / year × 20 years = **Rp3,600,000,000**.

InsuraLab must always frame this as a starting point only. Real financial planning also considers living expenses, debt, assets, inflation, children's education, and protection already held. **Do not imply that a person's life literally has a monetary price** — HLV estimates the *economic loss to dependants*, not the value of a life.

### 2.4 Life insurance's purpose — GENERAL CONCEPT [p.5]

To help replace the **economic loss** arising from lost earning capacity or a covered life risk — not to "replace" a person.

### 2.5 Sharia insurance — GENERAL CONCEPT [p.6]

Core idea: **mutual help and risk sharing (risk sharing)**, not risk transfer to a company. Participants contribute to a **tabarru' fund** to help other participants who suffer a covered risk; the company manages the fund per the `akad` (contract) and sharia rules.

Four key concepts from the material:
1. **Risk sharing** — risk borne collectively by participants via the tabarru' fund.
2. **Akad / contracts** — a commercial/management contract (`tijarah`) and the `tabarru'` contract.
3. **Contribution management** — allocation and management of funds explained from the outset per the akad.
4. **Underwriting surplus / deficit** — the difference between tabarru'-fund income and outgo in a period, managed per product and sharia rules.

Related terms InsuraLab glossary must carry: `ta'awun` (mutual assistance), `ujrah` (fee), `wakalah bil ujrah` (agency-for-a-fee management contract), `surplus underwriting`, `underwriting deficit`.

General characteristics discussed: **universal, fair, transparent, flexible, mutual assistance.** [p.6]

> Editorial rule: distinguish **conventional risk transfer** from **sharia risk sharing** every time both appear.

### 2.6 Unit link / PAYDI — GENERAL CONCEPT + consumer-warning emphasis [p.6–7]

**PAYDI** = Produk Asuransi yang Dikaitkan dengan Investasi (insurance product linked to investment). It combines **insurance protection + an investment component**.

Premium / contribution flow: `premium/contribution → costs & protection → investment allocation → unit value → policy benefit`.

Important concepts: investment value, fees, **cost of insurance**, withdrawal, switching, premium holiday, policy sustainability.

**Consumer warnings that must always accompany PAYDI content [p.7]:**
- Investment value is **not guaranteed** unless the product contract explicitly provides a guarantee.
- Unit value can **go down**; it is not a deposit.
- Insurance and other fees can reduce policy value.
- If investment value is insufficient to pay charges, the policy may need extra payments or may not be sustainable.
- A **premium holiday is not free protection** — charges can still be deducted from available value.
- **Withdrawal** can reduce investment value and policy sustainability.

---

## MODULE 3 — Ethics, legal & compliance

All items GENERAL CONCEPT unless noted. Regulation changes → treat specifics as **DYNAMIC RULE**; the material itself flags that Indonesian tax-information-access rules were updated in 2026. [p.10]

### 3.1 Ethical foundation [p.8]
Insurance depends on trust: the customer hands over identity, health, financial, life-goal, and payment data. The sales process must be **legal, ethical, fair, transparent, accountable**. Principles: fair & respectful, integrity (do right when unobserved), transparency & truth (accurate, balanced, non-misleading), ownership & responsibility (for application quality and service).

### 3.2 Sales-conduct risks [p.8]

| Risk | Meaning |
|---|---|
| **Mis-selling** | Selling a product that does not match the customer's need/ability. |
| **Misrepresentation** | Giving a picture, promise, or information inconsistent with reality/product documents. |
| **Churning** | Pushing policy replacement **within the same company** without adequate analysis/explanation, especially for the agent's gain. |
| **Twisting** | Pushing replacement of **another company's** policy improperly. |
| **Pooling** | A sale made by one agent recorded under another agent for qualification/commission. |
| **Sales manipulation** | Engineering transactions for contests, awards, targets, or promotion. |
| **Misuse of customer funds** | Using, withholding, or diverting premium/benefit money. |
| **Improper rebate / inducement** | Improper discounts/commission to force a purchase. |

### 3.3 Fraud awareness [p.9]
Fraud = intentional acts for gain or to cause loss via deception, manipulation, falsification, asset misuse, or concealment.

**Framework:** Prevention → Detection → Investigation / Reporting / Sanctions → Monitoring / Evaluation / Follow-up.
**Fraud triangle:** Pressure + Opportunity + Rationalization. Good prevention reduces opportunity, strengthens controls, builds an integrity culture.
Agent's duty on a fraud indication: **do not self-investigate**; document as needed and use the official reporting channel.

### 3.4 Anti-bribery & corruption (ABC) [p.9]
Bribery = **anything of value** offered/given/requested/received to improperly influence a decision — money, gifts, vouchers, travel, entertainment, facilities, secret commissions, other advantages.
- Gifts/entertainment must not be excessive or intended to influence business decisions.
- Dealings with **government officials** / decision-authorities need heightened care.
- **Conflicts of interest** must be recognised, disclosed, managed.
- If in doubt, don't decide alone — use the escalation/approval channel.

### 3.5 AML / APU / PPT / PPPSPM [p.9]
- **APU** = Anti Pencucian Uang (anti-money-laundering).
- **PPT** = Pencegahan Pendanaan Terorisme (counter-terrorist-financing).
- **PPPSPM** = Pencegahan Pendanaan Proliferasi Senjata Pemusnah Massal (counter-proliferation financing).

**Money-laundering phases:** Placement → Layering → Integration.
**KYC / CDD / EDD / Beneficial Owner / PEP:** Identify → Verify → Understand → Monitor.
- **KYC** — identity, occupation, source of income, transaction purpose, payer, beneficiary.
- **CDD** — identification, verification, monitoring against the customer profile. **EDD** — enhanced due diligence for higher risk.
- **Beneficial owner** — the party that truly owns/controls the benefit or structure.
- **PEP** — person with a prominent public function. **PEP is a risk classification and does not automatically imply wrongdoing**; it may require EDD.

### 3.6 FATCA / CRS [p.10] — DYNAMIC RULE for technical specifics
Concern **tax residency**, **tax identification (TIN)**, and cross-jurisdiction reporting obligations. The agent must collect data per the form, not fill it by assumption, and **must never coach a customer to hide tax residency or TIN**.

### 3.7 Economic sanctions [p.10]
Sanctions can restrict transactions/relationships with certain individuals, companies, organisations, countries, or sectors. The agent's job is not to memorise every list but to give **accurate data**, not avoid screening, and **escalate** potential matches for review. No evasion.

### 3.8 Personal data protection [p.10]
Agents handle highly sensitive data (ID, bank account, family info, health data, medical-exam results, financials). Access ≠ permission to use for other purposes.
Principles: **data minimization** (collect only what is needed), **purpose limitation** (use only for the stated lawful purpose), **security** (safe storage/transmission channels), **authorized disclosure** (share only with authorised parties), **appropriate retention** (follow retention/destruction policy).

### 3.9 Antitrust / competition [p.10]
No **price fixing**, **market allocation**, **bid rigging**, or **inappropriate exchange of competitively sensitive information** with competitors.

### 3.10 Confidentiality [p.10]
Broader than personal data: also internal information — strategy, pricing, databases, reports, product launches, systems, trade secrets. Share only on **need-to-know** with authorisation.

### 3.11 Field underwriting [p.10, p.17]
The agent is the **initial information source** for underwriting: gather **material information honestly, completely, accurately**. The agent does **not** decide acceptance/rejection.
**Prohibited:** blank forms; signing for the customer; hiding asked-about medical history; promising underwriting approval.

### 3.12 Social-media communication [p.11]
When a personal account is used to market products or identify as an Allianz marketer, compliance still applies. Do not post: confidential information, customer data, internal materials, or product claims inconsistent with official material.

### 3.13 Customer protection [p.11]
Applies across the **full lifecycle**: advertising, prospecting, needs analysis, recommendation, application, underwriting, issuance, servicing, claim, complaint, termination/maturity.
Chain: Fair treatment → Transparency → Suitability → Affordability → Customer understanding.

### 3.14 Decision check + escalation [p.11]
1. Is it legal? 2. Is it allowed (company/AAJI policy)? 3. Is it fair to the customer? 4. Would I be comfortable if this became public?
When uncertain: **STOP → ASK → ESCALATE.** Never guess or try to game a control.

---

## MODULE 4 — Product knowledge

**Teach product TYPES first, then provider examples.** Names, features, plans, benefit limits, ages, and availability change; actual explanations must reference the current RIPLAY and policy. [p.12]

### 4.1 Base policy vs rider — GENERAL CONCEPT [p.12]
The **base policy** is the main contract. A **rider** is additional coverage attached to a compatible base policy. Not every rider fits every product; availability depends on **product rules, age, underwriting, channel, and company terms**.

### 4.2 Risk → solution map — PROVIDER-SPECIFIC TRAINING EXAMPLE [p.12]

| Risk | Solutions discussed |
|---|---|
| Medical / hospitalisation cost | Allianz Flexi Medical Plan (AFMP) |
| Critical illness | CI Plus / CI Accelerated / Flexi CI |
| Premium payer suffers a risk | Payor Protection / Payor Benefit (and spouse versions) |
| Total permanent disability | TPD |
| Accidental death/disablement | ADDB |
| Additional life cover | Term Life |

### 4.3 AFMP (Allianz Flexi Medical Plan) — PROVIDER-SPECIFIC [p.12]
Medical-expense protection. Pays on an **indemnity / reimbursement** basis: eligible medical expense, plan limits, **deductible / risk sharing**, **co-share**, policy terms.
- **Deductible** — customer bears part of the cost first, per plan.
- **Co-share** — customer bears a percentage of eligible cost where the mechanism applies.
- **Cashless does NOT necessarily mean zero out-of-pocket** — excess, deductible, or non-eligible costs can remain.

### 4.4 CI Plus vs CI Accelerated — PROVIDER-SPECIFIC [p.13]
Both pay a **lump sum** per policy definition for critical illness.
- **CI Plus** — does **not** reduce the base life sum assured (per the discussed training concept).
- **CI Accelerated** — **reduces** the base life sum assured, because it pays part of the future base benefit early ("accelerated").

### 4.5 Flexi CI — PROVIDER-SPECIFIC TRAINING EXAMPLE / DYNAMIC RULE [p.13–14]

Pays on **diagnosis** of a critical condition per plan. Three tiers:

| Benefit group | Silver | Gold | Platinum |
|---|---|---|---|
| Advanced CI | 77 | 77 | 77 |
| Catastrophic CI | 6 | 6 | 6 |
| Diabetes complications | 4 | 4 | 4 |
| Angioplasty | 1 | 1 | 1 |
| Early CI | – | 30 | 60 |
| Intermediate CI | – | 10 | 20 |
| **Total conditions** | **88** | **128** | **168** |

The material shows total benefits **up to ~150% of Sum Assured** depending on claim structure and terms. **150% does NOT mean one diagnosis automatically pays 150%** — payout depends on stage, condition, prior benefits, limits, and product terms. [p.13]

**Feature references (DYNAMIC RULE — verify against current illustration/RIPLAY):** [p.13]
- Entry age: **1 month – 70 years** (nearest birthday).
- Protection: **up to age 100** per terms.
- Elimination period: **80 days** from rider effective date.
- Survival period: **0 days**.
- Underwriting: **full underwriting**, following the base policy.
- Currency: **IDR**.

**Benefit references (DYNAMIC RULE):** [p.14]

| Category | Benefit | Training-shown maximum |
|---|---|---|
| Advanced CI | 100% SA | Rp5,000,000,000 |
| Catastrophic CI | additional 20% SA | — |
| Diabetes complication | additional 20% SA | Rp200,000,000 |
| Angioplasty | additional 10% SA | Rp200,000,000 |
| Early CI | 50% SA | Rp1,250,000,000 |
| Intermediate CI | 100% SA | Rp2,500,000,000 |

> These figures are **PROVIDER-SPECIFIC TRAINING REFERENCES**. Store and display them as date/version-aware (`reference_status = needs_review` until re-verified). Never present as permanently current.

**Cost of Rider (COR) — training concept — DYNAMIC RULE:** [p.14]
- Year 1: COR not deducted / deferred (charged in years 2–3 per the training scheme).
- Year 2: 100% current-year COR + 50% of the deferred first-year COR (≈150% concept).
- Year 3: 100% current-year COR + the remaining 50% of first-year COR.
- Year 4 onward: 100% current-year COR.
- **Consult current official product documentation before operational use.**

**Claim procedure — training reference — NEEDS VERIFICATION:** [p.14]
Complete the CI claim form + required documents; submit within the deadline in the policy/procedure — **a training screenshot shows 60 days from diagnosis** (also referenced as "within 60 days after diagnosis"). Treat the 60-day figure as a training reference to verify against current policy wording. Allianz then assesses completeness and claim-definition fulfilment; benefit paid if all conditions are met.

### 4.6 Payor riders — PROVIDER-SPECIFIC [p.14]

| Rider | Trigger | Objective |
|---|---|---|
| **Payor Protection** | Payer dies | Protect premium payment per rider terms |
| **Spouse Payor Protection** | Payer's spouse dies | Same objective |
| **Payor Benefit** | Payer suffers qualifying CI / TPD | Protect premium payment when ability to pay is impaired |
| **Spouse Payor Benefit** | Payer's spouse suffers qualifying CI / TPD | Same objective |

### 4.7 TPD (Total Permanent Disability) — GENERAL CONCEPT + PROVIDER context [p.14]
Lump-sum benefit when the insured suffers total permanent disability per policy definition, due to illness or accident. Distinct from temporary disability; about permanent loss of earning capacity. Distinguish **standard vs accelerated** structures where applicable.

### 4.8 ADDB (Accidental Death & Disablement Benefit) — GENERAL CONCEPT + PROVIDER context [p.14]
Covers accidental death, total permanent disablement, and partial permanent disablement. Partial-disablement benefits are paid by a **percentage schedule** per covered condition. Benefits depend on accident definitions and benefit schedules.

### 4.9 Term Life — GENERAL CONCEPT [p.14]
Additional death protection for a defined period. Useful for **income replacement**, covering **Human Life Value**, temporary obligations (debt, education), and protection top-up while the base SA is insufficient.

### 4.10 Reimbursement vs lump sum — GENERAL CONCEPT [p.15]

| Model | Example | How it works |
|---|---|---|
| Reimbursement / indemnity | AFMP | Pays on eligible cost, limit, deductible/co-share, terms |
| Lump sum / benefit | Flexi CI, TPD, Term Life | Pays on a trigger, based on SA / benefit percentage; **need not equal the hospital bill** |

### 4.11 Layered protection — GENERAL CONCEPT [p.15]
Layers: **life → medical → critical illness → disability → accident → payor.**
A comprehensive design does **not** mean every rider is maximised. Priority follows **need, protection gap, affordability, and long-term sustainability**.

---

## MODULE 5 — Business operation process

### 5.1 End-to-end flow — GENERAL CONCEPT [p.16]

```
Prospect → Need Analysis → Application → Underwriting → Inforce → Policy Delivery → Service
```

New business is not just closing. A need becomes protection only once the application is processed, underwriting is done, requirements are met, the policy is issued, received by the customer, and then serviced across the policy's life. [p.16]

### 5.2 Submission quality — GENERAL CONCEPT [p.16]
Target: **COMPLETE + ACCURATE + CONSISTENT** from the first submission.

**Training examples of submission delays (PROVIDER/OPERATIONAL — some are DYNAMIC RULE):** [p.16]
- Blurry/unreadable ID photo or signature.
- Missing required signature on ID/application.
- SPAJ signature does not match the ID card.
- SPAJ/proposal date past the shown validity limit (**training example: 30 days**).
- Proposal premium differs from the SPAJ.
- Health questions not completed.
- FATCA/CRS data incomplete.
- Agent declaration incomplete.
- Credit-card validity data on the SPAJ inconsistent with submitted documents.

Principle: **Right First Time** — check before submitting rather than rushing into pending/rework. Do not treat old validity timelines as permanently current.

### 5.3 Underwriting — GENERAL CONCEPT [p.16–17]

Flow: **Identify risk → Classify risk → Decision.**
Considerations: medical, lifestyle, occupation, hobbies, financial risk, moral hazard.

| Outcome | Meaning |
|---|---|
| Standard | Accepted on standard terms |
| Extra premium / rating | Accepted with an additional risk charge |
| Exclusion | A specific risk/condition is excluded |
| Modified benefits | Benefit / SA adjusted |
| Additional information required | Medical/financial report or clarification needed |
| Postpone | Decision deferred until the condition can be evaluated |
| Decline | Risk not acceptable per underwriting policy |

The agent is the **field underwriter / information source**, not the final underwriter. A medical requirement is not an automatic rejection — it means more information is needed. [p.17]

### 5.4 Inforce — GENERAL CONCEPT [p.17]
Submission and payment alone do not necessarily mean the policy is active. A policy becomes inforce once issuance requirements, underwriting, payment, and effective-date conditions are met per procedure.

### 5.5 Policy delivery — GENERAL CONCEPT + PROVIDER documents [p.17]
Not just handing over a document. The agent ensures the customer receives the contract and understands: identity, benefits, sum assured, riders, premium/contribution, coverage term, exclusions, endorsements, and — **for PAYDI** — investment costs, risks, and sustainability, plus the claim procedure.

| Training document | Function |
|---|---|
| **STP – Bukti Serah Terima Polis** | Records chain of custody during physical-policy distribution |
| **BPP – Bukti Penerimaan Polis** | Proves the policyholder received the policy, and the receipt date |
| **e-Policy** | Electronic policy document (policy schedule, terms, riders, endorsements, receipts, related docs) |

Physical-policy print/delivery workflows can change as e-Policy and digital services are adopted — use the current SOP. (HISTORICAL REFERENCE for physical workflows.)

### 5.6 Welcome call — GENERAL CONCEPT + internal-trigger caution [p.17]
A **quality-assurance / customer-confirmation** mechanism. May confirm the customer knows about the purchase, premium, product characteristics, the signing process, and the explanation received. Training also shows possible **commission hold** in certain conditions — treat such internal operational triggers as dynamic, not timeless policy.

### 5.7 Selling cycle — GENERAL CONCEPT [p.17]

```
Prospecting → Approaching → Fact Finding → Design Solution → Presentation → Closing
→ Policy Delivery → Servicing → Ask Referral → (back to) Prospecting
```

It is a circle: good service produces trust, referrals, and long-term relationships.

### 5.8 Licensing — GENERAL CONCEPT + HISTORICAL REFERENCE [p.18]
Insurance marketers must meet certification/licensing requirements per company, association, and regulation. **Historical training timelines for termination or licensing status must not be presented as universal/current rules.**

### 5.9 Service experience — GENERAL CONCEPT [p.18]
Topics: customer-data changes, beneficiary changes, payment method, policy servicing, annual policy review, claim assistance, complaint handling, policy sustainability.
**Claim is a "moment of truth."** Agents may assist; agents must **never guarantee a claim result**.

### 5.10 Annual policy review — GENERAL CONCEPT [p.18]
Consider: family changes; income, debt, assets, goals; sum-assured adequacy; beneficiary and contact data; premium status; for PAYDI, whether investment value and charges still support sustainability.

---

## MODULE 6 — Agency structure & compensation (ADVANCED PROVIDER-SPECIFIC KNOWLEDGE)

> **This module exists as KNOWLEDGE only.** Do **not** implement a compensation calculator. Do **not** turn InsuraLab into an agency-recruitment platform. Everything below is PROVIDER-SPECIFIC and mostly **DYNAMIC RULE** or **HISTORICAL REFERENCE**.

### 6.1 Hierarchy — PROVIDER-SPECIFIC [p.19]

| Term | Meaning |
|---|---|
| **BE – Business Executive** | Initial marketer level in the ASN structure |
| **BP – Business Partner** | Leadership level after meeting promotion/qualification rules |
| **Upline** | Party positioned above someone in the hierarchy |
| **Downline** | Party positioned below someone in the hierarchy |
| **Direct** | Immediate, one-level relationship |
| **Generation** | Depth of BPs within a growing organisation |

**Rank ≠ relationship.** A BP can be the downline of the BP above **and** the upline of BEs/BPs below. BE/BP is a *rank*; upline/downline is a *hierarchy relationship*. [p.19]

### 6.2 Unit / Agency / Group Agency — PROVIDER-SPECIFIC [p.19]

| Structure | Concept |
|---|---|
| **Unit** | A BP plus the BEs directly under their supervision |
| **Agency** | A unit that has grown, with BP generations below it |
| **Group Agency** | A wider structure spanning several generations and units |

Training depicts BP generations **G1 through G8**. [p.19]

### 6.3 Producer / leader development — PROVIDER-SPECIFIC [p.20]

```
Recruit → License → Train → Activate → Produce → Develop
```

Recruiting alone is not enough; a healthy organisation is built from licensed, active, productive, compliant marketers who can serve customers. Overriding is meant to be understood **together with coaching and supervision responsibility**.

### 6.4 Nett ALP — PROVIDER-SPECIFIC [p.20]
**Nett Annualized Life Premium.** A production/performance metric used in qualification and certain calculations. **Nett ALP is NOT agent income.** Its exact calculation follows the product definition and compensation plan — do not assume "premium × 12".

### 6.5 Persistency — PROVIDER-SPECIFIC / DYNAMIC RULE [p.20]
Measures business continuity. The material repeatedly uses **13-month persistency, minimum 80%** for certain promotion/maintaining rules. Treat exact rules as **provider-plan and period specific**. Persistency discourages selling premium beyond the customer's ability to pay.

### 6.6 BE → BP promotion — PROVIDER-SPECIFIC / DYNAMIC RULE [p.20]

**OPTION 1** [p.20]
- Personal Nett ALP: **minimum Rp300,000,000**
- 13-month persistency: **minimum 80%** (subject to training reference / calendar-year rules)

**OPTION 2** [p.20]
- Personal Nett ALP: **minimum Rp200,000,000**; maximum accumulation **12 months from joining** (per training)
- Minimum **2 direct BE**
- Combined direct-BE Nett ALP: **Rp100,000,000**; minimum per direct BE: **Rp50,000,000**
- 13-month persistency: **minimum 80%**

**OPTION 3 — NEEDS VERIFICATION (source is internally inconsistent)** [p.20]
- The training **text** indicates: Personal Nett ALP **Rp100,000,000**; at least **2 direct BE**; combined direct-BE Nett ALP **Rp100,000,000**; minimum each **Rp50,000,000**; persistency **80%**.
- The training **diagram** appears to show **5 direct BEs** with ALP roughly **50 / 50 / 30 / 35 / 35 million**.
- **Do NOT silently resolve this discrepancy.** Record it as: *NEEDS VERIFICATION AGAINST OFFICIAL COMPENSATION GUIDE.* This is a core InsuraLab trust principle — **source inconsistencies must remain visible.**

### 6.7 Maintaining BP — PROVIDER-SPECIFIC / DYNAMIC RULE [p.20]
- Nett Unit ALP: **minimum Rp300,000,000**
- 13-month persistency: **minimum 80%**
- Minimum **2 active direct BE**
- Training definition of "active BE": **6 cases/year**, OR the BE was promoted to BP.
- Treat exact maintaining criteria as period/provider specific.

### 6.8 Three compensation layers — PROVIDER-SPECIFIC [p.21]

| Layer | Conceptual source |
|---|---|
| **Base commission** | Personal eligible production (BE/BP) |
| **Overriding direct** | Eligible direct unit/downline production, subject to qualification |
| **Overriding generation** | Eligible deeper-generation production, subject to qualification/maintaining |

**Educational principle:** overriding is linked to **eligible insurance production** and compensation rules — it is **not simply payment for recruiting people**.

### 6.9 PAYDI base commission — HISTORICAL / PROVIDER-SPECIFIC RATES [p.21]

Products referenced: **SmartLink Protection Life** and **AlliSya Protection Life**.

| Year | Rate |
|---|---|
| Year 1 | 23.25% |
| Year 2 | 23.25% |
| Year 3 | 5% |
| Year 4 | 5% |
| Year 5 | 5% |
| Top Up / Single Premium | 3% |

Percentage is on the product's defined commission basis, **not** on the Sum Assured. Treat as historical/provider-specific training rates.

### 6.10 Legacy Pro (conventional) — HISTORICAL / PROVIDER-SPECIFIC [p.21]

| MPP | Non-annual Y1 | Non-annual Y2 | Annual Y1 | Annual Y2 |
|---|---|---|---|---|
| 5 years | 15% | 5% | 20% | 10% |
| 10 years | 20% | 10% | 25% | 15% |
| 15 years | 24.5% | 15% | 28% | 20% |

Training note: no regular-premium commission from Y3 onward.

### 6.11 Legacy Max (sharia) — HISTORICAL / PROVIDER-SPECIFIC [p.21]
Use sharia terminology: **contribution** (not premium).

| MPP | Non-annual Y1 | Non-annual Y2 | Annual Y1 | Annual Y2 |
|---|---|---|---|---|
| 5 | 15% | 5% | 20% | 10% |
| 10 | 20% | 10% | 25% | 15% |
| 15 | 24.5% | 15% | 28% | 20% |

Single contribution: training reference **6%**.

### 6.12 Allianz PASTI — HISTORICAL / PROVIDER-SPECIFIC [p.21]

| MPP | Y1 | Y2 | Y3 | Y4 | Y5 |
|---|---|---|---|---|---|
| 5 | 20% | 5% | – | – | – |
| 10 | 25% | 15% | 5% | 5% | 5% |
| 15 | 30% | 25% | 5% | 5% | 5% |
| 20 | 30% | 30% | 5% | 5% | 5% |
| = insurance term | 30% | 30% | 5% | 5% | 5% |

Training note: no regular commission from Y6 onward.

### 6.13 AlliSya Aman — HISTORICAL / PROVIDER-SPECIFIC [p.22]

| Contribution payment period | Y1 | Y2 | Y3 | Y4 | Y5 |
|---|---|---|---|---|---|
| 5 | 15% | 5% | – | – | – |
| 10 | 20% | 15% | 5% | 5% | 5% |
| 15 | 23.25% | 20% | 5% | 5% | 5% |
| 20 | 23.25% | 23.25% | 5% | 5% | 5% |
| = insurance term | 23.25% | 23.25% | 5% | 5% | 5% |

Training note: no regular commission from Y6 onward.

### 6.14 Individual Health — PROVIDER-SPECIFIC / DYNAMIC RULE [p.22]
Base commission: **15% of Gross Premium**. Subsequent years: **15% of Gross Premium while the policy renews** per terms. A renewal-based commission pattern, unlike fixed-commission-year products. Verify against the current plan.

### 6.15 FYC & SYC — PROVIDER-SPECIFIC [p.22]
- **FYC** — First Year Commission (basis for overriding calculations).
- **SYC** — Second Year Commission (basis for overriding calculations).
- **`55% × FYC` means 55% of the eligible First Year Commission — NOT 55% of the customer's premium.**

### 6.16 Overriding direct & unit persistency — PROVIDER-SPECIFIC / NEEDS VERIFICATION [p.22]

Detailed persistency table:

| Rate band | 13-month unit persistency | Year 1 | Year 2 |
|---|---|---|---|
| **Upper** | ≥ 85% | 60% × FYC | 60% × SYC |
| **Standard** | 80% to < 85% | 55% × FYC | 55% × SYC |
| **Lower** | < 80% | 50% × FYC | 50% × SYC |

Another summary training slide displays Year 2 differently (references to `55% × FYC` / `50%` next year) and a **Top Up / single premium** example of **12% × commission**. **Do not silently merge the inconsistent historical slides** — record both and require verification.

### 6.17 Overriding generation — PROVIDER-SPECIFIC / HISTORICAL / NEEDS VERIFICATION [p.22–23]

Standard rate: **Year 1 = 17% × FYC**, **Year 2 = 17% × SYC**. The generation-qualification screenshot is explicitly tied to a historical validation period (BP validation end-Dec 2021; new business blocks > 2022) — so this is a **scheme for a specific period**.

| Right up to | Total qualified direct BP | Nett Unit ALP | Standard rate Y1 / Y2 |
|---|---|---|---|
| G1 | 1 BP | Rp300,000,000 | 17% × FYC / 17% × SYC |
| G2 | 2 BP | Rp300,000,000 | 17% × FYC / 17% × SYC |
| G3 | 3 BP | Rp300,000,000 | 17% × FYC / 17% × SYC |
| G4 | 4 BP | Rp400,000,000 | 17% × FYC / 17% × SYC |
| G5 | 5 BP* | Rp500,000,000 | 17% × FYC / 17% × SYC |
| G6 | 5 BP* | Rp600,000,000 | 17% × FYC / 17% × SYC |
| G7 | 5 BP* | Rp700,000,000 | 17% × FYC / 17% × SYC |
| G8 | 5 BP* | Rp800,000,000 | 17% × FYC / 17% × SYC |

\* On the screenshot the "5 BP" figure appears merged across G5–G8 — **mark as requiring official-guide verification.**

### 6.18 Overriding generation maintaining factor — PROVIDER-SPECIFIC / DYNAMIC RULE [p.23]

| End-of-calendar-year Nett Unit ALP | OG payout | Approx. rate |
|---|---|---|
| ≥ Rp300,000,000 | 100% × OG rate | 17% × FYC / 17% × SYC |
| < Rp300,000,000 | 50% × OG rate | 8.5% × FYC / 8.5% × SYC |

This historical concept suggests that missing the Rp300m maintaining target may **reduce** generation overriding rather than necessarily eliminating every OG payment. **Do not generalise beyond the referenced plan/period.**

### 6.19 Conceptual compensation formulas — KNOWLEDGE ONLY [p.23]

```
Base Commission        = Eligible Premium / Contribution × Applicable Product Commission Rate

Overriding Direct      = Eligible FYC / SYC × Direct Override Rate
                         (affected by persistency, qualification, period rules)

Overriding Generation  = Eligible FYC / SYC × Generation Rate × Maintaining Factor
                         (subject to generation qualification and period rules)
```

**There is NO compensation calculator in the MVP.** If this material ever becomes an application feature, the rules must be **versioned** (product, commission scheme, effective date, hierarchy, qualification, production, persistency, calculation detail) so historical transactions do not change when the compensation plan is updated. [p.23]

---

## Appendix A — Consolidated glossary seed (see also `glossary_terms`)

ADDB, AFMP, ALP, APU/PPT/PPPSPM, base policy, beneficial owner, beneficiary, BE, BP, cashless, CDD/EDD, churning, CI, co-share, COR, critical illness, deductible, downline, elimination period, economic sanctions, exclusion, FATCA/CRS, field underwriter, Flexi CI, fraud triangle, FYC/SYC, generation, Human Life Value, indemnity, inforce, KYC, layering, lump sum, maintaining, mis-selling, misrepresentation, moral hazard, Nett ALP, overriding (direct/generation), PAYDI / unit link, PEP, persistency, policyholder, pooling, premium, premium holiday, Payor, reimbursement, rider, RIPLAY, risk sharing, risk transfer, sharia insurance, SPAJ, STP/BPP, sum assured (UP), surplus/deficit underwriting, ta'awun, tabarru', tijarah, TPD, twisting, ujrah, underwriting, upline, wakalah bil ujrah, welcome call.

## Appendix B — Dynamic items that MUST be verified (never freeze)

| Area | Dynamic items |
|---|---|
| Product | Product names, benefits, condition counts, SA limits, entry age, rider compatibility, charges |
| Operational | Submission-document age limits, policy-delivery method, welcome call, commission hold |
| Compliance | Regulation versions, internal policy, gift/entertainment thresholds, sanction screening |
| Agency | BP promotion criteria, active-BE definition, maintaining, persistency, ALP requirements |
| Compensation | Commission / OD / OG rates, generation qualification, top-up treatment, effective dates |

## Appendix C — Reference sources for validation [p.26]

- Allianz Basic Training material and the screenshots underlying this documentation.
- Current Allianz policies and product RIPLAY.
- Allianz Code of Conduct / applicable compliance policy.
- AAJI Standards of Practice and Code of Ethics for Life-Insurance Marketers.
- OJK provisions on anti-fraud, APU-PPT-PPPSPM, consumer protection, and insurance-business conduct.
- Personal Data Protection Law and applicable FATCA/CRS tax provisions.
- ASN Basic Agency Guidelines / Compensation Plan, latest version.

---

## How this maps to the data model

- Every **GENERAL CONCEPT** section → candidate general article (`provider_id = NULL`).
- Every **PROVIDER-SPECIFIC** section → provider article / product record with `provider_id`, a `source` row pointing at the Basic Training PDF (`reference_period = "Allianz Basic Training – compilation Aug 2026"`), and `reference_status`:
  - stable structural concepts (CI Plus vs Accelerated, Payor purpose) → `current` only after editorial re-verification, else `needs_review`;
  - all numeric tables (Flexi CI limits, COR, commission rates, qualification thresholds) → `needs_review`;
  - anything tagged HISTORICAL REFERENCE (period-bound commission tables, generation qualification) → `historical`.
- The **Option 3** and **overriding slide** inconsistencies → `reference_status = needs_review` with the conflict written into the article body and `sources.notes`.
