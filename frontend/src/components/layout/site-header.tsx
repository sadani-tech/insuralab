"use client";

import { useEffect, useId, useRef, useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { cn } from "@/lib/utils";
import { MAIN_NAV, SECONDARY_NAV } from "@/lib/navigation";
import { Brand } from "./brand";

function isActive(pathname: string, href: string): boolean {
  return pathname === href || (href !== "/" && pathname.startsWith(`${href}/`));
}

export function SiteHeader() {
  const pathname = usePathname();
  const [open, setOpen] = useState(false);
  const menuId = useId();
  const panelRef = useRef<HTMLDivElement>(null);
  const triggerRef = useRef<HTMLButtonElement>(null);

  const closeMenu = () => setOpen(false);

  // Close on Escape; return focus to the trigger.
  useEffect(() => {
    if (!open) return;
    function onKey(e: KeyboardEvent) {
      if (e.key === "Escape") {
        setOpen(false);
        triggerRef.current?.focus();
      }
    }
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [open]);

  useEffect(() => {
    if (open) panelRef.current?.querySelector<HTMLElement>("a")?.focus();
  }, [open]);

  return (
    <header className="revamp-header">
      <div className="mx-auto flex w-full max-w-[1600px] items-center justify-between gap-4 px-5 py-3.5 sm:px-8">
        <Brand />

        <nav aria-label="Primary" className="hidden items-center gap-1 lg:flex">
          {MAIN_NAV.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              aria-current={isActive(pathname, item.href) ? "page" : undefined}
              className={cn(
                "rounded-md px-3 py-2 text-sm font-medium transition-colors",
                isActive(pathname, item.href)
                  ? "text-foreground bg-surface-muted"
                  : "text-muted-foreground hover:text-foreground hover:bg-surface-muted/60",
              )}
            >
              {item.label}
            </Link>
          ))}
          <span className="bg-border mx-2 h-5 w-px" aria-hidden="true" />
          {SECONDARY_NAV.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              aria-current={isActive(pathname, item.href) ? "page" : undefined}
              className={cn(
                "rounded-md px-3 py-2 text-sm font-medium transition-colors",
                isActive(pathname, item.href)
                  ? "text-foreground bg-surface-muted"
                  : "text-muted-foreground hover:text-foreground hover:bg-surface-muted/60",
              )}
            >
              {item.label}
            </Link>
          ))}
        </nav>

        <button
          ref={triggerRef}
          type="button"
          className="border-border-strong hover:bg-surface-muted inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-medium lg:hidden"
          aria-expanded={open}
          aria-controls={menuId}
          onClick={() => setOpen((v) => !v)}
        >
          {open ? "Close" : "Menu"}
        </button>
      </div>

      {open ? (
        <div id={menuId} ref={panelRef} className="border-border bg-background border-t lg:hidden">
          <nav
            aria-label="Mobile"
            className="mx-auto flex w-full max-w-6xl flex-col px-5 py-3 sm:px-8"
          >
            {[...MAIN_NAV, ...SECONDARY_NAV].map((item) => (
              <Link
                key={item.href}
                href={item.href}
                onClick={closeMenu}
                aria-current={isActive(pathname, item.href) ? "page" : undefined}
                className={cn(
                  "rounded-md px-3 py-3 text-sm font-medium",
                  isActive(pathname, item.href)
                    ? "text-foreground bg-surface-muted"
                    : "text-muted-foreground hover:bg-surface-muted/60",
                )}
              >
                {item.label}
                <span className="text-muted-foreground mt-0.5 block text-xs font-normal">
                  {item.description}
                </span>
              </Link>
            ))}
          </nav>
        </div>
      ) : null}
    </header>
  );
}
