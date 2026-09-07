import { Callout } from "@/components/ui/callout";

/**
 * The standing educational disclaimer. Small, inline, not a modal.
 * `variant="provider"` adds the independence line for provider case-study pages.
 */
export function EducationalDisclaimer({
  variant = "default",
  className,
}: {
  variant?: "default" | "provider";
  className?: string;
}) {
  return (
    <Callout tone="note" className={className}>
      <p>
        Konten ini bersifat edukatif dan bukan nasihat keuangan, hukum, atau pajak, dan bukan
        rekomendasi untuk membeli produk tertentu.
      </p>
      {variant === "provider" ? (
        <p className="mt-1.5">
          InsuraLab adalah platform edukasi independen dan bukan situs resmi perusahaan asuransi
          mana pun. Informasi produk dapat berubah — rujuk selalu dokumen polis dan RIPLAY resmi.
        </p>
      ) : null}
    </Callout>
  );
}
