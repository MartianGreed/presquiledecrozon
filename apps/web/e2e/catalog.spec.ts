import { catalogFixture, dates, expect, test } from "./fixtures";

test("home search, result cards, listing details and refresh", async ({
  page,
}) => {
  await page.goto("/");
  await expect(
    page.getByRole("heading", { name: /Se loger en Presqu’île de Crozon/i }),
  ).toBeVisible();
  await page.getByRole("textbox", { name: "Ville ou logement" }).fill("Crozon");
  await page.getByRole("button", { name: /Rechercher/ }).click();
  await expect(page).toHaveURL(/annonces\?q=Crozon/);
  await page.getByRole("heading", { name: "La maison des embruns" }).click();
  await expect(
    page.getByRole("heading", { name: "Votre séjour" }),
  ).toBeVisible();
  for (const heading of [
    "Les équipements",
    "Où se trouve le logement ?",
    "Conditions du séjour",
    "Tarifs",
  ])
    await expect(
      page.getByRole("heading", { name: heading, exact: true }),
    ).toBeVisible();
  await expect(page.getByText("Wi-Fi", { exact: true })).toBeVisible();
  const photo = page.getByRole("img", { name: "La maison des embruns" });
  await expect(photo).toBeVisible();
  await expect
    .poll(() => photo.evaluate((image: HTMLImageElement) => image.naturalWidth))
    .toBeGreaterThan(0);
  await page.reload();
  await expect(
    page.getByRole("heading", { name: "La maison des embruns" }),
  ).toBeVisible();
  await dates(page);
  await expect(page.locator(".quote .total")).toHaveText(/Total\s*810,00\s*€/);
});

test("empty search and unknown page offer a usable way back", async ({
  page,
}) => {
  await page.goto("/annonces");
  await page
    .getByRole("textbox", { name: "Rechercher", exact: true })
    .fill("Atlantide introuvable");
  await page.getByRole("button", { name: "Rechercher", exact: true }).click();
  await expect(
    page.getByRole("heading", { name: "Pas encore de logement à afficher" }),
  ).toBeVisible();
  await page
    .getByRole("textbox", { name: "Rechercher", exact: true })
    .fill("Crozon");
  await page.getByRole("button", { name: "Rechercher", exact: true }).click();
  await expect(
    page.getByRole("heading", { name: "La maison des embruns" }),
  ).toBeVisible();
  await page.goto("/page-inconnue");
  await expect(
    page.getByRole("heading", { name: "Cette page n’existe pas" }),
  ).toBeVisible();
  await page.getByRole("link", { name: "Retour à l’accueil" }).click();
  await expect(
    page.getByRole("heading", { name: /Se loger en Presqu’île de Crozon/i }),
  ).toBeVisible();
});

test("favorites persist, remain private and return to the empty state", async ({
  page,
  signedIn,
}) => {
  expect(signedIn).toContain("@example.test");
  await page.goto("/mon-compte/coups-de-coeur");
  await expect(
    page.getByRole("heading", { name: "Vos coups de cœur vous attendent" }),
  ).toBeVisible();
  await page.goto(`/annonce/${catalogFixture().slug}`);
  await page.getByRole("button", { name: /Coup de cœur/ }).click();
  await expect(page.getByRole("status")).toContainText("ajoutée");
  await page.goto("/mon-compte/coups-de-coeur");
  await page.getByRole("heading", { name: "La maison des embruns" }).click();
  await expect(page.getByRole("button", { name: /Enregistré/ })).toBeVisible();
  await page.reload();
  await page.getByRole("button", { name: /Enregistré/ }).click();
  await expect(page.getByRole("status")).toContainText("retirée");
  await page.goto("/mon-compte/coups-de-coeur");
  await expect(
    page.getByRole("heading", { name: "Vos coups de cœur vous attendent" }),
  ).toBeVisible();
});

test("invalid dates and capacity never produce a bookable quote", async ({
  page,
}) => {
  await page.goto(`/annonce/${catalogFixture().slug}`);
  await dates(page, 90, 0);
  await expect(page.getByRole("alert")).toContainText("1 à 366 nuits");
  await expect(
    page.getByRole("button", { name: "Demander la réservation" }),
  ).toHaveCount(0);
  await page.getByLabel("Voyageurs", { exact: true }).fill("5");
  await dates(page);
  expect(
    await page
      .getByLabel("Voyageurs", { exact: true })
      .evaluate((input: HTMLInputElement) => input.validity.rangeOverflow),
  ).toBe(true);
  await expect(page.locator(".quote")).toHaveCount(0);
  await page.getByLabel("Voyageurs", { exact: true }).fill("4");
  await dates(page);
  await expect(page.locator(".quote .total")).toHaveText(/810,00\s*€/);
});

test("catalog filters apply before pagination and reject malformed dates", async ({
  page,
}) => {
  const response = await page.request.get("/api/rentals?people=5");
  expect(response.status()).toBe(200);
  expect((await response.json()).total).toBe(0);
  expect(
    (
      await page.request.get("/api/rentals?start=2026-99-99&end=2026-12-31")
    ).status(),
  ).toBe(400);
});

test("search controls persist and carry the chosen stay into the listing", async ({
  page,
}) => {
  await page.goto("/");
  await page.getByRole("textbox", { name: "Ville ou logement" }).fill("Crozon");
  await page.getByLabel("Personnes", { exact: true }).selectOption("5");
  await page.getByRole("button", { name: "Rechercher", exact: true }).click();
  await expect(
    page.getByRole("heading", { name: "Pas encore de logement à afficher" }),
  ).toBeVisible();
  await page.getByLabel("Personnes", { exact: true }).selectOption("4");
  await page.getByLabel("Type de logement recherché").selectOption("Maison");
  await page.locator("summary").filter({ hasText: "Prix" }).click();
  await page.getByLabel("Prix de base maximum par nuit, en €").fill("109");
  await page.getByRole("button", { name: "Rechercher", exact: true }).click();
  await expect(
    page.getByRole("heading", { name: "Pas encore de logement à afficher" }),
  ).toBeVisible();
  await page.locator("summary").filter({ hasText: "Prix" }).click();
  await page.getByLabel("Prix de base maximum par nuit, en €").fill("110");
  await page.locator("summary").filter({ hasText: "Prix" }).click();
  await page.locator("summary").filter({ hasText: "Dates" }).click();
  const start = new Date();
  start.setUTCDate(start.getUTCDate() + 90);
  const end = new Date(start);
  end.setUTCDate(end.getUTCDate() + 8);
  await page
    .getByLabel("Arrivée recherchée")
    .fill(start.toISOString().slice(0, 10));
  await page
    .getByLabel("Départ recherché")
    .fill(end.toISOString().slice(0, 10));
  await page.getByRole("button", { name: "Rechercher", exact: true }).click();
  await expect(
    page.getByRole("heading", { name: "La maison des embruns" }),
  ).toBeVisible();
  await page.reload();
  await expect(page.getByLabel("Personnes", { exact: true })).toHaveValue("4");
  await page.getByRole("heading", { name: "La maison des embruns" }).click();
  await expect(page.getByLabel("Arrivée", { exact: true })).toHaveValue(
    start.toISOString().slice(0, 10),
  );
  await expect(page.getByLabel("Départ", { exact: true })).toHaveValue(
    end.toISOString().slice(0, 10),
  );
  await expect(page.getByLabel("Voyageurs", { exact: true })).toHaveValue("4");
});

test("calendar chooses an arrival and departure and updates the quote", async ({
  page,
}) => {
  await page.goto(`/annonce/${catalogFixture().slug}`);
  await page.getByRole("button", { name: "Mois suivant", exact: true }).click();
  const arrival = new Date();
  arrival.setUTCDate(5);
  arrival.setUTCMonth(arrival.getUTCMonth() + 1);
  const departure = new Date(arrival);
  departure.setUTCDate(13);
  const dateLabel = (date: Date) =>
    date.toLocaleDateString("fr-FR", {
      weekday: "long",
      day: "numeric",
      month: "long",
      year: "numeric",
      timeZone: "UTC",
    });
  await page
    .getByRole("button", { name: dateLabel(arrival), exact: true })
    .click();
  await expect(page.getByLabel("Arrivée", { exact: true })).toHaveValue(
    arrival.toISOString().slice(0, 10),
  );
  await page
    .getByRole("button", { name: dateLabel(departure), exact: true })
    .click();
  await expect(page.getByLabel("Départ", { exact: true })).toHaveValue(
    departure.toISOString().slice(0, 10),
  );
  await page
    .getByRole("button", { name: "Vérifier les disponibilités" })
    .click();
  await expect(page.locator(".quote .total")).toHaveText(/810,00\s*€/);
  departure.setUTCDate(16);
  await page
    .getByRole("button", { name: dateLabel(departure), exact: true })
    .click();
  await expect(page.getByLabel("Départ", { exact: true })).toHaveValue("");
  await expect(page.locator(".quote")).toHaveCount(0);
});
