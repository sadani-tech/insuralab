import type { ComponentPropsWithoutRef, ReactNode } from "react";
import Link from "next/link";
import ReactMarkdown from "react-markdown";
import remarkGfm from "remark-gfm";
import remarkDirective from "remark-directive";
import { cn } from "@/lib/utils";
import { remarkHeadingIds, remarkInsuralabDirectives } from "@/lib/markdown";
import { Callout } from "@/components/ui/callout";
import { GlossaryTooltip } from "./glossary-tooltip";

const CALLOUT_TONE: Record<string, "note" | "info" | "warning"> = {
  definition: "note",
  important: "info",
  example: "note",
  misconception: "warning",
  provider: "info",
  historical: "warning",
  "needs-review": "info",
};

function MdLink({ href, children }: { href?: string; children?: ReactNode }) {
  if (href && (href.startsWith("/") || href.startsWith("#"))) {
    return (
      <Link href={href} className="text-primary underline underline-offset-2">
        {children}
      </Link>
    );
  }
  return (
    <a
      href={href}
      target="_blank"
      rel="noopener noreferrer"
      className="text-primary underline underline-offset-2"
    >
      {children}
    </a>
  );
}

/**
 * Renders InsuraLab article Markdown: GFM (tables, lists, links) plus the
 * `:::callout` directives and the inline `:istilah[…]` glossary tooltips.
 * The glossary short definitions come from GlossaryProvider higher up the tree.
 */
export function MarkdownContent({ markdown, className }: { markdown: string; className?: string }) {
  return (
    <div className={cn("flex flex-col gap-5 text-[1.02rem] leading-[1.75]", className)}>
      <ReactMarkdown
        remarkPlugins={[remarkGfm, remarkDirective, remarkInsuralabDirectives, remarkHeadingIds]}
        components={
          {
            h2: ({ children, ...props }) => (
              <h2 {...props} className="mt-4 scroll-mt-24 text-xl font-semibold sm:text-2xl">
                {children}
              </h2>
            ),
            h3: ({ children, ...props }) => (
              <h3 {...props} className="mt-2 scroll-mt-24 text-lg font-semibold">
                {children}
              </h3>
            ),
            p: ({ children }) => <p className="text-foreground/90">{children}</p>,
            ul: ({ children }) => (
              <ul className="ml-5 flex list-disc flex-col gap-1.5">{children}</ul>
            ),
            ol: ({ children }) => (
              <ol className="ml-5 flex list-decimal flex-col gap-1.5">{children}</ol>
            ),
            li: ({ children }) => <li className="text-foreground/90">{children}</li>,
            a: MdLink,
            blockquote: ({ children }) => (
              <blockquote className="border-border-strong text-muted-foreground border-l-2 pl-4 italic">
                {children}
              </blockquote>
            ),
            code: ({ children }) => (
              <code className="bg-surface-muted rounded px-1.5 py-0.5 font-mono text-[0.9em]">
                {children}
              </code>
            ),
            pre: ({ children }) => (
              <pre className="bg-surface-muted overflow-x-auto rounded-[var(--radius-sm)] p-4 text-sm">
                {children}
              </pre>
            ),
            table: ({ children }) => (
              <div className="overflow-x-auto">
                <table className="w-full border-collapse text-sm">{children}</table>
              </div>
            ),
            th: ({ children }) => (
              <th className="border-border border px-3 py-2 text-left font-semibold">{children}</th>
            ),
            td: ({ children }) => (
              <td className="border-border border px-3 py-2 align-top">{children}</td>
            ),
            hr: () => <hr className="border-border" />,
            // Custom nodes produced by remarkInsuralabDirectives:
            callout: ({ node, children }: { node?: unknown; children?: ReactNode }) => {
              const props = (node as { properties?: { tone?: string; label?: string } })
                ?.properties;
              const tone = props?.tone ?? "note";
              return (
                <Callout tone={CALLOUT_TONE[tone] ?? "note"} title={props?.label}>
                  <div className="flex flex-col gap-2">{children}</div>
                </Callout>
              );
            },
            glossaryterm: ({ node, children }: { node?: unknown; children?: ReactNode }) => {
              const slug = (node as { properties?: { slug?: string } })?.properties?.slug ?? "";
              return <GlossaryTooltip slug={slug}>{children}</GlossaryTooltip>;
            },
          } as ComponentPropsWithoutRef<typeof ReactMarkdown>["components"]
        }
      >
        {markdown}
      </ReactMarkdown>
    </div>
  );
}
