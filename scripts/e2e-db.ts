import { load } from "@structure-ai/config";
import { SQL } from "bun";
import { Effect, Redacted } from "effect";
import { application } from "../apps/api/src/app";
import { settings } from "../apps/api/src/config";
import { TENANT } from "../apps/api/src/identity/service";
import { migrate } from "../apps/api/src/migrate";
import { rentals } from "../apps/api/src/rentals/service";
import {
  emptyRental,
  type Persona,
  type Rental,
  rentalSteps,
  type Subscription,
} from "../packages/contracts/src/models";

const preview = process.argv[2] === "preview";
if (
  preview &&
  (process.env.CONTREMAITRE_PREVIEW !== "1" ||
    process.env.MAIL_MODE !== "outbox" ||
    process.env.STRIPE_SECRET_KEY ||
    process.env.MAILJET_API_KEY)
)
  throw new Error(
    "Preview seeding requires CONTREMAITRE_PREVIEW=1, MAIL_MODE=outbox and no provider credentials.",
  );
const url = preview ? process.env.DATABASE_URL : process.env.TEST_DATABASE_URL;
if (!url || (!preview && !new URL(url).pathname.endsWith("_test")))
  throw new Error("A dedicated TEST_DATABASE_URL ending in _test is required.");
const sql = new SQL(url);
try {
  if (process.argv[2] === "email") {
    const rows =
      await sql`SELECT body FROM crozon_outbox WHERE recipient=${process.argv[3]!} AND body LIKE 'http%' ORDER BY created_at DESC LIMIT 1`;
    if (!rows[0]) throw new Error("No test email found.");
    process.stdout.write(rows[0].body);
  } else if (process.argv[2] === "paid-entitlement") {
    // Test-only business fixture. Never settle a real payment or use a preview database.
    const rows =
      await sql`SELECT r.id,r.owner_id FROM crozon_rentals r JOIN crozon_personas p ON p.id=r.owner_id WHERE r.id=${process.argv[3]!} AND p.email=${process.argv[4]!}`;
    if (!rows[0]) throw new Error("Test rental and owner do not match.");
    const sub: Subscription = {
      id: crypto.randomUUID(),
      rentalId: rows[0].id,
      personaId: rows[0].owner_id,
      planId: "annual",
      amount: 9900,
      months: 12,
      status: "paid",
      paymentIntentId: null,
      discountId: null,
      expiresAt: null,
    };
    await sql`INSERT INTO crozon_subscriptions(id,rental_id,persona_id,data) VALUES(${sub.id},${sub.rentalId},${sub.personaId},${sub})`;
  } else {
    await migrate(sql);
    if (!preview) {
      const names =
        await sql`SELECT tablename FROM pg_tables WHERE schemaname='public' AND (tablename LIKE 'crozon_%' OR tablename LIKE 'auth_%') AND tablename<>'crozon_schema'`;
      for (const row of names)
        await sql`TRUNCATE TABLE ${sql(row.tablename)} CASCADE`;
    }
    const config = await Effect.runPromise(
      load(settings, {
        env: {
          DATABASE_URL: url,
          APP_ORIGIN:
            (preview ? process.env.APP_ORIGIN : process.env.E2E_BASE_URL) ??
            "http://localhost:3000",
          UPLOAD_DIRECTORY: preview
            ? (process.env.UPLOAD_DIRECTORY ?? "/app/var/uploads")
            : "/tmp/crozon-test-uploads",
        },
      }),
    );
    const app = await application(sql, config);
    const actor = async (email: string, admin = false): Promise<Persona> => {
      const existing =
        await sql`SELECT id,email,profile,admin FROM crozon_personas WHERE email=${email}`;
      if (existing[0]) return existing[0] as Persona;
      await Effect.runPromise(
        app.accounts.auth.registerPassword({
          tenantId: TENANT,
          email,
          password: "Crozon browser test 2026!",
        }),
      );
      const rows =
        await sql`SELECT body FROM crozon_outbox WHERE recipient=${email} ORDER BY created_at DESC LIMIT 1`;
      const token = new URL(rows[0].body).searchParams.get("token")!;
      await Effect.runPromise(
        app.accounts.auth.verifyEmail(TENANT, Redacted.make(token)),
      );
      const session = await Effect.runPromise(
        app.accounts.auth.signInPassword(
          TENANT,
          email,
          "Crozon browser test 2026!",
        ),
      );
      const profile = {
        firstname: "Camille",
        lastname: "Martin",
        cellphone: "0600000000",
        description: "Bonjour !",
        preferredLanguage: "fr_FR",
        birthdate: "",
        gender: "",
      };
      await sql`INSERT INTO crozon_personas(id,email,profile,admin) VALUES(${session.user.id},${email},${profile},${admin})`;
      return { id: session.user.id, email, profile, admin };
    };
    const owner = await actor("browser-owner@example.test", true);
    await actor("browser-guest@example.test");
    const existing =
      await sql`SELECT data FROM crozon_rentals WHERE owner_id=${owner.id} LIMIT 1`;
    let rental: Rental;
    if (existing[0]) rental = existing[0].data;
    else {
      const catalog = rentals(sql);
      rental = await catalog.create(owner);
      const photo = "/images/coast.jpg";
      await sql`INSERT INTO crozon_uploads(id,persona_id,path) VALUES(${crypto.randomUUID()},${owner.id},${photo}) ON CONFLICT DO NOTHING`;
      const input = {
        ...emptyRental(),
        title: "La maison des embruns",
        description:
          "Une maison familiale lumineuse à deux pas de la mer, avec jardin et terrasse.",
        type: "Maison",
        peopleCount: 4,
        bedrooms: [{ name: "Chambre", beds: [{ type: "Double", count: 2 }] }],
        equipment: ["Wi-Fi", "Jardin", "Parking"],
        address: {
          street: "Rue de la plage",
          postcode: "29160",
          town: "Crozon",
          country: "France",
        },
        latitude: 48.24,
        longitude: -4.49,
        photos: [photo],
        dailyRate: 11000,
        weeklyRate: 70000,
        maxLeadMonths: 24,
      };
      for (const step of rentalSteps)
        rental = await catalog.save(
          owner,
          rental.id,
          rental.version,
          step,
          input,
        );
      const sub: Subscription = {
        id: crypto.randomUUID(),
        rentalId: rental.id,
        personaId: owner.id,
        planId: "annual",
        amount: 9900,
        months: 12,
        status: "paid",
        paymentIntentId: null,
        discountId: null,
        expiresAt: null,
      };
      await sql`INSERT INTO crozon_subscriptions(id,rental_id,persona_id,data) VALUES(${sub.id},${rental.id},${owner.id},${sub})`;
      rental = await catalog.publish(owner, rental.id, rental.version, true);
    }
    await sql`INSERT INTO crozon_reference(kind,id,data) VALUES('plans','annual',${{ id: "annual", name: "Un an sur la presqu’île", amount: 9900, months: 12, active: true }}) ON CONFLICT DO NOTHING`;
    await Bun.write(
      "/tmp/crozon-browser-fixture.json",
      JSON.stringify({ rentalId: rental.id, slug: rental.slug }),
    );
  }
} finally {
  await sql.close();
}
