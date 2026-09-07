import { cn } from "@/lib/utils";

/** Low-key loading placeholder. Prefer this over spinners. */
export function Skeleton({ className }: { className?: string }) {
  return (
    <div
      className={cn("bg-surface-muted animate-pulse rounded-[var(--radius-sm)]", className)}
      aria-hidden="true"
    />
  );
}
