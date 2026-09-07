import type { Metadata } from "next";
import { Container, PageHeader } from "@/components/ui/container";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { REFERENCE_STATUS_CONFIG } from "@/components/domain/reference-status-badge";
import { ButtonLink } from "@/components/ui/button";

export const metadata: Metadata = {
  title: "Methodology",
  description:
    "How InsuraLab organises knowledge: general vs provider-specific, and how sources and review dates work.",
};

const KNOWLEDGE_TYPES = [
  {
    title: "General knowledge",
    body: "Insurance principles that hold broadly across providers and time — what risk is, how a critical-illness benefit typically works, the difference between reimbursement and a lump sum. These carry no provider and no reference period.",
  },
  {
    title: "Provider-specific knowledge",
    body: "Figures, features, or rules taken from one provider's material — for example how a particular rider is described in a training deck. These are always attributed, dated, and marked with a review status. They are illustrative, not authoritative.",
  },
];

export default function MethodologyPage() {
  return (
    <>
      <PageHeader
        eyebrow="Methodology"
        title="How InsuraLab organises what it teaches"
        lead="Trust comes from being explicit about where knowledge ends and a single company's material begins — and from never presenting a dated figure as timeless fact."
      />

      <Container className="flex flex-col gap-14 py-14">
        <section className="flex flex-col gap-5">
          <h2 className="text-xl font-semibold">Two kinds of knowledge, kept separate</h2>
          <p className="text-muted-foreground max-w-2xl">
            Every article on InsuraLab is one of two types. The distinction is built into the data
            model, the API, and the way pages are laid out — not left to the reader to guess.
          </p>
          <div className="grid gap-4 sm:grid-cols-2">
            {KNOWLEDGE_TYPES.map((item) => (
              <Card key={item.title}>
                <CardTitle>{item.title}</CardTitle>
                <CardDescription>{item.body}</CardDescription>
              </Card>
            ))}
          </div>
        </section>

        <section className="flex flex-col gap-5">
          <h2 className="text-xl font-semibold">Reference status</h2>
          <p className="text-muted-foreground max-w-2xl">
            Provider-specific content carries one of three review states. The badge is deliberately
            calm — <em>needs review</em> means &ldquo;verify against the official document&rdquo;,
            not &ldquo;danger&rdquo;.
          </p>
          <div className="grid gap-4 sm:grid-cols-3">
            {(
              Object.entries(REFERENCE_STATUS_CONFIG) as [
                keyof typeof REFERENCE_STATUS_CONFIG,
                (typeof REFERENCE_STATUS_CONFIG)[keyof typeof REFERENCE_STATUS_CONFIG],
              ][]
            ).map(([key, config]) => (
              <Card key={key}>
                <Badge tone={config.tone}>{config.label}</Badge>
                <CardDescription className="mt-2">{config.description}</CardDescription>
              </Card>
            ))}
          </div>
        </section>

        <section className="flex flex-col gap-4">
          <h2 className="text-xl font-semibold">Sources and review dates</h2>
          <ul className="text-muted-foreground flex max-w-2xl flex-col gap-2 text-sm">
            <li>
              Every provider-specific claim is linked to a reusable <strong>source</strong> record
              with a title, publisher, and reference period.
            </li>
            <li>
              Volatile items — benefit limits, ages, commission rates, qualification thresholds —
              default to <strong>needs review</strong> until an editor re-checks them against a
              current official document.
            </li>
            <li>
              Period-bound schemes (old commission tables, qualification tied to a past year) are
              marked <strong>historical</strong> and kept for teaching insurance history — never
              shown as current.
            </li>
            <li>
              When a source contradicts itself, both readings are written into the content and the
              conflict is recorded in the source notes. InsuraLab does not silently pick one.
            </li>
          </ul>
        </section>

        <section className="flex flex-col gap-3">
          <h2 className="text-xl font-semibold">Metodologi assessment</h2>
          <p className="text-muted-foreground max-w-2xl">
            Protection Needs Assessment memakai aturan deterministik untuk mengubah jawaban menjadi
            relevansi topik pembelajaran—bukan skor risiko, diagnosis, atau rekomendasi produk.
          </p>
          <ButtonLink
            href="/assessment/protection-needs/methodology"
            variant="secondary"
            className="self-start"
          >
            Baca metodologi assessment
          </ButtonLink>
        </section>

        <section className="flex flex-col gap-3">
          <h2 className="text-xl font-semibold">What this page is not</h2>
          <p className="text-muted-foreground max-w-2xl">
            InsuraLab is an independent educational project. It is not affiliated with any insurer,
            it does not sell or recommend products, and it does not provide personalized financial,
            legal, or tax advice. For a real decision, rely on the current policy wording, RIPLAY,
            and illustration, and speak to a licensed professional.
          </p>
        </section>
      </Container>
    </>
  );
}
