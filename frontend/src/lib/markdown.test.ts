import { describe, expect, it } from "vitest";
import { CALLOUT_DIRECTIVES, extractToc } from "./markdown";

describe("extractToc", () => {
  it("collects h2 and h3 headings with slugged ids", () => {
    const md = [
      "## Konsep",
      "teks",
      "### Sub bagian",
      "## Kesalahpahaman umum",
      "#### terlalu dalam, diabaikan",
    ].join("\n");

    const toc = extractToc(md);
    expect(toc).toEqual([
      { id: "konsep", text: "Konsep", depth: 2 },
      { id: "sub-bagian", text: "Sub bagian", depth: 3 },
      { id: "kesalahpahaman-umum", text: "Kesalahpahaman umum", depth: 2 },
    ]);
  });

  it("strips inline markdown from heading text", () => {
    expect(extractToc("## Apa itu **risiko**?")[0]).toMatchObject({ text: "Apa itu risiko?" });
  });
});

describe("callout directive registry", () => {
  it("maps every authored directive name to a tone and label", () => {
    for (const name of ["definisi", "penting", "kesalahpahaman", "perlu-verifikasi"]) {
      expect(CALLOUT_DIRECTIVES[name]).toBeDefined();
      expect(CALLOUT_DIRECTIVES[name]!.label.length).toBeGreaterThan(0);
    }
  });
});
