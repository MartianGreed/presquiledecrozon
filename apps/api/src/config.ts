import { load, Settings } from "@structure-ai/config";
import { Effect, Option, Redacted } from "effect";
export const settings = Settings.struct({
  port: Settings.port("PORT", { default: 3000 }),
  origin: Settings.url("APP_ORIGIN", {
    default: new URL("http://localhost:3000"),
  }),
  databaseUrl: Settings.secret("DATABASE_URL"),
  googleOAuthClientId: Settings.optional(
    Settings.secret("GOOGLE_OAUTH_CLIENT_ID"),
  ),
  googleOAuthClientSecret: Settings.optional(
    Settings.secret("GOOGLE_OAUTH_CLIENT_SECRET"),
  ),
  facebookOAuthClientId: Settings.optional(
    Settings.secret("FACEBOOK_OAUTH_CLIENT_ID"),
  ),
  facebookOAuthClientSecret: Settings.optional(
    Settings.secret("FACEBOOK_OAUTH_CLIENT_SECRET"),
  ),
  facebookGraphVersion: Settings.string("FACEBOOK_GRAPH_VERSION", {
    default: "v25.0",
  }),
  stripeSecret: Settings.optional(Settings.secret("STRIPE_SECRET_KEY")),
  stripeWebhookSecret: Settings.optional(
    Settings.secret("STRIPE_WEBHOOK_SECRET"),
  ),
  mailjetKey: Settings.optional(Settings.secret("MAILJET_API_KEY")),
  mailjetSecret: Settings.optional(Settings.secret("MAILJET_SECRET_KEY")),
  emailSender: Settings.string("EMAIL_SENDER", {
    default: "bonjour@presquiledecrozon.fr",
  }),
  uploadDirectory: Settings.string("UPLOAD_DIRECTORY", {
    default: "./var/uploads",
  }),
  webDirectory: Settings.string("WEB_DIRECTORY", {
    default: "./dist/web/browser",
  }),
  googleMapsKey: Settings.optional(Settings.secret("GOOGLE_MAPS_API_KEY")),
  mailMode: Settings.literal("MAIL_MODE", ["mailjet", "outbox"], {
    default: "outbox",
  }),
});
export const loadConfig = () => Effect.runPromise(load(settings));
export type AppConfig = Awaited<ReturnType<typeof loadConfig>>;
export function secret(
  value: Option.Option<Redacted.Redacted<string>>,
): string | undefined {
  return Option.isSome(value) ? Redacted.value(value.value) : undefined;
}
