import type { Metadata } from "next";
import { ButtonLink } from "@/components/ui/button";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { Container, PageHeader, Section } from "@/components/ui/container";

export const metadata: Metadata = {
  title: "Metodologi Protection Needs Assessment",
  description:
    "Cara InsuraLab menentukan relevansi pembelajaran secara deterministik dan netral provider.",
};

const DIMENSIONS = [
  "Proteksi Jiwa",
  "Proteksi Kesehatan",
  "Proteksi Penyakit Kritis",
  "Proteksi Disabilitas",
  "Proteksi Kecelakaan",
];

export default function AssessmentMethodologyPage() {
  return (
    <>
      <PageHeader
        eyebrow="Metodologi · versi 1.0"
        title="Cara assessment menentukan relevansi pembelajaran"
        lead="Metodologi ini dirancang agar deterministik, dapat dijelaskan, dapat dikonfigurasi editor, dan independen dari provider."
      />
      <Section>
        <Container className="flex max-w-4xl flex-col gap-10">
          <section>
            <h2 className="text-xl font-semibold">Apa yang dilakukan</h2>
            <p className="text-muted-foreground mt-3">
              Setiap pilihan jawaban dapat meningkatkan relevansi satu atau lebih topik proteksi.
              Contohnya, memiliki tanggungan dapat membuat materi Asuransi Jiwa lebih relevan untuk
              dipelajari. Dampak tersebut dijumlahkan lalu dibandingkan dengan maksimum yang berlaku
              untuk pertanyaan yang benar-benar ditampilkan.
            </p>
          </section>

          <section>
            <h2 className="text-xl font-semibold">Lima dimensi pembelajaran</h2>
            <div className="mt-4 grid gap-3 sm:grid-cols-2">
              {DIMENSIONS.map((dimension) => (
                <Card key={dimension} className="p-4">
                  <CardTitle>{dimension}</CardTitle>
                </Card>
              ))}
            </div>
          </section>

          <section>
            <h2 className="text-xl font-semibold">Tingkat relevansi</h2>
            <div className="mt-4 grid gap-4 sm:grid-cols-3">
              <Card>
                <CardTitle>Fondasi Pembelajaran</CardTitle>
                <CardDescription>Topik tetap tersedia sebagai pengetahuan dasar.</CardDescription>
              </Card>
              <Card>
                <CardTitle>Layak Dieksplorasi</CardTitle>
                <CardDescription>
                  Beberapa jawaban membuat topik ini berguna untuk dipahami lebih lanjut.
                </CardDescription>
              </Card>
              <Card>
                <CardTitle>Sangat Relevan</CardTitle>
                <CardDescription>
                  Beberapa faktor situasi membuat materi ini menonjol dalam urutan belajar.
                </CardDescription>
              </Card>
            </div>
          </section>

          <section>
            <h2 className="text-xl font-semibold">Apa yang tidak dilakukan</h2>
            <ul className="text-muted-foreground mt-3 grid gap-2">
              <li>
                — Tidak menilai risiko medis, aktuaria, underwriting, atau kelayakan penerimaan.
              </li>
              <li>
                — Tidak menghitung uang pertanggungan, premi, kekurangan proteksi, atau kondisi
                finansial ideal.
              </li>
              <li>
                — Tidak merekomendasikan produk, provider, pembelian, atau keputusan finansial
                pribadi.
              </li>
              <li>
                — Tidak menggunakan AI, machine learning, probabilitas, atau skor tersembunyi.
              </li>
            </ul>
          </section>

          <section>
            <h2 className="text-xl font-semibold">Privasi dan keterbatasan</h2>
            <p className="text-muted-foreground mt-3">
              Jawaban dikirim ke Laravel hanya untuk evaluasi saat itu dan tidak disimpan di
              database. Hasil disimpan sementara di session browser agar halaman hasil dapat dibuka
              tanpa menaruh jawaban pada URL. Metodologi dapat berubah; perubahan pertanyaan atau
              bobot akan menaikkan versi dan dapat menghasilkan urutan belajar yang berbeda.
            </p>
          </section>

          <ButtonLink href="/assessment/protection-needs" className="self-start">
            Mulai Assessment
          </ButtonLink>
        </Container>
      </Section>
    </>
  );
}
