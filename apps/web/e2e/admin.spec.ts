import { expect, login, test } from "./fixtures";

test("ordinary accounts cannot enter administration", async ({
  page,
  signedIn,
}) => {
  expect(signedIn).toContain("@example.test");
  await page.goto("/mon-compte");
  await expect(
    page.getByRole("heading", { name: "Administration" }),
  ).toHaveCount(0);
  await page.goto("/admin");
  await expect(page.getByRole("alert")).toContainText(
    "Accès réservé à l’administration",
  );
  await expect(
    page.getByRole("heading", { name: "browser-owner@example.test" }),
  ).toHaveCount(0);
});

test("administrator creates, edits and removes an equipment reference", async ({
  page,
}, info) => {
  await login(page, "browser-owner@example.test");
  await page.getByRole("heading", { name: "Administration" }).click();
  await page
    .getByRole("combobox", { name: "Section", exact: true })
    .selectOption("equipment");
  await page
    .getByLabel("Nom", { exact: true })
    .fill(`Kayak ${info.project.name}`);
  await page.getByRole("button", { name: "Enregistrer", exact: true }).click();
  const row = page.getByRole("article").filter({
    has: page.getByRole("heading", {
      name: `Kayak ${info.project.name}`,
      exact: true,
    }),
  });
  await expect(row).toBeVisible();
  await row.getByRole("button", { name: "Modifier", exact: true }).click();
  await page
    .getByLabel("Nom", { exact: true })
    .fill(`Kayak double ${info.project.name}`);
  await page.getByRole("button", { name: "Enregistrer", exact: true }).click();
  await expect(
    page.getByRole("heading", {
      name: `Kayak ${info.project.name}`,
      exact: true,
    }),
  ).toHaveCount(0);
  await page.reload();
  await page
    .getByRole("combobox", { name: "Section", exact: true })
    .selectOption("equipment");
  const updated = page.getByRole("article").filter({
    has: page.getByRole("heading", {
      name: `Kayak double ${info.project.name}`,
      exact: true,
    }),
  });
  await expect(updated).toBeVisible();
  await updated.getByRole("button", { name: "Supprimer", exact: true }).click();
  await expect(updated).toHaveCount(0);
});
