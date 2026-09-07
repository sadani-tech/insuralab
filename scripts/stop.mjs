// Best-effort stop of local dev servers started outside `npm start`.
// `npm start` (scripts/dev.mjs) already stops its children on Ctrl+C.
import { execSync } from "node:child_process";

const targets =
  process.platform === "win32"
    ? [
        `for /f "tokens=5" %a in ('netstat -ano ^| findstr :8000 ^| findstr LISTENING') do taskkill /f /pid %a`,
        `for /f "tokens=5" %a in ('netstat -ano ^| findstr :3000 ^| findstr LISTENING') do taskkill /f /pid %a`,
      ]
    : [`lsof -ti tcp:8000 | xargs -r kill`, `lsof -ti tcp:3000 | xargs -r kill`];

for (const cmd of targets) {
  try {
    execSync(cmd, { stdio: "ignore", shell: true });
  } catch {
    // nothing listening — fine
  }
}
console.log("Stopped dev servers on ports 3000 and 8000 (if any were running).");
