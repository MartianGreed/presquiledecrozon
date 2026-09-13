import { account, expect, test } from "./fixtures";

test("register, use and remove a passkey with a real WebAuthn exchange", async ({
  page,
  browser,
  signedIn,
}) => {
  expect(signedIn).toContain("@example.test");
  const cdp = await page.context().newCDPSession(page);
  await cdp.send("WebAuthn.enable");
  const { authenticatorId } = await cdp.send(
    "WebAuthn.addVirtualAuthenticator",
    {
      options: {
        protocol: "ctap2",
        transport: "internal",
        hasResidentKey: true,
        hasUserVerification: true,
        isUserVerified: true,
        automaticPresenceSimulation: true,
      },
    },
  );
  try {
    await page.goto("/mon-compte/parametres");
    await expect(
      page.getByRole("heading", { name: "Paramètres du compte" }),
    ).toBeVisible();
    await page.getByLabel("Nom de la clé d’accès").fill("Mon ordinateur");
    await page.getByRole("button", { name: "Ajouter une clé d’accès" }).click();
    await expect(
      page.getByText("Mon ordinateur", { exact: true }),
    ).toBeVisible();
    const keys = await page.request
      .get("/api/me/passkeys")
      .then((r) => r.json());
    expect(keys).toHaveLength(1);
    expect(Object.keys(keys[0]).sort()).toEqual(["createdAt", "id", "label"]);
    const outsider = await browser.newContext({
      baseURL: new URL(page.url()).origin,
    });
    try {
      await account(outsider.request);
      const denied = await outsider.request.delete(
        `/api/me/passkeys/${encodeURIComponent(keys[0].id)}`,
        { headers: { origin: new URL(page.url()).origin } },
      );
      expect(denied.status()).toBe(404);
    } finally {
      await outsider.close();
    }
    await page.reload();
    await expect(
      page.getByText("Mon ordinateur", { exact: true }),
    ).toBeVisible();
    if (await page.getByRole("button", { name: /Menu/ }).isVisible())
      await page.getByRole("button", { name: /Menu/ }).click();
    await page.getByRole("button", { name: "Déconnexion" }).click();
    await expect(
      page.getByRole("dialog", { name: "Se connecter", exact: true }),
    ).toBeVisible();
    const exchange = page.waitForRequest((request) =>
      request.url().endsWith("/auth/passkeys/authenticate/verify"),
    );
    await page
      .getByRole("button", { name: "Se connecter avec une clé d’accès" })
      .click();
    const payload = (await exchange).postDataJSON();
    await expect(
      page.getByRole("heading", { name: "Mon compte", exact: true }),
    ).toBeVisible();
    const replay = await page.request.post(
      "/api/auth/passkeys/authenticate/verify",
      { data: payload, headers: { origin: new URL(page.url()).origin } },
    );
    expect(replay.status()).toBeGreaterThanOrEqual(400);
    expect(replay.status()).toBeLessThan(500);
    await page.goto("/mon-compte/parametres");
    await page
      .getByRole("button", { name: "Supprimer Mon ordinateur" })
      .click();
    await expect(page.getByText("Mon ordinateur", { exact: true })).toHaveCount(
      0,
    );
    await expect(
      page.getByText("Aucune clé d’accès enregistrée."),
    ).toBeVisible();
  } finally {
    await cdp.send("WebAuthn.removeVirtualAuthenticator", { authenticatorId });
    await cdp.detach();
  }
});
