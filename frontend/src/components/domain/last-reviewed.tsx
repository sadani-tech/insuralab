/** Formats an ISO date (YYYY-MM-DD) as a readable "last reviewed" line. */
export function LastReviewed({
  date,
  className,
}: {
  date: string | null | undefined;
  className?: string;
}) {
  if (!date) return null;

  const parsed = new Date(date);
  const formatted = Number.isNaN(parsed.getTime())
    ? date
    : parsed.toLocaleDateString("en-GB", { day: "numeric", month: "long", year: "numeric" });

  return (
    <span className={className}>
      Last reviewed <time dateTime={date}>{formatted}</time>
    </span>
  );
}
