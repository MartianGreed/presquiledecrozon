import { beforeAll, describe, expect, test } from "bun:test";
import { load } from "@structure-ai/config";
import { SQL } from "bun";
import { Effect } from "effect";
import sharp from "sharp";
import Stripe from "stripe";
import {
  type Booking,
  emptyRental,
  type Persona,
  type Rental,
  rentalSteps,
  type Subscription,
} from "../../../packages/contracts/src/models";
import { application } from "../src/app";
import { settings } from "../src/config";
import { migrate } from "../src/migrate";

const databaseUrl = process.env.TEST_DATABASE_URL;
const suite = databaseUrl ? describe : describe.skip;
suite("PostgreSQL application acceptance", () => {
  let sql: SQL;
  let app: Awaited<ReturnType<typeof application>>;
  let ownerCookie = "",
    guestCookie = "",
    otherCookie = "";
  let owner: Persona, _guest: Persona;
  let rental: Rental;
  let booking: Booking;
  const origin = "http://localhost:3000";
  async function request(
    path: string,
    method = "GET",
    payload?: unknown,
    cookie = "",
    headers: Record<string, string> = {},
  ) {
    return app.handler(
      new Request(origin + path, {
        method,
        headers: {
          origin,
          ...(cookie ? { cookie } : {}),
          ...(payload !== undefined
            ? { "content-type": "application/json" }
            : {}),
          ...headers,
        },
        body: payload === undefined ? undefined : JSON.stringify(payload),
      }),
    );
  }
  async function account(email: string): Promise<[string, Persona]> {
    const registered = await request("/api/auth/register/password", "POST", {
      email,
      password: "A long test password 2026!",
    });
    expect(registered.status).toBe(201);
    const rows =
      await sql`SELECT body FROM crozon_outbox WHERE recipient=${email} ORDER BY created_at DESC LIMIT 1`;
    const token = new URL(rows[0].body).searchParams.get("token");
    expect(token).toBeTruthy();
    expect(
      (await request("/api/auth/verify-email", "POST", { token })).status,
    ).toBe(200);
    const response = await request("/api/auth/sign-in/password", "POST", {
      email,
      password: "A long test password 2026!",
    });
    expect(response.status).toBe(200);
    const cookie = response.headers.get("set-cookie")!.split(";")[0]!;
    const me = await request("/api/me", "GET", undefined, cookie);
    expect(me.status).toBe(200);
    return [cookie, (await me.json()) as Persona];
  }
  beforeAll(async () => {
    expect(new URL(databaseUrl!).pathname.endsWith("_test")).toBe(true);
    sql = new SQL(databaseUrl!);
    await migrate(sql);
    const tables =
      await sql`SELECT tablename FROM pg_tables WHERE schemaname='public' AND (tablename LIKE 'crozon_%' OR tablename LIKE 'auth_%') AND tablename<>'crozon_schema'`;
    for (const row of tables)
      await sql`TRUNCATE TABLE ${sql(row.tablename)} CASCADE`;
    const config = await Effect.runPromise(
      load(settings, {
        env: {
          DATABASE_URL: databaseUrl!,
          APP_ORIGIN: origin,
          STRIPE_SECRET_KEY: "sk_test_placeholder",
          STRIPE_WEBHOOK_SECRET: "whsec_acceptance",
          UPLOAD_DIRECTORY: "/tmp/crozon-test-uploads",
        },
      }),
    );
    app = await application(sql, config);
    [ownerCookie, owner] = await account("owner@example.test");
    [guestCookie, _guest] = await account("guest@example.test");
    [otherCookie] = await account("other@example.test");
  }, 30000);
  test("anonymous, cross-origin and malformed mutations are refused", async () => {
    expect((await request("/api/me")).status).toBe(401);
    expect(
      (
        await request("/api/rentals", "POST", {}, ownerCookie, {
          origin: "https://evil.test",
        })
      ).status,
    ).toBe(403);
    expect(
      (await request("/api/me", "PUT", { firstname: "Only" }, ownerCookie))
        .status,
    ).toBe(400);
    expect(
      (await request("/api/admin/personas", "GET", undefined, guestCookie))
        .status,
    ).toBe(403);
  });
  test("profile is required and draft editor preserves every section", async () => {
    expect(
      (await request("/api/rentals", "POST", {}, ownerCookie)).status,
    ).toBe(400);
    const profile = {
      firstname: "Marie",
      lastname: "Martin",
      cellphone: "0600000000",
      description: "Bonjour",
      preferredLanguage: "fr_FR",
      birthdate: "",
      gender: "",
    };
    expect((await request("/api/me", "PUT", profile, ownerCookie)).status).toBe(
      200,
    );
    expect((await request("/api/me", "PUT", profile, guestCookie)).status).toBe(
      200,
    );
    const created = await request("/api/rentals", "POST", {}, ownerCookie);
    expect(created.status).toBe(200);
    rental = (await created.json()) as Rental;
    expect((await request(`/api/rentals/${rental.id}`)).status).toBe(404);
    expect(
      (
        await request(
          `/api/rentals/${rental.id}`,
          "GET",
          undefined,
          guestCookie,
        )
      ).status,
    ).toBe(404);
    const picture = await sharp({
      create: { width: 8, height: 8, channels: 3, background: "#205a60" },
    })
      .png()
      .toBuffer();
    const uploaded = await app.handler(
      new Request(`${origin}/api/media`, {
        method: "POST",
        headers: { origin, cookie: ownerCookie, "content-type": "image/png" },
        body: new Uint8Array(picture),
      }),
    );
    expect(uploaded.status).toBe(200);
    const { path } = (await uploaded.json()) as { path: string };
    const input = {
      ...emptyRental(),
      title: "Maison près de la plage",
      description: "Une maison familiale avec jardin sur la presqu’île.",
      bedrooms: [{ name: "Chambre", beds: [{ type: "Double", count: 1 }] }],
      address: {
        street: "1 rue de la plage",
        town: "Crozon",
        postcode: "29160",
        country: "France",
      },
      latitude: 48.24,
      longitude: -4.49,
      photos: [path],
      dailyRate: 10000,
      weeklyRate: 60000,
      maxLeadMonths: 24,
    };
    for (const step of rentalSteps) {
      const response = await request(
        `/api/rentals/${rental.id}`,
        "PUT",
        { version: rental.version, step, rental: input },
        ownerCookie,
      );
      expect(response.status).toBe(200);
      rental = (await response.json()) as Rental;
    }
    expect(rental.completedSteps).toHaveLength(11);
    expect(
      (
        await request(
          `/api/rentals/${rental.id}`,
          "PUT",
          { version: 1, step: "description", rental: input },
          ownerCookie,
        )
      ).status,
    ).toBe(409);
    expect(
      (
        await request(
          `/api/rentals/${rental.id}`,
          "PUT",
          { version: rental.version, step: "description", rental: input },
          guestCookie,
        )
      ).status,
    ).toBe(403);
    expect(
      (
        await request(
          `/api/rentals/${rental.id}/publish`,
          "POST",
          { published: true, version: rental.version },
          ownerCookie,
        )
      ).status,
    ).toBe(402);
  });
  test("signed bound payment events activate once; publication consumes entitlement", async () => {
    const subscription: Subscription = {
      id: crypto.randomUUID(),
      rentalId: rental.id,
      personaId: owner.id,
      planId: "annual",
      amount: 9900,
      months: 12,
      status: "pending",
      paymentIntentId: "cs_test_valid",
      discountId: null,
      expiresAt: null,
    };
    await sql`INSERT INTO crozon_subscriptions(id,rental_id,persona_id,payment_intent_id,data) VALUES(${subscription.id},${rental.id},${owner.id},${subscription.paymentIntentId},${subscription})`;
    const payload = JSON.stringify({
      id: "evt_test_1",
      type: "checkout.session.completed",
      data: {
        object: {
          id: "cs_test_valid",
          client_reference_id: subscription.id,
          metadata: { subscriptionId: subscription.id, rentalId: rental.id },
          amount_total: 9900,
          currency: "eur",
          payment_status: "paid",
        },
      },
    });
    const signature = await Stripe.webhooks.generateTestHeaderStringAsync({
      payload,
      secret: "whsec_acceptance",
    });
    const webhook = () =>
      app.handler(
        new Request(`${origin}/api/stripe/webhook`, {
          method: "POST",
          headers: { "stripe-signature": signature },
          body: payload,
        }),
      );
    const wrongAmount = JSON.stringify({
      ...JSON.parse(payload),
      id: "evt_wrong_amount",
      data: { object: { ...JSON.parse(payload).data.object, amount_total: 1 } },
    });
    const wrongSignature = await Stripe.webhooks.generateTestHeaderStringAsync({
      payload: wrongAmount,
      secret: "whsec_acceptance",
    });
    expect(
      (
        await app.handler(
          new Request(`${origin}/api/stripe/webhook`, {
            method: "POST",
            headers: { "stripe-signature": wrongSignature },
            body: wrongAmount,
          }),
        )
      ).status,
    ).toBe(400);
    expect(
      (
        await sql`SELECT data FROM crozon_subscriptions WHERE id=${subscription.id}`
      )[0].data.status,
    ).toBe("pending");
    expect((await webhook()).status).toBe(200);
    expect((await webhook()).status).toBe(200);
    expect(
      Number((await sql`SELECT count(*) AS n FROM crozon_payment_events`)[0].n),
    ).toBe(1);
    const response = await request(
      `/api/rentals/${rental.id}/publish`,
      "POST",
      { published: true, version: rental.version },
      ownerCookie,
    );
    expect(response.status).toBe(200);
    rental = (await response.json()) as Rental;
    expect((await request(`/api/rentals/${rental.slug}`)).status).toBe(200);
    expect(
      (
        await request(
          `/api/rentals/${rental.slug}`,
          "GET",
          undefined,
          "analytics=1; crozon_session=expired",
        )
      ).status,
    ).toBe(200);
    expect(
      (
        (await request("/api/rentals").then((r) => r.json())) as {
          total: number;
        }
      ).total,
    ).toBe(1);
    expect(
      (
        await app.handler(
          new Request(`${origin}/api/stripe/webhook`, {
            method: "POST",
            body: payload,
          }),
        )
      ).status,
    ).toBe(400);
  });
  test("favorites are idempotent and scoped to each persona", async () => {
    expect(
      (await request(`/api/favorites/${rental.id}`, "PUT", {}, guestCookie))
        .status,
    ).toBe(200);
    expect(
      (await request(`/api/favorites/${rental.id}`, "PUT", {}, guestCookie))
        .status,
    ).toBe(200);
    expect(
      await request("/api/favorites", "GET", undefined, guestCookie).then((r) =>
        r.json(),
      ),
    ).toHaveLength(1);
    expect(
      await request("/api/favorites", "GET", undefined, otherCookie).then((r) =>
        r.json(),
      ),
    ).toHaveLength(0);
  });
  test("booking requests reject own rentals, excess capacity and overlapping races", async () => {
    const start = new Date();
    start.setUTCDate(start.getUTCDate() + 30);
    const end = new Date(start);
    end.setUTCDate(end.getUTCDate() + 7);
    const input = {
      rentalId: rental.id,
      start: start.toISOString().slice(0, 10),
      end: end.toISOString().slice(0, 10),
      peopleCount: 2,
      message: "Bonjour, nous souhaitons réserver.",
    };
    expect(
      (
        await request("/api/bookings", "POST", input, ownerCookie, {
          "idempotency-key": "owner-request",
        })
      ).status,
    ).toBe(403);
    expect(
      (
        await request("/api/quotes", "POST", {
          ...input,
          message: undefined,
          peopleCount: 99,
        })
      ).status,
    ).toBe(400);
    const results = await Promise.all(
      ["request-one", "request-two"].map((key) =>
        request("/api/bookings", "POST", input, guestCookie, {
          "idempotency-key": key,
        }),
      ),
    );
    expect(results.map((r) => r.status).sort()).toEqual([200, 409]);
    const index = results.findIndex((r) => r.status === 200);
    booking = (await results[index]!.json()) as Booking;
    const retry = await request("/api/bookings", "POST", input, guestCookie, {
      "idempotency-key": index === 0 ? "request-one" : "request-two",
    });
    expect(retry.status).toBe(200);
    expect(((await retry.json()) as Booking).id).toBe(booking.id);
    expect(
      (
        await request(
          `/api/bookings/${booking.id}`,
          "GET",
          undefined,
          otherCookie,
        )
      ).status,
    ).toBe(403);
    expect(
      (
        await request(
          `/api/bookings/${booking.id}/confirm`,
          "POST",
          {},
          guestCookie,
        )
      ).status,
    ).toBe(403);
    expect(
      (
        await request(
          `/api/bookings/${booking.id}/confirm`,
          "POST",
          {},
          ownerCookie,
        )
      ).status,
    ).toBe(200);
  });
  test("conversation membership and notification ownership are enforced", async () => {
    expect(
      await request(
        `/api/conversations/${booking.id}/messages`,
        "GET",
        undefined,
        ownerCookie,
      )
        .then((r) => r.json())
        .then((r) => (r as { items: unknown[] }).items),
    ).toHaveLength(1);
    expect(
      (
        await request(
          `/api/conversations/${booking.id}/messages`,
          "POST",
          { body: "Bonjour !" },
          ownerCookie,
        )
      ).status,
    ).toBe(200);
    expect(
      (
        await request(
          `/api/conversations/${booking.id}/messages`,
          "POST",
          { body: "Intrusion" },
          otherCookie,
        )
      ).status,
    ).toBe(403);
    expect(
      (
        (await request(
          "/api/notifications",
          "GET",
          undefined,
          guestCookie,
        ).then((r) => r.json())) as unknown[]
      ).length,
    ).toBeGreaterThan(0);
  });
  test("administrators manage references and disabled accounts lose access", async () => {
    await sql`UPDATE crozon_personas SET admin=true WHERE id=${owner.id}`;
    const plan = {
      id: "test-plan",
      name: "Annuel",
      months: 12,
      amount: 9900,
      active: true,
    };
    expect(
      (await request("/api/admin/plans", "PUT", plan, ownerCookie)).status,
    ).toBe(200);
    expect(
      (await request("/api/plans").then((r) => r.json())) as unknown[],
    ).toHaveLength(1);
    const others =
      await sql`SELECT id FROM crozon_personas WHERE email='other@example.test'`;
    expect(
      (
        await request(
          `/api/admin/personas/${others[0].id}`,
          "POST",
          { disabled: true },
          ownerCookie,
        )
      ).status,
    ).toBe(200);
    expect(
      (await request("/api/me", "GET", undefined, otherCookie)).status,
    ).toBe(403);
    expect(
      (
        await request(
          `/api/admin/personas/${owner.id}`,
          "POST",
          { disabled: true },
          ownerCookie,
        )
      ).status,
    ).toBe(400);
    expect(
      (
        await request(
          "/api/admin/plans/test-plan",
          "DELETE",
          undefined,
          ownerCookie,
        )
      ).status,
    ).toBe(200);
  });
  test("initialized legacy requests submit once and cancellation releases dates", async () => {
    const start = new Date();
    start.setUTCDate(start.getUTCDate() + 90);
    const end = new Date(start);
    end.setUTCDate(end.getUTCDate() + 7);
    const initial: Booking = {
      ...booking,
      id: crypto.randomUUID(),
      status: "initialised",
      start: start.toISOString().slice(0, 10),
      end: end.toISOString().slice(0, 10),
    };
    await sql`INSERT INTO crozon_bookings(id,rental_id,persona_id,start_date,end_date,status,data) VALUES(${initial.id},${rental.id},${initial.personaId},${initial.start},${initial.end},'initialised',${initial})`;
    expect(
      (
        await request(
          `/api/bookings/${initial.id}/request`,
          "POST",
          { body: "Nous confirmons cette demande." },
          guestCookie,
        )
      ).status,
    ).toBe(200);
    expect(
      (
        await request(
          `/api/bookings/${initial.id}/request`,
          "POST",
          { body: "Nous confirmons cette demande." },
          guestCookie,
        )
      ).status,
    ).toBe(200);
    expect(
      Number(
        (
          await sql`SELECT count(*) AS n FROM crozon_messages WHERE booking_id=${initial.id}`
        )[0].n,
      ),
    ).toBe(1);
    expect(
      (
        await request(
          `/api/bookings/${initial.id}/cancel`,
          "POST",
          {},
          ownerCookie,
        )
      ).status,
    ).toBe(200);
    expect(
      (
        await request("/api/quotes", "POST", {
          rentalId: rental.id,
          start: initial.start,
          end: initial.end,
          peopleCount: 2,
        })
      ).status,
    ).toBe(200);
  });
  test("password recovery is single-use and revokes old sessions", async () => {
    expect(
      (
        await request("/api/auth/password/reset/request", "POST", {
          email: "guest@example.test",
        })
      ).status,
    ).toBe(202);
    const rows =
      await sql`SELECT body FROM crozon_outbox WHERE recipient='guest@example.test' AND subject='Réinitialisez votre mot de passe' ORDER BY created_at DESC LIMIT 1`;
    const token = new URL(rows[0].body).searchParams.get("token");
    const payload = { token, newPassword: "A replacement long password!" };
    expect(
      (await request("/api/auth/password/reset/complete", "POST", payload))
        .status,
    ).toBe(200);
    expect(
      (await request("/api/auth/password/reset/complete", "POST", payload))
        .status,
    ).toBeGreaterThanOrEqual(400);
    expect(
      (await request("/api/me", "GET", undefined, guestCookie)).status,
    ).toBe(401);
  });
  test("state survives a new application instance and logout revokes the session", async () => {
    const config = await Effect.runPromise(
      load(settings, {
        env: { DATABASE_URL: databaseUrl!, APP_ORIGIN: origin },
      }),
    );
    const next = await application(sql, config);
    expect(
      (await next.handler(new Request(`${origin}/api/rentals/${rental.slug}`)))
        .status,
    ).toBe(200);
    expect(
      (await request("/api/auth/sign-out", "POST", {}, ownerCookie)).status,
    ).toBe(200);
    expect(
      (await request("/api/me", "GET", undefined, ownerCookie)).status,
    ).toBe(401);
    await sql.close();
  });
});
