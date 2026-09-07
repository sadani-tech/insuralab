import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import { Container } from "@/components/ui/container";
import { Badge } from "@/components/ui/badge";
import { Callout } from "@/components/ui/callout";
import { Breadcrumb } from "@/components/ui/breadcrumb";
import { ProductTypeBadge } from "@/components/domain/product-type-badge";
import { ReferenceStatusBadge } from "@/components/domain/reference-status-badge";
import { LastReviewed } from "@/components/domain/last-reviewed";
import { SourceList } from "@/components/domain/source-list";
import { KnowledgeCard } from "@/components/domain/knowledge-card";
import { MarkdownContent } from "@/components/learn/markdown-content";
import { ApiError, getProduct } from "@/lib/api";

export const revalidate = 300;

async function loadProduct(slug: string) {
  try {
    return await getProduct(slug);
  } catch (err) {
    if (err instanceof ApiError && err.status === 404) notFound();
    throw err;
  }
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  try {
    const p = await getProduct(slug);
    return {
      title: `${p.name} — Penjelasan Edukatif`,
      description: p.short_description ?? undefined,
    };
  } catch {
    return { title: "Produk" };
  }
}

export default async function ProductPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const product = await loadProduct(slug);

  const isPaydi = product.product_structure === "paydi";
  const isSharia = product.insurance_scheme === "sharia";

  return (
    <>
      <div className="border-border bg-surface-muted/40 border-b">
        <Container className="flex flex-col gap-4 py-8">
          <Breadcrumb
            items={[
              { label: "Home", href: "/" },
              { label: "Products", href: "/products" },
              ...(product.provider
                ? [{ label: product.provider.name, href: `/providers/${product.provider.slug}` }]
                : []),
              { label: product.name },
            ]}
          />
          <h1 className="text-3xl font-semibold sm:text-4xl">{product.name}</h1>
          <div className="flex flex-wrap items-center gap-2 text-sm">
            {product.provider ? (
              <Link
                href={`/providers/${product.provider.slug}`}
                className="text-primary underline underline-offset-2"
              >
                {product.provider.name}
              </Link>
            ) : null}
            <ProductTypeBadge
              structure={product.product_structure}
              scheme={product.insurance_scheme}
            />
            <ReferenceStatusBadge status={product.trust.reference_status} />
          </div>
          <p className="text-muted-foreground text-sm">
            {product.trust.reference_period ? (
              <>Periode referensi: {product.trust.reference_period}. </>
            ) : null}
            <LastReviewed date={product.trust.last_reviewed_at} />
          </p>
        </Container>
      </div>

      <Container className="flex max-w-3xl flex-col gap-8 py-10">
        <Callout tone="info" title="Konten edukatif independen">
          <p>
            InsuraLab adalah platform edukasi independen dan bukan situs resmi provider ini. Semua
            figur bersifat referensi pelatihan yang harus diperiksa terhadap dokumen resmi terkini
            (polis, RIPLAY, ilustrasi) sebelum penggunaan aktual.
          </p>
        </Callout>

        {isPaydi ? (
          <Callout tone="warning" title="Catatan risiko: produk unit link / PAYDI">
            <p>
              Produk ini menggabungkan proteksi dengan komponen investasi. Nilai investasi{" "}
              <strong>tidak dijamin</strong> kecuali kontrak menyatakannya, dan{" "}
              <strong>dapat turun</strong>. Biaya asuransi, penarikan, switching, dan cuti premi
              dapat mengurangi nilai polis dan keberlanjutannya. Proyeksi ilustrasi bukan janji
              hasil.
            </p>
          </Callout>
        ) : null}

        {isSharia ? (
          <Callout tone="note" title="Terminologi syariah">
            <p>
              Produk ini berbasis prinsip berbagi risiko. Gunakan istilah{" "}
              <strong>kontribusi</strong> (bukan premi): sebagiannya masuk ke dana{" "}
              <strong>tabarru&rsquo;</strong> untuk saling menolong antarpeserta, dan operator
              mengelola dana atas dasar akad (imbalan/ujrah).
            </p>
          </Callout>
        ) : null}

        {product.protection_categories.length ? (
          <section className="flex flex-col gap-2">
            <h2 className="text-base font-semibold">Risiko yang ditangani</h2>
            <div className="flex flex-wrap gap-2">
              {product.protection_categories.map((pc) => (
                <Badge key={pc.id} tone={pc.is_primary ? "primary" : "neutral"}>
                  {pc.name}
                </Badge>
              ))}
            </div>
          </section>
        ) : null}

        {product.description ? (
          <div className="reading-measure">
            <MarkdownContent markdown={product.description} />
          </div>
        ) : null}

        {product.related_terms.length ? (
          <section className="flex flex-col gap-2">
            <h2 className="text-base font-semibold">Istilah penting</h2>
            <div className="flex flex-wrap gap-2 text-sm">
              {product.related_terms.map((t) => (
                <Link
                  key={t.slug}
                  href={`/glossary/${t.slug}`}
                  className="border-border hover:border-border-strong rounded-full border px-3 py-1"
                  title={t.short_definition}
                >
                  {t.term}
                </Link>
              ))}
            </div>
          </section>
        ) : null}

        {product.related_articles.length ? (
          <section className="flex flex-col gap-3">
            <h2 className="text-lg font-semibold">Pahami konsepnya lebih dulu</h2>
            <p className="text-muted-foreground text-sm">
              Produk berada di bawah konsep dalam hierarki belajar InsuraLab. Pahami &ldquo;apa
              itu&rdquo;-nya sebelum bergantung pada detail produk tertentu.
            </p>
            <div className="grid gap-4 sm:grid-cols-2">
              {product.related_articles.map((a) => (
                <KnowledgeCard key={a.id} article={a} href={`/learn/${a.slug}`} />
              ))}
            </div>
          </section>
        ) : null}

        {product.sources.length ? (
          <section>
            <h2 className="mb-3 text-base font-semibold">Sumber &amp; Referensi</h2>
            <SourceList sources={product.sources} />
          </section>
        ) : null}
      </Container>
    </>
  );
}
