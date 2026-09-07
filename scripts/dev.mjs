// Runs the Laravel API and the Next.js dev server together, with prefixed output.
// Cross-platform (Windows / macOS / Linux). Ctrl+C stops both.
import { spawn } from "node:child_process";
import { fileURLToPath } from "node:url";
import { dirname, join } from "node:path";

const root = join(dirname(fileURLToPath(import.meta.url)), "..");
const npm = process.platform === "win32" ? "npm.cmd" : "npm";

const procs = [
  {
    name: "api",
    cmd: "php",
    args: ["artisan", "serve", "--host=127.0.0.1", "--port=8000"],
    cwd: join(root, "backend"),
    color: "\x1b[36m",
  },
  {
    name: "web",
    cmd: npm,
    args: ["run", "dev"],
    cwd: join(root, "frontend"),
    color: "\x1b[35m",
  },
];

const children = [];
let shuttingDown = false;

function shutdown(code = 0) {
  if (shuttingDown) return;
  shuttingDown = true;
  for (const c of children) {
    try {
      c.kill("SIGINT");
    } catch {}
  }
  process.exit(code);
}

for (const p of procs) {
  const child = spawn(p.cmd, p.args, { cwd: p.cwd, shell: process.platform === "win32" });
  children.push(child);
  const tag = `${p.color}[${p.name}]\x1b[0m `;
  const pipe = (stream, out) => {
    stream.setEncoding("utf8");
    stream.on("data", (d) => {
      for (const line of d.split(/\r?\n/)) if (line) out.write(tag + line + "\n");
    });
  };
  pipe(child.stdout, process.stdout);
  pipe(child.stderr, process.stderr);
  child.on("exit", (code) => {
    process.stdout.write(tag + `exited with code ${code}\n`);
    shutdown(code ?? 0);
  });
}

process.on("SIGINT", () => shutdown(0));
process.on("SIGTERM", () => shutdown(0));
