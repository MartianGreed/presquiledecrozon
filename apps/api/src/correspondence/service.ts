import type { SQL } from "bun";
import type {
  Booking,
  Message,
  Notification,
  Page,
  Persona,
  Rental,
} from "../../../../packages/contracts/src/models";
import { getBooking } from "../bookings/service";
import { event } from "../database";
import { assert } from "../errors";
import { getRental, visible } from "../rentals/service";
export function correspondence(sql: SQL) {
  return {
    async favorites(actor: Persona): Promise<Rental[]> {
      const rows =
        await sql`SELECT r.data FROM crozon_favorites f JOIN crozon_rentals r ON r.id=f.rental_id WHERE f.persona_id=${actor.id} ORDER BY r.id LIMIT 500`;
      return rows
        .map((r: { data: Rental }) => r.data)
        .filter((rental: Rental) => visible(rental));
    },
    async favorite(
      actor: Persona,
      id: string,
      saved: boolean,
    ): Promise<{ saved: boolean }> {
      if (saved) {
        const rental = await getRental(sql, id);
        assert(visible(rental), 404, "not_found", "Annonce introuvable.");
        await sql`INSERT INTO crozon_favorites(persona_id,rental_id) VALUES(${actor.id},${id}) ON CONFLICT DO NOTHING`;
      } else
        await sql`DELETE FROM crozon_favorites WHERE persona_id=${actor.id} AND rental_id=${id}`;
      return { saved };
    },
    async conversations(actor: Persona, page: number): Promise<Page<Booking>> {
      const rows =
        await sql`SELECT data,count(*) OVER() AS total FROM crozon_bookings WHERE persona_id=${actor.id} OR data->>'ownerId'=${actor.id} ORDER BY data->>'createdAt' DESC,id LIMIT 25 OFFSET ${(page - 1) * 25}`;
      return {
        items: rows.map((r: { data: Booking }) => r.data),
        page,
        total: Number(rows[0]?.total ?? 0),
      };
    },
    async messages(
      actor: Persona,
      id: string,
      page: number,
    ): Promise<Page<Message>> {
      await getBooking(sql, id, actor);
      const rows =
        await sql`SELECT data,count(*) OVER() AS total FROM crozon_messages WHERE booking_id=${id} ORDER BY created_at,id LIMIT 50 OFFSET ${(page - 1) * 50}`;
      return {
        items: rows.map((r: { data: Message }) => r.data),
        page,
        total: Number(rows[0]?.total ?? 0),
      };
    },
    async send(actor: Persona, id: string, body: string): Promise<Message> {
      assert(body.trim(), 400, "message", "Le message est vide.");
      return sql.begin(async (db) => {
        const booking = await getBooking(db, id, actor);
        assert(
          actor.id === booking.ownerId || actor.id === booking.personaId,
          403,
          "forbidden",
          "Vous ne participez pas à cette conversation.",
        );
        const message: Message = {
          id: crypto.randomUUID(),
          bookingId: id,
          personaId: actor.id,
          body: body.trim(),
          createdAt: new Date().toISOString(),
        };
        await db`INSERT INTO crozon_messages(id,booking_id,persona_id,data) VALUES(${message.id},${id},${actor.id},${message})`;
        await event(
          db,
          actor.id === booking.ownerId ? booking.personaId : booking.ownerId,
          "Vous avez reçu un nouveau message.",
          `/mon-compte/messages?conversation=${id}`,
        );
        return message;
      });
    },
    async notifications(actor: Persona): Promise<Notification[]> {
      const rows =
        await sql`SELECT data FROM crozon_notifications WHERE persona_id=${actor.id} ORDER BY created_at DESC LIMIT 100`;
      return rows.map((r: { data: Notification }) => r.data);
    },
    async read(actor: Persona, id: string) {
      await sql`UPDATE crozon_notifications SET data=jsonb_set(data,'{read}','true') WHERE id=${id} AND persona_id=${actor.id}`;
      return { ok: true };
    },
  };
}
