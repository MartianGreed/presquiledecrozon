import { execFileSync } from "node:child_process";
import {
  account,
  catalogFixture,
  expect,
  futureDate,
  login,
  test,
} from "./fixtures";

test("traveler contacts an owner without booking and searches the conversation", async ({
  page,
  browser,
  signedIn,
}) => {
  expect(signedIn).toContain("@example.test");
  await page.goto(`/annonce/${catalogFixture().slug}`);
  await page.getByRole("button", { name: "Contacter le propriétaire" }).click();
  await expect(
    page.getByRole("heading", { name: "Mes messages" }),
  ).toBeVisible();
  await page
    .getByLabel("Votre message")
    .fill("Le jardin est-il entièrement clos ?");
  await page.getByRole("button", { name: "Envoyer le message" }).click();
  await expect(
    page.getByText("Le jardin est-il entièrement clos ?", { exact: true }),
  ).toBeVisible();
  const conversationUrl = page.url();
  expect(
    (await page.request.get("/api/bookings").then((r) => r.json())).total,
  ).toBe(0);
  const owner = await browser.newPage({ viewport: page.viewportSize() });
  const outsider = await browser.newPage({ viewport: page.viewportSize() });
  try {
    await account(outsider.request);
    await outsider.goto(conversationUrl);
    await expect(outsider.getByRole("alert")).toContainText(
      "ne participez pas",
    );
    await login(owner, "browser-owner@example.test");
    await owner.goto(conversationUrl);
    await expect(
      owner.getByText("Le jardin est-il entièrement clos ?", { exact: true }),
    ).toBeVisible();
    await owner.getByLabel("Votre message").fill("Oui, le jardin est clos.");
    await owner.getByRole("button", { name: "Envoyer le message" }).click();
    await expect(
      owner.getByText("Oui, le jardin est clos.", { exact: true }),
    ).toBeVisible();
    await page.reload();
    await expect(
      page.getByText("Oui, le jardin est clos.", { exact: true }),
    ).toBeVisible();
    await page.getByLabel("Rechercher une conversation").fill("Camille");
    await page.getByRole("button", { name: "Rechercher", exact: true }).click();
    await expect(page).toHaveURL(/q=Camille/);
    await expect(
      page.getByRole("status").filter({ hasText: "Chargement…" }),
    ).toHaveCount(0);
    await expect(
      page
        .getByRole("navigation", { name: "Conversations" })
        .getByText("La maison des embruns"),
    ).toBeVisible();
    await page.getByLabel("Rechercher une conversation").fill("Aucun résultat");
    await page.getByRole("button", { name: "Rechercher", exact: true }).click();
    await expect(
      page.getByRole("navigation", { name: "Conversations" }).getByRole("link"),
    ).toHaveCount(0);
  } finally {
    await owner.close();
    await outsider.close();
  }
});

test("completed traveler publishes a review and owner replies and moderates it", async ({
  page,
  browser,
  signedIn,
}, info) => {
  const offset = info.project.name === "chromium" ? 210 : 230;
  const rental = catalogFixture();
  const bookingResponse = await page.request.post("/api/bookings", {
    headers: {
      origin: process.env.E2E_BASE_URL ?? "http://localhost:3000",
      "idempotency-key": crypto.randomUUID(),
    },
    data: {
      rentalId: rental.rentalId,
      start: futureDate(offset),
      end: futureDate(offset + 7),
      peopleCount: 2,
      message: "Préparation de notre séjour.",
    },
  });
  expect(bookingResponse.ok()).toBe(true);
  const booking = await bookingResponse.json();
  execFileSync("bun", [
    "scripts/e2e-db.ts",
    "completed-stay",
    booking.id,
    signedIn,
  ]);
  await page.goto(`/annonce/${rental.slug}`);
  const body = `Une maison agréable, séjour ${info.project.name}.`;
  await page
    .getByRole("combobox", { name: "Note", exact: true })
    .selectOption({ label: "5 sur 5" });
  await page.getByLabel("Votre avis", { exact: true }).fill(body);
  await page.getByRole("button", { name: "Publier mon avis" }).click();
  await expect(page.getByRole("status")).toContainText("Votre avis est publié");
  await expect(page.getByText(body, { exact: true })).toBeVisible();
  await page.reload();
  await expect(
    page.getByRole("button", { name: "Publier mon avis" }),
  ).toHaveCount(0);
  const owner = await browser.newPage({ viewport: page.viewportSize() });
  try {
    await login(owner, "browser-owner@example.test");
    await owner.goto(`/annonce/${rental.slug}`);
    const card = owner.locator(".review-card").filter({ hasText: body });
    await card.getByRole("textbox").fill("Merci pour votre visite !");
    await card.getByRole("button", { name: "Enregistrer ma réponse" }).click();
    await expect(
      card.getByText("Merci pour votre visite !", { exact: true }),
    ).toBeVisible();
    await owner.goto("/admin/avis");
    const moderation = owner.locator(".review-card").filter({ hasText: body });
    await moderation.getByRole("button", { name: "Masquer cet avis" }).click();
    await expect(moderation.getByText("Masqué", { exact: true })).toBeVisible();
    await page.reload();
    await expect(page.getByText(body, { exact: true })).toHaveCount(0);
    await moderation.getByRole("button", { name: "Publier cet avis" }).click();
    await expect(moderation.getByText("Publié", { exact: true })).toBeVisible();
    await page.reload();
    await expect(page.getByText(body, { exact: true })).toBeVisible();
  } finally {
    await owner.close();
  }
});
