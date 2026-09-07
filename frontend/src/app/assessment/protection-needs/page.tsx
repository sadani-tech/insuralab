import type { Metadata } from "next";
import { AssessmentRunner } from "@/components/assessment/assessment-runner";
import { ButtonLink } from "@/components/ui/button";
import { Container, PageHeader, Section } from "@/components/ui/container";
import { EmptyState } from "@/components/ui/empty-state";
import { getProtectionNeedsAssessment } from "@/lib/api";

export const metadata: Metadata = {
  title: "Mulai Protection Needs Assessment",
  description: "Refleksi edukatif untuk memetakan topik proteksi yang relevan dipelajari.",
};

export default async function ProtectionNeedsAssessmentPage() {
  let assessment = null;
  try {
    assessment = await getProtectionNeedsAssessment();
  } catch {
    // Expected backend/network failure is rendered below.
  }

  if (!assessment) {
    return (
      <Section>
        <Container>
          <EmptyState
            title="Assessment sedang tidak tersedia"
            description="Definisi pertanyaan belum dapat dimuat. Periksa koneksi dan coba lagi."
            action={<ButtonLink href="/assessment/protection-needs">Coba Lagi</ButtonLink>}
          />
        </Container>
      </Section>
    );
  }

  return (
    <>
      <PageHeader
        eyebrow="Assessment · 2–4 menit"
        title="Peta situasi finansialmu, lalu pelajari konsepnya"
        lead="Pilih jawaban yang paling mendekati situasimu. Tidak ada jawaban benar atau salah, dan assessment ini bukan penilaian risiko."
      />
      <Section>
        <Container>
          <AssessmentRunner assessment={assessment} />
        </Container>
      </Section>
    </>
  );
}
