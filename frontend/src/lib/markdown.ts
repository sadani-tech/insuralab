import GithubSlugger from "github-slugger";
import { visit } from "unist-util-visit";

/** Callout directive names authored in article Markdown → tone + default label. */
export const CALLOUT_DIRECTIVES: Record<string, { tone: string; label: string }> = {
  definisi: { tone: "definition", label: "Definisi" },
  penting: { tone: "important", label: "Penting" },
  contoh: { tone: "example", label: "Contoh" },
  kesalahpahaman: { tone: "misconception", label: "Kesalahpahaman umum" },
  "catatan-provider": { tone: "provider", label: "Konten provider-spesifik" },
  "referensi-historis": { tone: "historical", label: "Referensi historis" },
  "perlu-verifikasi": { tone: "needs-review", label: "Perlu verifikasi" },
};

/**
 * remark plugin: turn our `:::name` container directives and the inline
 * `:istilah[teks]{slug=…}` text directive into plain HTML element names that
 * react-markdown can map to React components. Unknown directive names are left
 * as-is (rendered as text) rather than throwing.
 */
export function remarkInsuralabDirectives() {
  return (tree: unknown) => {
    visit(tree as never, (node: never) => {
      const n = node as {
        type: string;
        name?: string;
        attributes?: Record<string, string>;
        children?: { value?: string }[];
        data?: Record<string, unknown>;
      };

      if (n.type === "containerDirective" && n.name && CALLOUT_DIRECTIVES[n.name]) {
        const config = CALLOUT_DIRECTIVES[n.name]!;
        // A `:::name[Custom label]` puts the label in the first paragraph's
        // directiveLabel; keep it simple and use the default label.
        n.data = {
          ...(n.data ?? {}),
          hName: "callout",
          hProperties: { tone: config.tone, label: config.label },
        };
      }

      if (n.type === "textDirective" && n.name === "istilah") {
        const slug = n.attributes?.slug ?? "";
        n.data = {
          ...(n.data ?? {}),
          hName: "glossaryterm",
          hProperties: { slug },
        };
      }
    });
  };
}

/**
 * remark plugin: add slug ids to `h2`/`h3` so the table of contents can link to
 * them. Uses the same slugger the ToC extraction below uses.
 */
export function remarkHeadingIds() {
  return (tree: unknown) => {
    const slugger = new GithubSlugger();
    visit(tree as never, "heading", (node: never) => {
      const n = node as {
        depth: number;
        children: { value?: string }[];
        data?: Record<string, unknown>;
      };
      if (n.depth !== 2 && n.depth !== 3) return;
      const text = n.children.map((c) => c.value ?? "").join("");
      const id = slugger.slug(text);
      n.data = {
        ...(n.data ?? {}),
        hProperties: { ...(((n.data ?? {}) as { hProperties?: object }).hProperties ?? {}), id },
      };
    });
  };
}

export type TocEntry = { id: string; text: string; depth: 2 | 3 };

/** Parse `## ` / `### ` headings from raw Markdown for the ToC. */
export function extractToc(markdown: string): TocEntry[] {
  const slugger = new GithubSlugger();
  const entries: TocEntry[] = [];
  for (const line of markdown.split("\n")) {
    const match = /^(#{2,3})\s+(.+?)\s*$/.exec(line);
    if (!match) continue;
    const depth = match[1]!.length as 2 | 3;
    const text = match[2]!.replace(/[*_`]/g, "").trim();
    entries.push({ id: slugger.slug(text), text, depth });
  }
  return entries;
}
