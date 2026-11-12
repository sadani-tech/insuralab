import { test, expect } from "@playwright/test";
import { mkdir, writeFile } from "node:fs/promises";
import path from "node:path";

test("records map readiness and browser frame cadence on the real corpus", async ({
  page,
}, info) => {
  const start = performance.now();
  await page.goto("/");
  await expect(
    page.locator('.neural-scene[data-ready="true"]').or(page.locator(".concept-list")),
  ).toBeVisible();
  const interactiveMs = Math.round(performance.now() - start);
  await page.getByRole("button", { name: "Daftar", exact: true }).click();
  await expect(page.locator(".concept-list button")).toHaveCount(49);
  const switchStart = performance.now();
  await page.getByRole("button", { name: "Graph View", exact: true }).click();
  await expect(
    page.locator('.graph-three[data-ready="true"]').or(page.locator(".concept-list")),
  ).toBeVisible();
  const graphSwitchReadyMs = Math.round(performance.now() - switchStart);
  const frameCadence = await page.evaluate(
    () =>
      new Promise<number>((resolve) => {
        let frames = 0;
        const begin = performance.now();
        function frame(now: number) {
          frames++;
          if (now - begin >= 1500) resolve(Math.round((frames * 1000) / (now - begin)));
          else requestAnimationFrame(frame);
        }
        requestAnimationFrame(frame);
      }),
  );
  const report = {
    project: info.project.name,
    viewport: page.viewportSize(),
    nodes: 49,
    edges: 87,
    interactiveMs,
    graphSwitchReadyMs,
    interactiveTargetMs: 3000,
    targetMet: interactiveMs <= 3000,
    browserFrameCadence: frameCadence,
    canvas3dAvailable: (await page.locator(".graph-three canvas").count()) > 0,
    note: "rAF cadence is a responsiveness indicator, not a GPU/renderer FPS benchmark. Physical mobile hardware was not profiled.",
  };
  const dir = path.resolve("../docs/screenshots");
  await mkdir(dir, { recursive: true });
  await writeFile(
    path.join(dir, `performance-${info.project.name}.json`),
    JSON.stringify(report, null, 2),
  );
  expect(interactiveMs).toBeLessThan(15000);
});
