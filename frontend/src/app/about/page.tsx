import type { Metadata } from "next";
import { Container, PageHeader } from "@/components/ui/container";
import { ButtonLink } from "@/components/ui/button";

export const metadata: Metadata = {
  title: "About",
  description:
    "InsuraLab is an independent learning platform for understanding insurance before you buy it.",
};

export default function AboutPage() {
  return (
    <>
      <PageHeader
        eyebrow="About"
        title="Understand insurance before you buy it"
        lead="InsuraLab exists to make insurance knowledge easier to understand — starting from the risk a household is trying to protect, not from a product."
      />
      <Container className="flex flex-col gap-10 py-14">
        <section className="flex flex-col gap-3">
          <h2 className="text-xl font-semibold">Why it exists</h2>
          <p className="text-muted-foreground max-w-2xl">
            Most people meet insurance for the first time in a sales conversation, with no framework
            for thinking about what they actually need. InsuraLab is the opposite starting point: a
            calm place to learn what risks exist, how categories of protection respond to them, and
            what to check in a real policy — before any decision.
          </p>
        </section>

        <section className="flex flex-col gap-3">
          <h2 className="text-xl font-semibold">The approach</h2>
          <ul className="text-muted-foreground flex max-w-2xl flex-col gap-2 text-sm">
            <li>
              General insurance knowledge is kept clearly separate from any one company&apos;s
              material.
            </li>
            <li>
              Provider-specific figures are always attributed, dated, and marked with a review
              status.
            </li>
            <li>Historical information is labelled as history, never presented as current fact.</li>
            <li>There is no &ldquo;buy now&rdquo;, no lead capture, and no pressure.</li>
          </ul>
        </section>

        <section className="flex flex-col gap-3">
          <h2 className="text-xl font-semibold">What InsuraLab is not</h2>
          <p className="text-muted-foreground max-w-2xl">
            It is not a registered insurance institution, a broker, an agency, or a price-comparison
            site. It does not provide personalized financial, legal, or tax advice. Allianz is the
            first provider case study — it is not the identity of the platform.
          </p>
        </section>

        <div className="flex flex-wrap gap-3">
          <ButtonLink href="/methodology" variant="secondary">
            Read the methodology
          </ButtonLink>
          <ButtonLink href="/learn">Start learning</ButtonLink>
        </div>
      </Container>
    </>
  );
}
