import type { Metadata } from "next";
import { Container, PageHeader } from "@/components/ui/container";
import { GlossaryBrowser } from "@/components/glossary/glossary-browser";

export const metadata: Metadata = {
  title: "Glosarium",
  description:
    "Kamus asuransi berbahasa sederhana — definisi singkat setiap istilah, ditautkan ke artikel pembelajaran.",
};

export default function GlossaryPage() {
  return (
    <>
      <PageHeader
        eyebrow="InsuraLab Pedia"
        title="Kamus Asuransi Berbahasa Sederhana"
        lead="Setiap istilah punya definisi satu kalimat dan penjelasan yang lebih lengkap. Cari berdasarkan kata kunci, huruf awal, atau kategori."
      />
      <Container className="py-12">
        <GlossaryBrowser />
      </Container>
    </>
  );
}
