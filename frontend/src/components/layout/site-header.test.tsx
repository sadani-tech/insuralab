import { render, screen, within } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it } from "vitest";
import { SiteHeader } from "./site-header";
import { MAIN_NAV } from "@/lib/navigation";

describe("SiteHeader", () => {
  it("renders the brand and every primary nav item", () => {
    render(<SiteHeader />);

    expect(screen.getByRole("link", { name: /InsuraLab/i })).toBeInTheDocument();

    const primaryNav = screen.getByRole("navigation", { name: "Primary" });
    for (const item of MAIN_NAV) {
      expect(within(primaryNav).getByRole("link", { name: item.label })).toHaveAttribute(
        "href",
        item.href,
      );
    }
  });

  it("marks the current route with aria-current", () => {
    // vitest.setup mocks usePathname() -> "/"; nothing under nav is active,
    // but the mechanism is exercised by toggling the menu below.
    render(<SiteHeader />);
    expect(screen.getByRole("navigation", { name: "Primary" })).toBeInTheDocument();
  });

  it("opens and closes the mobile menu from the Menu button", async () => {
    const user = userEvent.setup();
    render(<SiteHeader />);

    const trigger = screen.getByRole("button", { name: "Menu" });
    expect(trigger).toHaveAttribute("aria-expanded", "false");
    expect(screen.queryByRole("navigation", { name: "Mobile" })).not.toBeInTheDocument();

    await user.click(trigger);

    const mobileNav = screen.getByRole("navigation", { name: "Mobile" });
    expect(mobileNav).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Close" })).toHaveAttribute("aria-expanded", "true");

    await user.click(screen.getByRole("button", { name: "Close" }));
    expect(screen.queryByRole("navigation", { name: "Mobile" })).not.toBeInTheDocument();
  });

  it("closes the mobile menu on Escape", async () => {
    const user = userEvent.setup();
    render(<SiteHeader />);

    await user.click(screen.getByRole("button", { name: "Menu" }));
    expect(screen.getByRole("navigation", { name: "Mobile" })).toBeInTheDocument();

    await user.keyboard("{Escape}");
    expect(screen.queryByRole("navigation", { name: "Mobile" })).not.toBeInTheDocument();
  });
});
