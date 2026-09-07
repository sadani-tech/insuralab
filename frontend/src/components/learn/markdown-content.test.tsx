import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it } from "vitest";
import { MarkdownContent } from "./markdown-content";
import { GlossaryProvider } from "./glossary-context";

describe("MarkdownContent", () => {
  it("renders a :::kesalahpahaman directive as a titled callout", () => {
    render(
      <MarkdownContent markdown={":::kesalahpahaman\nAsuransi menanggung semua biaya.\n:::"} />,
    );
    expect(screen.getByText("Kesalahpahaman umum")).toBeInTheDocument();
    expect(screen.getByText(/menanggung semua biaya/i)).toBeInTheDocument();
  });

  it("renders GFM tables inside a horizontal-scroll wrapper", () => {
    const { container } = render(<MarkdownContent markdown={"| A | B |\n|---|---|\n| 1 | 2 |"} />);
    expect(container.querySelector(".overflow-x-auto table")).toBeInTheDocument();
  });

  it("turns :istilah[...] into a tooltip trigger that reveals the short definition", async () => {
    const user = userEvent.setup();
    render(
      <GlossaryProvider
        terms={{ underwriting: { term: "Underwriting", short_definition: "Penilaian risiko." } }}
      >
        <MarkdownContent markdown={"Proses :istilah[Underwriting]{slug=underwriting} penting."} />
      </GlossaryProvider>,
    );

    const trigger = screen.getByRole("button", { name: "Underwriting" });
    expect(trigger).toHaveAttribute("aria-expanded", "false");
    expect(screen.queryByRole("tooltip")).not.toBeInTheDocument();

    await user.hover(trigger);
    expect(screen.getByRole("tooltip")).toHaveTextContent("Penilaian risiko.");
    expect(screen.getByRole("link", { name: /Pelajari lebih lanjut/i })).toHaveAttribute(
      "href",
      "/glossary/underwriting",
    );

    await user.unhover(trigger);
    expect(screen.queryByRole("tooltip")).not.toBeInTheDocument();
  });
});
