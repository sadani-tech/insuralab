import Link from "next/link";
import { cn } from "@/lib/utils";

/** The InsuraLab wordmark. Links home. One brand for the whole platform. */
export function Brand({ className }: { className?: string }) {
  return (
    <Link
      href="/"
      className={cn(
        "inline-flex items-center gap-2 rounded-sm font-semibold tracking-tight",
        className,
      )}
      aria-label="InsuraLab — home"
    >
      <span
        aria-hidden="true"
        className="bg-primary text-primary-foreground grid h-7 w-7 place-items-center rounded-md font-mono text-sm"
      >
        iL
      </span>
      <span className="text-lg">
        Insura<span className="text-muted-foreground">Lab</span>
      </span>
    </Link>
  );
}
