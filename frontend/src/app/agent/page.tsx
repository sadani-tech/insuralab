import Link from "next/link";
import {
  ArrowRight,
  BookOpen,
  CalendarDays,
  CheckCircle2,
  LockKeyhole,
  ShieldCheck,
} from "lucide-react";
export const metadata = { title: "Persiapan Workspace Agen" };

/** Public availability screen only. No agent data, authentication token, or mock CRM is rendered. */
export default function AgentPage() {
  return (
    <section className="agent-onboarding">
      <div className="agent-intro">
        <span className="agent-emblem">
          <ShieldCheck size={38} strokeWidth={1.3} />
        </span>
        <p className="eyebrow">WORKSPACE AGEN</p>
        <h1>
          Pendampingan yang baik
          <br />
          dimulai dari persiapan.
        </h1>
        <p>
          Ruang kerja agen sedang dipersiapkan untuk membantu Anda mengelola kegiatan dan
          mendampingi nasabah secara lebih terarah.
        </p>
        <span className="subtle-tag">
          <LockKeyhole size={14} /> Area operasional belum diaktifkan
        </span>
        <Link className="knowledge-button" href="/panduan">
          Pelajari panduan yang tersedia <ArrowRight size={17} />
        </Link>
      </div>
      <div className="agent-roadmap">
        <p className="eyebrow">ARAH PENGEMBANGAN BERIKUTNYA</p>
        {[
          {
            icon: CalendarDays,
            title: "Aktivitas yang tertata",
            body: "Rencana prospek, appointment, tugas, dan tindak lanjut dengan pencatatan manual yang jelas.",
          },
          {
            icon: BookOpen,
            title: "Materi dalam konteks",
            body: "Rujukan pengetahuan dan checklist untuk mempersiapkan percakapan yang berimbang.",
          },
          {
            icon: CheckCircle2,
            title: "Layanan berkelanjutan",
            body: "Rencana pelacakan pengajuan, catatan polis, serta pendampingan proses layanan dan klaim.",
          },
        ].map((x) => (
          <div key={x.title}>
            <x.icon size={23} strokeWidth={1.4} />
            <section>
              <h2>{x.title}</h2>
              <p>{x.body}</p>
            </section>
          </div>
        ))}
        <p className="agent-privacy">
          <LockKeyhole size={15} />
          Halaman ini hanya menjelaskan persiapan fitur. Tidak menerima atau menampilkan data
          nasabah.
        </p>
      </div>
    </section>
  );
}
