import { Container } from "@/components/ui/container";
import { ButtonLink } from "@/components/ui/button";

export default function TermNotFound() {
  return (
    <Container className="flex flex-1 flex-col items-center justify-center gap-4 py-24 text-center">
      <p className="text-accent font-mono text-xs font-semibold tracking-[0.14em] uppercase">404</p>
      <h1 className="text-2xl font-semibold sm:text-3xl">Istilah tidak ditemukan</h1>
      <p className="text-muted-foreground max-w-md">
        Istilah ini belum ada di glosarium InsuraLab.
      </p>
      <ButtonLink href="/glossary" className="mt-2">
        Kembali ke Glosarium
      </ButtonLink>
    </Container>
  );
}
