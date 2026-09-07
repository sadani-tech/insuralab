import { render, screen } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import * as api from "@/lib/api";
import ProductsPage from "./page";
import ProductPage from "./[slug]/page";

afterEach(() => vi.restoreAllMocks());

const providerSummary = {
  id: 1,
  name: "Allianz",
  slug: "allianz",
  country_of_origin: "Jerman",
  last_reviewed_at: "2026-08-18",
};

const productSummary = {
  id: 2,
  name: "Allianz Flexi CI",
  slug: "allianz-flexi-ci",
  short_description: "Rider penyakit kritis.",
  insurance_scheme: "conventional",
  product_structure: "other",
  provider: providerSummary,
  product_category: {
    id: 3,
    name: "Critical Illness Rider",
    slug: "critical-illness-rider",
    kind: "rider",
    description: null,
  },
  protection_categories: [],
  reference_status: "needs_review" as const,
  reference_period: "Agustus 2026",
  last_reviewed_at: "2026-08-18",
};

describe("Products landing", () => {
  it("frames products as educational and never as a purchase", async () => {
    vi.spyOn(api, "getProducts").mockResolvedValue({
      data: [productSummary],
      meta: { total: 1 } as never,
    });

    render(await ProductsPage({ searchParams: Promise.resolve({}) }));

    expect(
      screen.getByText(/tidak menjual dan tidak merekomendasikan produk/i),
    ).toBeInTheDocument();
    expect(screen.queryByRole("button", { name: /beli|apply|get quote/i })).not.toBeInTheDocument();
    expect(screen.getByRole("link", { name: /Allianz Flexi CI/ })).toHaveAttribute(
      "href",
      "/products/allianz-flexi-ci",
    );
  });
});

describe("Product detail", () => {
  const detail = {
    ...productSummary,
    description:
      "## Apa produk ini?\n\nRider penyakit kritis.\n\n## Batasan penting\n\nAngka pelatihan.",
    protection_categories: [
      {
        id: 1,
        key: "critical_illness",
        name: "Critical Illness",
        slug: "critical-illness",
        short_description: null,
        description: null,
        icon: null,
        sort_order: 0,
        is_primary: true,
      },
    ],
    metadata: { payout_model: "lump_sum" },
    related_articles: [
      {
        id: 5,
        title: "Apa Itu Penyakit Kritis",
        slug: "penyakit-kritis",
        excerpt: "Konsep.",
        level: "beginner" as const,
        estimated_reading_minutes: 3,
        is_featured: false,
        knowledge_type: "general" as const,
        category: { id: 4, name: "Critical Illness", slug: "critical-illness" },
        provider: null,
        published_at: null,
        reference_status: "current" as const,
        last_reviewed_at: null,
      },
    ],
    related_terms: [{ term: "Lump Sum", slug: "lump-sum", short_definition: "Jumlah tetap." }],
    trust: {
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
        notes: null,
      },
    ],
  };

  it("shows reference status, concept-first links, glossary terms and sources", async () => {
    vi.spyOn(api, "getProduct").mockResolvedValue(detail);

    render(await ProductPage({ params: Promise.resolve({ slug: "allianz-flexi-ci" }) }));

    expect(screen.getByText("Needs review")).toBeInTheDocument();
    expect(
      screen.getByRole("heading", { name: /Pahami konsepnya lebih dulu/i }),
    ).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /Apa Itu Penyakit Kritis/ })).toHaveAttribute(
      "href",
      "/learn/penyakit-kritis",
    );
    expect(screen.getByRole("link", { name: "Lump Sum" })).toHaveAttribute(
      "href",
      "/glossary/lump-sum",
    );
    expect(screen.getByText(/Sumber & Referensi/)).toBeInTheDocument();
    expect(screen.queryByText(/produk unit link \/ PAYDI/)).not.toBeInTheDocument();
  });

  it("shows the PAYDI risk note for a unit-link product", async () => {
    vi.spyOn(api, "getProduct").mockResolvedValue({
      ...detail,
      product_structure: "paydi",
    });

    render(await ProductPage({ params: Promise.resolve({ slug: "smartlink" }) }));
    expect(screen.getByText(/produk unit link \/ PAYDI/)).toBeInTheDocument();
    expect(screen.getByText(/tidak dijamin/)).toBeInTheDocument();
  });

  it("shows the sharia terminology note for a sharia product", async () => {
    vi.spyOn(api, "getProduct").mockResolvedValue({
      ...detail,
      insurance_scheme: "sharia",
    });

    render(await ProductPage({ params: Promise.resolve({ slug: "allisya" }) }));
    expect(screen.getByText("Terminologi syariah")).toBeInTheDocument();
  });
});
