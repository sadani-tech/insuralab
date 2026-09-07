import { Callout } from "@/components/ui/callout";
import type { ReferenceStatus } from "@/lib/api/schemas";

/**
 * The prose trust notices shown on provider-specific and dated content. Kept
 * calm — `needs_review` means "verify", not "danger" (ADR-0004).
 */
export function ProviderContentNotice({ providerName }: { providerName?: string | null }) {
  return (
    <Callout tone="info" title="Konten provider-spesifik">
      <p>
        Konten ini membahas informasi yang terkait dengan{" "}
        {providerName ? <strong>{providerName}</strong> : "provider tertentu"}. Ketentuan produk dan
        proses dapat berubah. InsuraLab adalah platform edukasi independen dan bukan situs resmi
        provider ini.
      </p>
    </Callout>
  );
}

export function HistoricalNotice() {
  return (
    <Callout tone="warning" title="Referensi historis">
      <p>
        Informasi ini disimpan untuk tujuan pembelajaran dan menggambarkan materi pada periode
        tertentu. Jangan menganggapnya sebagai ketentuan yang masih berlaku saat ini.
      </p>
    </Callout>
  );
}

export function NeedsReviewNotice() {
  return (
    <Callout tone="info" title="Perlu verifikasi">
      <p>
        Informasi provider-spesifik ini membutuhkan pengecekan terhadap sumber resmi terbaru sebelum
        digunakan sebagai referensi aktual.
      </p>
    </Callout>
  );
}

export function TrustNotice({
  isProviderSpecific,
  referenceStatus,
  providerName,
}: {
  isProviderSpecific: boolean;
  referenceStatus: ReferenceStatus | null;
  providerName?: string | null;
}) {
  if (!isProviderSpecific) return null;
  return (
    <div className="flex flex-col gap-3">
      <ProviderContentNotice providerName={providerName} />
      {referenceStatus === "historical" ? <HistoricalNotice /> : null}
      {referenceStatus === "needs_review" ? <NeedsReviewNotice /> : null}
    </div>
  );
}
