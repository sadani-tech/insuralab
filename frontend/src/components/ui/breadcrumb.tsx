import { Fragment } from "react";
import Link from "next/link";

export type Crumb = { label: string; href?: string };

export function Breadcrumb({ items }: { items: Crumb[] }) {
  return (
    <nav aria-label="Breadcrumb">
      <ol className="text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm">
        {items.map((item, i) => (
          <Fragment key={i}>
            {i > 0 ? (
              <li aria-hidden="true" className="opacity-50">
                /
              </li>
            ) : null}
            <li>
              {item.href && i < items.length - 1 ? (
                <Link href={item.href} className="hover:text-foreground">
                  {item.label}
                </Link>
              ) : (
                <span aria-current={i === items.length - 1 ? "page" : undefined}>{item.label}</span>
              )}
            </li>
          </Fragment>
        ))}
      </ol>
    </nav>
  );
}
