import type { NextConfig } from "next";
import path from "node:path";
import { fileURLToPath } from "node:url";

const projectRoot = path.dirname(fileURLToPath(import.meta.url));

const nextConfig: NextConfig = {
  devIndicators: false,
  // Pin the workspace root to this `frontend/` directory. Without this, Next 16
  // walks up the filesystem looking for a lockfile and finds an unrelated
  // `package-lock.json` in a parent folder, producing an ambiguous-root warning
  // and potentially mis-resolving modules. The frontend is self-contained
  // (its own package-lock.json + node_modules), so this is the correct root.
  turbopack: {
    root: projectRoot,
  },
};

export default nextConfig;
