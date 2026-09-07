import { cn } from "@/lib/utils";

const NODES = [
  { cx: 90, cy: 70, r: 7, label: "Life" },
  { cx: 240, cy: 44, r: 6, label: "Health" },
  { cx: 360, cy: 96, r: 8, label: "Critical illness" },
  { cx: 300, cy: 190, r: 6, label: "Disability" },
  { cx: 150, cy: 200, r: 7, label: "Accident" },
  { cx: 210, cy: 120, r: 10, label: "Risk" },
];

const LINKS: [number, number][] = [
  [5, 0],
  [5, 1],
  [5, 2],
  [5, 3],
  [5, 4],
  [0, 4],
  [1, 2],
];

/**
 * A 2D abstract stand-in for the future Three.js "Protection Universe" (Phase 9).
 * Intentionally simple and replaceable — it establishes visual direction, nothing
 * more. Motion is limited to a slow pulse and is disabled under
 * prefers-reduced-motion (handled globally in globals.css).
 */
export function ProtectionConstellation({ className }: { className?: string }) {
  return (
    <svg
      viewBox="0 0 450 250"
      role="img"
      aria-label="Abstract diagram: five protection categories connected to a central risk node."
      className={cn("h-full w-full", className)}
    >
      <defs>
        <radialGradient id="ilx-glow" cx="50%" cy="50%" r="50%">
          <stop offset="0%" stopColor="var(--color-primary)" stopOpacity="0.18" />
          <stop offset="100%" stopColor="var(--color-primary)" stopOpacity="0" />
        </radialGradient>
      </defs>

      <rect width="450" height="250" fill="url(#ilx-glow)" />

      {LINKS.map(([a, b], i) => {
        const from = NODES[a]!;
        const to = NODES[b]!;
        return (
          <line
            key={i}
            x1={from.cx}
            y1={from.cy}
            x2={to.cx}
            y2={to.cy}
            stroke="var(--color-border-strong)"
            strokeWidth="1"
          />
        );
      })}

      {NODES.map((node, i) => (
        <g key={node.label}>
          <circle
            cx={node.cx}
            cy={node.cy}
            r={node.r}
            fill={i === 5 ? "var(--color-primary)" : "var(--color-surface)"}
            stroke={i === 5 ? "var(--color-primary)" : "var(--color-accent)"}
            strokeWidth="1.5"
            style={{
              animation: "ilx-pulse 4s ease-in-out infinite",
              animationDelay: `${i * 0.4}s`,
              transformOrigin: `${node.cx}px ${node.cy}px`,
            }}
          />
          <text
            x={node.cx}
            y={node.cy + node.r + 13}
            textAnchor="middle"
            className="fill-[var(--color-muted-foreground)] text-[10px]"
          >
            {node.label}
          </text>
        </g>
      ))}

      <style>{`
        @keyframes ilx-pulse {
          0%, 100% { transform: scale(1); opacity: 0.85; }
          50% { transform: scale(1.12); opacity: 1; }
        }
      `}</style>
    </svg>
  );
}
