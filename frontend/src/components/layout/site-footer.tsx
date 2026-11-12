import Link from "next/link";
export function SiteFooter() {
  return (
    <footer className="revamp-footer">
      <span>
        © {new Date().getFullYear()} InsuraLab <span className="footer-separator">/</span>{" "}
        Pengetahuan untuk perlindungan.
      </span>
      <nav aria-label="Informasi platform">
        <Link href="/about">Tentang kami</Link>
        <Link href="/methodology">Metodologi</Link>
        <Link href="/disclaimer">Catatan penggunaan</Link>
      </nav>
    </footer>
  );
}
