import { defineConfig, devices } from "@playwright/test";

const testDatabaseUrl = process.env.TEST_DATABASE_URL;
if (!testDatabaseUrl || !new URL(testDatabaseUrl).pathname.endsWith("_test"))
  throw new Error(
    "Playwright requires a dedicated TEST_DATABASE_URL ending in _test.",
  );

export default defineConfig({
  testDir: "./apps/web/e2e",
  globalSetup: "./apps/web/e2e/setup.ts",
  workers: 1,
  fullyParallel: false,
  timeout: 30000,
  retries: 0,
  use: {
    baseURL: process.env.E2E_BASE_URL ?? "http://localhost:3000",
    trace: "retain-on-failure",
    actionTimeout: 10000,
    screenshot: "only-on-failure",
  },
  projects: [
    { name: "chromium", use: { ...devices["Desktop Chrome"] } },
    { name: "mobile-chromium", use: { ...devices["Pixel 7"] } },
  ],
  reporter: [
    ["list"],
    ["html", { outputFolder: "playwright-report", open: "never" }],
  ],
  webServer: {
    command: "bun apps/api/src/migrate.ts && bun apps/api/src/main.ts",
    url: `${process.env.E2E_BASE_URL ?? "http://localhost:3000"}/health/ready`,
    reuseExistingServer: false,
    env: {
      DATABASE_URL: testDatabaseUrl,
      APP_ORIGIN: process.env.E2E_BASE_URL ?? "http://localhost:3000",
      UPLOAD_DIRECTORY: "/tmp/crozon-test-uploads",
      MAIL_MODE: "outbox",
      STRIPE_SECRET_KEY: "",
      STRIPE_WEBHOOK_SECRET: "",
      MAILJET_API_KEY: "",
      MAILJET_SECRET_KEY: "",
    },
  },
});
