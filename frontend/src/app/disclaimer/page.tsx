import type { Metadata } from "next";
import { Container, PageHeader } from "@/components/ui/container";

export const metadata: Metadata = {
  title: "Disclaimer",
  description:
    "InsuraLab is educational. It is not financial, legal, or tax advice, and not an official insurer website.",
};

const POINTS: { heading: string; body: string }[] = [
  {
    heading: "Informasi edukatif",
    body: "Seluruh konten InsuraLab disusun untuk membantu Anda memahami risiko, proteksi, dan cara kerja asuransi. Konten ini bukan nasihat dan tidak disesuaikan dengan keadaan pribadi Anda.",
  },
  {
    heading: "Bukan nasihat keuangan, hukum, atau pajak",
    body: "InsuraLab tidak memberikan nasihat keuangan, hukum, atau perpajakan yang bersifat personal, dan tidak merekomendasikan produk asuransi tertentu untuk dibeli. Untuk keputusan nyata, konsultasikan dengan profesional berlisensi.",
  },
  {
    heading: "Bukan situs resmi perusahaan asuransi",
    body: "InsuraLab adalah platform edukasi independen dan tidak berafiliasi dengan perusahaan asuransi mana pun. Nama provider digunakan sebagai studi kasus edukatif.",
  },
  {
    heading: "Informasi produk dapat berubah",
    body: "Manfaat, batasan, usia masuk, biaya, dan ketentuan produk berubah dari waktu ke waktu. Angka yang ditampilkan berasal dari materi rujukan bertanggal dan harus diperiksa terhadap dokumen resmi terkini.",
  },
  {
    heading: "Rujuk dokumen resmi",
    body: "Untuk keputusan sebenarnya, gunakan polis, RIPLAY, ringkasan informasi produk, dan ilustrasi resmi yang berlaku dari penerbit polis.",
  },
  {
    heading: "Klaim bergantung pada ketentuan polis",
    body: "Pembayaran klaim tunduk pada definisi, pengecualian, dan penilaian penanggung sesuai ketentuan polis. InsuraLab tidak menjamin kelayakan produk, persetujuan underwriting, atau persetujuan klaim.",
  },
];

export default function DisclaimerPage() {
  return (
    <>
      <PageHeader
        eyebrow="Disclaimer"
        title="Cara membaca konten InsuraLab"
        lead="Ringkas dan tanpa istilah hukum yang menakut-nakuti. Intinya: InsuraLab membantu Anda belajar, bukan menggantikan dokumen resmi atau nasihat profesional."
      />
      <Container className="flex flex-col gap-8 py-14">
        <ol className="flex flex-col gap-6">
          {POINTS.map((point, i) => (
            <li key={point.heading} className="flex gap-4">
              <span className="text-muted-foreground font-mono text-sm">
                {String(i + 1).padStart(2, "0")}
              </span>
              <div>
                <h2 className="font-semibold">{point.heading}</h2>
                <p className="text-muted-foreground mt-1 max-w-2xl text-sm">{point.body}</p>
              </div>
            </li>
          ))}
        </ol>
        <p className="text-muted-foreground border-border max-w-2xl border-t pt-6 text-sm">
          This content is educational and is not financial, legal, or tax advice, nor a
          recommendation to buy any particular product. InsuraLab is an independent learning
          platform and is not an official insurer website.
        </p>
      </Container>
    </>
  );
}
