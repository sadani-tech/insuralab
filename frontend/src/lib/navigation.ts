export type NavItem = { label: string; href: string; description: string };
export const MAIN_NAV: NavItem[] = [
  { label: "Jelajah", href: "/", description: "Temukan hubungan antar konsep perlindungan." },
  { label: "Panduan", href: "/panduan", description: "Belajar melalui langkah yang jelas." },
  { label: "Tanya", href: "/tanya", description: "Cari jawaban terkurasi dan rujukannya." },
  { label: "Sumber", href: "/sumber", description: "Baca dasar dan konteks penjelasan." },
];
export const SECONDARY_NAV: NavItem[] = [
  { label: "Workspace Agen", href: "/agent", description: "Persiapan ruang kerja privat agen." },
];
