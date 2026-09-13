import { execFileSync } from "node:child_process";
import { readFileSync } from "node:fs";
import {
  type APIRequestContext,
  test as base,
  expect,
  type Page,
} from "@playwright/test";

export const password = "Crozon browser test 2026!";
export function emailLink(email: string) {
  return execFileSync("bun", ["scripts/e2e-db.ts", "email", email], {
    encoding: "utf8",
  }).trim();
}
export function catalogFixture(): { rentalId: string; slug: string } {
  return JSON.parse(readFileSync("/tmp/crozon-browser-fixture.json", "utf8"));
}
export function futureDate(days: number) {
  const date = new Date();
  date.setUTCDate(date.getUTCDate() + days);
  return date.toISOString().slice(0, 10);
}
export async function api(
  request: APIRequestContext,
  path: string,
  data: unknown,
  method = "POST",
) {
  const response = await request.fetch(`/api${path}`, {
    method,
    data,
    headers: { origin: process.env.E2E_BASE_URL ?? "http://localhost:3000" },
  });
  expect(response.ok(), `${method} ${path}: ${await response.text()}`).toBe(
    true,
  );
  return response.json();
}
// Setup uses the real auth API and outbox. The registration scenario exercises those steps in the UI.
export async function account(request: APIRequestContext, profile = true) {
  const email = `browser-${crypto.randomUUID()}@example.test`;
  await api(request, "/auth/register/password", { email, password });
  await api(request, "/auth/verify-email", {
    token: new URL(emailLink(email)).searchParams.get("token"),
  });
  await api(request, "/auth/sign-in/password", { email, password });
  if (profile)
    await api(
      request,
      "/me",
      {
        firstname: "Alex",
        lastname: "Martin",
        cellphone: "0600000000",
        description: "Bonjour !",
        preferredLanguage: "fr_FR",
        birthdate: "",
        gender: "",
      },
      "PUT",
    );
  return email;
}
export async function login(page: Page, email: string, value = password) {
  await page.goto("/login");
  await page.getByLabel("Adresse e-mail").fill(email);
  await page.getByLabel("Mot de passe", { exact: true }).fill(value);
  await page.getByRole("button", { name: "Se connecter", exact: true }).click();
  await expect(
    page.getByRole("heading", { name: "Mon compte", exact: true }),
  ).toBeVisible();
}
export async function dates(page: Page, offset = 90, nights = 8) {
  await page.getByLabel("Arrivée", { exact: true }).fill(futureDate(offset));
  await page
    .getByLabel("Départ", { exact: true })
    .fill(futureDate(offset + nights));
  await page
    .getByRole("button", { name: "Vérifier les disponibilités" })
    .click();
}
export const test = base.extend<{ signedIn: string; browserHealth: undefined }>(
  {
    signedIn: async ({ page }, use) => {
      await use(await account(page.request));
    },
    browserHealth: [
      async ({ page }, use, info) => {
        const errors: string[] = [];
        page.on("pageerror", (error) => errors.push(error.message));
        await use(undefined);
        if (info.status === info.expectedStatus) {
          expect(errors, "Uncaught browser errors").toEqual([]);
          await expect(
            page.getByRole("status").filter({ hasText: "Chargement…" }),
          ).toHaveCount(0);
          expect(
            await page.evaluate(
              () => document.documentElement.scrollWidth <= innerWidth,
            ),
            "Horizontal overflow",
          ).toBe(true);
          await info.attach("final-page", {
            body: await page.screenshot({ fullPage: true }),
            contentType: "image/png",
          });
        }
      },
      { auto: true },
    ],
  },
);
export { expect };
