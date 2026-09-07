import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { env } from "@/lib/env";
import { Providers } from "@/components/providers";
import { SiteHeader } from "@/components/layout/site-header";
import { SiteFooter } from "@/components/layout/site-footer";
import { SkipLink } from "@/components/layout/skip-link";

const geistSans = Geist({ variable: "--font-geist-sans", subsets: ["latin"] });
const geistMono = Geist_Mono({ variable: "--font-geist-mono", subsets: ["latin"] });

export const metadata: Metadata = {
  metadataBase: new URL(env.siteUrl),
  title: {
    default: "InsuraLab — Belajar Asuransi dengan Lebih Mudah",
    template: "%s · InsuraLab",
  },
  description:
    "Platform edukasi independen untuk memahami risiko, proteksi, asuransi, provider, dan produk " +
    "dengan penjelasan yang lebih mudah dipahami.",
  applicationName: "InsuraLab",
  openGraph: {
    type: "website",
    siteName: "InsuraLab",
    title: "InsuraLab — Understand insurance before you buy it",
    description:
      "An independent, provider-agnostic insurance education platform. Understand risk, protection, " +
      "and how insurance works before making a decision.",
    url: env.siteUrl,
    locale: "id_ID",
  },
  twitter: { card: "summary_large_image" },
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang="id" className={`${geistSans.variable} ${geistMono.variable} h-full antialiased`}>
      <body className="flex min-h-full flex-col">
        <Providers>
          <SkipLink />
          <SiteHeader />
          <main id="main-content" className="flex flex-1 flex-col">
            {children}
          </main>
          <SiteFooter />
        </Providers>
      </body>
    </html>
  );
}
