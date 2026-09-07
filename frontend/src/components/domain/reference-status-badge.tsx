import { Badge } from "@/components/ui/badge";
import type { ReferenceStatus } from "@/lib/api/schemas";

type Config = {
  label: string;
  tone: "success" | "warning" | "info";
  description: string;
};

/**
 * Visual treatment for the trust state of provider-specific / dynamic content
 * (ADR-0004). Deliberately un-alarming: `needs_review` is informational blue,
 * not red — it means "verify", not "danger".
 */
export const REFERENCE_STATUS_CONFIG: Record<ReferenceStatus, Config> = {
  current: {
    label: "Current",
    tone: "success",
    description: "Reviewed against a current official document.",
  },
  historical: {
    label: "Historical",
    tone: "warning",
    description: "Tied to a past period. Shown for educational context, not as current fact.",
  },
  needs_review: {
    label: "Needs review",
    tone: "info",
    description:
      "Not yet re-verified, or the source is inconsistent. Check official documentation.",
  },
};

export function ReferenceStatusBadge({
  status,
  className,
}: {
  status: ReferenceStatus | null | undefined;
  className?: string;
}) {
  if (!status) return null;
  const config = REFERENCE_STATUS_CONFIG[status];

  return (
    <Badge tone={config.tone} className={className} title={config.description}>
      {config.label}
    </Badge>
  );
}
