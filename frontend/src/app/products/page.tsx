import type { Metadata } from "next";
import Link from "next/link";
import { Container, PageHeader, Section } from "@/components/ui/container";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { EmptyState } from "@/components/ui/empty-state";
import { ProductTypeBadge } from "@/components/domain/product-type-badge";
import { ReferenceStatusBadge } from "@/components/domain/reference-status-badge";
import { EducationalDisclaimer } from "@/components/domain/educational-disclaimer";
import { getProducts } from "@/lib/api";

export const metadata: Metadata = {
  title: "Products",
  description:
    "Eksplorasi edukatif jenis produk asuransi — struktur, manfaat, batasan, dan konsep perlindungannya. Bukan untuk pembelian.",
};

export const revalidate = 300;

const SCHEMES = [
  { key: "", label: "Semua skema" },
  { key: "conventional", label: "Konvensional" },
  { key: "sharia", label: "Syariah" },
];
const STRUCTURES = [
  { key: "", label: "Semua struktur" },
  { key: "traditional", label: "Tradisional" },
  { key: "paydi", label: "Unit link / PAYDI" },
  { key: "other", label: "Rider / lainnya" },
];
const STATUSES = [
  { key: "", label: "Semua status" },
  { key: "current", label: "Current" },
  { key: "needs_review", label: "Perlu verifikasi" },
  { key: "historical", label: "Historis" },
];

type SP = {
  provider?: string;
  category?: string;
  insurance_scheme?: string;
  product_structure?: string;
  reference_status?: string;
};

function buildHref(current: SP, patch: Partial<SP>): string {
  const merged = { ...current, ...patch };
  const qs = new URLSearchParams();
  for (const [k, v] of Object.entries(merged)) if (v) qs.set(k, v);
  const s = qs.toString();
  return s ? `/products?${s}` : "/products";
}

export default async function ProductsPage({ searchParams }: { searchParams: Promise<SP> }) {
  const sp = await searchParams;

  let products: Awaited<ReturnType<typeof getProducts>>["data"] = [];
  let failed = false;
  try {
    products = (
      await getProducts({
        per_page: 50,
        provider: sp.provider,
        category: sp.category,
        insurance_scheme: sp.insurance_scheme,
        product_structure: sp.product_structure,
        reference_status: sp.reference_status,
      })
    ).data;
  } catch {
    failed = true;
  }

  return (
    <>
      <PageHeader
        eyebrow="Products"
        title="Pelajari jenis produk sebelum melihat detail provider"
        lead="Produk asuransi memiliki struktur, manfaat, batasan, dan ketentuan yang berbeda. Pelajari jenis produk dan konsep perlindungannya lebih dulu. InsuraLab tidak menjual dan tidak merekomendasikan produk."
      />

      <Section>
        <Container className="flex flex-col gap-6">
          <div className="flex flex-col gap-3">
            <FilterRow label="Skema" options={SCHEMES} param="insurance_scheme" sp={sp} />
            <FilterRow label="Struktur" options={STRUCTURES} param="product_structure" sp={sp} />
            <FilterRow
              label="Status referensi"
              options={STATUSES}
              param="reference_status"
              sp={sp}
            />
          </div>

          {failed ? (
            <EmptyState
              title="Daftar produk belum tersedia"
              description="Jalankan backend InsuraLab untuk menampilkan produk."
            />
          ) : products.length === 0 ? (
            <EmptyState title="Tidak ada produk untuk filter ini" description="Coba filter lain." />
          ) : (
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {products.map((p) => (
                <Link key={p.id} href={`/products/${p.slug}`}>
                  <Card className="hover:border-border-strong h-full transition-colors">
                    <div className="flex flex-wrap items-center gap-2">
                      <ProductTypeBadge
                        structure={p.product_structure}
                        scheme={p.insurance_scheme}
                      />
                      <ReferenceStatusBadge status={p.reference_status} />
                    </div>
                    <CardTitle className="mt-2">{p.name}</CardTitle>
                    <p className="text-muted-foreground mt-1 text-xs">
                      {p.provider?.name} · {p.product_category?.name}
                    </p>
                    {p.short_description ? (
                      <CardDescription className="mt-2 line-clamp-3">
                        {p.short_description}
                      </CardDescription>
                    ) : null}
                  </Card>
                </Link>
              ))}
            </div>
          )}

          <EducationalDisclaimer variant="provider" className="max-w-2xl" />
        </Container>
      </Section>
    </>
  );
}

function FilterRow({
  label,
  options,
  param,
  sp,
}: {
  label: string;
  options: { key: string; label: string }[];
  param: keyof SP;
  sp: SP;
}) {
  const active = sp[param] ?? "";
  return (
    <div className="flex flex-wrap items-center gap-2">
      <span className="text-muted-foreground w-28 shrink-0 text-sm font-medium">{label}</span>
      {options.map((o) => (
        <Link key={o.key} href={buildHref(sp, { [param]: o.key || undefined })}>
          <Badge tone={active === o.key ? "primary" : "neutral"}>{o.label}</Badge>
        </Link>
      ))}
    </div>
  );
}
