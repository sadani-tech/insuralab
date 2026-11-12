import Link from "next/link";
import { ShieldCheck, ArrowRight } from "lucide-react";
export const metadata = { title: "Tentang InsuraLab" };
export default function AboutPage() {
  return (
    <div className="static-reading-page">
      <article className="guide-reader">
        <ShieldCheck size={35} strokeWidth={1.3} className="text-primary" />
        <p className="eyebrow">TENTANG INSURALAB</p>
        <h1>Pengetahuan untuk perlindungan.</h1>
        <p className="reader-lead">
          Ruang untuk memahami asuransi dengan lebih tenang, sebelum mengambil keputusan.
        </p>
        <h2>Mulai dari pemahaman.</h2>
        <p>
          InsuraLab menghubungkan konsep, panduan, jawaban terkurasi, dan sumber. Anda bisa
          menjelajahi peta atau mulai dari pertanyaan yang dekat dengan keseharian.
        </p>
        <h2>Pengetahuan yang bisa ditelusuri.</h2>
        <p>
          Materi umum dibedakan dari studi provider dan produk. Konteks sumber dan versinya tetap
          terlihat. Penjelasan pengantar baru belum ditinjau ahli independen; informasi produk harus
          dicocokkan dengan dokumen resmi.
        </p>
        <h2>Tetap berguna tanpa AI.</h2>
        <p>
          Pencarian, FAQ, glossary, panduan, dan sumber merupakan bagian utama perpustakaan. Bantuan
          AI bersifat opsional dan tidak menjadi syarat belajar.
        </p>
        <h2>Persiapan ruang kerja agen.</h2>
        <p>
          Area operasional dirancang untuk tahap berikutnya. Saat ini belum ada CRM aktif atau
          pengumpulan data nasabah. InsuraLab tidak menetapkan keputusan underwriting atau
          persetujuan klaim.
        </p>
        <Link href="/" className="knowledge-button">
          Jelajahi pengetahuan <ArrowRight size={16} />
        </Link>
      </article>
    </div>
  );
}
