import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { ReferenceStatusBadge, REFERENCE_STATUS_CONFIG } from "./reference-status-badge";

describe("ReferenceStatusBadge", () => {
  it("renders nothing when there is no status", () => {
    const { container } = render(<ReferenceStatusBadge status={null} />);
    expect(container).toBeEmptyDOMElement();
  });

  it("labels each known status", () => {
    render(<ReferenceStatusBadge status="current" />);
    expect(screen.getByText("Current")).toBeInTheDocument();

    render(<ReferenceStatusBadge status="historical" />);
    expect(screen.getByText("Historical")).toBeInTheDocument();

    render(<ReferenceStatusBadge status="needs_review" />);
    expect(screen.getByText("Needs review")).toBeInTheDocument();
  });

  it("keeps needs_review un-alarming (info tone, not danger)", () => {
    expect(REFERENCE_STATUS_CONFIG.needs_review.tone).toBe("info");
    expect(REFERENCE_STATUS_CONFIG.historical.tone).toBe("warning");
    expect(REFERENCE_STATUS_CONFIG.current.tone).toBe("success");
  });

  it("exposes the explanation as a tooltip", () => {
    render(<ReferenceStatusBadge status="needs_review" />);
    expect(screen.getByText("Needs review")).toHaveAttribute(
      "title",
      REFERENCE_STATUS_CONFIG.needs_review.description,
    );
  });
});
