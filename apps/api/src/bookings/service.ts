import { createHash } from "node:crypto";
import type { SQL } from "bun";
import type {
  Booking,
  Message,
  Page,
  Persona,
} from "../../../../packages/contracts/src/models";
import type { Connection } from "../database";
import { event } from "../database";
import { assert } from "../errors";
import {
  overlaps,
  quote,
  validateDates,
  validatePreferences,
} from "../rentals/domain";
import { getRental, visible } from "../rentals/service";
export async function getBooking(
  db: Connection,
  id: string,
  actor: Persona,
  lock = false,
): Promise<Booking> {
  const rows = lock
    ? await db`SELECT data FROM crozon_bookings WHERE id=${id} FOR UPDATE`
    : await db`SELECT data FROM crozon_bookings WHERE id=${id}`;
  const booking: Booking | undefined = rows[0]?.data;
  assert(booking, 404, "not_found", "Réservation introuvable.");
  assert(
    booking.personaId === actor.id ||
      booking.ownerId === actor.id ||
      actor.admin,
    403,
    "forbidden",
    "Vous ne participez pas à cette réservation.",
  );
  return booking;
}
interface RequestBooking {
  rentalId: string;
  start: string;
  end: string;
  peopleCount: number;
  message: string;
}
export function bookings(sql: SQL) {
  async function available(
    db: Connection,
    input: Omit<RequestBooking, "message">,
  ) {
    const rental = await getRental(db, input.rentalId, true);
    validateDates(input.start, input.end);
    assert(
      visible(rental),
      404,
      "not_found",
      "Cette annonce n’est pas disponible.",
    );
    assert(
      input.peopleCount <= rental.peopleCount,
      400,
      "capacity",
      "La capacité du logement est dépassée.",
    );
    validatePreferences(rental, input.start, new Date());
    assert(
      !rental.unavailable.some((p) => overlaps(p, input)),
      409,
      "unavailable",
      "Ce logement est indisponible pour ces dates.",
    );
    const conflicts =
      await db`SELECT id FROM crozon_bookings WHERE rental_id=${input.rentalId} AND status IN ('booked','confirmed') AND start_date<${input.end}::date AND end_date>${input.start}::date LIMIT 1`;
    assert(
      conflicts.length === 0,
      409,
      "unavailable",
      "Ce logement est déjà réservé pour ces dates.",
    );
    return rental;
  }
  return {
    async quote(input: Omit<RequestBooking, "message">) {
      return sql.begin(async (db) => {
        const rental = await available(db, input);
        return quote(rental, input.start, input.end);
      });
    },
    async request(
      actor: Persona,
      input: RequestBooking,
      key: string,
    ): Promise<Booking> {
      assert(
        actor.profile,
        400,
        "profile_required",
        "Complétez vos informations personnelles avant de réserver.",
      );
      assert(
        /^[A-Za-z0-9_-]{8,100}$/.test(key),
        400,
        "idempotency",
        "Une clé de requête est nécessaire.",
      );
      const fingerprint = createHash("sha256")
        .update(JSON.stringify(input))
        .digest("hex");
      return sql.begin(async (db) => {
        await db`SELECT pg_advisory_xact_lock(hashtextextended(${`${actor.id}:${key}`},0))`;
        const previous =
          await db`SELECT fingerprint,result FROM crozon_idempotency WHERE persona_id=${actor.id} AND key=${key}`;
        if (previous[0]) {
          assert(
            previous[0].fingerprint === fingerprint,
            409,
            "idempotency",
            "Cette clé a déjà été utilisée pour une autre demande.",
          );
          return previous[0].result as Booking;
        }
        const rental = await available(db, input);
        assert(
          rental.ownerId !== actor.id,
          403,
          "own_rental",
          "Vous ne pouvez pas réserver votre propre logement.",
        );
        const id = crypto.randomUUID();
        const createdAt = new Date().toISOString();
        const booking: Booking = {
          id,
          rentalId: rental.id,
          ownerId: rental.ownerId,
          personaId: actor.id,
          rentalTitle: rental.title,
          start: input.start,
          end: input.end,
          peopleCount: input.peopleCount,
          status: "booked",
          quote: quote(rental, input.start, input.end),
          createdAt,
        };
        await db`INSERT INTO crozon_bookings(id,rental_id,persona_id,start_date,end_date,status,data) VALUES(${id},${rental.id},${actor.id},${input.start},${input.end},'booked',${booking})`;
        const message: Message = {
          id: crypto.randomUUID(),
          bookingId: id,
          personaId: actor.id,
          body: input.message,
          createdAt,
        };
        await db`INSERT INTO crozon_messages(id,booking_id,persona_id,data) VALUES(${message.id},${id},${actor.id},${message})`;
        await event(
          db,
          rental.ownerId,
          "Vous avez reçu une demande de réservation.",
          `/mon-compte/reservation/${id}`,
        );
        await db`INSERT INTO crozon_idempotency(persona_id,key,fingerprint,result) VALUES(${actor.id},${key},${fingerprint},${booking})`;
        return booking;
      });
    },
    async submitInitial(
      actor: Persona,
      id: string,
      message: string,
    ): Promise<Booking> {
      return sql.begin(async (db) => {
        const booking = await getBooking(db, id, actor, true);
        assert(
          booking.personaId === actor.id,
          403,
          "forbidden",
          "Cette demande appartient à un autre voyageur.",
        );
        if (booking.status === "booked") return booking;
        assert(
          booking.status === "initialised",
          409,
          "status",
          "Cette demande a déjà été traitée.",
        );
        const rental = await available(db, {
          rentalId: booking.rentalId,
          start: booking.start,
          end: booking.end,
          peopleCount: booking.peopleCount,
        });
        assert(
          rental.ownerId !== actor.id,
          403,
          "own_rental",
          "Vous ne pouvez pas réserver votre propre logement.",
        );
        booking.status = "booked";
        booking.quote = quote(rental, booking.start, booking.end);
        await db`UPDATE crozon_bookings SET status='booked',data=${booking} WHERE id=${id}`;
        const first: Message = {
          id: crypto.randomUUID(),
          bookingId: id,
          personaId: actor.id,
          body: message,
          createdAt: new Date().toISOString(),
        };
        await db`INSERT INTO crozon_messages(id,booking_id,persona_id,data) VALUES(${first.id},${id},${actor.id},${first})`;
        await event(
          db,
          rental.ownerId,
          "Vous avez reçu une demande de réservation.",
          `/mon-compte/reservation/${id}`,
        );
        return booking;
      });
    },
    async list(
      actor: Persona,
      page: number,
      owner = false,
    ): Promise<Page<Booking>> {
      const rows =
        await sql`SELECT data,count(*) OVER() AS total FROM crozon_bookings WHERE ${owner ? sql`data->>'ownerId'=${actor.id}` : sql`persona_id=${actor.id}`} ORDER BY start_date DESC,id LIMIT 25 OFFSET ${(page - 1) * 25}`;
      return {
        items: rows.map((r: { data: Booking }) => r.data),
        page,
        total: Number(rows[0]?.total ?? 0),
      };
    },
    detail: (actor: Persona, id: string) => getBooking(sql, id, actor),
    async transition(
      actor: Persona,
      id: string,
      status: "confirmed" | "cancelled",
    ): Promise<Booking> {
      return sql.begin(async (db) => {
        const booking = await getBooking(db, id, actor, true);
        assert(
          booking.ownerId === actor.id || actor.admin,
          403,
          "forbidden",
          "Seul le propriétaire peut gérer cette réservation.",
        );
        if (booking.status === status) return booking;
        assert(
          booking.status === "booked" ||
            (status === "cancelled" && booking.status === "confirmed"),
          409,
          "status",
          "Cette réservation ne peut plus être modifiée.",
        );
        booking.status = status;
        await db`UPDATE crozon_bookings SET status=${status},data=${booking} WHERE id=${id}`;
        await event(
          db,
          booking.personaId,
          status === "confirmed"
            ? "Votre réservation est confirmée."
            : "Votre réservation est annulée.",
          `/mon-compte/reservation/${id}`,
        );
        return booking;
      });
    },
  };
}
