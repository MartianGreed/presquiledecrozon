import { afterAll, beforeAll, describe, expect, test } from "bun:test";
import { load } from "@structure-ai/config";
import { SQL } from "bun";
import { Effect, Redacted } from "effect";
import { application } from "../src/app";
import { settings } from "../src/config";
import { migrate } from "../src/migrate";
import {
  importLegacy,
  legacyArray,
  mapLegacy,
  type Snapshot,
} from "../src/migration/legacy";

test("Doctrine arrays preserve UTF-8 strings and reject PHP objects", () => {
  expect(legacyArray('a:2:{i:0;s:4:"WiFi";i:1;s:8:"Séchoir";}')).toEqual([
    "WiFi",
    "Séchoir",
  ]);
  expect(() => legacyArray('O:4:"Evil":0:{}')).toThrow();
});
const url = process.env.MIGRATION_TEST_DATABASE_URL;
(url ? describe : describe.skip)("legacy migration rehearsal", () => {
  let sql: SQL;
  let snapshot: Snapshot;
  beforeAll(async () => {
    expect(new URL(url!).pathname.endsWith("_test")).toBe(true);
    sql = new SQL(url!);
    await migrate(sql);
    const tables =
      await sql`SELECT tablename FROM pg_tables WHERE schemaname='public' AND (tablename LIKE 'crozon_%' OR tablename LIKE 'auth_%') AND tablename<>'crozon_schema'`;
    for (const row of tables)
      await sql`TRUNCATE TABLE ${sql(row.tablename)} CASCADE`;
    const password = await Bun.password.hash("Legacy password 2026!", {
      algorithm: "bcrypt",
      cost: 4,
    });
    snapshot = {
      version: 1,
      sourceTimeZone: "Europe/Paris",
      mediaBaseUrl: "https://cdn.example.test/rental/",
      tables: {
        user: [
          {
            id: "legacy-owner",
            email: "legacy-owner@example.test",
            roles: ["ROLE_ADMIN"],
            password: password.replace("$2b$", "$2y$"),
            profile_id: "profile-1",
            created_at: "2024-06-01 12:00:00",
          },
          {
            id: "legacy-guest",
            email: "legacy-guest@example.test",
            roles: ["ROLE_USER"],
            password,
            created_at: "2024-06-01 12:00:00",
          },
        ],
        profile: [
          {
            id: "profile-1",
            firstname: "Élodie",
            lastname: "Martin",
            preferred_language: "fr_FR",
            birthdate: "1990-04-12",
          },
        ],
        rental: [
          {
            id: "rental-1",
            owner_id: "legacy-owner",
            slug: "maison-historique",
            status: "published",
            description_id: "description-1",
            address_id: "address-1",
            geolocation_id: "geo-1",
            preferences_id: "pref-1",
            condition_id: "condition-1",
            gallery_id: "gallery-1",
            daily_rate: 10000,
            weekly_rate: 60000,
            custom_furnitures: "a:0:{}",
          },
        ],
        description: [
          {
            id: "description-1",
            title: "Maison historique",
            description: "Une maison familiale au bord de la mer.",
          },
        ],
        configuration: [
          {
            id: "config-1",
            rental_id: "rental-1",
            type_id: "type-1",
            people_count: 4,
          },
        ],
        rental_type: [{ id: "type-1", label: "Maison" }],
        bedroom: [{ id: "room-1", configuration_id: "config-1" }],
        bedroom_bed: [{ bedroom_id: "room-1", bed_id: "bed-1", count: 2 }],
        bed: [{ id: "bed-1", label: "Double" }],
        address: [
          { id: "address-1", town_id: "town-1", address: "Rue du port" },
        ],
        town: [{ id: "town-1", name: "Crozon", postal_code_id: "postcode-1" }],
        postal_code: [{ id: "postcode-1", code: "29160" }],
        geolocation: [{ id: "geo-1", coordinates: { lat: 48.24, lng: -4.49 } }],
        preferences: [
          {
            id: "pref-1",
            accepted_last_booking: "P1D",
            max_time_before_booking: "P1Y",
            begin_booking_at: "16:00:00",
            end_booking_at: "10:00:00",
          },
        ],
        condition: [
          {
            id: "condition-1",
            animals_accepted: true,
            smoking_allowed: false,
            additionnal_rules: "a:0:{}",
          },
        ],
        gallery: [{ id: "gallery-1", cover_id: "photo-1" }],
        media: [
          {
            id: "photo-1",
            name: "photo.jpg",
            created_at: "2024-06-01 12:00:00",
          },
        ],
        subscription: [
          {
            id: "plan-1",
            name: "Annuel",
            amount: 9900,
            validity_duration: "P1Y",
          },
        ],
        rental_subscription: [
          {
            id: "sub-1",
            rental_id: "rental-1",
            subscription_id: "plan-1",
            amount: 9900,
            is_consumed: true,
            provider_charge_id: "ch_historical",
            expires_at: "2099-01-01",
          },
        ],
        booking: [
          {
            id: "booking-1",
            rental_id: "rental-1",
            booker_id: "legacy-guest",
            start_at: "2024-07-01",
            end_at: "2024-07-08",
            people_count: 2,
            status: "done",
            prices: [{ count: 1, price: 600 }],
            created_at: "2024-06-01 12:00:00",
          },
        ],
        conversation: [{ id: "conversation-1", booking_id: "booking-1" }],
        message: [
          {
            id: "message-1",
            conversation_id: "conversation-1",
            sender_id: "legacy-guest",
            message: "Bonjour Élodie",
            send_at: "2024-06-01 12:30:00",
          },
        ],
        favorite: [
          {
            id: "favorite-1",
            rental_id: "rental-1",
            persona_id: "legacy-guest",
          },
        ],
        notification: [
          {
            id: "notification-1",
            target_id: "rental-1",
            target_class: "App\\Entity\\Rental\\Rental",
            label: "Annonce publiée",
            created_at: "2024-06-01 12:00:00",
          },
        ],
      },
    };
  });
  afterAll(async () => {
    await sql.close();
  });
  test("preserves identifiers, cents, media paths, relationships and timestamps", async () => {
    const counts = await importLegacy(sql, snapshot);
    expect(counts.user).toBe(2);
    const rows = await sql`SELECT data FROM crozon_rentals WHERE id='rental-1'`;
    expect(rows[0].data.dailyRate).toBe(10000);
    expect(rows[0].data.slug).toBe("maison-historique");
    expect(rows[0].data.photos).toEqual([
      "https://cdn.example.test/rental/2024/01/06/photo.jpg",
    ]);
    expect(
      (await sql`SELECT data FROM crozon_bookings WHERE id='booking-1'`)[0].data
        .quote.total,
    ).toBe(60000);
    expect(
      (await sql`SELECT data FROM crozon_messages WHERE id='message-1'`)[0].data
        .createdAt,
    ).toBe("2024-06-01T10:30:00.000Z");
    expect(
      Number((await sql`SELECT count(*) AS n FROM crozon_legacy_archive`)[0].n),
    ).toBe(
      Object.values(snapshot.tables).reduce(
        (sum, rows) => sum + rows.length,
        0,
      ),
    );
  });
  test("same snapshot is a no-op and a different snapshot is refused", async () => {
    await importLegacy(sql, snapshot);
    expect(
      Number((await sql`SELECT count(*) AS n FROM crozon_personas`)[0].n),
    ).toBe(2);
    await expect(
      importLegacy(sql, {
        ...snapshot,
        mediaBaseUrl: "https://other.example.test/",
      }),
    ).rejects.toThrow("different snapshot");
  });
  test("existing Symfony bcrypt passwords can still sign in", async () => {
    const config = await Effect.runPromise(
      load(settings, { env: { DATABASE_URL: url! } }),
    );
    const app = await application(sql, config);
    const session = await Effect.runPromise(
      app.accounts.auth.signInPassword(
        "crozon",
        "legacy-owner@example.test",
        "Legacy password 2026!",
      ),
    );
    expect(session.user.id).toBe("legacy-owner");
    expect(Redacted.value(session.token)).toBeTruthy();
  });
  test("ambiguous legacy relationships fail before import", () => {
    const broken = structuredClone(snapshot);
    broken.tables.conversation = [{ id: "conversation-1" }];
    expect(() => mapLegacy(broken)).toThrow("no booking");
  });
});
