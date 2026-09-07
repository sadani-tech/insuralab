import { ButtonLink } from "@/components/ui/button";
import { Container, Section } from "@/components/ui/container";
import { EmptyState } from "@/components/ui/empty-state";

export default function QuizNotFound() {
  return (
    <Section>
      <Container>
        <EmptyState
          title="Quiz tidak ditemukan"
          description="Quiz mungkin belum diterbitkan atau sudah tidak tersedia."
          action={<ButtonLink href="/quiz">Kembali ke Daftar Quiz</ButtonLink>}
        />
      </Container>
    </Section>
  );
}
