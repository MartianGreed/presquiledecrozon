import type { SQL } from "bun";
import type {
  Page,
  Persona,
  Profile,
  Review,
} from "../../../../packages/contracts/src/models";
import { getBooking } from "../bookings/service";
import { assert } from "../errors";
import { getRental, visible } from "../rentals/service";

interface ReviewRow {
  id: string;
  rental_id: string;
  title: string;
  profile: Profile | null;
  rating: number;
  body: string;
  reply: string;
  created_at: Date;
  published: boolean;
}
function present(row: ReviewRow): Review {
  return {
    id: row.id,
    rentalId: row.rental_id,
    rentalTitle: row.title,
    author: {
      name:
        [row.profile?.firstname, row.profile?.lastname?.slice(0, 1)]
          .filter(Boolean)
          .join(" ") || "Voyageur",
      ...(row.profile?.avatarUrl ? { avatarUrl: row.profile.avatarUrl } : {}),
    },
    rating: row.rating,
    body: row.body,
    reply: row.reply,
    createdAt: row.created_at.toISOString(),
    published: row.published,
  };
}
export function reviews(sql: SQL) {
  return {
    async list(
      rentalId: string,
      page: number,
      actor?: Persona,
    ): Promise<Page<Review> & { average: number }> {
      const rental = await getRental(sql, rentalId);
      assert(
        visible(rental) || actor?.id === rental.ownerId || actor?.admin,
        404,
        "not_found",
        "Annonce introuvable.",
      );
      const rows =
        await sql`SELECT v.*,p.profile,r.data->>'title' AS title FROM crozon_reviews v JOIN crozon_personas p ON p.id=v.persona_id JOIN crozon_rentals r ON r.id=v.rental_id WHERE v.rental_id=${rental.id} AND v.published AND NOT p.disabled ORDER BY v.created_at DESC,v.id LIMIT 20 OFFSET ${(page - 1) * 20}`;
      const count =
        await sql`SELECT count(*) AS total,coalesce(avg(v.rating),0) AS average FROM crozon_reviews v JOIN crozon_personas p ON p.id=v.persona_id WHERE v.rental_id=${rental.id} AND v.published AND NOT p.disabled`;
      return {
        items: rows.map(present),
        page,
        total: Number(count[0].total),
        average: Number(count[0].average),
      };
    },
    async eligible(actor: Persona, rentalId: string) {
      const rows =
        await sql`SELECT b.id,b.data->>'start' AS start,b.data->>'end' AS end FROM crozon_bookings b LEFT JOIN crozon_reviews v ON v.booking_id=b.id WHERE b.persona_id=${actor.id} AND b.rental_id=${rentalId} AND b.status IN ('confirmed','done') AND b.end_date<${new Date().toISOString().slice(0, 10)}::date AND v.id IS NULL ORDER BY b.end_date DESC LIMIT 100`;
      return rows.map((row: { id: string; start: string; end: string }) => ({
        id: row.id,
        start: row.start,
        end: row.end,
      }));
    },
    async create(
      actor: Persona,
      bookingId: string,
      input: { rating: number; body: string },
    ) {
      return sql.begin(async (db) => {
        const booking = await getBooking(db, bookingId, actor, true);
        assert(
          booking.personaId === actor.id && booking.ownerId !== actor.id,
          403,
          "forbidden",
          "Seul le voyageur peut évaluer ce séjour.",
        );
        assert(
          ["confirmed", "done"].includes(booking.status) &&
            booking.end < new Date().toISOString().slice(0, 10),
          400,
          "review",
          "Vous pourrez laisser un avis après la fin de votre séjour.",
        );
        assert(input.body.trim(), 400, "review", "Votre avis est vide.");
        const rows =
          await db`INSERT INTO crozon_reviews(id,booking_id,rental_id,persona_id,rating,body) VALUES(${crypto.randomUUID()},${booking.id},${booking.rentalId},${actor.id},${input.rating},${input.body.trim()}) ON CONFLICT(booking_id) DO NOTHING RETURNING id`;
        assert(
          rows[0],
          409,
          "review",
          "Vous avez déjà laissé un avis pour ce séjour.",
        );
        return { id: rows[0].id as string };
      });
    },
    async reply(actor: Persona, id: string, body: string) {
      const rows =
        await sql`SELECT r.owner_id FROM crozon_reviews v JOIN crozon_rentals r ON r.id=v.rental_id WHERE v.id=${id}`;
      assert(rows[0], 404, "not_found", "Avis introuvable.");
      assert(
        rows[0].owner_id === actor.id,
        403,
        "forbidden",
        "Seul le propriétaire peut répondre.",
      );
      await sql`UPDATE crozon_reviews SET reply=${body.trim()} WHERE id=${id}`;
      return { ok: true };
    },
    async moderate(actor: Persona, id: string, published: boolean) {
      assert(
        actor.admin,
        403,
        "forbidden",
        "Accès réservé à l’administration.",
      );
      const rows =
        await sql`UPDATE crozon_reviews SET published=${published} WHERE id=${id} RETURNING id`;
      assert(rows[0], 404, "not_found", "Avis introuvable.");
      return { ok: true };
    },
    async administration(actor: Persona, page: number): Promise<Page<Review>> {
      assert(
        actor.admin,
        403,
        "forbidden",
        "Accès réservé à l’administration.",
      );
      const rows =
        await sql`SELECT v.*,p.profile,r.data->>'title' AS title FROM crozon_reviews v JOIN crozon_personas p ON p.id=v.persona_id JOIN crozon_rentals r ON r.id=v.rental_id ORDER BY v.created_at DESC,v.id LIMIT 20 OFFSET ${(page - 1) * 20}`;
      const count = await sql`SELECT count(*) AS total FROM crozon_reviews`;
      return { items: rows.map(present), page, total: Number(count[0].total) };
    },
  };
}
