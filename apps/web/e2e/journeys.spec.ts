import { execFileSync } from "node:child_process";
import { readFileSync } from "node:fs";
import { expect, type Page, test } from "@playwright/test";

let fixture: { rentalId: string; slug: string };
test.beforeAll(() => {
  fixture = JSON.parse(
    readFileSync("/tmp/crozon-browser-fixture.json", "utf8"),
  );
});
async function login(page: Page, email: string) {
  await page.goto("/login");
  await page.getByLabel("Adresse e-mail").fill(email);
  await page.getByLabel(/^Mot de passe/).fill("Crozon browser test 2026!");
  await page.getByRole("button", { name: "Se connecter", exact: true }).click();
  await expect(
    page.getByRole("heading", { name: "Mon compte", exact: true }),
  ).toBeVisible();
}
test("public discovery, deep links and mobile layout", async ({ page }) => {
  const errors: string[] = [];
  page.on("pageerror", (error) => errors.push(error.message));
  await page.goto("/");
  await expect(
    page.getByRole("heading", { name: /Votre prochaine/ }),
  ).toBeVisible();
  await page.getByRole("textbox", { name: "Ville ou logement" }).fill("Crozon");
  await page.getByRole("button", { name: /Trouver ma location/ }).click();
  await expect(
    page.getByRole("heading", { name: "Les locations de vacances" }),
  ).toBeVisible();
  await page.getByRole("heading", { name: "La maison des embruns" }).click();
  await expect(
    page.getByRole("heading", { name: "Votre séjour" }),
  ).toBeVisible();
  await page.reload();
  await expect(
    page.getByRole("heading", { name: "La maison des embruns" }),
  ).toBeVisible();
  await page.setViewportSize({ width: 390, height: 844 });
  expect(
    await page.evaluate(
      () => document.documentElement.scrollWidth <= innerWidth,
    ),
  ).toBe(true);
  await page.screenshot({
    path: "test-results/crozon-mobile.png",
    fullPage: true,
  });
  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto("/");
  await page.screenshot({
    path: "test-results/crozon-home.png",
    fullPage: true,
  });
  expect(errors).toEqual([]);
});
test("registration, email verification, profile and logout", async ({
  page,
}) => {
  const email = `browser-new-${Date.now()}@example.test`;
  await page.goto("/creer-mon-compte");
  await page.getByLabel("Adresse e-mail").fill(email);
  await page.getByLabel(/^Mot de passe/).fill("Crozon browser test 2026!");
  await page
    .getByLabel("Confirmer le mot de passe")
    .fill("Crozon browser test 2026!");
  await page
    .getByRole("button", { name: "Créer mon compte", exact: true })
    .click();
  await expect(page.getByRole("status")).toContainText("Consultez votre boîte");
  const url = execFileSync("bun", ["scripts/e2e-db.ts", "email", email], {
    encoding: "utf8",
  }).trim();
  await page.goto(url);
  await page
    .getByRole("button", { name: "Vérifier mon adresse", exact: true })
    .click();
  await expect(page.getByRole("status")).toContainText(
    "Votre adresse est vérifiée",
  );
  await login(page, email);
  await page.getByRole("heading", { name: "Mon profil" }).click();
  await page.getByLabel("Prénom", { exact: true }).fill("Alex");
  await page.getByLabel("Nom", { exact: true }).fill("Martin");
  await page
    .getByRole("button", { name: "Enregistrer mes informations" })
    .click();
  await expect(page.getByRole("status")).toContainText("enregistrées");
  await page.getByRole("button", { name: "Déconnexion" }).click();
  await expect(
    page.getByRole("heading", { name: "Se connecter" }),
  ).toBeVisible();
  await page.goto("/mon-compte");
  await expect(
    page.getByRole("heading", { name: "Se connecter" }),
  ).toBeVisible();
});
test("favorite, quote, booking request, owner confirmation and conversation", async ({
  page,
  browser,
}) => {
  await login(page, "browser-guest@example.test");
  await page.goto(`/annonce/${fixture.slug}`);
  await page.getByRole("button", { name: /Coup de cœur/ }).click();
  await expect(page.getByRole("status")).toContainText("ajoutée");
  const start = new Date();
  start.setUTCDate(start.getUTCDate() + 60);
  const end = new Date(start);
  end.setUTCDate(end.getUTCDate() + 8);
  await page
    .getByLabel("Arrivée", { exact: true })
    .fill(start.toISOString().slice(0, 10));
  await page
    .getByLabel("Départ", { exact: true })
    .fill(end.toISOString().slice(0, 10));
  await page
    .getByRole("button", { name: "Vérifier les disponibilités" })
    .click();
  await expect(page.locator(".quote .total")).toBeVisible();
  await page
    .getByLabel("Message au propriétaire")
    .fill("Bonjour, nous venons en famille.");
  await page.getByRole("button", { name: "Demander la réservation" }).click();
  await expect(
    page.getByText("Demande envoyée", { exact: true }),
  ).toBeVisible();
  const bookingUrl = page.url();
  const owner = await browser.newPage();
  await login(owner, "browser-owner@example.test");
  await owner.goto(bookingUrl);
  await owner.getByRole("button", { name: "Confirmer la réservation" }).click();
  await expect(owner.getByText("Confirmée", { exact: true })).toBeVisible();
  await owner.getByRole("link", { name: /Ouvrir la conversation/ }).click();
  await expect(
    owner.getByText("Bonjour, nous venons en famille."),
  ).toBeVisible();
  await owner.getByLabel("Votre message").fill("Bienvenue à Crozon !");
  await owner.getByRole("button", { name: "Envoyer le message" }).click();
  await expect(
    owner.getByText("Bienvenue à Crozon !", { exact: true }),
  ).toBeVisible();
  await owner.close();
  await page.reload();
  await expect(page.getByText("Confirmée", { exact: true })).toBeVisible();
});
test("owner can resume and save the rental editor", async ({ page }) => {
  await login(page, "browser-owner@example.test");
  await page.goto(
    `/deposez-votre-annonce/description?rental_id=${fixture.rentalId}`,
  );
  await expect(page.getByLabel("Titre de votre annonce")).toHaveValue(
    "La maison des embruns",
  );
  await page
    .getByLabel(/^Description/)
    .fill(
      "Une maison familiale lumineuse avec jardin. Votre séjour au grand air vous attend.",
    );
  await page.getByRole("button", { name: "Enregistrer et continuer" }).click();
  await expect(
    page.getByRole("heading", { name: "Adresse", exact: true }),
  ).toBeVisible();
  await page.reload();
  await expect(page.getByLabel("Ville", { exact: true })).toHaveValue("Crozon");
});
