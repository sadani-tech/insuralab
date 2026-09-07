import { Card, CardDescription, CardTitle } from "@/components/ui/card";

/**
 * A protection concept expressed as a RISK, not a product. Life, health,
 * critical illness, disability, accident, income.
 */
export function ProtectionCategoryCard({
  name,
  description,
}: {
  name: string;
  description: string | null;
}) {
  return (
    <Card className="h-full">
      <CardTitle>{name}</CardTitle>
      {description ? <CardDescription>{description}</CardDescription> : null}
    </Card>
  );
}
