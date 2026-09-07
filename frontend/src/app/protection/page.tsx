import type { Metadata } from "next";
import { ShellPage } from "@/components/layout/shell-page";
import { ProtectionCategoryCard } from "@/components/domain/protection-category-card";
import { EmptyState } from "@/components/ui/empty-state";
import { getProtectionCategories } from "@/lib/api";

export const metadata: Metadata = {
  title: "Protection",
  description:
    "Protection categories framed as risks: life, health, critical illness, disability, accident, income.",
};

export const revalidate = 300;

export default async function ProtectionPage() {
  let categories: Awaited<ReturnType<typeof getProtectionCategories>> = [];
  let failed = false;
  try {
    categories = await getProtectionCategories();
  } catch {
    failed = true;
  }

  return (
    <ShellPage
      eyebrow="Protection"
      title="Start from the risk, not the product"
      lead="Each protection category answers a financial question: what happens to a household's money if this risk occurs? The guided category pages arrive in Phase 6."
      phaseNote="Category detail pages are being built in Phase 6. The concepts below come from the live API."
    >
      {failed ? (
        <EmptyState
          title="Preview unavailable"
          description="The content API isn't reachable right now."
        />
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {categories.map((category) => (
            <ProtectionCategoryCard
              key={category.id}
              name={category.name}
              description={category.short_description}
            />
          ))}
        </div>
      )}
    </ShellPage>
  );
}
