import type { ComponentProps } from "react";
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";

const badgeVariants = cva(
  "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium",
  {
    variants: {
      tone: {
        neutral: "border-border-strong bg-surface-muted text-muted-foreground",
        primary: "border-transparent bg-[var(--color-primary)]/10 text-primary",
        success: "border-transparent bg-success-surface text-success",
        warning: "border-transparent bg-warning-surface text-warning",
        info: "border-transparent bg-info-surface text-info",
        danger: "border-transparent bg-danger-surface text-danger",
      },
    },
    defaultVariants: { tone: "neutral" },
  },
);

export function Badge({
  className,
  tone,
  ...props
}: ComponentProps<"span"> & VariantProps<typeof badgeVariants>) {
  return <span className={cn(badgeVariants({ tone }), className)} {...props} />;
}

export { badgeVariants };
