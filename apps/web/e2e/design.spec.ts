import { expect, test } from "./fixtures";

test("Figma home typography, palette and booking steps", async ({ page }) => {
  await page.goto("/");
  await expect(
    page.getByRole("heading", { name: /Se loger en Presqu’île de Crozon/i }),
  ).toBeVisible();
  await expect(page.locator("body")).toHaveCSS("font-family", /Circular/);
  await expect(page.locator(".search-form button[type=submit]")).toHaveCSS(
    "background-color",
    "rgb(248, 157, 164)",
  );
  await expect(
    page.getByRole("heading", { name: "1. Je choisis" }),
  ).toBeVisible();
  await expect(
    page.getByRole("heading", { name: "2. Je réserve" }),
  ).toBeVisible();
  await expect(
    page.getByRole("heading", { name: "3. Je profite" }),
  ).toBeVisible();
});

test("account navigation follows the Figma sidebar", async ({
  page,
  signedIn,
}) => {
  expect(signedIn).toContain("@example.test");
  await page.goto("/mon-compte/informations");
  const navigation = page.getByRole("navigation", {
    name: "Mon espace personnel",
  });
  await expect(navigation).toBeVisible();
  await expect(
    navigation.getByRole("link", { name: "Profil", exact: true }),
  ).toHaveAttribute("aria-current", "page");
  await navigation.getByRole("link", { name: "Messagerie" }).click();
  await expect(
    page.getByRole("heading", { name: "Mes messages" }),
  ).toBeVisible();
  await expect(
    navigation.getByRole("link", { name: "Messagerie" }),
  ).toHaveAttribute("aria-current", "page");
});

test("authentication dialog traps focus, reveals the password and closes with Escape", async ({
  page,
}) => {
  await page.goto("/login");
  const dialog = page.getByRole("dialog", { name: "Se connecter" });
  await expect(dialog).toBeVisible();
  await page
    .getByLabel("Mot de passe", { exact: true })
    .fill("a private password");
  await page.getByLabel("Afficher le mot de passe").check();
  await expect(
    page.getByLabel("Mot de passe", { exact: true }),
  ).toHaveAttribute("type", "text");
  await dialog.getByRole("link", { name: "Créer mon compte" }).focus();
  await page.keyboard.press("Tab");
  expect(
    await dialog.evaluate((element) =>
      element.contains(document.activeElement),
    ),
  ).toBe(true);
  await page.keyboard.press("Escape");
  await expect(page).toHaveURL(/\/$/);
  await expect(page.getByRole("dialog")).toHaveCount(0);
});

test("mobile navigation opens, follows a link and closes", async ({
  page,
}, info) => {
  await page.goto("/");
  const menu = page.getByRole("button", { name: /Menu/ });
  if (info.project.name === "mobile-chromium") await menu.click();
  else await expect(menu).not.toBeVisible();
  if (info.project.name === "mobile-chromium")
    await expect(menu).toHaveAttribute("aria-expanded", "true");
  await page
    .getByRole("navigation", { name: "Navigation principale" })
    .getByRole("link", { name: "Se loger", exact: true })
    .click();
  await expect(
    page.getByRole("heading", { name: "Les locations de vacances" }),
  ).toBeVisible();
  if (info.project.name === "mobile-chromium")
    await expect(menu).toHaveAttribute("aria-expanded", "false");
  else await expect(menu).not.toBeVisible();
});
