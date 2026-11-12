import Link from "next/link";
import { FileText } from "lucide-react";
export const metadata = { title: "Cara Kami Mengelola Pengetahuan" };
export default function MethodologyPage() {
  return (
    <div className="static-reading-page">
      <article className="guide-reader">
        <FileText size={32} strokeWidth={1.3} className="text-primary" />
        <p className="eyebrow">METODOLOGI</p>
        <h1>Cara kami mengelola pengetahuan.</h1>
        <p className="reader-lead">
          Sumber, konteks, dan keterbatasan adalah bagian dari setiap pemahaman.
        </p>
        <h2>Pengetahuan umum</h2>
        <p>
          Definisi dan panduan pengantar membantu mengenali istilah. Hubungan dalam peta adalah
          asosiasi bacaan yang dijelaskan secara editorial, bukan jaminan atau penilaian kebutuhan
          pribadi.
        </p>
        <h2>Studi provider dan produk</h2>
        <p>
          Identitas penerbit, versi dokumen, serta batas informasi harus dibedakan. Brosur dan
          ringkasan membantu membaca; ketentuan kontrak tetap mengikuti polis yang berlaku.
        </p>
        <h2>Izin akses berbeda dari review akurasi.</h2>
        <p>
          Sumber baru berstatus belum ditinjau sampai hak penggunaannya diperiksa. Dokumen internal
          dan data pribadi tidak otomatis boleh dipublikasikan karena tersedia di komputer atau
          dapat dibuka oleh agen.
        </p>
        <h2>Status informasi</h2>
        <ul>
          <li>
            <strong>Perlu ditinjau:</strong> kebenaran, versi, atau konteks belum dikonfirmasi
            sepenuhnya.
          </li>
          <li>
            <strong>Historis:</strong> rujukan untuk periode lalu, bukan ketentuan terkini.
          </li>
          <li>
            <strong>Terkini:</strong> hanya digunakan setelah pemeriksaan yang sesuai; bukan jaminan
            informasi tidak akan berubah.
          </li>
        </ul>
        <p>
          Materi pengantar baru ditandai belum ditinjau ahli independen. Tidak ada tanggal review
          manusia yang dibuat-buat. Bukti yang sudah dirujuk memiliki versi; perubahan teks memakai
          passage baru.
        </p>
        <h2>Jawaban terkurasi terlebih dahulu.</h2>
        <p>
          Hasil pencarian menampilkan materi tersimpan yang relevan. Pencarian yang lemah tidak
          diubah menjadi jawaban otomatis. Bantuan AI tidak aktif secara default dan, bila kelak
          digunakan, harus tunduk pada akses sumber yang sama.
        </p>
        <Link className="knowledge-button" href="/sumber">
          Baca catatan sumber
        </Link>
      </article>
    </div>
  );
}
