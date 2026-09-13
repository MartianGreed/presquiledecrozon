import type { SQL } from "bun";
import type {
  Conversation,
  Message,
  Notification,
  Page,
  Persona,
  Rental,
} from "../../../../packages/contracts/src/models";
import { getBooking } from "../bookings/service";
import { type Connection, event } from "../database";
import { assert } from "../errors";
import { getRental, visible } from "../rentals/service";

async function membership(db: Connection, actor: Persona, id: string) {
  const rows =
    await db`SELECT persona_id,owner_id FROM crozon_direct_conversations WHERE id=${id}`;
  if (rows[0]) {
    assert(
      [rows[0].persona_id, rows[0].owner_id].includes(actor.id),
      403,
      "forbidden",
      "Vous ne participez pas à cette conversation.",
    );
    return {
      personaId: rows[0].persona_id as string,
      ownerId: rows[0].owner_id as string,
      table: "crozon_direct_messages",
    };
  }
  const booking = await getBooking(db, id, actor);
  return {
    personaId: booking.personaId,
    ownerId: booking.ownerId,
    table: "crozon_messages",
  };
}
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
    async contact(actor: Persona, rentalId: string) {
      const rental = await getRental(sql, rentalId);
      assert(visible(rental), 404, "not_found", "Annonce introuvable.");
      assert(
        actor.id !== rental.ownerId,
        403,
        "forbidden",
        "Vous êtes le propriétaire de cette annonce.",
      );
      assert(
        actor.profile,
        400,
        "profile_required",
        "Complétez votre profil avant de contacter un propriétaire.",
      );
      const rows =
        await sql`INSERT INTO crozon_direct_conversations(id,rental_id,persona_id,owner_id) VALUES(${crypto.randomUUID()},${rental.id},${actor.id},${rental.ownerId}) ON CONFLICT(rental_id,persona_id) DO UPDATE SET rental_id=excluded.rental_id RETURNING id`;
      return { id: rows[0].id as string };
    },
    async conversations(
      actor: Persona,
      page: number,
      search = "",
    ): Promise<Page<Conversation>> {
      const rows =
        await sql`SELECT c.*,p.profile,count(*) OVER() AS total FROM crozon_conversation_index c JOIN crozon_personas p ON p.id=CASE WHEN c.persona_id=${actor.id} THEN c.owner_id ELSE c.persona_id END WHERE (c.persona_id=${actor.id} OR c.owner_id=${actor.id}) AND concat_ws(' ',c.title,p.profile->>'firstname',p.profile->>'lastname') ILIKE ${`%${search}%`} ORDER BY c.created_at DESC,c.id LIMIT 25 OFFSET ${(page - 1) * 25}`;
      return {
        items: rows.map(
          (r: {
            id: string;
            rental_id: string;
            title: string;
            start_date: string | null;
            end_date: string | null;
            kind: "booking" | "direct";
            profile: {
              firstname?: string;
              lastname?: string;
              avatarUrl?: string;
            } | null;
          }) => ({
            id: r.id,
            rentalId: r.rental_id,
            rentalTitle: r.title,
            start: r.start_date,
            end: r.end_date,
            kind: r.kind,
            counterpart: {
              name:
                [r.profile?.firstname, r.profile?.lastname]
                  .filter(Boolean)
                  .join(" ") || "Votre interlocuteur",
              ...(r.profile?.avatarUrl
                ? { avatarUrl: r.profile.avatarUrl }
                : {}),
            },
          }),
        ),
        page,
        total: Number(rows[0]?.total ?? 0),
      };
    },
    async messages(
      actor: Persona,
      id: string,
      page: number,
    ): Promise<Page<Message>> {
      const conversation = await membership(sql, actor, id);
      const rows =
        await sql`SELECT data,count(*) OVER() AS total FROM ${sql(conversation.table)} WHERE booking_id=${id} ORDER BY created_at,id LIMIT 50 OFFSET ${(page - 1) * 50}`;
      return {
        items: rows.map((r: { data: Message }) => r.data),
        page,
        total: Number(rows[0]?.total ?? 0),
      };
    },
    async send(actor: Persona, id: string, body: string): Promise<Message> {
      assert(body.trim(), 400, "message", "Le message est vide.");
      return sql.begin(async (db) => {
        const booking = await membership(db, actor, id);
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
        await db`INSERT INTO ${db(booking.table)}(id,booking_id,persona_id,data) VALUES(${message.id},${id},${actor.id},${message})`;
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
