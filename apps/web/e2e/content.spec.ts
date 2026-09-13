import { expect, futureDate, login, test } from "./fixtures";

test("event proposal is private until an administrator publishes it", async ({
  page,
  browser,
  signedIn,
}, info) => {
  expect(signedIn).toContain("@example.test");
  const title = `Festival de démonstration ${info.project.name}`;
  await page.goto("/proposer-un-evenement");
  await page.getByLabel("Nom de l’événement", { exact: true }).fill(title);
  await page.getByLabel("Thématique", { exact: true }).fill("Culture");
  await page.getByLabel("Date de début", { exact: true }).fill(futureDate(30));
  await page.getByLabel("Date de fin", { exact: true }).fill(futureDate(31));
  await page.getByLabel("Horaire de début", { exact: true }).fill("10:00");
  await page.getByLabel("Horaire de fin", { exact: true }).fill("18:00");
  await page.getByLabel("Adresse", { exact: true }).fill("Place de la mairie");
  await page.getByLabel("Commune", { exact: true }).fill("Crozon");
  await page
    .getByLabel("Résumé", { exact: true })
    .fill("Un événement créé uniquement pour le test.");
  await page
    .getByLabel("Description détaillée", { exact: true })
    .fill(
      "Présentation du festival de démonstration pour le parcours de publication.",
    );
  await page
    .getByLabel("E-mail public de contact", { exact: true })
    .fill("private-organizer@example.test");
  await page
    .getByRole("checkbox", { name: /J’autorise la publication/ })
    .check();
  await page
    .getByLabel("Images de la publication")
    .setInputFiles("apps/web/public/images/coast.jpg");
  await expect(page.getByRole("img", { name: "Image jointe" })).toBeVisible();
  await page.getByRole("button", { name: "Envoyer ma proposition" }).click();
  await expect(page.getByRole("status")).toContainText(
    "examinée avant publication",
  );
  expect(
    (
      await page.request
        .get(`/api/content?kind=event&q=${encodeURIComponent(title)}`)
        .then((r) => r.json())
    ).total,
  ).toBe(0);
  const owner = await browser.newPage({ viewport: page.viewportSize() });
  try {
    await login(owner, "browser-owner@example.test");
    await owner.goto("/admin/publications");
    await owner
      .getByRole("button", { name: `Modifier ${title}`, exact: true })
      .click();
    const form = owner.locator("#content-form");
    await form
      .getByRole("combobox", { name: "État de publication", exact: true })
      .selectOption("published");
    await form
      .getByRole("button", { name: "Enregistrer la publication" })
      .click();
    await expect(owner.getByRole("status")).toContainText("en ligne");
    await page.goto("/evenements");
    await page.getByLabel("Recherche", { exact: true }).fill(title);
    await page.getByRole("button", { name: "Rechercher", exact: true }).click();
    await page.getByRole("link", { name: title, exact: true }).click();
    await expect(
      page.getByRole("heading", { name: title, exact: true }),
    ).toBeVisible();
    await expect(
      page.getByRole("img", { name: title, exact: true }),
    ).toBeVisible();
    await expect(
      page.getByText("private-organizer@example.test", { exact: true }),
    ).toHaveCount(0);
    await expect(page.getByText(signedIn, { exact: true })).toHaveCount(0);
  } finally {
    await owner.close();
  }
});

test("editorial drafts stay private and can be published and withdrawn", async ({
  page,
  browser,
}, info) => {
  await page.goto("/conditions-generales");
  await expect(
    page.getByRole("heading", { name: "Contenu à venir" }),
  ).toBeVisible();
  const title = `Document de test ${info.project.name}`;
  const slug = `document-test-${info.project.name}`;
  const owner = await browser.newPage({ viewport: page.viewportSize() });
  try {
    await login(owner, "browser-owner@example.test");
    await owner.goto("/admin/publications");
    await owner.getByRole("button", { name: "Nouvelle publication" }).click();
    const form = owner.locator("#content-form");
    await form.getByLabel("Titre", { exact: true }).fill(title);
    await form.getByLabel("Adresse de la page", { exact: true }).fill(slug);
    await form
      .getByLabel("Description détaillée", { exact: true })
      .fill("Texte de test, pas un contenu juridique approuvé.");
    await form
      .getByRole("button", { name: "Enregistrer la publication" })
      .click();
    await expect(owner.getByRole("status")).toContainText("brouillon");
    await page.goto(`/informations/${slug}`);
    await expect(
      page.getByRole("heading", { name: "Contenu à venir" }),
    ).toBeVisible();
    await form
      .getByRole("checkbox", { name: /J’autorise la publication/ })
      .check();
    await form
      .getByRole("combobox", { name: "État de publication", exact: true })
      .selectOption("published");
    await form
      .getByRole("button", { name: "Enregistrer la publication" })
      .click();
    await expect(owner.getByRole("status")).toContainText("en ligne");
    await page.reload();
    await expect(
      page.getByRole("heading", { name: title, exact: true }),
    ).toBeVisible();
    await form
      .getByRole("combobox", { name: "État de publication", exact: true })
      .selectOption("draft");
    await form
      .getByRole("button", { name: "Enregistrer la publication" })
      .click();
    await expect(owner.getByRole("status")).toContainText("brouillon");
    await page.reload();
    await expect(
      page.getByRole("heading", { name: "Contenu à venir" }),
    ).toBeVisible();
  } finally {
    await owner.close();
  }
});

test("activities and restaurants can be published and found from public lists", async ({
  page,
  browser,
}, info) => {
  const owner = await browser.newPage({ viewport: page.viewportSize() });
  try {
    await login(owner, "browser-owner@example.test");
    await owner.goto("/admin/publications");
    for (const [kind, path] of [
      ["activity", "activites"],
      ["restaurant", "restaurants"],
    ]) {
      const title = `Fiche ${kind} ${info.project.name}`;
      await owner.getByRole("button", { name: "Nouvelle publication" }).click();
      const form = owner.locator("#content-form");
      await form
        .getByRole("combobox", { name: "Type de publication", exact: true })
        .selectOption(kind!);
      await form.getByLabel("Titre", { exact: true }).fill(title);
      await form
        .getByLabel("Description détaillée", { exact: true })
        .fill("Fiche de démonstration pour vérifier la publication.");
      await form
        .getByRole("checkbox", { name: /J’autorise la publication/ })
        .check();
      await form
        .getByRole("combobox", { name: "État de publication", exact: true })
        .selectOption("published");
      await form
        .getByRole("button", { name: "Enregistrer la publication" })
        .click();
      await expect(owner.getByRole("status")).toContainText("en ligne");
      await page.goto(`/${path}`);
      await page.getByRole("link", { name: title, exact: true }).click();
      await expect(
        page.getByRole("heading", { name: title, exact: true }),
      ).toBeVisible();
    }
  } finally {
    await owner.close();
  }
});
