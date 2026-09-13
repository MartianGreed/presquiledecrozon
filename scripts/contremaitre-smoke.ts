import assert from "node:assert/strict";
import { mkdir } from "node:fs/promises";
import { join } from "node:path";
import { chromium, expect } from "@playwright/test";

const baseURL = process.env.CONTREMAITRE_BASE_URL;
const artifacts = process.env.CONTREMAITRE_ARTIFACTS;
assert(
  baseURL && artifacts,
  "Run through contremaitre verify --profile smoke.",
);
await mkdir(artifacts, { recursive: true });
const browser = await chromium.launch();
try {
  const context = await browser.newContext({
    baseURL,
    viewport: { width: 1440, height: 1000 },
  });
  const page = await context.newPage();
  const errors: string[] = [];
  page.on("pageerror", (error) => errors.push(error.message));
  const health = await context.request.get("/health/ready");
  expect(health.status()).toBe(200);
  const catalog = await context.request.get("/api/rentals");
  expect(catalog.status()).toBe(200);
  const { items } = await catalog.json();
  expect(items.length).toBeGreaterThan(0);
  await page.goto("/");
  await expect(
    page.getByRole("heading", { name: /Votre prochaine/ }),
  ).toBeVisible();
  await page.screenshot({ path: join(artifacts, "home.png"), fullPage: true });
  await page.goto(`/annonce/${items[0].slug}`);
  await expect(
    page.getByRole("heading", { name: "Votre séjour", exact: true }),
  ).toBeVisible();
  await page.reload();
  await expect(
    page.getByRole("heading", { name: items[0].title, exact: true }),
  ).toBeVisible();
  await page.setViewportSize({ width: 390, height: 844 });
  expect(
    await page.evaluate(
      () => document.documentElement.scrollWidth <= innerWidth,
    ),
  ).toBe(true);
  await page.screenshot({
    path: join(artifacts, "rental-mobile.png"),
    fullPage: true,
  });
  for (const email of [
    "browser-owner@example.test",
    "browser-guest@example.test",
  ]) {
    await page.goto("/login");
    await page.getByLabel("Adresse e-mail").fill(email);
    await page.getByLabel(/^Mot de passe/).fill("Crozon browser test 2026!");
    await page
      .getByRole("button", { name: "Se connecter", exact: true })
      .click();
    await expect(
      page.getByRole("heading", { name: "Mon compte", exact: true }),
    ).toBeVisible();
    await page.getByRole("button", { name: "Déconnexion" }).click();
    await expect(
      page.getByRole("heading", { name: "Se connecter", exact: true }),
    ).toBeVisible();
  }
  expect(errors).toEqual([]);
  console.log(
    "Readiness, seeded catalog, deep links, mobile layout, owner and traveler login/logout passed.",
  );
} finally {
  await browser.close();
}
