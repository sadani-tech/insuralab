import type { Metadata } from "next";
import { AssessmentResult } from "@/components/assessment/assessment-result";
import { Container, PageHeader, Section } from "@/components/ui/container";

export const metadata: Metadata = {
  title: "Peta Pembelajaran Proteksi",
  description: "Hasil edukatif Protection Needs Assessment InsuraLab.",
};

export default function AssessmentResultPage() {
  return (
    <>
      <PageHeader
        eyebrow="Hasil Assessment"
        title="Peta Pembelajaran Proteksi"
        lead="Hasil ini menunjukkan topik perlindungan yang mungkin relevan untuk kamu pelajari berdasarkan jawabanmu. Ini bukan penilaian risiko atau rekomendasi produk."
      />
      <Section>
        <Container>
          <AssessmentResult />
        </Container>
      </Section>
    </>
  );
}
