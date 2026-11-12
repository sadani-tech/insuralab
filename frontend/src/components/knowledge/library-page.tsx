import Link from "next/link";
import { Suspense } from "react";
import { BookOpen, RefreshCw } from "lucide-react";
import { getLibrary } from "@/lib/knowledge";
import { KnowledgeWorkspace } from "./workspace";
import { KnowledgeExplorer } from "./explorer";

export async function LibraryPage({
  section = "explore",
}: {
  section?: "explore" | "guides" | "answers" | "sources";
}) {
  const library = await getLibrary().catch(() => null);
  if (!library) {
    return (
      <section className="library-unavailable">
        <BookOpen size={36} strokeWidth={1.4} />
        <p className="eyebrow">RUANG PENGETAHUAN</p>
        <h1>Materi belum dapat dimuat.</h1>
        <p>
          Koneksi ke perpustakaan sedang terganggu. Coba kembali sebentar lagi; materi Anda tetap
          tersimpan.
        </p>
        <Link
          href={
            section === "explore"
              ? "/"
              : section === "guides"
                ? "/panduan"
                : section === "answers"
                  ? "/tanya"
                  : "/sumber"
          }
          className="knowledge-button"
        >
          <RefreshCw size={16} /> Muat ulang
        </Link>
      </section>
    );
  }
  return (
    <Suspense fallback={<p className="graph-wait">Menyiapkan perpustakaan…</p>}>
      {section === "explore" ? (
        <KnowledgeExplorer library={library} />
      ) : (
        <KnowledgeWorkspace library={library} section={section} />
      )}
    </Suspense>
  );
}
