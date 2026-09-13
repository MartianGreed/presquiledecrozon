import {
  account,
  api,
  catalogFixture,
  emailLink,
  expect,
  login,
  password,
  test,
} from "./fixtures";

test("registration rejects mismatch, verifies email, persists profile and logs out", async ({
  page,
}) => {
  const email = `browser-${crypto.randomUUID()}@example.test`;
  await page.goto("/creer-mon-compte");
  await page.getByLabel("Adresse e-mail").fill(email);
  await page.getByLabel(/^Mot de passe/).fill(password);
  await page.getByLabel("Confirmer le mot de passe").fill(`${password}x`);
  await page
    .getByRole("button", { name: "Créer mon compte", exact: true })
    .click();
  await expect(page.getByRole("alert")).toContainText("ne correspondent pas");
  await page.getByLabel("Confirmer le mot de passe").fill(password);
  await page
    .getByRole("button", { name: "Créer mon compte", exact: true })
    .click();
  await expect(page.getByRole("status")).toContainText("Consultez votre boîte");
  const link = emailLink(email);
  await page.goto("/login");
  await page.getByLabel("Adresse e-mail").fill(email);
  await page.getByLabel("Mot de passe", { exact: true }).fill(password);
  await page.getByRole("button", { name: "Se connecter", exact: true }).click();
  await expect(page.getByRole("alert")).toContainText("Vérifiez votre adresse");
  await page.goto(link);
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
  await page.getByLabel("Téléphone").fill("0612345678");
  await page
    .getByLabel("À propos de vous")
    .fill("Nous aimons les promenades sur la côte.");
  await page
    .getByRole("button", { name: "Enregistrer mes informations" })
    .click();
  await expect(page.getByRole("status")).toContainText("enregistrées");
  await page.reload();
  await expect(page.getByLabel("Prénom", { exact: true })).toHaveValue("Alex");
  await expect(page.getByLabel("Téléphone")).toHaveValue("0612345678");
  if (await page.getByRole("button", { name: /Menu/ }).isVisible())
    await page.getByRole("button", { name: /Menu/ }).click();
  await page.getByRole("button", { name: "Déconnexion" }).click();
  await expect(
    page.getByRole("heading", { name: "Se connecter", exact: true }),
  ).toBeVisible();
  await page.goto("/mon-compte/informations");
  await expect(
    page.getByRole("heading", { name: "Se connecter" }),
  ).toBeVisible();
});

test("password recovery changes credentials, revokes sessions and rejects token replay", async ({
  page,
  browser,
  signedIn,
}) => {
  const oldSession = await browser.newPage();
  try {
    await login(oldSession, signedIn);
    await api(page.request, "/auth/sign-out", {});
    await page.goto("/login");
    await page.getByRole("link", { name: "Mot de passe oublié ?" }).click();
    await expect(
      page.getByRole("heading", { name: "Mot de passe oublié", exact: true }),
    ).toBeVisible();
    await page.getByLabel("Adresse e-mail").fill(signedIn);
    await page.getByRole("button", { name: "Recevoir le lien" }).click();
    await expect(page.getByRole("status")).toContainText(
      "Si un compte correspond",
    );
    const link = emailLink(signedIn);
    await page.goto(link);
    await page
      .getByLabel("Nouveau mot de passe", { exact: true })
      .fill(`${password} changed`);
    await page
      .getByRole("button", { name: "Enregistrer mon mot de passe" })
      .click();
    await expect(
      page.getByRole("heading", { name: "Mon compte", exact: true }),
    ).toBeVisible();
    if (await page.getByRole("button", { name: /Menu/ }).isVisible())
      await page.getByRole("button", { name: /Menu/ }).click();
    await page.getByRole("button", { name: "Déconnexion" }).click();
    await oldSession.reload();
    await expect(
      oldSession.getByRole("heading", { name: "Se connecter" }),
    ).toBeVisible();
    await page.getByLabel("Adresse e-mail").fill(signedIn);
    await page.getByLabel("Mot de passe", { exact: true }).fill(password);
    await page
      .getByRole("button", { name: "Se connecter", exact: true })
      .click();
    await expect(page.getByRole("alert")).toContainText("incorrect");
    await login(page, signedIn, `${password} changed`);
    await page.goto(link);
    await page
      .getByLabel("Nouveau mot de passe", { exact: true })
      .fill(`${password} replay`);
    await page
      .getByRole("button", { name: "Enregistrer mon mot de passe" })
      .click();
    await expect(page.getByRole("alert")).toContainText("invalide ou a expiré");
  } finally {
    await oldSession.close();
  }
});

test("unknown email recovery does not reveal account existence", async ({
  page,
}) => {
  await page.goto("/reinitialisation-mot-de-passe");
  await page
    .getByLabel("Adresse e-mail")
    .fill(`absent-${crypto.randomUUID()}@example.test`);
  await page.getByRole("button", { name: "Recevoir le lien" }).click();
  await expect(page.getByRole("status")).toContainText(
    "Si un compte correspond",
  );
});

test("protected deep link returns to the requested page after login", async ({
  page,
}) => {
  const email = await account(page.request);
  await api(page.request, "/auth/sign-out", {});
  await page.goto("/mon-compte/messages");
  await expect(page).toHaveURL(/login\?next=/);
  await page.getByLabel("Adresse e-mail").fill(email);
  await page.getByLabel("Mot de passe", { exact: true }).fill(password);
  await page.getByRole("button", { name: "Se connecter", exact: true }).click();
  await expect(
    page.getByRole("heading", { name: "Mes messages" }),
  ).toBeVisible();
  await expect(
    page.getByText(
      "Vos conversations apparaîtront après une demande de réservation.",
    ),
  ).toBeVisible();
});

test("anonymous favorite returns to the chosen listing after sign-in", async ({
  page,
}) => {
  const email = await account(page.request);
  await api(page.request, "/auth/sign-out", {});
  const { slug } = catalogFixture();
  await page.goto(`/annonce/${slug}`);
  await page.getByRole("button", { name: /Coup de cœur/ }).click();
  await expect(
    page.getByRole("heading", { name: "Se connecter" }),
  ).toBeVisible();
  await page.getByLabel("Adresse e-mail").fill(email);
  await page.getByLabel("Mot de passe", { exact: true }).fill(password);
  await page.getByRole("button", { name: "Se connecter", exact: true }).click();
  await expect(page).toHaveURL(new RegExp(`/annonce/${slug}$`));
  await page.getByRole("button", { name: /Coup de cœur/ }).click();
  await expect(page.getByRole("status")).toContainText("ajoutée");
});
