import type { ElementType, ReactNode } from "react";
import { cn } from "@/lib/utils";

/**
 * Page-width container. One max-width and one horizontal padding scale for the
 * whole site, so pages don't each reinvent spacing.
 */
export function Container({
  as: Tag = "div",
  className,
  children,
}: {
  as?: ElementType;
  className?: string;
  children: ReactNode;
}) {
  return <Tag className={cn("mx-auto w-full max-w-6xl px-5 sm:px-8", className)}>{children}</Tag>;
}

/** Vertical rhythm wrapper for a page section. */
export function Section({
  as: Tag = "section",
  className,
  children,
  ...props
}: {
  as?: ElementType;
  className?: string;
  children: ReactNode;
} & Record<string, unknown>) {
  return (
    <Tag className={cn("py-14 sm:py-20", className)} {...props}>
      {children}
    </Tag>
  );
}

/** Heading + optional lead paragraph for a section. */
export function SectionHeader({
  eyebrow,
  title,
  lead,
  align = "start",
  className,
}: {
  eyebrow?: string;
  title: ReactNode;
  lead?: ReactNode;
  align?: "start" | "center";
  className?: string;
}) {
  return (
    <div
      className={cn(
        "flex flex-col gap-3",
        align === "center" && "items-center text-center",
        className,
      )}
    >
      {eyebrow ? (
        <span className="text-accent font-mono text-xs font-semibold tracking-[0.14em] uppercase">
          {eyebrow}
        </span>
      ) : null}
      <h2 className="text-2xl font-semibold sm:text-3xl">{title}</h2>
      {lead ? (
        <p
          className={cn(
            "text-muted-foreground max-w-2xl text-base",
            align === "center" && "mx-auto",
          )}
        >
          {lead}
        </p>
      ) : null}
    </div>
  );
}

/** Large heading block for the top of a route (used by the shell pages). */
export function PageHeader({
  eyebrow,
  title,
  lead,
  children,
}: {
  eyebrow?: string;
  title: ReactNode;
  lead?: ReactNode;
  children?: ReactNode;
}) {
  return (
    <header className="border-border bg-surface-muted/40 border-b">
      <Container className="flex flex-col gap-4 py-14 sm:py-20">
        {eyebrow ? (
          <span className="text-accent font-mono text-xs font-semibold tracking-[0.14em] uppercase">
            {eyebrow}
          </span>
        ) : null}
        <h1 className="max-w-3xl text-3xl font-semibold sm:text-4xl">{title}</h1>
        {lead ? <p className="text-muted-foreground max-w-2xl text-lg">{lead}</p> : null}
        {children}
      </Container>
    </header>
  );
}
