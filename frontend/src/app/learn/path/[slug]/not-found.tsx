import { ButtonLink } from "@/components/ui/button";
import { Container, Section } from "@/components/ui/container";
import { EmptyState } from "@/components/ui/empty-state";

export default function LearningPathNotFound() {
  return (
    <Section>
      <Container>
        <EmptyState
          title="Jalur belajar tidak ditemukan"
          description="Jalur ini mungkin belum diterbitkan."
          action={<ButtonLink href="/learn/path">Lihat Semua Jalur</ButtonLink>}
        />
      </Container>
    </Section>
  );
}
