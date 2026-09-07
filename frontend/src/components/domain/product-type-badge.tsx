import { Badge } from "@/components/ui/badge";

const STRUCTURE_LABELS: Record<string, string> = {
  traditional: "Traditional",
  paydi: "Unit link / PAYDI",
  other: "Rider / other",
};

const SCHEME_LABELS: Record<string, string> = {
  conventional: "Conventional",
  sharia: "Sharia",
};

export function ProductTypeBadge({
  structure,
  scheme,
}: {
  structure?: string | null;
  scheme?: string | null;
}) {
  return (
    <span className="inline-flex flex-wrap gap-1.5">
      {structure ? <Badge tone="neutral">{STRUCTURE_LABELS[structure] ?? structure}</Badge> : null}
      {scheme ? <Badge tone="neutral">{SCHEME_LABELS[scheme] ?? scheme}</Badge> : null}
    </span>
  );
}
