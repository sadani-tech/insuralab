import { Container } from "@/components/ui/container";
import { ButtonLink } from "@/components/ui/button";

export default function ArticleNotFound() {
  return (
    <Container className="flex flex-1 flex-col items-center justify-center gap-4 py-24 text-center">
      <p className="text-accent font-mono text-xs font-semibold tracking-[0.14em] uppercase">404</p>
      <h1 className="text-2xl font-semibold sm:text-3xl">Artikel tidak ditemukan</h1>
      <p className="text-muted-foreground max-w-md">
        Tautannya mungkin sudah berubah, atau artikel ini belum diterbitkan.
      </p>
      <ButtonLink href="/learn" className="mt-2">
        Kembali ke Learn
      </ButtonLink>
    </Container>
  );
}
