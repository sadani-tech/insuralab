import { render, screen } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import * as api from "@/lib/api";
import LearnPage from "./page";
import ArticlePage from "./[slug]/page";
import { LearningProgressProvider } from "@/components/learning/learning-progress";

const generalArticle = {
  id: 1,
  title: "Apa Itu Risiko?",
  slug: "apa-itu-risiko",
  excerpt: "Ringkasan risiko.",
  level: "beginner" as const,
  estimated_reading_minutes: 3,
  is_featured: true,
  knowledge_type: "general" as const,
  category: { id: 1, name: "Fundamentals", slug: "fundamentals" },
  provider: null,
  published_at: null,
  reference_status: "current" as const,
  last_reviewed_at: null,
};

afterEach(() => vi.restoreAllMocks());

describe("Learn landing", () => {
  it("renders the hero, the Start Here sequence and the article list from the API", async () => {
    vi.spyOn(api, "getLearningPaths").mockResolvedValue([
      {
        id: 1,
        title: "Dasar-Dasar Asuransi",
        slug: "dasar-dasar-asuransi",
        description: "Urutan singkat.",
        level: "beginner",
        estimated_duration_minutes: 30,
        lessons_count: 1,
        quizzes_count: 0,
      },
    ]);
    vi.spyOn(api, "getLearningPath").mockResolvedValue({
      id: 1,
      title: "Dasar-Dasar Asuransi",
      slug: "dasar-dasar-asuransi",
      description: "Urutan singkat.",
      level: "beginner",
      estimated_duration_minutes: 30,
      lessons_count: 1,
      quizzes_count: 0,
      items: [{ position: 1, type: "article", note: null, article: generalArticle, quiz: null }],
    });
    vi.spyOn(api, "getCategories").mockResolvedValue([
      {
        id: 1,
        name: "Fundamentals",
        slug: "fundamentals",
        description: null,
        sort_order: 0,
        articles_count: 5,
      },
    ]);
    vi.spyOn(api, "getArticles").mockResolvedValue({
      data: [{ ...generalArticle, reference_status: "current", last_reviewed_at: null }],
      meta: { total: 1 } as never,
    });

    render(
      <LearningProgressProvider>
        {await LearnPage({ searchParams: Promise.resolve({}) })}
      </LearningProgressProvider>,
    );

    expect(
      screen.getByRole("heading", { name: /Belajar Asuransi dari Dasarnya/i }),
    ).toBeInTheDocument();
    expect(screen.getByRole("heading", { name: "Dasar-Dasar Asuransi" })).toBeInTheDocument();
    expect(screen.getAllByText("Apa Itu Risiko?").length).toBeGreaterThan(0);
  });
});

describe("Article reader", () => {
  const providerDetail = {
    ...generalArticle,
    id: 9,
    title: "Flexi CI dalam Materi Pelatihan",
    slug: "flexi-ci",
    knowledge_type: "provider_specific" as const,
    provider: {
      id: 1,
      name: "Allianz",
      slug: "allianz",
      country_of_origin: "Germany",
      last_reviewed_at: "2026-08-18",
    },
    body: "## Konsep\n\nIsi.\n\n:::perlu-verifikasi\nAngka pelatihan.\n:::",
    key_takeaways: ["Poin satu."],
    tags: [],
    glossary_terms: [],
    related: [],
    trust: {
      is_provider_specific: true,
      reference_status: "needs_review" as const,
      reference_period: "Agustus 2026",
      last_reviewed_at: "2026-08-18",
    },
    sources: [
      {
        id: 1,
        title: "Allianz Basic Training",
        publisher: "Allianz",
        url: null,
        published_at: null,
        accessed_at: null,
        reference_period: "Agustus 2026",
        notes: "Catatan inkonsistensi.",
      },
    ],
  };

  it("shows the provider content notice and needs-review notice for a provider-specific article", async () => {
    vi.spyOn(api, "getArticle").mockResolvedValue(providerDetail);

    render(
      <LearningProgressProvider>
        {await ArticlePage({
          params: Promise.resolve({ slug: "flexi-ci" }),
          searchParams: Promise.resolve({}),
        })}
      </LearningProgressProvider>,
    );

    expect(screen.getByText("Konten provider-spesifik")).toBeInTheDocument();
    expect(
      screen.getByText(/membutuhkan pengecekan terhadap sumber resmi terbaru/i),
    ).toBeInTheDocument();
    expect(screen.getByText("Poin Penting")).toBeInTheDocument();
    expect(screen.getByText(/Sumber & Referensi/)).toBeInTheDocument();
  });

  it("does not show provider notices for a general article", async () => {
    vi.spyOn(api, "getArticle").mockResolvedValue({
      ...providerDetail,
      knowledge_type: "general",
      provider: null,
      trust: {
        is_provider_specific: false,
        reference_status: null,
        reference_period: null,
        last_reviewed_at: null,
      },
    });

    render(
      <LearningProgressProvider>
        {await ArticlePage({
          params: Promise.resolve({ slug: "x" }),
          searchParams: Promise.resolve({}),
        })}
      </LearningProgressProvider>,
    );
    expect(screen.queryByText("Konten provider-spesifik")).not.toBeInTheDocument();
  });
});
