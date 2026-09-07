import type { ComponentProps, ElementType, ReactNode } from "react";
import { cn } from "@/lib/utils";

export function Card({
  as: Tag = "div",
  className,
  children,
  ...props
}: { as?: ElementType } & ComponentProps<"div">) {
  return (
    <Tag
      className={cn(
        "border-border bg-surface rounded-[var(--radius)] border p-5 sm:p-6",
        className,
      )}
      {...props}
    >
      {children}
    </Tag>
  );
}

export function CardTitle({ className, children }: { className?: string; children: ReactNode }) {
  return <h3 className={cn("text-base font-semibold", className)}>{children}</h3>;
}

export function CardDescription({
  className,
  children,
}: {
  className?: string;
  children: ReactNode;
}) {
  return <p className={cn("text-muted-foreground mt-1.5 text-sm", className)}>{children}</p>;
}
