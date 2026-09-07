import Link from "next/link";
import { Container } from "@/components/ui/container";
import { FOOTER_SECTIONS } from "@/lib/navigation";
import { Brand } from "./brand";

export function SiteFooter() {
  return (
    <footer className="border-border bg-surface-muted/40 mt-auto border-t">
      <Container className="grid gap-10 py-12 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
        <div className="flex flex-col gap-3">
          <Brand />
          <p className="text-muted-foreground max-w-xs text-sm">
            An independent, provider-agnostic platform for understanding risk, protection, and how
            insurance works — before making a decision.
          </p>
        </div>

        {FOOTER_SECTIONS.map((section) => (
          <nav key={section.title} aria-label={section.title} className="flex flex-col gap-2">
            <h2 className="text-muted-foreground text-xs font-semibold tracking-wide uppercase">
              {section.title}
            </h2>
            {section.items.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                className="text-muted-foreground hover:text-foreground text-sm"
              >
                {item.label}
              </Link>
            ))}
          </nav>
        ))}
      </Container>

      <Container className="border-border border-t py-6">
        <p className="text-muted-foreground text-xs">
          InsuraLab adalah platform edukasi independen dan bukan situs resmi perusahaan asuransi
          tertentu. Konten bersifat edukatif dan bukan nasihat keuangan, hukum, atau pajak.
        </p>
      </Container>
    </footer>
  );
}
