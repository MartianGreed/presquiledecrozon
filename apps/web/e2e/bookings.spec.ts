import {
  account,
  api,
  catalogFixture,
  dates,
  expect,
  futureDate,
  login,
  password,
  test,
} from "./fixtures";

test("booking request, private conversation, confirmation, cancellation and released dates", async ({
  page,
  browser,
  signedIn,
}, info) => {
  expect(signedIn).toContain("@example.test");
  const offset = info.project.name === "mobile-chromium" ? 130 : 110;
  await page.goto(`/annonce/${catalogFixture().slug}`);
  await dates(page, offset);
  await expect(page.locator(".quote .total")).toHaveText(/810,00\s*€/);
  await page
    .getByLabel("Message au propriétaire")
    .fill("Bonjour, nous venons en famille.");
  await page.getByRole("button", { name: "Demander la réservation" }).click();
  await expect(
    page.getByText("Demande envoyée", { exact: true }),
  ).toBeVisible();
  const bookingUrl = page.url();
  await page.goto("/mon-profil/vacances");
  await page.getByRole("link", { name: /La maison des embruns/ }).click();
  await expect(page).toHaveURL(bookingUrl);
  await expect(
    page.getByRole("button", { name: "Confirmer la réservation" }),
  ).toHaveCount(0);
  const owner = await browser.newPage({ viewport: page.viewportSize() });
  const stranger = await browser.newPage({ viewport: page.viewportSize() });
  try {
    await account(stranger.request);
    await stranger.goto(bookingUrl);
    await expect(stranger.getByRole("alert")).toContainText(
      "ne participez pas",
    );
    await stranger.goto(
      `/mon-compte/messages?conversation=${bookingUrl.split("/").pop()}`,
    );
    await expect(stranger.getByRole("alert")).toBeVisible();
    await expect(
      stranger.getByText("Bonjour, nous venons en famille.", { exact: true }),
    ).toHaveCount(0);
    await stranger.goto(`/annonce/${catalogFixture().slug}`);
    await dates(stranger, offset);
    await expect(stranger.getByRole("alert")).toContainText("déjà réservé");
    await login(owner, "browser-owner@example.test");
    await owner
      .getByRole("button", { name: /réservation/i })
      .first()
      .click();
    await expect(owner).toHaveURL(bookingUrl);
    await owner
      .getByRole("button", { name: "Confirmer la réservation" })
      .click();
    await expect(owner.getByText("Confirmée", { exact: true })).toBeVisible();
    await owner.getByRole("link", { name: /Ouvrir la conversation/ }).click();
    await expect(
      owner.getByText("Bonjour, nous venons en famille.", { exact: true }),
    ).toBeVisible();
    await owner.getByLabel("Votre message").fill("Bienvenue à Crozon !");
    await owner.getByRole("button", { name: "Envoyer le message" }).click();
    await expect(
      owner.getByText("Bienvenue à Crozon !", { exact: true }),
    ).toBeVisible();
    await page.getByRole("link", { name: /Ouvrir la conversation/ }).click();
    await expect(
      page.getByText("Bienvenue à Crozon !", { exact: true }),
    ).toBeVisible();
    await page.getByLabel("Votre message").fill("Merci, à bientôt !");
    await page.getByRole("button", { name: "Envoyer le message" }).click();
    await owner.reload();
    await expect(
      owner.getByText("Merci, à bientôt !", { exact: true }),
    ).toBeVisible();
    await owner.goto(bookingUrl);
    await owner.getByRole("button", { name: "Annuler la réservation" }).click();
    await expect(owner.getByText("Annulée", { exact: true })).toBeVisible();
    await page.goto(bookingUrl);
    await expect(page.getByText("Annulée", { exact: true })).toBeVisible();
    await stranger.reload();
    await dates(stranger, offset);
    await expect(stranger.locator(".quote .total")).toHaveText(/810,00\s*€/);
  } finally {
    await owner.close();
    await stranger.close();
  }
});

test("booking asks an unprofiled traveler to complete personal information", async ({
  page,
}) => {
  await account(page.request, false);
  await page.goto(`/annonce/${catalogFixture().slug}`);
  await dates(page, 150);
  await page.getByRole("button", { name: "Demander la réservation" }).click();
  await expect(page.getByRole("alert")).toContainText(
    "informations personnelles",
  );
  await page.getByRole("link", { name: "Compléter mon profil" }).click();
  await expect(
    page.getByRole("heading", { name: "Mes informations personnelles" }),
  ).toBeVisible();
});

test("anonymous booking keeps the selected stay through sign-in", async ({
  page,
}) => {
  const email = await account(page.request);
  await api(page.request, "/auth/sign-out", {});
  const { slug } = catalogFixture();
  await page.goto(`/annonce/${slug}`);
  await dates(page, 160);
  await page
    .getByLabel("Message au propriétaire")
    .fill("Bonjour, nous arrivons en train.");
  await page.getByRole("button", { name: "Demander la réservation" }).click();
  await expect(
    page.getByRole("heading", { name: "Se connecter" }),
  ).toBeVisible();
  await page.getByLabel("Adresse e-mail").fill(email);
  await page.getByLabel("Mot de passe", { exact: true }).fill(password);
  await page.getByRole("button", { name: "Se connecter", exact: true }).click();
  await expect(page).toHaveURL(new RegExp(`/annonce/${slug}$`));
  await expect(page.getByLabel("Arrivée", { exact: true })).toHaveValue(
    futureDate(160),
  );
  await expect(page.getByLabel("Départ", { exact: true })).toHaveValue(
    futureDate(168),
  );
  await page
    .getByRole("button", { name: "Vérifier les disponibilités" })
    .click();
  await expect(page.getByLabel("Message au propriétaire")).toHaveValue(
    "Bonjour, nous arrivons en train.",
  );
});
