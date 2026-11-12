"use client";

import { useCallback, useState } from "react";
import Link from "next/link";
import { useSearchParams, usePathname } from "next/navigation";
import {
  ArrowDown,
  ArrowRight,
  ArrowUpRight,
  BookOpen,
  Check,
  ChevronRight,
  Compass,
  FileText,
  HelpCircle,
  Layers3,
  LockKeyhole,
  Search,
  ShieldCheck,
  Sparkles,
  X,
} from "lucide-react";
import { groups, matchesQuery, type Entry, type Library } from "@/lib/knowledge";

function EntryCard({ entry }: { entry: Entry }) {
  return (
    <Link className="entry-card" href={`/panduan?entry=${entry.slug}`}>
      <span className="entry-icon">
        <BookOpen size={21} strokeWidth={1.5} />
      </span>
      <span className="eyebrow">PANDUAN MANDIRI · {entry.steps.length} LANGKAH</span>
      <h3>{entry.title}</h3>
      <p>{entry.summary}</p>
      <span className="text-link">
        Buka panduan <ArrowUpRight size={16} />
      </span>
    </Link>
  );
}

function SourceReference({ library, id }: { library: Library; id: number }) {
  const source = library.passages.find((p) => p.id === id);
  if (!source) return null;
  return (
    <Link className="source-reference" href={`/sumber?source=${id}`}>
      <FileText size={17} />
      <span>
        <strong>{source.title}</strong>
        <small>
          {source.locator} · {source.version}
        </small>
      </span>
      <ArrowUpRight size={15} />
    </Link>
  );
}

export function KnowledgeWorkspace({
  library,
  section,
}: {
  library: Library;
  section: "explore" | "guides" | "answers" | "sources";
}) {
  const params = useSearchParams();
  const pathname = usePathname();
  const query = params.get("q") ?? "";
  const group = params.get("group") ?? "all";
  const [aiNotice, setAiNotice] = useState(false);
  const patch = useCallback(
    (changes: Record<string, string | null>, replace = false) => {
      const next = new URLSearchParams(window.location.search);
      Object.entries(changes).forEach(([key, value]) =>
        value ? next.set(key, value) : next.delete(key),
      );
      window.history[replace ? "replaceState" : "pushState"](
        null,
        "",
        `${pathname}${next.size ? `?${next}` : ""}`,
      );
    },
    [pathname],
  );
  const guides = library.entries.filter((e) => e.kind === "guide");
  const faqs = library.entries.filter((e) => e.kind === "faq");
  const entry = library.entries.find((e) => e.slug === params.get("entry"));
  const passage = library.passages.find((p) => String(p.id) === params.get("source"));
  const titles = {
    explore: [
      "RUANG EKSPLORASI",
      "Pahami proteksi. Temukan keterkaitannya.",
      "Satu peta untuk mengenal risiko, memahami polis, dan melangkah dengan lebih tenang.",
    ],
    guides: [
      "BELAJAR DENGAN ARAH",
      "Langkah kecil. Pemahaman yang lebih utuh.",
      "Panduan praktis yang bisa Anda baca, tandai, dan pelajari sesuai kebutuhan.",
    ],
    answers: [
      "RUANG TANYA",
      "Pertanyaan Anda layak mendapat kejelasan.",
      "Temukan jawaban yang tersimpan, dilengkapi konteks dan rujukan yang dapat dibaca.",
    ],
    sources: [
      "PERPUSTAKAAN SUMBER",
      "Pemahaman yang punya dasar.",
      "Telusuri asal penjelasan, konteks versi, dan batas informasi yang Anda pelajari.",
    ],
  };
  const heading = titles[section];
  const showReader = entry && (section === "guides" || section === "answers");

  return (
    <div className="knowledge-shell">
      <aside className="knowledge-sidebar" aria-label="Kategori pengetahuan">
        <p className="eyebrow">RUANG PENGETAHUAN</p>
        <Link
          href="/"
          className={`sidebar-overview ${section === "explore" && group === "all" ? "active" : ""}`}
        >
          <Compass size={19} /> Peta perlindungan <ChevronRight size={15} />
        </Link>
        <p className="sidebar-label">JELAJAHI TOPIK</p>
        <nav>
          {Object.entries(groups).map(([key, g]) => (
            <Link
              key={key}
              href={`/?group=${key}`}
              aria-current={section === "explore" && group === key ? "page" : undefined}
              className={`sidebar-topic ${section === "explore" && group === key ? "active" : ""}`}
            >
              <i style={{ backgroundColor: g.color }} />
              {g.title}
              <span>{library.nodes.filter((n) => n.group === key).length}</span>
            </Link>
          ))}
        </nav>
        <div className="sidebar-divider" />
        <Link className="sidebar-topic" href="/glossary">
          <BookOpen size={17} /> Kamus asuransi <ArrowUpRight size={14} />
        </Link>
        <Link className="sidebar-topic" href="/learn">
          <Layers3 size={17} /> Bacaan mendalam <ArrowUpRight size={14} />
        </Link>
        <div className="sidebar-reassurance">
          <ShieldCheck size={27} strokeWidth={1.3} />
          <h3>Kenali sebelum memilih.</h3>
          <p>Ruang untuk belajar dengan tenang, tanpa tekanan membeli.</p>
          <Link href="/about">
            Tentang InsuraLab <ArrowRight size={13} />
          </Link>
        </div>
        <span className="sidebar-footnote">Pengetahuan untuk perlindungan.</span>
      </aside>

      <div className="knowledge-main">
        <div className="workspace-breadcrumb">
          <span>InsuraLab</span>
          <ChevronRight size={12} />
          <span>
            {section === "explore"
              ? "Jelajah pengetahuan"
              : section === "guides"
                ? "Panduan"
                : section === "answers"
                  ? "Tanya"
                  : "Sumber"}
          </span>
          <span className="independent-note">
            <ShieldCheck size={14} /> Edukasi independen
          </span>
        </div>
        <div className="workspace-heading">
          <p className="eyebrow">{heading[0]}</p>
          <h1>{heading[1]}</h1>
          <p>{heading[2]}</p>
        </div>

        {!showReader && !passage && (
          <div className="workspace-search">
            <Search size={19} />
            <input
              aria-label="Cari pengetahuan"
              value={query}
              maxLength={160}
              onChange={(e) => patch({ q: e.target.value || null }, true)}
              placeholder={
                section === "answers"
                  ? "Cari pertanyaan, misalnya: apakah cashless punya batas?"
                  : "Cari konsep, pertanyaan, atau kata kunci…"
              }
            />
            {query ? (
              <button aria-label="Hapus pencarian" onClick={() => patch({ q: null }, true)}>
                <X size={17} />
              </button>
            ) : (
              <span className="search-hint">Mulai dari rasa ingin tahu</span>
            )}
          </div>
        )}

        {showReader ? (
          <Reader entry={entry} library={library} />
        ) : section === "guides" ? (
          <>
            <div className="section-heading">
              <p>
                {guides.filter((e) => matchesQuery(query, e.title, e.summary, e.keywords)).length}{" "}
                panduan untuk belajar mandiri
              </p>
              <span className="subtle-tag">
                <Check size={13} /> Bisa dipakai tanpa AI
              </span>
            </div>
            <div className="entry-grid guides-grid">
              {guides
                .filter((e) => matchesQuery(query, e.title, e.summary, e.keywords))
                .map((e) => (
                  <EntryCard key={e.slug} entry={e} />
                ))}
            </div>
            {query && !guides.some((e) => matchesQuery(query, e.title, e.summary, e.keywords)) && (
              <EmptyResults />
            )}
          </>
        ) : null}

        {section === "answers" && !showReader && (
          <div className="answers-layout">
            <section>
              <div className="section-heading">
                <h2>{query ? "Hasil terkait pertanyaan Anda" : "Pertanyaan yang sering muncul"}</h2>
                <span className="subtle-tag">Jawaban terkurasi</span>
              </div>
              <p className="search-disclaimer">
                Pilih pertanyaan yang sesuai. Hasil terkait membantu menelusuri materi dan bukan
                jawaban otomatis untuk setiap pertanyaan.
              </p>
              <div className="faq-list">
                {faqs
                  .filter((e) => matchesQuery(query, e.title, e.summary, e.keywords))
                  .map((e) => (
                    <details key={e.slug} open={params.get("entry") === e.slug}>
                      <summary>
                        <HelpCircle size={18} />
                        <span>{e.title}</span>
                        <ArrowDown size={16} />
                      </summary>
                      <p>{e.body}</p>
                      <div className="topic-pills">
                        {e.terms.map((slug) => (
                          <Link key={slug} href={`/?node=${slug}`}>
                            {library.nodes.find((n) => n.slug === slug)?.title ?? slug}
                            <ArrowUpRight size={11} />
                          </Link>
                        ))}
                      </div>
                      <SourceReference library={library} id={e.passage_id} />
                    </details>
                  ))}
              </div>
              {!faqs.some((e) => matchesQuery(query, e.title, e.summary, e.keywords)) && (
                <EmptyResults />
              )}
            </section>
            <aside className="answer-aside">
              <span className="entry-icon">
                <BookOpen size={23} />
              </span>
              <h3>Kejelasan dimulai dari sumber.</h3>
              <p>
                Jawaban di sini tersimpan dalam perpustakaan. Anda dapat membaca dasar penjelasannya
                dan menjelajahi istilah terkait.
              </p>
              <Link className="text-link" href="/sumber">
                Telusuri sumber <ArrowRight size={15} />
              </Link>
              <div className="sidebar-divider" />
              <button className="ai-option" onClick={() => setAiNotice(true)}>
                <Sparkles size={16} />
                Tentang bantuan AI
              </button>
              {aiNotice && (
                <p role="status" className="ai-status">
                  Bantuan AI belum tersedia. Pencarian, FAQ, panduan, dan sumber tetap dapat
                  digunakan sepenuhnya.
                </p>
              )}
            </aside>
          </div>
        )}

        {section === "sources" &&
          (passage ? (
            <article className="source-reader">
              <Link className="text-link" href="/sumber">
                ← Semua sumber
              </Link>
              <p className="eyebrow">CATATAN SUMBER</p>
              <h2>{passage.title}</h2>
              <p className="source-meta">
                {passage.publisher}
                <br />
                {passage.locator} · {passage.version}
              </p>
              <div className="editorial-note">
                <FileText size={16} />
                Parafrasa/catatan editorial; cocokkan ketentuan pada dokumen resmi.
              </div>
              <div className="source-body">
                {passage.body.split("\n\n").map((p, i) => (
                  <p key={i}>{p}</p>
                ))}
              </div>
              {passage.url && (
                <a
                  className="knowledge-button"
                  href={passage.url}
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  Buka halaman resmi <ArrowUpRight size={16} />
                </a>
              )}
            </article>
          ) : (
            <>
              <div className="sources-list">
                {library.passages
                  .filter((p) => matchesQuery(query, p.title, p.locator, p.body))
                  .map((p) => (
                    <Link key={p.id} href={`/sumber?source=${p.id}`}>
                      <span className="entry-icon">
                        <FileText size={22} />
                      </span>
                      <div>
                        <span className="eyebrow">{p.publisher}</span>
                        <h2>{p.locator}</h2>
                        <p>{p.title}</p>
                        <small>{p.version}</small>
                      </div>
                      <ArrowUpRight size={20} />
                    </Link>
                  ))}
              </div>
              {!library.passages.some((p) => matchesQuery(query, p.title, p.locator, p.body)) && (
                <EmptyResults />
              )}
            </>
          ))}
        <div className="workspace-footnote">
          <ShieldCheck size={15} />
          <p>
            Belajar dengan sumber, melangkah dengan pemahaman. Konten pengantar belum ditinjau ahli
            independen dan tidak menggantikan polis atau nasihat profesional.
          </p>
        </div>
      </div>
    </div>
  );
}

function EmptyResults() {
  return (
    <div className="empty-results">
      <Search size={25} />
      <h3>Belum ada hasil yang sesuai.</h3>
      <p>
        Coba satu istilah seperti “polis”, “klaim”, atau “cashless”. Anda juga dapat menjelajahi
        topik melalui peta.
      </p>
      <Link className="text-link" href="/">
        Buka peta pengetahuan <ArrowRight size={15} />
      </Link>
    </div>
  );
}

function Reader({ entry, library }: { entry: Entry; library: Library }) {
  const [checked, setChecked] = useState<number[]>([]);
  return (
    <article className="guide-reader" key={entry.slug}>
      <Link href="/panduan" className="text-link">
        ← Semua panduan
      </Link>
      <p className="eyebrow">PANDUAN BELAJAR MANDIRI</p>
      <h2>{entry.title}</h2>
      <p className="reader-lead">{entry.summary}</p>
      <p>{entry.body}</p>
      <div className="checklist-header">
        <h3>Langkah yang bisa Anda ikuti</h3>
        <span>
          {checked.length}/{entry.steps.length} ditandai
        </span>
      </div>
      <div className="guide-checklist">
        {entry.steps.map((step, i) => (
          <label key={step}>
            <input
              type="checkbox"
              checked={checked.includes(i)}
              onChange={(e) =>
                setChecked((c) => (e.target.checked ? [...c, i] : c.filter((v) => v !== i)))
              }
            />
            <span>
              <small>LANGKAH {String(i + 1).padStart(2, "0")}</small>
              {step}
            </span>
          </label>
        ))}
      </div>
      <p className="checklist-note">
        <LockKeyhole size={13} />
        Tanda langkah hanya untuk sesi halaman ini. Tidak ada informasi pribadi yang dikirim.
      </p>
      <h3 className="panel-section-title">Konsep untuk melengkapi pemahaman</h3>
      <div className="topic-pills">
        {entry.terms.map((slug) => (
          <Link href={`/?node=${slug}`} key={slug}>
            {library.nodes.find((n) => n.slug === slug)?.title ?? slug}
            <ArrowUpRight size={12} />
          </Link>
        ))}
      </div>
      <SourceReference library={library} id={entry.passage_id} />
    </article>
  );
}
