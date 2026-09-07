import { Container } from "@/components/ui/container";
import { ButtonLink } from "@/components/ui/button";

export default function NotFound() {
  return (
    <Container className="flex flex-1 flex-col items-center justify-center gap-4 py-24 text-center">
      <p className="text-accent font-mono text-xs font-semibold tracking-[0.14em] uppercase">404</p>
      <h1 className="text-2xl font-semibold sm:text-3xl">We couldn&apos;t find that page</h1>
      <p className="text-muted-foreground max-w-md">
        The link may be out of date, or the page hasn&apos;t been built yet. The main sections are
        all reachable from the menu.
      </p>
      <div className="mt-2 flex gap-3">
        <ButtonLink href="/">Back to home</ButtonLink>
        <ButtonLink href="/learn" variant="secondary">
          Go to Learn
        </ButtonLink>
      </div>
    </Container>
  );
}
