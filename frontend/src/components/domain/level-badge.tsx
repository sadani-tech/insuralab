import { Badge } from "@/components/ui/badge";
import type { ContentLevel } from "@/lib/api/schemas";

const LABELS: Record<ContentLevel, string> = {
  beginner: "Beginner",
  intermediate: "Intermediate",
  advanced: "Advanced",
};

export function LevelBadge({
  level,
  className,
}: {
  level: ContentLevel | null | undefined;
  className?: string;
}) {
  if (!level) return null;
  return (
    <Badge tone="neutral" className={className}>
      {LABELS[level]}
    </Badge>
  );
}
