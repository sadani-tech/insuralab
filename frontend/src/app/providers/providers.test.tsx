import { render, screen } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import * as api from "@/lib/api";
import ProvidersPage from "./page";
import ProviderPage from "./[slug]/page";

afterEach(() => vi.restoreAllMocks());

const providerSummary = {
  id: 1,
  name: "Allianz",
  slug: "allianz",
  country_of_origin: "Jerman",
  last_reviewed_at: "2026-08-18",
  products_count: 11,
  articles_count: 4,
};

describe("Providers landing", () => {
  it("lists providers as case studies and states there is no ranking", async () => {
    vi.spyOn(api, "getProviders").mockResolvedValue({
      data: [providerSummary],
      meta: { total: 1 } as never,
    });

    render(await ProvidersPage());

    expect(screen.getByRole("link", { name: /Allianz/ })).toHaveAttribute(
      "href",
      "/providers/allianz",
    );
    expect(screen.getByText(/tidak memberi peringkat, skor, atau label/i)).toBeInTheDocument();
    // no rank / score / winner badge anywhere
    expect(screen.queryByText(/^#\d/)).not.toBeInTheDocument();
  });
});

describe("Provider detail", () => {
  it("shows the independence notice, the Indonesian entities, and provider articles", async () => {
    vi.spyOn(api, "getProvider").mockResolvedValue({
      ...providerSummary,
      description: "Allianz adalah kelompok jasa keuangan global.",
      presence_in_indonesia: "Beroperasi melalui tiga entitas.",
      website_url: "https://www.allianz.co.id",
      status: "active",
      metadata: {
        entities: [
          { name: "Allianz Life Indonesia", focus: "Jiwa & kesehatan" },
          { name: "Allianz Utama Indonesia", focus: "Asuransi umum" },
        ],
        business_areas: ["Jiwa", "Syariah"],
        distribution: "ASN — kanal keagenan, bukan penerbit polis.",
      },
      counts: { products: 11, articles: 4 },
      articles: [
        {
          id: 9,
          title: "Struktur Keagenan Allianz",
          slug: "struktur-keagenan-allianz",
          excerpt: "BE, BP, generasi.",
          level: "advanced",
          estimated_reading_minutes: 5,
          is_featured: false,
          knowledge_type: "provider_specific",
          category: { id: 10, name: "Ethics & Conduct", slug: "ethics-conduct" },
          provider: providerSummary,
          published_at: null,
          reference_status: "needs_review",
          last_reviewed_at: "2026-08-18",
        },
      ],
      sources: [],
    });
    vi.spyOn(api, "getProducts").mockResolvedValue({ data: [], meta: { total: 0 } as never });

    render(await ProviderPage({ params: Promise.resolve({ slug: "allianz" }) }));

    expect(screen.getByText("Konten edukatif independen")).toBeInTheDocument();
    expect(screen.getByText("Allianz Life Indonesia")).toBeInTheDocument();
    expect(screen.getByText("Allianz Utama Indonesia")).toBeInTheDocument();
    expect(screen.getByText(/ASN — kanal keagenan, bukan penerbit polis/)).toBeInTheDocument();
    expect(screen.getByRole("link", { name: /Struktur Keagenan Allianz/ })).toHaveAttribute(
      "href",
      "/learn/struktur-keagenan-allianz",
    );
  });
});
