import { test, expect } from "@playwright/test";
import { mkdir } from "node:fs/promises";
import path from "node:path";

test.beforeEach(async ({ page }) => {
  page.on("request", (request) => {
    expect(request.url(), "Core flow must not call an AI provider").not.toMatch(
      /api\.(openai|anthropic)|generativelanguage|embeddings|\/completions/,
    );
  });
});

async function screenshot(page: import("@playwright/test").Page, name: string, project: string) {
  const dir = path.resolve("../docs/screenshots");
  await mkdir(dir, { recursive: true });
  await page.screenshot({ path: path.join(dir, `${name}-${project}.png`), fullPage: true });
}

test("explore, focus, inspect a relation and follow its evidence", async ({ page }, info) => {
  await page.goto("/");
  await expect(page.getByRole("heading", { name: "Jelajah perlindungan" })).toBeVisible();
  await expect(page.getByRole("button", { name: "Neural View", exact: true })).toHaveAttribute(
    "aria-pressed",
    "true",
  );
  await expect(page.locator(".neural-scene")).toHaveAttribute("data-ready", "true");
  await screenshot(page, "explorer", info.project.name);
  await page.getByRole("textbox", { name: "Cari pengetahuan" }).fill("cashless");
  await page
    .locator(".concept-list button")
    .filter({ has: page.getByText("Cashless", { exact: true }) })
    .click();
  await expect(page).toHaveURL(/node=cashless/);
  await expect(page.getByRole("heading", { name: "Cashless", exact: true })).toBeVisible();
  await page.locator(".related-concepts summary").first().click();
  await page.getByRole("link", { name: "Dasar hubungan" }).first().click();
  await expect(page.locator(".source-reader")).toBeVisible();
  await screenshot(page, "source-reader", info.project.name);
  expect(
    await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth),
  ).toBeTruthy();
});

test("keyword answers, no-result handling, and optional AI availability", async ({
  page,
}, info) => {
  await page.goto("/tanya");
  await page.getByRole("textbox", { name: "Cari pengetahuan" }).fill("cashless");
  await page.getByText("Apakah cashless berarti semua biaya dibayar?", { exact: true }).click();
  await expect(page.locator("details[open]")).toContainText(
    "Cashless menjelaskan fasilitas penyelesaian tagihan",
  );
  await screenshot(page, "answers", info.project.name);
  await page.getByRole("button", { name: "Tentang bantuan AI" }).click();
  await expect(page.getByRole("status")).toContainText("Bantuan AI belum tersedia");
  await page.getByRole("textbox", { name: "Cari pengetahuan" }).fill("zzzx-nonexistent");
  await expect(page.getByRole("heading", { name: "Belum ada hasil yang sesuai." })).toBeVisible();
});

test("guide checklist and private workspace availability", async ({ page }, info) => {
  await page.goto("/panduan?entry=membaca-riplay");
  await expect(
    page.getByRole("heading", { name: "Membaca RIPLAY dengan lebih percaya diri", exact: true }),
  ).toBeVisible();
  await page.getByRole("checkbox").first().check();
  await expect(page.getByText("1/5 ditandai")).toBeVisible();
  await screenshot(page, "guide-reader", info.project.name);
  await page.goto("/agent");
  await expect(page.getByText("Area operasional belum diaktifkan")).toBeVisible();
  await expect(page.getByRole("textbox")).toHaveCount(0);
  await screenshot(page, "agent", info.project.name);
});

test("direct URLs, browser history, list and reduced motion", async ({ page }) => {
  await page.emulateMedia({ reducedMotion: "reduce" });
  await page.goto("/?view=list&node=polis&group=polis");
  await expect(page.getByRole("heading", { name: "Polis", exact: true })).toBeVisible();
  // The mobile sheet can be closed so the underlying list remains reachable.
  await page.getByRole("button", { name: "Tutup detail konsep" }).click();
  await expect(page).toHaveURL(/node=none/);
  await page.goBack();
  await expect(page).toHaveURL(/node=polis/);
});

test("Graph View is a distinct light network and preserves question context across modes", async ({
  page,
}, info) => {
  await page.goto("/?view=graph");
  await expect(page.locator(".graph-three canvas").or(page.locator(".concept-list"))).toBeVisible({
    timeout: 60000,
  });
  if (await page.locator(".graph-three").isVisible())
    await expect(page.locator(".graph-three")).toHaveAttribute("data-ready", "true");
  await expect(page.locator(".hpt-visual")).toHaveClass(/is-graph/);
  await screenshot(page, "graph-view", info.project.name);
  await page
    .getByRole("textbox", { name: "Apa yang ingin Anda pahami?" })
    .fill("Apakah cashless berarti semua biaya dibayar?");
  await page.getByRole("button", { name: "Cari jawaban terkurasi", exact: true }).click();
  await expect(page.locator(".hpt-question-results")).toContainText("Berikut materi terkait");
  await page
    .locator(".hpt-answer summary")
    .filter({ hasText: "Apakah cashless berarti semua biaya dibayar?" })
    .click();
  await expect(page.locator(".hpt-answer[open]")).toContainText("Cashless menjelaskan fasilitas");
  await screenshot(page, "explorer-question", info.project.name);
  await page.getByRole("button", { name: "Neural View", exact: true }).click();
  await expect(page.locator(".neural-scene")).toHaveAttribute("data-ready", "true");
  await expect(page).toHaveURL(/ask=/);
  await expect(page.locator(".hpt-visual")).toHaveClass(/is-neural/);
  await expect(page.locator(".hpt-question-results")).toContainText("Apakah cashless");
  await screenshot(page, "neural-view", info.project.name);
  await page.getByRole("button", { name: "Jeda gerak", exact: true }).click();
  await expect(page.getByRole("button", { name: "Lanjutkan gerak" })).toBeVisible();
  await page.getByRole("textbox", { name: "Apa yang ingin Anda pahami?" }).fill("zzzx-nonexistent");
  await page.getByRole("button", { name: "Cari jawaban terkurasi", exact: true }).click();
  await expect(page.locator(".hpt-question-results")).toContainText("Belum ada materi yang cocok");
  await expect(page.locator(".hpt-answer")).toHaveCount(0);
});

test("both renderers respect reduced motion", async ({ page }) => {
  await page.emulateMedia({ reducedMotion: "reduce" });
  for (const mode of ["neural", "graph"]) {
    await page.goto(`/?view=${mode}`);
    const scene = page.locator(mode === "neural" ? ".neural-scene" : ".graph-three");
    await expect(scene).toHaveAttribute("data-ready", "true");
    await expect(
      page.getByRole("button", { name: "Gerak dinonaktifkan sesuai preferensi perangkat" }),
    ).toBeDisabled();
    // Wait through initial texture/camera setup, then require a stationary scene.
    // Continuous auto-rotation would never produce equal frames over this interval.
    await expect
      .poll(
        async () => {
          const before = await scene.locator("canvas").screenshot();
          await page.waitForTimeout(350);
          return (await scene.locator("canvas").screenshot()).equals(before);
        },
        { timeout: 10000 },
      )
      .toBe(true);
  }
});

for (const mode of ["neural", "graph"])
  test(`missing WebGL in ${mode} retains an equivalent list and type filters`, async ({ page }) => {
    await page.addInitScript(() => {
      const original = HTMLCanvasElement.prototype.getContext;
      HTMLCanvasElement.prototype.getContext = function (
        this: HTMLCanvasElement,
        contextId: string,
        ...options: unknown[]
      ) {
        if (contextId === "webgl" || contextId === "webgl2" || contextId === "experimental-webgl")
          return null;
        return Reflect.apply(original, this, [contextId, ...options]);
      } as typeof original;
    });
    await page.goto(`/?view=${mode}`);
    await expect(page.locator(".concept-list")).toBeVisible();
    await page.getByRole("combobox", { name: "Jenis simpul" }).selectOption("product");
    await expect(page.locator(".concept-list button")).toHaveCount(1);
    await page.getByRole("button", { name: "Reset tampilan" }).click();
    await expect(page.getByRole("combobox", { name: "Jenis simpul" })).toHaveValue("all");
  });
