# Content coverage
Date: 12 November 2026.

## Persisted initial corpus
- 45 canonical glossary concepts with separate concise explorer labels/explanations.
- 85 explicit editorial reading connections. These are learning associations, not asserted causal rules.
- 15 editorial FAQ answers.
- 7 guides: six general checklists plus one document-reading case study.
- 7 source passages: six first-party topical notes and one local RIPLAY reading note.
- Two public source records: first-party notes and the public product-document reference.
- One canonical issuer and one product case-study record projected into the graph, plus two source nodes. Total initial explorer: 49 nodes and 87 connections.
- Existing detailed articles and glossary records remain stored. Records depending on nonpublic/unknown source rights are withheld from public APIs until reviewed.

The new explanatory text is not claimed as independently reviewed. UI labels identify it as introductory editorial material needing expert review. Product case information is needs_review, without invented review dates or current numerical benefit claims.

## Local corpus inventory and actual use
The reference folder contains 117 PDFs, 13 videos, 2 presentations, 1 spreadsheet, text/Markdown, and images. Inventory does not mean extraction, ingestion, public permission, or expert review.

A sample local RIPLAY, riplay-umum-asuransi-allianz-preferred-medical.pdf, pages 1–3, was read for document structure. SHA-256: 9A39ACBE9BC13731A92EBEF1C16FBEAB7D5092A47425E5FB852ED50857E519D3.
Its public product page is https://www.allianz.co.id/produk/asuransi-individu/asuransi-kesehatan/allianz-preferred-medical.html .
The app stores a short first-party reading note and public source link, not a copy of the PDF. The local version is not certified as the latest version.

Restricted playbooks, named personal illustrations, and unverified earnings/recruitment claims were not imported or used as public teaching evidence. Recording transcription, image extraction, spreadsheet ingestion, and bulk PDF imports were not performed.

## Where material is used
- /: map, aliases, topic/type filters, relation explanations, relevant guide and FAQ links.
- /tanya: stored answers and explicit related source links; AI unavailability leaves the answers usable.
- /panduan: seven readers and ordered checklists; checkbox state is only in the current page instance.
- /sumber: passage/version reader and official external page where appropriate.
- /admin: entries, relation evidence, passage versions, glossary explorer summary and rights management.

## Maintenance
Run python scripts/curate-knowledge.py to regenerate the authored JSON, then php artisan db:seed --class=KnowledgeSeeder from backend for first-time records. Imports preserve publication withdrawals and CMS-edited entry text. Existing data is not deleted. Editorial updates should be made through CMS, with a new passage version for changed cited evidence.

## Gaps
The rest of the reference folder remains unreviewed and unimported. Expert review of concepts, product-specific terms and dates, additional authorized source documents, and richer agent templates are subsequent editorial work. General guides are reusable educational scaffolding rather than reproductions of internal sales scripts.
