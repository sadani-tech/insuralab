import Link from "next/link";
import { Container, Section, SectionHeader } from "@/components/ui/container";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { ButtonLink } from "@/components/ui/button";
import { Reveal } from "@/components/ui/reveal";
import { ProtectionConstellation } from "@/components/marketing/protection-constellation";
import { HomeGlossaryPeek } from "@/components/marketing/home-glossary-peek";
import { KnowledgeCard } from "@/components/domain/knowledge-card";
import { EducationalDisclaimer } from "@/components/domain/educational-disclaimer";
import { getFeaturedArticles, getProtectionCategories } from "@/lib/api";

export const revalidate = 300;

async function safe<T>(promise: Promise<T>, fallback: T): Promise<T> {
  try {
    return await promise;
  } catch {
    return fallback;
  }
}

const RISKS = [
  {
    title: "Medical cost",
    body: "Hospital bills that erode savings faster than they can be rebuilt.",
  },
  {
    title: "Loss of income",
    body: "Household earnings stop, but rent, food, and school fees do not.",
  },
  {
    title: "Critical illness",
    body: "A serious diagnosis brings treatment costs and time away from work.",
  },
  { title: "Death", body: "Dependants lose years of the income they were counting on." },
  { title: "Disability", body: "Earning capacity ends, while living costs continue for decades." },
  {
    title: "Accident",
    body: "A sudden injury with costs the everyday budget was never sized for.",
  },
];

const ENTRY_POINTS = [
  { label: "Saya baru mengenal asuransi", href: "/learn", hint: "Start from what risk is." },
  {
    label: "Saya ingin memahami jenis proteksi",
    href: "/protection",
    hint: "Life, health, CI, disability, accident.",
  },
  {
    label: "Saya ingin memahami polis saya",
    href: "/learn",
    hint: "Vocabulary and things to check.",
  },
  {
    label: "Saya ingin mengenal provider dan produk",
    href: "/providers",
    hint: "Case studies, kept independent.",
  },
];

const JOURNEY = ["Risiko", "Proteksi", "Konsep asuransi", "Provider", "Produk"];

export default async function HomePage() {
  const [featured, protectionCategories] = await Promise.all([
    safe(getFeaturedArticles(3), []),
    safe(getProtectionCategories(), []),
  ]);

  return (
    <>
      {/* 1 — HERO */}
      <Section className="border-border bg-surface-muted/40 border-b pt-16 pb-14 sm:pt-20">
        <Container className="grid items-center gap-12 lg:grid-cols-[1.1fr_0.9fr]">
          <div className="flex flex-col gap-6">
            <span className="text-accent font-mono text-xs font-semibold tracking-[0.14em] uppercase">
              Platform edukasi asuransi · independen
            </span>
            <h1 className="text-4xl font-semibold sm:text-5xl">
              Pahami asuransi sebelum memilih proteksi.
            </h1>
            <p className="text-muted-foreground max-w-xl text-lg">
              Asuransi tidak harus rumit. Pelajari risiko, jenis perlindungan, produk, dan cara
              kerjanya dengan penjelasan yang lebih sederhana.
            </p>
            <div className="flex flex-wrap gap-3">
              <ButtonLink href="/learn" size="lg">
                Mulai Belajar
              </ButtonLink>
              <ButtonLink href="/protection" size="lg" variant="secondary">
                Kenali Jenis Proteksi
              </ButtonLink>
            </div>
            <p className="text-muted-foreground text-sm">
              We don&apos;t tell people what to buy. We help people understand what they are
              protecting.
            </p>
          </div>
          <div className="border-border bg-surface overflow-hidden rounded-[var(--radius-lg)] border">
            <ProtectionConstellation className="aspect-[9/5]" />
            <p className="text-muted-foreground border-border border-t px-4 py-2 text-xs">
              A placeholder for the interactive Protection Universe — arriving in a later phase.
            </p>
          </div>
        </Container>
      </Section>

      {/* 2 — WHY INSURANCE EXISTS */}
      <Section>
        <Container>
          <SectionHeader
            eyebrow="Why insurance exists"
            title="Life carries financial risks"
            lead="Insurance helps manage the financial impact of some of those risks, according to the terms of a policy. It starts with naming the risk."
          />
          <Reveal className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {RISKS.map((risk) => (
              <Card key={risk.title} className="h-full">
                <CardTitle>{risk.title}</CardTitle>
                <CardDescription>{risk.body}</CardDescription>
              </Card>
            ))}
          </Reveal>
        </Container>
      </Section>

      {/* 3 — LEARNING ENTRY POINTS */}
      <Section className="bg-surface-muted/40">
        <Container>
          <SectionHeader eyebrow="Where to start" title="Pick the door that fits you" />
          <div className="mt-8 grid gap-4 sm:grid-cols-2">
            {ENTRY_POINTS.map((entry) => (
              <Link
                key={entry.label}
                href={entry.href}
                className="border-border bg-surface hover:border-border-strong flex flex-col gap-1 rounded-[var(--radius)] border p-5 transition-colors"
              >
                <span className="font-medium">{entry.label}</span>
                <span className="text-muted-foreground text-sm">{entry.hint}</span>
              </Link>
            ))}
          </div>
        </Container>
      </Section>

      {/* 4 — PROTECTION CATEGORIES */}
      <Section>
        <Container>
          <SectionHeader
            eyebrow="Protection categories"
            title="Five ways a risk hits a household's money"
            lead="These are concepts, not products. Each one describes a risk and the kind of protection that responds to it."
          />
          <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {(protectionCategories.length
              ? protectionCategories
              : [
                  { id: -1, name: "Life", short_description: "Replacing lost household income." },
                  {
                    id: -2,
                    name: "Health",
                    short_description: "Reducing the impact of medical costs.",
                  },
                  {
                    id: -3,
                    name: "Critical Illness",
                    short_description: "A lump sum on a serious diagnosis.",
                  },
                  {
                    id: -4,
                    name: "Disability",
                    short_description: "Support when earning capacity ends.",
                  },
                  {
                    id: -5,
                    name: "Accident",
                    short_description: "Benefit for accidental injury or death.",
                  },
                ]
            ).map((category) => (
              <Card key={category.id} className="h-full">
                <CardTitle>{category.name}</CardTitle>
                <CardDescription>{category.short_description}</CardDescription>
              </Card>
            ))}
          </div>
        </Container>
      </Section>

      {/* 5 — LEARNING JOURNEY PREVIEW */}
      <Section className="bg-surface-muted/40">
        <Container>
          <SectionHeader
            eyebrow="The InsuraLab path"
            title="From risk to product — in that order"
            lead="Every part of InsuraLab reinforces this flow. If a feature only makes sense the other way around, it does not belong here."
          />
          <ol className="mt-8 flex flex-wrap items-center gap-3">
            {JOURNEY.map((step, i) => (
              <li key={step} className="flex items-center gap-3">
                <span className="border-border-strong bg-surface rounded-full border px-4 py-2 text-sm font-medium">
                  {step}
                </span>
                {i < JOURNEY.length - 1 ? (
                  <span aria-hidden="true" className="text-muted-foreground">
                    →
                  </span>
                ) : null}
              </li>
            ))}
          </ol>
        </Container>
      </Section>

      {/* 6 — PROVIDER EDUCATION PREVIEW */}
      <Section>
        <Container className="grid gap-8 lg:grid-cols-[1fr_1fr]">
          <div className="flex flex-col gap-4">
            <SectionHeader
              eyebrow="Providers"
              title="Learn about a provider without the platform taking sides"
            />
            <p className="text-muted-foreground max-w-xl">
              General insurance knowledge stays separate from any single company&apos;s material.
              When a provider is covered, its figures are attributed, dated, and marked with a
              review status.
            </p>
            <div>
              <ButtonLink href="/providers" variant="secondary">
                See how providers are covered
              </ButtonLink>
            </div>
          </div>
          <Card className="flex flex-col gap-3">
            <div className="flex items-center gap-2">
              <Badge tone="primary">Provider pertama di InsuraLab</Badge>
              <Badge tone="neutral">Independen</Badge>
            </div>
            <CardTitle>Allianz</CardTitle>
            <CardDescription>
              Used as the first provider case study. Allianz is a data record here — never the
              identity of InsuraLab. Its three Indonesian entities and the ASN distribution channel
              are described, not merged.
            </CardDescription>
          </Card>
        </Container>
      </Section>

      {/* 7 — TRUST / METHODOLOGY */}
      <Section className="bg-surface-muted/40">
        <Container className="grid gap-10 lg:grid-cols-[1fr_1fr]">
          <div className="flex flex-col gap-4">
            <SectionHeader
              eyebrow="Why this can be trusted"
              title="Sources recorded. Dates shown. Uncertainty visible."
            />
            <ul className="text-muted-foreground flex flex-col gap-2 text-sm">
              <li>Provider-specific information is attributed to a dated source.</li>
              <li>
                Volatile figures are marked <em>needs review</em> until re-verified.
              </li>
              <li>Historical material is labelled as history, never shown as current.</li>
              <li>General concepts and provider-specific material stay distinct.</li>
            </ul>
            <div>
              <ButtonLink href="/methodology" variant="secondary">
                Read the methodology
              </ButtonLink>
            </div>
          </div>
          <Card>
            <CardTitle className="mb-3">A few terms from the glossary</CardTitle>
            <HomeGlossaryPeek />
          </Card>
        </Container>
      </Section>

      {/* Featured reading (live from the API) */}
      {featured.length ? (
        <Section>
          <Container>
            <SectionHeader eyebrow="From Learn" title="Start with the fundamentals" />
            <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {featured.map((article) => (
                <KnowledgeCard key={article.id} article={article} href={`/learn/${article.slug}`} />
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      {/* 8 — FINAL EDUCATIONAL CTA */}
      <Section className="border-border border-t">
        <Container className="flex flex-col items-start gap-5">
          <h2 className="text-2xl font-semibold sm:text-3xl">Mulai dari dasar.</h2>
          <p className="text-muted-foreground max-w-xl">
            Tidak perlu istilah rumit untuk memulai. Pahami satu konsep, lalu lanjut ke berikutnya.
          </p>
          <ButtonLink href="/learn" size="lg">
            Pelajari Apa Itu Asuransi
          </ButtonLink>
          <EducationalDisclaimer className="mt-4 max-w-2xl" />
        </Container>
      </Section>
    </>
  );
}
