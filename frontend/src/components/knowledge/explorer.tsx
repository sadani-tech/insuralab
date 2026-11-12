"use client";

import {
  Component,
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
  type ReactNode,
} from "react";
import dynamic from "next/dynamic";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import {
  ArrowRight,
  ArrowUp,
  ArrowUpRight,
  BookOpen,
  Brain,
  ChevronRight,
  FileText,
  Info,
  List,
  MessageSquareText,
  Network,
  RotateCcw,
  Search,
  Sparkles,
  X,
} from "lucide-react";
import { groups, matchesQuery, type Entry, type Library } from "@/lib/knowledge";

const NeuralView = dynamic(() => import("./neural-view"), {
  ssr: false,
  loading: () => (
    <div className="graph-wait" role="status">
      Menyiapkan Neural View…
    </div>
  ),
});
const GraphView = dynamic(() => import("./graph-3d"), {
  ssr: false,
  loading: () => (
    <div className="graph-wait" role="status">
      Menyiapkan Graph View…
    </div>
  ),
});

class SceneBoundary extends Component<
  { children: ReactNode; onFallback: () => void },
  { failed: boolean }
> {
  state = { failed: false };
  static getDerivedStateFromError() {
    return { failed: true };
  }
  componentDidCatch() {
    this.props.onFallback();
  }
  render() {
    return this.state.failed ? (
      <div className="graph-wait">
        <p>Visualisasi tidak tersedia pada perangkat ini.</p>
        <button className="knowledge-button" onClick={this.props.onFallback}>
          Buka daftar konsep
        </button>
      </div>
    ) : (
      this.props.children
    );
  }
}

const questionStopwords = new Set([
  "apa",
  "apakah",
  "itu",
  "yang",
  "dan",
  "atau",
  "di",
  "ke",
  "dari",
  "untuk",
  "dengan",
  "saya",
  "bagaimana",
  "mengapa",
  "kenapa",
  "tentang",
  "sebuah",
  "bisa",
  "adalah",
  "semua",
  "berarti",
]);
function relatedAnswers(question: string, entries: Entry[]) {
  const tokens = question
    .toLocaleLowerCase("id")
    .replace(/[^\p{L}\p{N}\s-]/gu, " ")
    .split(/\s+/)
    .filter((t) => t.length > 2 && !questionStopwords.has(t));
  if (!tokens.length) return [];
  return entries
    .map((entry) => ({
      entry,
      score: tokens.filter((t) => matchesQuery(t, entry.title, entry.keywords)).length,
    }))
    .filter((item) => item.score > 0)
    .sort((a, b) => b.score - a.score)
    .slice(0, 6)
    .map((item) => item.entry);
}

export function KnowledgeExplorer({ library }: { library: Library }) {
  const params = useSearchParams();
  const rawView = params.get("view");
  // Preserve previous shared URLs while giving the two visual modes distinct identities.
  const view =
    rawView === "list" || (!rawView && Boolean(params.get("q") || params.get("kind")))
      ? "list"
      : rawView === "graph" || rawView === "3d" || rawView === "2d"
        ? "graph"
        : "neural";
  const query = params.get("q") ?? "";
  const question = params.get("ask") ?? "";
  const group = params.get("group") ?? "all";
  const kind = params.get("kind") ?? "all";
  const [draft, setDraft] = useState(question);
  const [aiNotice, setAiNotice] = useState(false);
  const [sceneKey, setSceneKey] = useState(0);
  const heading = useRef<HTMLHeadingElement>(null);
  const composer = useRef<HTMLTextAreaElement>(null);
  const patch = useCallback((changes: Record<string, string | null>, replace = false) => {
    const next = new URLSearchParams(window.location.search);
    Object.entries(changes).forEach(([key, value]) =>
      value ? next.set(key, value) : next.delete(key),
    );
    window.history[replace ? "replaceState" : "pushState"](
      null,
      "",
      `/${next.size ? `?${next}` : ""}`,
    );
  }, []);
  const fallback = useCallback(() => patch({ view: "list" }, true), [patch]);
  const select = useCallback(
    (id: number) => {
      const node = library.nodes.find((n) => n.id === id);
      if (node) patch({ node: node.slug });
    },
    [library.nodes, patch],
  );
  const selected = library.nodes.find((n) => n.slug === params.get("node"));
  useEffect(() => {
    if (selected) heading.current?.focus({ preventScroll: true });
    const escape = (event: KeyboardEvent) => {
      if (event.key === "Escape" && selected) patch({ node: null });
    };
    document.addEventListener("keydown", escape);
    return () => document.removeEventListener("keydown", escape);
  }, [selected, patch]);
  const nodes = useMemo(
    () =>
      library.nodes.filter(
        (n) =>
          (group === "all" || n.group === group) &&
          (kind === "all" || n.kind === kind) &&
          matchesQuery(query, n.title, n.summary, n.aliases),
      ),
    [library.nodes, group, kind, query],
  );
  const edges = useMemo(() => {
    const ids = new Set(nodes.map((n) => n.id));
    return library.edges.filter((e) => ids.has(e.source) && ids.has(e.target));
  }, [library.edges, nodes]);
  const faqs = library.entries.filter((e) => e.kind === "faq");
  const results = question ? relatedAnswers(question, library.entries) : [];
  const highlighted = useMemo(() => {
    if (!question) return [];
    const slugs = new Set(relatedAnswers(question, library.entries).flatMap((e) => e.terms));
    return library.nodes.filter((n) => slugs.has(n.slug)).map((n) => n.id);
  }, [question, library.entries, library.nodes]);
  const related = selected
    ? library.edges.filter((e) => e.source === selected.id || e.target === selected.id)
    : [];
  const selectedPassage = library.passages.find((p) => p.id === selected?.passage_id);
  const showList = view === "list";
  const submit = (value: string) => {
    if (!value.trim()) {
      composer.current?.focus();
      return;
    }
    setDraft(value);
    patch({ ask: value.trim().slice(0, 500), node: null });
  };

  return (
    <div className="hpt-workspace">
      <section
        className={`hpt-visual ${view === "neural" && !showList ? "is-neural" : "is-graph"}`}
        aria-label="Explorer pengetahuan"
      >
        <div className="hpt-viewbar">
          <h1>Jelajah perlindungan</h1>
          <div className="hpt-mode-switch" aria-label="Tampilan peta">
            <button aria-pressed={view === "neural"} onClick={() => patch({ view: "neural" })}>
              <Brain size={15} />
              Neural View
            </button>
            <button aria-pressed={view === "graph"} onClick={() => patch({ view: "graph" })}>
              <Network size={15} />
              Graph View
            </button>
            <button
              aria-label="Daftar"
              aria-pressed={view === "list"}
              onClick={() => patch({ view: "list" })}
            >
              <List size={15} />
              <span>Daftar</span>
            </button>
          </div>
        </div>
        <div className="hpt-stage">
          {!showList && (
            <div className="hpt-scene-title">
              <span className="eyebrow">
                {view === "neural" ? "NEURAL INSURALAB" : "PETA PENGETAHUAN"}
              </span>
              <h2>
                {selected
                  ? selected.title
                  : question && highlighted.length
                    ? "Keterkaitan dari materi yang ditemukan."
                    : view === "neural"
                      ? "Setiap pemahaman saling terhubung."
                      : "Kenali hubungan di balik perlindungan."}
              </h2>
              <p>
                {nodes.length} simpul · {edges.length} hubungan editorial
              </p>
            </div>
          )}
          {!showList && (
            <div className="hpt-scene-count">
              <span className="map-status-dot" /> Tanpa AI
            </div>
          )}
          {nodes.length === 0 ? (
            <div className="graph-wait">
              <Search size={25} />
              <h3>Konsep belum ditemukan.</h3>
              <p>Coba istilah lain atau tampilkan semua topik.</p>
              <button
                className="knowledge-button"
                onClick={() => patch({ q: null, group: null, kind: null })}
              >
                Lihat semua konsep
              </button>
            </div>
          ) : showList ? (
            <div className="hpt-list-wrap">
              <div className="hpt-list-title">
                <h2>Telusuri konsep</h2>
                <span>{nodes.length} hasil</span>
              </div>
              <div className="concept-list">
                {nodes.map((n) => (
                  <button
                    key={n.id}
                    onClick={() => select(n.id)}
                    aria-pressed={n.id === selected?.id}
                  >
                    <i style={{ backgroundColor: groups[n.group]?.color }} />
                    <span>
                      <strong>{n.title}</strong>
                      <small>{n.summary}</small>
                    </span>
                    <ChevronRight size={16} />
                  </button>
                ))}
              </div>
            </div>
          ) : (
            <SceneBoundary key={`${view}-${sceneKey}`} onFallback={fallback}>
              {view === "neural" ? (
                <NeuralView
                  nodes={nodes}
                  edges={edges}
                  selected={selected?.id}
                  highlighted={highlighted}
                  onSelect={select}
                  onFallback={fallback}
                />
              ) : (
                <GraphView
                  nodes={nodes}
                  edges={edges}
                  selected={selected?.id}
                  highlighted={highlighted}
                  onSelect={select}
                  onFallback={fallback}
                />
              )}
            </SceneBoundary>
          )}
          {!showList && (
            <div className="hpt-scene-caption">
              <span>Seret untuk memutar · Gulir untuk zoom · Pilih penanda untuk membaca</span>
              {view === "neural" && (
                <small>
                  Partikel membentuk ilustrasi; penanda besar mewakili konsep yang tersedia.
                </small>
              )}
            </div>
          )}
        </div>
        <div className="hpt-map-tools">
          <label className="hpt-concept-search">
            <Search size={17} />
            <input
              aria-label="Cari pengetahuan"
              value={query}
              maxLength={160}
              onChange={(e) => patch({ q: e.target.value || null, view: "list" }, true)}
              placeholder="Cari konsep, mis. cashless atau polis…"
            />
            {query && (
              <button aria-label="Hapus pencarian" onClick={() => patch({ q: null })}>
                <X size={15} />
              </button>
            )}
          </label>
          <div className="hpt-filters">
            <select
              aria-label="Jenis simpul"
              value={kind}
              onChange={(e) =>
                patch({ kind: e.target.value === "all" ? null : e.target.value, view: "list" })
              }
            >
              {Object.entries({
                all: "Semua jenis",
                concept: "Konsep",
                risk: "Risiko",
                protection: "Proteksi",
                process: "Proses",
                provider: "Penerbit",
                product: "Produk",
                source: "Sumber",
              }).map(([value, label]) => (
                <option key={value} value={value}>
                  {label}
                </option>
              ))}
            </select>
            <button
              aria-label="Reset tampilan"
              onClick={() => {
                patch({ q: null, group: null, kind: null, node: null });
                setSceneKey((k) => k + 1);
              }}
            >
              <RotateCcw size={15} />
              <span>Reset</span>
            </button>
          </div>
        </div>
        <div className="hpt-topic-strip" aria-label="Filter topik">
          <button aria-pressed={group === "all"} onClick={() => patch({ group: null })}>
            Semua topik
          </button>
          {Object.entries(groups).map(([key, item]) => (
            <button
              key={key}
              aria-pressed={group === key}
              onClick={() => patch({ group: group === key ? null : key })}
            >
              <i style={{ background: item.color }} />
              {item.title}
            </button>
          ))}
        </div>
      </section>

      <section className="hpt-conversation" aria-label="Pertanyaan dan pengetahuan">
        <form
          className="hpt-composer"
          onSubmit={(e) => {
            e.preventDefault();
            submit(draft);
          }}
        >
          <label htmlFor="explorer-question">Apa yang ingin Anda pahami?</label>
          <textarea
            id="explorer-question"
            ref={composer}
            value={draft}
            maxLength={500}
            placeholder="Tanyakan seputar risiko, polis, atau perlindungan…"
            onChange={(e) => setDraft(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === "Enter" && !e.shiftKey && !e.nativeEvent.isComposing) {
                e.preventDefault();
                submit(draft);
              }
            }}
          />
          <div>
            <span>Enter untuk mencari · Shift+Enter baris baru</span>
            <button aria-label="Cari jawaban terkurasi" type="submit" disabled={!draft.trim()}>
              <ArrowUp size={21} />
            </button>
          </div>
        </form>
        <div className="hpt-curated-note">
          <Info size={14} />
          <span>Jawaban terkurasi, dengan sumber yang dapat dibaca.</span>
          <button aria-label="Tentang bantuan AI" onClick={() => setAiNotice((v) => !v)}>
            <Sparkles size={14} />
            AI opsional
          </button>
        </div>
        {aiNotice && (
          <p className="hpt-ai-notice" role="status">
            Bantuan AI belum tersedia. Pencarian, jawaban, dan materi terkurasi tetap dapat
            digunakan tanpa AI.
          </p>
        )}

        {selected ? (
          <aside className="concept-panel hpt-detail" aria-label="Detail konsep">
            <div className="panel-topline">
              <span className="eyebrow">KONSEP TERPILIH</span>
              <button aria-label="Tutup detail konsep" onClick={() => patch({ node: "none" })}>
                <X size={18} />
              </button>
            </div>
            <span className="concept-group" style={{ color: groups[selected.group]?.color }}>
              {groups[selected.group]?.title}
            </span>
            <h2 ref={heading} tabIndex={-1}>
              {selected.title}
            </h2>
            <p className="concept-description">{selected.summary}</p>
            <div className="editorial-note">
              <Info size={14} />
              Catatan pengantar · belum ditinjau ahli independen
            </div>
            {selectedPassage && (
              <Link className="source-reference" href={`/sumber?source=${selectedPassage.id}`}>
                <FileText size={18} />
                <span>
                  <strong>{selectedPassage.title}</strong>
                  <small>
                    {selectedPassage.locator} · {selectedPassage.version}
                  </small>
                </span>
                <ArrowUpRight size={15} />
              </Link>
            )}
            <h3 className="panel-section-title">
              Keterkaitan konsep <span>{related.length}</span>
            </h3>
            <div className="related-concepts">
              {related.map((edge) => {
                const other = library.nodes.find(
                  (n) => n.id === (edge.source === selected.id ? edge.target : edge.source),
                );
                return (
                  other && (
                    <details key={edge.id}>
                      <summary>
                        <span>{other.title}</span>
                        <ChevronRight size={14} />
                      </summary>
                      <span className="relation-label">{edge.label}</span>
                      <p>{edge.explanation}</p>
                      <div className="relation-actions">
                        <button onClick={() => select(other.id)}>
                          Buka konsep <ArrowRight size={12} />
                        </button>
                        <Link href={`/sumber?source=${edge.passage_id}`}>
                          Dasar hubungan <ArrowUpRight size={12} />
                        </Link>
                      </div>
                    </details>
                  )
                );
              })}
            </div>
            <button className="knowledge-button" onClick={() => submit(selected.title)}>
              Cari jawaban terkait <ArrowRight size={15} />
            </button>
            <Link
              className="panel-guide-link"
              href={`/panduan?entry=${library.entries.find((e) => e.kind === "guide" && e.terms.includes(selected.slug))?.slug ?? "membaca-riplay"}`}
            >
              <BookOpen size={16} />
              Baca panduan terkait
            </Link>
          </aside>
        ) : question ? (
          <section className="hpt-question-results" aria-label="Hasil pertanyaan">
            <div className="hpt-section-label">
              <span>HASIL PENELUSURAN</span>
              <button
                aria-label="Hapus pertanyaan"
                onClick={() => {
                  patch({ ask: null });
                  setDraft("");
                }}
              >
                <X size={16} />
              </button>
            </div>
            <h2>{question}</h2>
            <p>
              {results.length
                ? "Berikut materi terkait. Pilih pertanyaan yang sesuai untuk membaca jawaban editorialnya."
                : "Belum ada materi yang cocok. Coba istilah lebih singkat, misalnya polis, premi, atau klaim."}
            </p>
            {results.map((entry) => (
              <details className="hpt-answer" key={entry.slug}>
                <summary>
                  <span>
                    <small>{entry.kind === "faq" ? "JAWABAN TERKURASI" : "PANDUAN"}</small>
                    {entry.title}
                  </span>
                  <ChevronRight size={17} />
                </summary>
                <p>{entry.body}</p>
                <Link href={`/${entry.kind === "faq" ? "tanya" : "panduan"}?entry=${entry.slug}`}>
                  Baca selengkapnya <ArrowUpRight size={14} />
                </Link>
                <Link href={`/sumber?source=${entry.passage_id}`}>
                  Lihat sumber <FileText size={14} />
                </Link>
                <div className="hpt-answer-concepts">
                  {entry.terms.slice(0, 4).map((slug) => {
                    const node = library.nodes.find((n) => n.slug === slug);
                    return (
                      node && (
                        <button key={slug} onClick={() => select(node.id)}>
                          <Network size={12} />
                          {node.title}
                        </button>
                      )
                    );
                  })}
                </div>
              </details>
            ))}
            {!results.length && (
              <Link className="knowledge-button" href="/tanya">
                Jelajahi semua jawaban <ArrowRight size={15} />
              </Link>
            )}
          </section>
        ) : (
          <section className="hpt-question-examples">
            <div className="hpt-section-label">
              <span>MULAI DARI PERTANYAAN</span>
              <MessageSquareText size={16} />
            </div>
            <div className="hpt-example-grid">
              {faqs.slice(0, 4).map((entry) => (
                <button key={entry.slug} onClick={() => submit(entry.title)}>
                  <span>
                    <MessageSquareText size={16} />
                    PERLINDUNGAN
                  </span>
                  <strong>{entry.title}</strong>
                  <small>
                    Telusuri jawaban <ArrowRight size={14} />
                  </small>
                </button>
              ))}
            </div>
          </section>
        )}

        <section className="hpt-knowledge-base">
          <div className="hpt-section-label">
            <span>BASIS PENGETAHUAN</span>
            <BookOpen size={16} />
          </div>
          <p>Pelajari konsep, baca panduan, dan periksa sumbernya.</p>
          <div className="hpt-library-links">
            <Link href="/tanya">
              <MessageSquareText size={19} />
              <span>
                <strong>{faqs.length} jawaban terkurasi</strong>
                <small>Pertanyaan yang sering muncul</small>
              </span>
              <ArrowUpRight size={16} />
            </Link>
            <Link href="/panduan">
              <BookOpen size={19} />
              <span>
                <strong>
                  {library.entries.filter((e) => e.kind === "guide").length} panduan praktis
                </strong>
                <small>Langkah dan checklist mandiri</small>
              </span>
              <ArrowUpRight size={16} />
            </Link>
            <Link href="/sumber">
              <FileText size={19} />
              <span>
                <strong>Sumber & catatan bacaan</strong>
                <small>Versi, rujukan, dan batas informasi</small>
              </span>
              <ArrowUpRight size={16} />
            </Link>
          </div>
          <p className="hpt-review-note">
            Materi pengantar masih memerlukan tinjauan ahli. Informasi produk mengikuti versi
            sumber, bukan rekomendasi individual.
          </p>
        </section>
      </section>
    </div>
  );
}
