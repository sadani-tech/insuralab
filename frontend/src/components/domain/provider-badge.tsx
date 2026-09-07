import { Badge } from "@/components/ui/badge";

/**
 * Marks content as belonging to a specific provider case study. InsuraLab is
 * provider-agnostic (ADR-0002) — "the provider" is a variable, never the
 * platform identity.
 */
export function ProviderBadge({
  name,
  className,
}: {
  name: string | null | undefined;
  className?: string;
}) {
  if (!name) {
    return (
      <Badge tone="neutral" className={className}>
        General knowledge
      </Badge>
    );
  }
  return (
    <Badge tone="primary" className={className}>
      {name} case study
    </Badge>
  );
}
