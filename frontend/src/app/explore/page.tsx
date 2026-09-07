import type { Metadata } from "next";
import { ShellPage } from "@/components/layout/shell-page";
import { ProtectionConstellation } from "@/components/marketing/protection-constellation";

export const metadata: Metadata = {
  title: "Explore",
  description: "A visual way to see how protection categories relate to everyday financial risks.",
};

export default function ExplorePage() {
  return (
    <ShellPage
      eyebrow="InsuraLab Verse"
      title="See how protection maps to risk"
      lead="A spatial, interactive view of how each protection category answers a financial risk. The full interactive version — with a non-visual equivalent — is built in Phase 9. The sketch below is a 2D placeholder, not the final visualisation."
      phaseNote="The interactive exploration (and its accessible fallback) is built in Phase 9. No 3D libraries are loaded yet."
    >
      <div className="border-border bg-surface overflow-hidden rounded-[var(--radius-lg)] border">
        <ProtectionConstellation />
      </div>
    </ShellPage>
  );
}
