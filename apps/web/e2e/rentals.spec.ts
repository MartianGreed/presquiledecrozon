import { execFileSync } from "node:child_process";
import type { Page } from "@playwright/test";
import { api, dates, expect, futureDate, test } from "./fixtures";

async function next(page: Page, heading: string) {
  await page.getByRole("button", { name: "Enregistrer et continuer" }).click();
  await expect(
    page.getByRole("heading", { name: heading, exact: true }),
  ).toBeVisible();
}

test("owner completes all listing steps, uploads photos, resumes and controls publication", async ({
  page,
  browser,
  signedIn,
}, info) => {
  test.setTimeout(60000);
  const title = `Maison du navigateur ${info.project.name}`;
  await page.goto("/mon-compte/annonces");
  await expect(
    page.getByRole("heading", { name: "Pas encore de logement à afficher" }),
  ).toBeVisible();
  await page
    .getByRole("link", { name: "Déposer une annonce", exact: true })
    .last()
    .click();
  await expect(
    page.getByRole("heading", { name: "Le logement", exact: true }),
  ).toBeVisible();
  const id = new URL(page.url()).searchParams.get("rental_id")!;
  await page.getByLabel("Type de logement").fill("Maison");
  await page.getByLabel("Nombre de voyageurs").fill("4");
  await page.getByRole("button", { name: "Enregistrer et continuer" }).click();
  await expect(page.getByRole("alert")).toContainText("chambres et couchages");
  await page.getByRole("button", { name: "Ajouter une chambre" }).click();
  await page.getByLabel("Nom de la chambre").fill("Chambre côté mer");
  await page.getByLabel("Nombre", { exact: true }).fill("2");
  await next(page, "Équipements");
  await page
    .getByLabel("Équipements disponibles")
    .fill("Wi-Fi\nJardin\nParking");
  await next(page, "Description");
  await page.getByLabel("Titre de votre annonce").fill(title);
  await page
    .getByLabel(/^Description/)
    .fill(
      "Une maison familiale avec vue sur la mer, un jardin et une terrasse.",
    );
  await next(page, "Adresse");
  await page.getByRole("link", { name: "Reprendre plus tard" }).click();
  await page
    .getByRole("link", { name: "Déposer une annonce", exact: true })
    .last()
    .click();
  await expect(
    page.getByRole("heading", { name: "Adresse", exact: true }),
  ).toBeVisible();
  expect(new URL(page.url()).searchParams.get("rental_id")).toBe(id);
  await page.getByLabel("Adresse", { exact: true }).fill("12 rue de la plage");
  await page.getByLabel("Code postal").fill("29160");
  await page.getByLabel("Ville", { exact: true }).fill("Crozon");
  await page.getByLabel("Pays", { exact: true }).fill("France");
  await next(page, "Localisation");
  await page.getByLabel("Latitude").fill("48.24");
  await page.getByLabel("Longitude").fill("-4.49");
  await next(page, "Photos");
  await page.getByRole("button", { name: "Enregistrer et continuer" }).click();
  await expect(page.getByRole("alert")).toContainText("au moins une photo");
  await page.getByLabel("Ajouter des photos").setInputFiles({
    name: "invalid.png",
    mimeType: "image/png",
    buffer: Buffer.from("not an image"),
  });
  await expect(page.getByRole("alert")).toContainText(/image|photo/i);
  // Each upload receives its own stored media ID, including identical source files.
  await page
    .getByLabel("Ajouter des photos")
    .setInputFiles(Array(4).fill("apps/web/public/images/coast.jpg"));
  await expect(
    page.getByRole("img", { name: "Photo de votre logement" }),
  ).toHaveCount(4);
  await expect
    .poll(() =>
      page
        .getByRole("img", { name: "Photo de votre logement" })
        .first()
        .evaluate((image: HTMLImageElement) => image.naturalWidth),
    )
    .toBeGreaterThan(0);
  await next(page, "Préférences");
  await page.getByLabel("Délai minimum").fill("2");
  await page.getByLabel("Réservation possible").fill("12");
  await next(page, "Calendrier");
  await page.getByRole("button", { name: "Ajouter une période" }).click();
  await page.getByLabel("Du", { exact: true }).fill(futureDate(200));
  await page.getByLabel("Au", { exact: true }).fill(futureDate(207));
  await next(page, "Taxes et services");
  await page.getByLabel("Taxe de séjour", { exact: true }).fill("Incluse");
  await page.getByLabel("Forfait ménage").fill("45.50");
  await page.getByLabel("Forfait linge").fill("20");
  await page.getByLabel("Linge fourni").fill("Draps\nServiettes");
  await next(page, "Tarifs");
  await page.getByLabel("Tarif par nuit").fill("110");
  await page.getByLabel("Tarif par semaine").fill("700");
  await page
    .getByRole("button", { name: "Ajouter un tarif saisonnier" })
    .click();
  await page.getByLabel("Du", { exact: true }).fill(futureDate(180));
  await page.getByLabel("Au", { exact: true }).fill(futureDate(187));
  await page.getByLabel("Nuit, en €", { exact: true }).fill("150");
  await page.getByLabel("Semaine, en €", { exact: true }).fill("900");
  await next(page, "Conditions");
  await page.getByLabel("Animaux acceptés", { exact: true }).check();
  await page
    .getByLabel("Règles supplémentaires")
    .fill("Respectez le calme après 22 h.");
  await next(page, "Votre annonce est enregistrée");
  await page.getByRole("link", { name: "Choisir mon abonnement" }).click();
  await expect(
    page.getByRole("combobox", { name: "Abonnement", exact: true }),
  ).toContainText("99,00");
  await page
    .getByRole("button", { name: "Continuer vers le paiement sécurisé" })
    .click();
  await expect(page.getByRole("alert")).toContainText(
    "paiement est temporairement indisponible",
  );
  // The return page must never create a paid entitlement.
  await page.goto(`/abonnement/confirm/${id}`);
  await page.getByRole("link", { name: "Gérer mes annonces" }).click();
  await page.getByRole("button", { name: "Publier", exact: true }).click();
  await expect(page.getByRole("alert")).toContainText("abonnement payé");
  const anonymous = await browser.newPage({ viewport: page.viewportSize() });
  try {
    await anonymous.goto(`/previsualisation/annonce?rental_id=${id}`);
    await expect(anonymous.getByRole("alert")).toBeVisible();
    await expect(anonymous.getByRole("heading", { name: title })).toHaveCount(
      0,
    );
    // This fixture represents an already-paid subscription. It does not test Stripe.
    execFileSync("bun", [
      "scripts/e2e-db.ts",
      "paid-entitlement",
      id,
      signedIn,
    ]);
    await page.getByRole("button", { name: "Publier", exact: true }).click();
    await expect(page.getByRole("status")).toContainText("publiée");
    await page.getByRole("heading", { name: title }).click();
    const publicUrl = page.url();
    await anonymous.goto(publicUrl);
    await expect(anonymous.getByRole("heading", { name: title })).toBeVisible();
    await expect(anonymous.getByRole("img", { name: title })).toHaveCount(4);
    await expect(
      anonymous.getByText("Respectez le calme après 22 h."),
    ).toBeVisible();
    await dates(anonymous, 90);
    await expect(anonymous.locator(".quote .total")).toHaveText(/875,50\s*€/);
    await dates(anonymous, 180);
    await expect(anonymous.locator(".quote .total")).toHaveText(
      /1\s*115,50\s*€/,
    );
    await dates(anonymous, 201);
    await expect(anonymous.getByRole("alert")).toContainText("indisponible");
    await expect(anonymous.locator(".quote")).toHaveCount(0);
    await anonymous
      .getByRole("link", { name: "Presqu'île de Crozon", exact: true })
      .click();
    await expect(
      anonymous.getByRole("heading", { name: /Votre prochaine/ }),
    ).toBeVisible();
    await page.goto("/mon-compte/annonces");
    await page.getByRole("button", { name: "Désactiver", exact: true }).click();
    await expect(page.getByRole("status")).toContainText("désactivée");
    await anonymous.goBack();
    await expect(anonymous.getByRole("alert")).toBeVisible();
    await expect(anonymous.getByRole("heading", { name: title })).toHaveCount(
      0,
    );
    await page.getByRole("link", { name: "Modifier", exact: true }).click();
    await page.getByRole("link", { name: /Description/, exact: false }).click();
    await expect(page.getByLabel("Titre de votre annonce")).toHaveValue(title);
    await page.reload();
    await expect(page.getByLabel("Titre de votre annonce")).toHaveValue(title);
  } finally {
    await anonymous.close();
  }
});

test("editor rejects a stale tab without overwriting the saved description", async ({
  page,
  browser,
  signedIn,
}) => {
  expect(signedIn).toContain("@example.test");
  const draft = await api(page.request, "/rentals", {});
  const url = `/deposez-votre-annonce/description?rental_id=${draft.id}`;
  await page.goto(url);
  const other = await page.context().newPage();
  try {
    await other.goto(url);
    await expect(other.getByLabel("Titre de votre annonce")).toBeVisible();
    await page.getByLabel("Titre de votre annonce").fill("Le titre enregistré");
    await page
      .getByLabel(/^Description/)
      .fill("Cette description doit être conservée après le conflit.");
    await next(page, "Adresse");
    await other.getByLabel("Titre de votre annonce").fill("Le titre périmé");
    await other
      .getByLabel(/^Description/)
      .fill("Cette description ne doit pas remplacer la première.");
    await other
      .getByRole("button", { name: "Enregistrer et continuer" })
      .click();
    await expect(other.getByRole("alert")).toContainText("a changé");
    await other.reload();
    await expect(other.getByLabel("Titre de votre annonce")).toHaveValue(
      "Le titre enregistré",
    );
    await page.goto(url);
    await expect(page.getByLabel("Titre de votre annonce")).toHaveValue(
      "Le titre enregistré",
    );
    const intruder = await browser.newPage();
    try {
      await intruder.goto(`/previsualisation/annonce?rental_id=${draft.id}`);
      await expect(intruder.getByRole("alert")).toBeVisible();
      await expect(
        intruder.getByRole("heading", { name: "Le titre enregistré" }),
      ).toHaveCount(0);
    } finally {
      await intruder.close();
    }
  } finally {
    await other.close();
  }
});
