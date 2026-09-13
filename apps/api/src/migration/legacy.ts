import { createHash } from "node:crypto";
import type { SQL } from "bun";
import {
  type Booking,
  type Discount,
  emptyRental,
  type Message,
  type Persona,
  type Plan,
  type Rental,
  rentalSteps,
  type Subscription,
} from "../../../../packages/contracts/src/models";
import { assert } from "../errors";
import { completeStep, quote, validateDates } from "../rentals/domain";
export interface Snapshot {
  version: 1;
  sourceTimeZone: string;
  mediaBaseUrl: string;
  tables: Record<string, Record<string, unknown>[]>;
}
const text = (value: unknown, fallback = ""): string =>
  value === undefined || value === null ? fallback : String(value);
const number = (value: unknown, fallback = 0): number => {
  const result =
    value === undefined || value === null ? fallback : Number(value);
  assert(Number.isFinite(result), 400, "migration", "Invalid numeric field.");
  return result;
};
const object = (value: unknown): Record<string, unknown> => {
  assert(
    typeof value === "object" && value !== null && !Array.isArray(value),
    400,
    "migration",
    "Expected a legacy object.",
  );
  return value as Record<string, unknown>;
};
export function legacyArray(value: unknown): unknown[] {
  if (value === null || value === undefined || value === "") return [];
  if (Array.isArray(value)) return value;
  assert(typeof value === "string", 400, "migration", "Invalid legacy array.");
  if (value.startsWith("[")) return JSON.parse(value) as unknown[];
  // Doctrine's PHP serialization. Only scalar values and arrays are accepted, never objects/references.
  const bytes = Buffer.from(value);
  let offset = 0;
  function expect(mark: string) {
    const token = Buffer.from(mark);
    assert(
      bytes
        .subarray(offset, offset + token.length)
        .equals(new Uint8Array(token)),
      400,
      "migration",
      "Invalid PHP array encoding.",
    );
    offset += token.length;
  }
  function until(mark: string) {
    const end = bytes.indexOf(mark, offset);
    assert(end >= offset, 400, "migration", "Truncated PHP array.");
    const result = bytes.subarray(offset, end).toString();
    offset = end + mark.length;
    return result;
  }
  function parse(depth = 0): unknown {
    assert(depth < 20, 400, "migration", "PHP array nesting exceeds limit.");
    const type = String.fromCharCode(bytes[offset++]!);
    if (type === "N") {
      expect(";");
      return null;
    }
    expect(":");
    if (type === "s") {
      const length = Number(until(":"));
      assert(
        Number.isInteger(length) && length >= 0 && length <= 1_000_000,
        400,
        "migration",
        "Invalid string length.",
      );
      expect('"');
      const result = bytes.subarray(offset, offset + length).toString();
      offset += length;
      expect('";');
      return result;
    }
    if (type === "i" || type === "d") return Number(until(";"));
    if (type === "b") return until(";") === "1";
    assert(
      type === "a",
      400,
      "migration",
      "Only scalar PHP arrays can be imported.",
    );
    const count = Number(until(":"));
    assert(
      Number.isInteger(count) && count >= 0 && count <= 10000,
      400,
      "migration",
      "Invalid array length.",
    );
    expect("{");
    const result: unknown[] = [];
    for (let i = 0; i < count; i++) {
      const key = parse(depth + 1);
      assert(
        typeof key === "number" && key === i,
        400,
        "migration",
        "Associative PHP arrays need explicit mapping.",
      );
      result.push(parse(depth + 1));
    }
    expect("}");
    return result;
  }
  const result = parse();
  assert(
    offset === bytes.length && Array.isArray(result),
    400,
    "migration",
    "Invalid serialized array.",
  );
  return result;
}
function months(value: unknown): number {
  const match = /^P(?:(\d+)Y)?(?:(\d+)M)?$/.exec(text(value));
  assert(match, 400, "migration", "Unsupported subscription interval.");
  const result = number(match[1]) * 12 + number(match[2]);
  assert(result > 0, 400, "migration", "Invalid interval.");
  return result;
}
function isoDate(value: unknown): string {
  return text(value).slice(0, 10);
}
export function mapLegacy(snapshot: Snapshot) {
  assert(snapshot.version === 1, 400, "migration", "Unknown snapshot version.");
  const tables = snapshot.tables;
  const rows = (table: string) => tables[table] ?? [];
  const indexes = new Map<string, Map<string, Record<string, unknown>>>();
  const find = (
    table: string,
    id: unknown,
  ): Record<string, unknown> | undefined => {
    if (!id) return;
    let map = indexes.get(table);
    if (!map) {
      map = new Map(rows(table).map((row) => [text(row.id), row]));
      indexes.set(table, map);
    }
    const value = map.get(text(id));
    assert(
      value,
      400,
      "migration",
      `Missing ${table} relationship ${text(id)}.`,
    );
    return value;
  };
  const media = (id: unknown): string => {
    const row = find("media", id);
    if (!row) return "";
    const created = isoDate(row.created_at);
    assert(
      /^\d{4}-\d{2}-\d{2}$/.test(created),
      400,
      "migration",
      "Media creation date is required.",
    );
    const [year, month, day] = created.split("-");
    const name = text(row.name);
    assert(
      name && !name.includes("/") && !name.includes("\\"),
      400,
      "migration",
      "Invalid media name.",
    );
    const url = new URL(
      `${year}/${day}/${month}/${encodeURIComponent(name)}`,
      snapshot.mediaBaseUrl.endsWith("/")
        ? snapshot.mediaBaseUrl
        : `${snapshot.mediaBaseUrl}/`,
    );
    assert(
      url.protocol === "https:" || url.hostname === "localhost",
      400,
      "migration",
      "Media base URL must use HTTPS.",
    );
    return url.toString();
  };
  const personas = rows("user").map((row) => {
    const profile = find("profile", row.profile_id);
    const persona: Persona = {
      id: text(row.id),
      email: text(row.email).trim().toLowerCase(),
      admin: legacyArray(row.roles).includes("ROLE_ADMIN"),
      profile: profile
        ? {
            firstname: text(profile.firstname),
            lastname: text(profile.lastname),
            cellphone: text(profile.cellphone),
            description: text(profile.description),
            preferredLanguage: text(profile.preferred_language, "fr_FR"),
            birthdate: isoDate(profile.birthdate),
            gender: text(profile.gender),
          }
        : null,
    };
    const passwordHash = text(row.password);
    assert(
      /^\$(argon2id|argon2i|2[aby])\$/.test(passwordHash),
      400,
      "migration",
      `Unsupported password hash for persona ${persona.id}.`,
    );
    return { persona, passwordHash, createdAt: text(row.created_at) };
  });
  const plans: Plan[] = rows("subscription").map((row) => ({
    id: text(row.id),
    name: text(row.name),
    amount: number(row.amount),
    months: months(row.validity_duration),
    active: true,
  }));
  const subscriptions: Subscription[] = rows("rental_subscription").map(
    (row) => {
      const rental = find("rental", row.rental_id)!;
      const plan = plans.find((plan) => plan.id === row.subscription_id);
      assert(plan, 400, "migration", "Missing subscription plan.");
      return {
        id: text(row.id),
        rentalId: text(row.rental_id),
        personaId: text(rental.owner_id),
        planId: plan.id,
        amount: number(row.amount),
        months: plan.months,
        status: row.is_consumed
          ? "consumed"
          : row.provider_charge_id
            ? "paid"
            : "pending",
        paymentIntentId: null,
        discountId: row.discount_id ? text(row.discount_id) : null,
        expiresAt: row.expires_at
          ? `${isoDate(row.expires_at)}T00:00:00.000Z`
          : null,
      };
    },
  );
  const discounts: Discount[] = rows("discount").map((row) => ({
    id: text(row.id),
    code: text(row.code),
    type: row.type === "%" ? "percent" : "fixed",
    amount: row.type === "%" ? number(row.amount) / 100 : number(row.amount),
    expiresAt: isoDate(row.expires_at),
    maxUses: 100000,
    uses: subscriptions.filter(
      (s) => s.discountId === row.id && s.status !== "pending",
    ).length,
    payeeId: row.payee_id ? text(row.payee_id) : null,
  }));
  const rentals: Rental[] = rows("rental").map((row) => {
    const configuration = rows("configuration").find(
      (c) => c.rental_id === row.id,
    );
    const description = find("description", row.description_id);
    const address = find("address", row.address_id);
    const town = find("town", address?.town_id);
    const postal =
      find("postal_code", town?.postal_code_id) ??
      find(
        "postal_code",
        rows("town_postal_code").find((t) => t.town_id === town?.id)
          ?.postal_code_id,
      );
    const geolocation = find("geolocation", row.geolocation_id);
    const coordinates = geolocation
      ? object(geolocation.coordinates)
      : undefined;
    const preferences = find("preferences", row.preferences_id);
    const tax = find("tax", row.tax_id);
    const conditions = find("condition", row.condition_id);
    const gallery = find("gallery", row.gallery_id);
    const photoIds = [
      gallery?.cover_id,
      ...rows("gallery_media")
        .filter((m) => m.gallery_id === gallery?.id)
        .map((m) => m.media_id),
    ].filter(Boolean);
    const active = subscriptions.find(
      (s) =>
        s.rentalId === row.id &&
        s.status === "consumed" &&
        s.expiresAt &&
        s.expiresAt > new Date().toISOString(),
    );
    const rental: Rental = {
      ...emptyRental(),
      id: text(row.id),
      ownerId: text(row.owner_id),
      slug: text(row.slug, text(row.id)),
      status:
        row.status === "published"
          ? "published"
          : row.status === "disabled"
            ? "disabled"
            : row.status === "expired"
              ? "expired"
              : "draft",
      version: 1,
      completedSteps: [],
      subscriptionExpiresAt: active?.expiresAt ?? null,
      title: text(description?.title),
      description: text(description?.description),
      type: text(find("rental_type", configuration?.type_id)?.label, "Maison"),
      peopleCount: number(configuration?.people_count, 1),
      bedrooms: rows("bedroom")
        .filter((b) => b.configuration_id === configuration?.id)
        .map((bedroom, index) => ({
          name: `Chambre ${index + 1}`,
          beds: rows("bedroom_bed")
            .filter((b) => b.bedroom_id === bedroom.id)
            .map((b) => ({
              type: text(find("bed", b.bed_id)?.label),
              count: number(b.count, 1),
            })),
        })),
      equipment: [
        ...rows("rental_furniture")
          .filter((f) => f.rental_id === row.id)
          .map((f) => text(find("furniture", f.furniture_id)?.name)),
        ...legacyArray(row.custom_furnitures).map((v) => text(v)),
      ],
      address: {
        street: [address?.address, address?.address2]
          .filter(Boolean)
          .join(", "),
        town: text(town?.name),
        postcode: text(postal?.code),
        country: "France",
      },
      latitude: coordinates ? number(coordinates.lat) : null,
      longitude: coordinates ? number(coordinates.lng) : null,
      photos: [...new Set(photoIds.map(media))],
      minLeadDays: number(
        /^P(\d+)D$/.exec(text(preferences?.accepted_last_booking, "P1D"))?.[1],
        1,
      ),
      maxLeadMonths: months(preferences?.max_time_before_booking ?? "P1Y"),
      arrivalTime: text(preferences?.begin_booking_at, "16:00").slice(0, 5),
      departureTime: text(preferences?.end_booking_at, "10:00").slice(0, 5),
      unavailable: rows("unavailability")
        .filter((p) => p.rental_id === row.id)
        .map((p) => ({ start: isoDate(p.start_at), end: isoDate(p.end_at) })),
      dailyRate: number(row.daily_rate),
      weeklyRate: number(row.weekly_rate),
      rates: rows("price")
        .filter((p) => p.rental_id === row.id)
        .map((p) => ({
          start: isoDate(p.range_start),
          end: isoDate(p.range_end),
          daily: number(p.daily_rate),
          weekly: number(p.weekly_rate),
        })),
      localTax: text(tax?.local_tax, "Incluse"),
      cleaningTax: number(tax?.cleaning_tax),
      linensTax: number(tax?.linens_tax),
      linens: rows("tax_linens")
        .filter((l) => l.tax_id === tax?.id)
        .map((l) => text(find("linens", l.linens_id)?.label)),
      animalsAccepted: Boolean(conditions?.animals_accepted),
      smokingAllowed: Boolean(conditions?.smoking_allowed),
      rules: legacyArray(conditions?.additionnal_rules).map((v) => text(v)),
    };
    rental.completedSteps = rentalSteps.filter((step) => {
      try {
        completeStep(rental, step);
        return true;
      } catch {
        return false;
      }
    });
    if (rental.status === "published")
      assert(
        rental.subscriptionExpiresAt,
        400,
        "migration",
        `Published rental ${rental.id} has no active entitlement; resolve it before cutover.`,
      );
    return rental;
  });
  const bookings: Booking[] = rows("booking").map((row) => {
    const rental = rentals.find((r) => r.id === row.rental_id);
    assert(rental, 400, "migration", "Missing booked rental.");
    const start = isoDate(row.start_at),
      end = isoDate(row.end_at);
    const nights = validateDates(start, end);
    assert(
      ["initialised", "booked", "confirmed", "done", "cancelled"].includes(
        text(row.status),
      ),
      400,
      "migration",
      "Unknown booking status.",
    );
    const prices = legacyArray(row.prices);
    const accommodation = prices.length
      ? prices.reduce<number>((sum, item) => {
          const price = object(item);
          return (
            sum + Math.round(number(price.price) * 100) * number(price.count)
          );
        }, 0)
      : quote(rental, start, end).accommodation;
    return {
      id: text(row.id),
      rentalId: rental.id,
      ownerId: rental.ownerId,
      personaId: text(row.booker_id),
      rentalTitle: rental.title,
      start,
      end,
      peopleCount: number(row.people_count),
      status: text(row.status) as Booking["status"],
      quote: {
        nights,
        accommodation,
        cleaning: 0,
        linens: 0,
        total: accommodation,
        currency: "EUR",
      },
      createdAt: text(row.created_at),
    };
  });
  const messages: Message[] = rows("message").map((row) => {
    const conversation = find("conversation", row.conversation_id);
    assert(
      conversation?.booking_id,
      400,
      "migration",
      `Conversation ${text(row.conversation_id)} has no booking; map it before cutover.`,
    );
    return {
      id: text(row.id),
      bookingId: text(conversation.booking_id),
      personaId: text(row.sender_id),
      body: text(row.message),
      createdAt: text(row.send_at ?? row.created_at),
    };
  });
  const notifications = rows("notification").map((row) => {
    const target = text(row.target_id);
    const rental = rentals.find((r) => r.id === target);
    const booking = bookings.find((b) => b.id === target);
    const personaId =
      rental?.ownerId ??
      booking?.ownerId ??
      personas.find((p) => p.persona.id === target)?.persona.id;
    assert(
      personaId,
      400,
      "migration",
      `Notification ${text(row.id)} has an unknown target; map it before cutover.`,
    );
    return {
      id: text(row.id),
      personaId,
      message: text(row.label),
      href: rental
        ? `/annonce/${rental.slug}`
        : booking
          ? `/mon-compte/reservation/${booking.id}`
          : "/mon-compte",
      read: Boolean(row.read_at),
      createdAt: text(row.created_at),
    };
  });
  const references = Object.entries({
    beds: "bed",
    equipment: "furniture",
    "rental-types": "rental_type",
    towns: "town",
    linens: "linens",
  }).flatMap(([kind, table]) =>
    rows(table).map((row) => ({
      kind,
      data: { id: text(row.id), name: text(row.name ?? row.label) },
    })),
  );
  return {
    personas,
    rentals,
    bookings,
    messages,
    notifications,
    subscriptions,
    plans,
    discounts,
    references,
    favorites: rows("favorite")
      .filter((f) => f.persona_id)
      .map((f) => ({
        personaId: text(f.persona_id),
        rentalId: text(f.rental_id),
      })),
  };
}
export async function importLegacy(sql: SQL, snapshot: Snapshot) {
  const checksum = createHash("sha256")
    .update(JSON.stringify(snapshot))
    .digest("hex");
  return sql.begin(async (db) => {
    await db`SELECT pg_advisory_xact_lock(720927)`;
    const previous =
      await db`SELECT checksum,counts FROM crozon_imports WHERE id='symfony-v1'`;
    if (previous[0]) {
      assert(
        previous[0].checksum === checksum,
        409,
        "migration",
        "A different snapshot was already imported. Restore an empty target for another rehearsal.",
      );
      return previous[0].counts;
    }
    const data = mapLegacy(snapshot);
    const existing = await db`SELECT count(*) AS n FROM crozon_personas`;
    assert(
      Number(existing[0].n) === 0,
      409,
      "migration",
      "Import requires an empty target application database.",
    );
    const timestamp = async (value: string) => {
      assert(value, 400, "migration", "A legacy timestamp is missing.");
      const rows =
        await db`SELECT ${value}::timestamp AT TIME ZONE ${snapshot.sourceTimeZone} AS value`;
      return rows[0].value as Date;
    };
    for (const { persona, passwordHash, createdAt } of data.personas) {
      const created = await timestamp(createdAt);
      await db`INSERT INTO auth_users(tenant_id,id,email,email_verified,created_at,updated_at) VALUES('crozon',${persona.id},${persona.email},true,${created},${created})`;
      await db`INSERT INTO auth_passwords(tenant_id,user_id,email,password_hash,updated_at) VALUES('crozon',${persona.id},${persona.email},${passwordHash},${created})`;
      await db`INSERT INTO crozon_personas(id,email,admin,profile) VALUES(${persona.id},${persona.email},${persona.admin},${persona.profile})`;
    }
    for (const rental of data.rentals)
      await db`INSERT INTO crozon_rentals(id,owner_id,slug,status,version,data) VALUES(${rental.id},${rental.ownerId},${rental.slug},${rental.status},1,${rental})`;
    for (const booking of data.bookings) {
      booking.createdAt = (await timestamp(booking.createdAt)).toISOString();
      await db`INSERT INTO crozon_bookings(id,rental_id,persona_id,start_date,end_date,status,data) VALUES(${booking.id},${booking.rentalId},${booking.personaId},${booking.start},${booking.end},${booking.status},${booking})`;
    }
    for (const message of data.messages) {
      message.createdAt = (await timestamp(message.createdAt)).toISOString();
      await db`INSERT INTO crozon_messages(id,booking_id,persona_id,created_at,data) VALUES(${message.id},${message.bookingId},${message.personaId},${message.createdAt},${message})`;
    }
    for (const notification of data.notifications) {
      notification.createdAt = (
        await timestamp(notification.createdAt)
      ).toISOString();
      await db`INSERT INTO crozon_notifications(id,persona_id,data,created_at) VALUES(${notification.id},${notification.personaId},${notification},${notification.createdAt})`;
    }
    for (const subscription of data.subscriptions)
      await db`INSERT INTO crozon_subscriptions(id,rental_id,persona_id,data) VALUES(${subscription.id},${subscription.rentalId},${subscription.personaId},${subscription})`;
    for (const favorite of data.favorites)
      await db`INSERT INTO crozon_favorites(persona_id,rental_id) VALUES(${favorite.personaId},${favorite.rentalId}) ON CONFLICT DO NOTHING`;
    for (const reference of [
      ...data.references,
      ...data.plans.map((data) => ({ kind: "plans", data })),
      ...data.discounts.map((data) => ({ kind: "discounts", data })),
    ])
      await db`INSERT INTO crozon_reference(kind,id,data) VALUES(${reference.kind},${reference.data.id},${reference.data})`;
    // Preserve every original row, including unsupported metadata and old identifiers. This is never exposed over HTTP.
    const counts: Record<string, number> = {};
    for (const [table, rows] of Object.entries(snapshot.tables)) {
      counts[table] = rows.length;
      for (const row of rows) {
        const key = text(
          row.id,
          createHash("sha256").update(JSON.stringify(row)).digest("hex"),
        );
        await db`INSERT INTO crozon_legacy_archive(source_table,source_key,data) VALUES(${table},${key},${row})`;
      }
    }
    const overlaps =
      await db`SELECT a.id FROM crozon_bookings a JOIN crozon_bookings b ON a.rental_id=b.rental_id AND a.id<b.id AND a.start_date<b.end_date AND b.start_date<a.end_date WHERE a.status IN ('booked','confirmed') AND b.status IN ('booked','confirmed') LIMIT 1`;
    assert(
      !overlaps.length,
      409,
      "migration",
      "Legacy bookings overlap. Resolve the conflict before cutover.",
    );
    await db`INSERT INTO crozon_imports(id,checksum,counts) VALUES('symfony-v1',${checksum},${counts})`;
    return counts;
  });
}
