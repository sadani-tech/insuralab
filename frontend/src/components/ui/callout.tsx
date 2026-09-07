import type { ReactNode } from "react";
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";

const calloutVariants = cva("rounded-[var(--radius)] border px-4 py-3.5 text-sm", {
  variants: {
    tone: {
      note: "border-border bg-surface-muted/60 text-foreground",
      info: "border-transparent bg-info-surface text-info",
      warning: "border-transparent bg-warning-surface text-warning",
    },
  },
  defaultVariants: { tone: "note" },
});

/**
 * Inline aside for context that shouldn't interrupt reading. Not a modal.
 * Used for the educational disclaimer and trust notes.
 */
export function Callout({
  tone,
  title,
  children,
  className,
}: VariantProps<typeof calloutVariants> & {
  title?: ReactNode;
  children: ReactNode;
  className?: string;
}) {
  return (
    <div className={cn(calloutVariants({ tone }), className)} role="note">
      {title ? <p className="mb-1 font-semibold">{title}</p> : null}
      <div className="[&_a]:underline [&_a]:underline-offset-2">{children}</div>
    </div>
  );
}
