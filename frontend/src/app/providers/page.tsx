import type { Metadata } from "next";
import Link from "next/link";
import { Container, PageHeader, Section } from "@/components/ui/container";
import { Card, CardDescription, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { EmptyState } from "@/components/ui/empty-state";
import { EducationalDisclaimer } from "@/components/domain/educational-disclaimer";
import { LastReviewed } from "@/components/domain/last-reviewed";
import { getProviders } from "@/lib/api";

export const metadata: Metadata = {
  title: "Providers",
  description:
    "Kenali perusahaan asuransi melalui informasi yang terstruktur. InsuraLab memisahkan informasi provider dari pengetahuan asuransi umum.",
};

export const revalidate = 300;

export default async function ProvidersPage() {
  let providers: Awaited<ReturnType<typeof getProviders>>["data"] = [];
  let failed = false;
  try {
    providers = (await getProviders()).data;
  } catch {
    failed = true;
  }

  return (
    <>
      <PageHeader
        eyebrow="Providers"
        title="Kenali perusahaan asuransi melalui informasi yang terstruktur"
        lead="Provider adalah perusahaan yang menyediakan produk dan layanan asuransi. InsuraLab memisahkan informasi provider dari pengetahuan asuransi umum agar Anda dapat memahami keduanya secara lebih objektif."
      />

      <Section>
        <Container className="flex flex-col gap-6">
          {failed ? (
            <EmptyState
              title="Daftar provider belum tersedia"
              description="Jalankan backend InsuraLab untuk menampilkan provider."
            />
          ) : (
            <div className="grid gap-4 sm:grid-cols-2">
              {providers.map((provider) => (
                <Link key={provider.id} href={`/providers/${provider.slug}`}>
                  <Card className="hover:border-border-strong h-full transition-colors">
                    <div className="flex flex-wrap items-center gap-2">
                      <CardTitle>{provider.name}</CardTitle>
                      <Badge tone="primary">studi kasus</Badge>
                    </div>
                    <CardDescription>
                      {provider.country_of_origin ? `Asal: ${provider.country_of_origin}. ` : ""}
                      {provider.products_count ?? 0} catatan produk edukatif ·{" "}
                      {provider.articles_count ?? 0} artikel provider-spesifik.
                    </CardDescription>
                    <p className="text-muted-foreground mt-3 text-xs">
                      <LastReviewed date={provider.last_reviewed_at} />
                    </p>
                  </Card>
                </Link>
              ))}
            </div>
          )}

          <p className="text-muted-foreground text-sm">
            InsuraLab tidak memberi peringkat, skor, atau label &ldquo;terbaik&rdquo; pada provider.
            Halaman ini bersifat edukatif.
          </p>
          <EducationalDisclaimer variant="provider" className="max-w-2xl" />
        </Container>
      </Section>
    </>
  );
}
