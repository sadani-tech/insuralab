import Link from "next/link";
import { ShieldCheck } from "lucide-react";
import { cn } from "@/lib/utils";
export function Brand({ className }: { className?: string }) {
  return (
    <Link href="/" className={cn("brand-wordmark", className)} aria-label="InsuraLab — home">
      <span className="brand-shield">
        <ShieldCheck size={24} strokeWidth={1.6} />
      </span>
      <span>
        Insura<span className="brand-lab">Lab</span>
        <small>PAHAMI. LINDUNGI.</small>
      </span>
    </Link>
  );
}
