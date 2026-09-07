import type { Metadata } from "next";
import { ButtonLink } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Container, PageHeader, Section } from "@/components/ui/container";
import { EmptyState } from "@/components/ui/empty-state";
import { getProtectionNeedsAssessment } from "@/lib/api";

export const metadata: Metadata = {
  title: "Protection Needs Assessment",
  description:
    "Kenali topik proteksi yang relevan untuk dipelajari melalui refleksi edukatif singkat.",
};

export default async function AssessmentLandingPage() {
  let assessment = null;
  try {
    assessment = await getProtectionNeedsAssessment();
  } catch {
    // The landing page still explains the feature and offers a useful retry.
  }

  return (
    <>
      <PageHeader
        eyebrow="Protection Needs Assessment"
        title="Kenali Topik Proteksi yang Relevan untuk Dipelajari"
        lead={
          assessment?.intro ??
          "Jawab beberapa pertanyaan sederhana mengenai kondisi finansial dan perlindungan yang sudah kamu miliki. InsuraLab akan membantu menunjukkan topik proteksi yang mungkin relevan untuk kamu pelajari lebih lanjut."
        }
      >
        {assessment ? (
          <div className="mt-3 flex flex-col gap-3 sm:flex-row">
            <ButtonLink href="/assessment/protection-needs" size="lg">
              Mulai Assessment
            </ButtonLink>
            <ButtonLink
              href="/assessment/protection-needs/methodology"
              variant="secondary"
              size="lg"
            >
              Bagaimana Assessment Ini Bekerja?
            </ButtonLink>
          </div>
        ) : null}
      </PageHeader>

      <Section>
        <Container>
          {assessment ? (
            <div className="grid gap-5 md:grid-cols-3">
              <Card>
                <p className="text-accent font-mono text-xs font-semibold">DURASI</p>
                <h2 className="mt-2 text-lg font-semibold">Sekitar 2–4 menit</h2>
                <p className="text-muted-foreground mt-2 text-sm">
                  Pertanyaannya singkat, berbasis rentang, dan dapat diubah dengan tombol kembali.
                </p>
              </Card>
              <Card>
                <p className="text-accent font-mono text-xs font-semibold">PRIVASI</p>
                <h2 className="mt-2 text-lg font-semibold">Tanpa login dan identitas</h2>
                <p className="text-muted-foreground mt-2 text-sm">
                  Tidak meminta nama, kontak, diagnosis medis, saldo, gaji tepat, atau nomor polis.
                </p>
              </Card>
              <Card>
                <p className="text-accent font-mono text-xs font-semibold">TUJUAN</p>
                <h2 className="mt-2 text-lg font-semibold">Belajar, bukan membeli</h2>
                <p className="text-muted-foreground mt-2 text-sm">
                  Tidak ada komitmen pembelian. Hasil bukan nasihat keuangan atau rekomendasi
                  produk.
                </p>
              </Card>
            </div>
          ) : (
            <EmptyState
              title="Assessment belum dapat dimuat"
              description="Pastikan backend InsuraLab aktif, lalu coba muat ulang halaman ini."
              action={<ButtonLink href="/assessment">Coba Lagi</ButtonLink>}
            />
          )}
        </Container>
      </Section>
    </>
  );
}
