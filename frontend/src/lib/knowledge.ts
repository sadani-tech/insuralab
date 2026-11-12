import { z } from "zod";
import { apiGet, singleEnvelope } from "@/lib/api/client";

const nodeSchema = z.object({
  id: z.number(),
  slug: z.string(),
  title: z.string(),
  summary: z.string(),
  body: z.string(),
  group: z.string(),
  aliases: z.array(z.string()),
  reference_status: z.string(),
  reviewed_at: z.string().nullable(),
  kind: z.string().default("concept"),
  passage_id: z.number().optional(),
});
const entrySchema = z.object({
  slug: z.string(),
  kind: z.enum(["faq", "guide"]),
  title: z.string(),
  summary: z.string(),
  body: z.string(),
  steps: z.array(z.string()),
  keywords: z.array(z.string()),
  terms: z.array(z.string()),
  passage_id: z.number(),
});
export const librarySchema = z.object({
  nodes: z.array(nodeSchema),
  edges: z.array(
    z.object({
      id: z.number(),
      source: z.number(),
      target: z.number(),
      label: z.string(),
      explanation: z.string(),
      passage_id: z.number(),
    }),
  ),
  entries: z.array(entrySchema),
  passages: z.array(
    z.object({
      id: z.number(),
      title: z.string(),
      publisher: z.string().nullable(),
      url: z.string().nullable(),
      version: z.string(),
      locator: z.string(),
      body: z.string(),
    }),
  ),
  ai: z.object({ available: z.boolean(), mode: z.string() }),
});
export type Library = z.infer<typeof librarySchema>;
export type Concept = Library["nodes"][number];
export type Entry = Library["entries"][number];
export type Edge = Library["edges"][number];
export const groups: Record<string, { title: string; color: string; description: string }> = {
  dasar: {
    title: "Dasar perlindungan",
    color: "#167a68",
    description: "Pahami risiko dan cara perlindungan bekerja.",
  },
  polis: {
    title: "Mengenal polis",
    color: "#4c70ab",
    description: "Kenali hak, kewajiban, dan batas perlindungan.",
  },
  proteksi: {
    title: "Jenis proteksi",
    color: "#a67739",
    description: "Jelajahi bentuk perlindungan dan manfaatnya.",
  },
  klaim: {
    title: "Klaim & layanan",
    color: "#997296",
    description: "Pahami proses dan cara pembayaran manfaat.",
  },
  proses: {
    title: "Proses & pendampingan",
    color: "#607c8a",
    description: "Telusuri perjalanan dari pengajuan ke layanan.",
  },
  syariah: {
    title: "Prinsip syariah",
    color: "#6d8850",
    description: "Pelajari akad dan prinsip saling menolong.",
  },
};
export async function getLibrary(): Promise<Library> {
  return (
    await apiGet("knowledge", singleEnvelope(librarySchema), {
      revalidate: false,
      signal: AbortSignal.timeout(8000),
    })
  ).data;
}
export function matchesQuery(query: string, ...fields: (string | string[])[]): boolean {
  const tokens = query.toLocaleLowerCase("id").trim().split(/\s+/).filter(Boolean);
  const text = fields.flat().join(" ").toLocaleLowerCase("id");
  return tokens.every((token) => text.includes(token));
}
