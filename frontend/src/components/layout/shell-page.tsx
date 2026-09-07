import type { ReactNode } from "react";
import { Container, PageHeader } from "@/components/ui/container";
import { Callout } from "@/components/ui/callout";

/**
 * Standard layout for a route whose full experience arrives in a later phase.
 * Real, specific explanatory copy only — never lorem ipsum.
 */
export function ShellPage({
  eyebrow,
  title,
  lead,
  phaseNote,
  children,
}: {
  eyebrow: string;
  title: string;
  lead: string;
  phaseNote: string;
  children?: ReactNode;
}) {
  return (
    <>
      <PageHeader eyebrow={eyebrow} title={title} lead={lead} />
      <Container as="div" className="flex flex-col gap-8 py-12">
        <Callout tone="info" title="In progress">
          {phaseNote}
        </Callout>
        {children}
      </Container>
    </>
  );
}
