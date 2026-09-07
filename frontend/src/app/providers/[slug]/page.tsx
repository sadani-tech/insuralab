import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import { Container } from "@/components/ui/container";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Callout } from "@/components/ui/callout";
import { Breadcrumb } from "@/components/ui/breadcrumb";
import { LastReviewed } from "@/components/domain/last-reviewed";
import { SourceList } from "@/components/domain/source-list";
import { ReferenceStatusBadge } from "@/components/domain/reference-status-badge";
import { LevelBadge } from "@/components/domain/level-badge";
import { MarkdownContent } from "@/components/learn/markdown-content";
import { ApiError, getProducts, getProvider } from "@/lib/api";

export const revalidate = 300;

async function loadProvider(slug: string) {
  try {
    return await getProvider(slug);
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
    const p = await getProvider(slug);
    return {
      title: `${p.name} — Profil Provider & Materi Edukasi`,
      description: p.presence_in_indonesia ?? p.description?.slice(0, 150) ?? undefined,
    };
  } catch {
    return { title: "Provider" };
  }
}

export default async function ProviderPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const provider = await loadProvider(slug);

  let products: Awaited<ReturnType<typeof getProducts>>["data"] = [];
  try {
    products = (await getProducts({ provider: slug, per_page: 50 })).data;
  } catch {
    products = [];
  }

  const entities = provider.metadata.entities ?? [];
  const businessAreas = provider.metadata.business_areas ?? [];

  return (
    <>
      <div className="border-border bg-surface-muted/40 border-b">
        <Container className="flex flex-col gap-4 py-8">
          <Breadcrumb
            items={[
              { label: "Home", href: "/" },
              { label: "Providers", href: "/providers" },
              { label: provider.name },
            ]}
          />
          <div className="flex flex-wrap items-center gap-2">
            <h1 className="text-3xl font-semibold sm:text-4xl">{provider.name}</h1>
            <Badge tone="primary">studi kasus provider pertama</Badge>
          </div>
          {provider.country_of_origin ? (
            <p className="text-muted-foreground text-sm">Asal: {provider.country_of_origin}</p>
          ) : null}
          <p className="text-muted-foreground text-sm">
            <LastReviewed date={provider.last_reviewed_at} />
          </p>
        </Container>
      </div>

      <Container className="flex max-w-3xl flex-col gap-10 py-10">
        <Callout tone="info" title="Konten edukatif independen">
          <p>
            InsuraLab adalah platform edukasi independen dan bukan situs resmi provider ini.
            Informasi provider dapat berubah — rujuk sumber resmi provider untuk detail terkini.
          </p>
        </Callout>

        {provider.description ? (
          <section className="flex flex-col gap-3">
            <h2 className="text-xl font-semibold">Ringkasan</h2>
            <div className="reading-measure">
              <MarkdownContent markdown={provider.description} />
            </div>
          </section>
        ) : null}

        {entities.length ? (
          <section className="flex flex-col gap-3">
            <h2 className="text-xl font-semibold">Entitas di Indonesia</h2>
            <p className="text-muted-foreground text-sm">
              Entitas yang tercantum pada polis adalah pihak yang menanggung risiko. Ketiganya tidak
              boleh disamakan.
            </p>
            <div className="grid gap-3 sm:grid-cols-3">
              {entities.map((e) => (
                <Card key={e.name}>
                  <CardTitle>{e.name}</CardTitle>
                  {e.focus ? <CardDescription>{e.focus}</CardDescription> : null}
                </Card>
              ))}
            </div>
          </section>
        ) : null}

        {businessAreas.length ? (
          <section className="flex flex-col gap-3">
            <h2 className="text-xl font-semibold">Bidang usaha</h2>
            <div className="flex flex-wrap gap-2">
              {businessAreas.map((area) => (
                <Badge key={area} tone="neutral">
                  {area}
                </Badge>
              ))}
            </div>
            {provider.metadata.distribution ? (
              <p className="text-muted-foreground text-sm">{provider.metadata.distribution}</p>
            ) : null}
          </section>
        ) : null}

        {provider.articles.length ? (
          <section className="flex flex-col gap-3">
            <h2 className="text-xl font-semibold">Materi edukasi provider</h2>
            <p className="text-muted-foreground text-sm">
              Artikel yang membahas materi terkait provider ini. Beberapa memerlukan verifikasi
              terhadap dokumen resmi terkini.
            </p>
            <ul className="border-border bg-surface flex flex-col rounded-[var(--radius)] border">
              {provider.articles.map((a) => (
                <li key={a.id} className="border-border border-b last:border-b-0">
                  <Link
                    href={`/learn/${a.slug}`}
                    className="hover:bg-surface-muted/60 flex flex-col gap-1 px-5 py-4"
                  >
                    <span className="flex flex-wrap items-center gap-2">
                      <span className="font-medium">{a.title}</span>
                      <LevelBadge level={a.level} />
                      <ReferenceStatusBadge status={a.reference_status} />
                    </span>
                    {a.excerpt ? (
                      <span className="text-muted-foreground text-sm">{a.excerpt}</span>
                    ) : null}
                  </Link>
                </li>
              ))}
            </ul>
          </section>
        ) : null}

        {products.length ? (
          <section className="flex flex-col gap-3">
            <h2 className="text-xl font-semibold">Catatan produk edukatif</h2>
            <p className="text-muted-foreground text-sm">
              Catatan produk untuk dipelajari — bukan halaman penjualan. Pahami konsep umumnya lebih
              dulu.
            </p>
            <div className="grid gap-4 sm:grid-cols-2">
              {products.map((p) => (
                <Link key={p.id} href={`/products/${p.slug}`}>
                  <Card className="hover:border-border-strong h-full transition-colors">
                    <div className="flex flex-wrap items-center gap-2">
                      <Badge tone="neutral">{p.product_category?.name}</Badge>
                      <ReferenceStatusBadge status={p.reference_status} />
                    </div>
                    <CardTitle className="mt-2">{p.name}</CardTitle>
                    {p.short_description ? (
                      <CardDescription className="line-clamp-3">
                        {p.short_description}
                      </CardDescription>
                    ) : null}
                  </Card>
                </Link>
              ))}
            </div>
          </section>
        ) : null}

        {provider.sources.length ? (
          <section>
            <h2 className="mb-3 text-xl font-semibold">Sumber &amp; Referensi</h2>
            <SourceList sources={provider.sources} />
          </section>
        ) : null}
      </Container>
    </>
  );
}
