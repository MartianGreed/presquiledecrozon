import type { SQL } from "bun";
import {
  emptyRental,
  type Page,
  type Persona,
  type Rental,
  type RentalInput,
  type RentalStep,
  rentalSteps,
  type Subscription,
} from "../../../../packages/contracts/src/models";
import type { Connection } from "../database";
import { event } from "../database";
import { assert } from "../errors";
import { completeStep } from "./domain";
export async function getRental(
  db: Connection,
  id: string,
  lock = false,
): Promise<Rental> {
  const rows = lock
    ? await db`SELECT data FROM crozon_rentals WHERE id=${id} FOR UPDATE`
    : await db`SELECT data FROM crozon_rentals WHERE id=${id}`;
  assert(rows[0], 404, "not_found", "Annonce introuvable.");
  return rows[0].data;
}
export function ownRental(actor: Persona, rental: Rental): void {
  assert(
    rental.ownerId === actor.id || actor.admin,
    403,
    "forbidden",
    "Cette annonce appartient à un autre propriétaire.",
  );
}
export function visible(rental: Rental, now = new Date()): boolean {
  return (
    rental.status === "published" &&
    rental.subscriptionExpiresAt !== null &&
    rental.subscriptionExpiresAt > now.toISOString()
  );
}
export async function storeRental(
  db: Connection,
  rental: Rental,
): Promise<void> {
  await db`UPDATE crozon_rentals SET data=${rental},version=${rental.version},status=${rental.status},slug=${rental.slug} WHERE id=${rental.id}`;
}
export function rentals(sql: SQL) {
  return {
    async list(
      page: number,
      search: string,
      actor?: Persona,
    ): Promise<Page<Rental>> {
      const offset = (page - 1) * 25;
      const condition = actor
        ? sql`owner_id=${actor.id}`
        : sql`status='published' AND data->>'subscriptionExpiresAt'>${new Date().toISOString()}`;
      const term = `%${search.replace(/[\\%_]/g, "\\$&")}%`;
      const rows =
        await sql`SELECT data,count(*) OVER() AS total FROM crozon_rentals WHERE ${condition} AND (data->>'title' ILIKE ${term} OR data->'address'->>'town' ILIKE ${term}) ORDER BY id LIMIT 25 OFFSET ${offset}`;
      return {
        items: rows.map((r: { data: Rental }) => r.data),
        page,
        total: Number(rows[0]?.total ?? 0),
      };
    },
    async detail(slug: string, actor?: Persona): Promise<Rental> {
      const rows =
        await sql`SELECT data FROM crozon_rentals WHERE slug=${slug} OR id=${slug}`;
      const rental: Rental | undefined = rows[0]?.data;
      assert(
        rental &&
          (visible(rental) ||
            (actor !== undefined && rental.ownerId === actor.id) ||
            actor?.admin),
        404,
        "not_found",
        "Annonce introuvable.",
      );
      return rental;
    },
    async create(actor: Persona): Promise<Rental> {
      assert(
        actor.profile,
        400,
        "profile_required",
        "Veuillez renseigner vos informations personnelles.",
      );
      const id = crypto.randomUUID();
      const rental: Rental = {
        ...emptyRental(),
        id,
        ownerId: actor.id,
        slug: id,
        status: "draft",
        version: 1,
        completedSteps: [],
        subscriptionExpiresAt: null,
      };
      await sql`INSERT INTO crozon_rentals(id,owner_id,slug,status,version,data) VALUES(${id},${actor.id},${id},'draft',1,${rental})`;
      return rental;
    },
    async save(
      actor: Persona,
      id: string,
      version: number,
      step: RentalStep,
      input: RentalInput,
    ): Promise<Rental> {
      return sql.begin(async (db) => {
        const old = await getRental(db, id, true);
        ownRental(actor, old);
        assert(
          old.version === version,
          409,
          "conflict",
          "L’annonce a changé. Rechargez avant de réessayer.",
        );
        completeStep(input, step);
        for (const photo of input.photos) {
          if (old.photos.includes(photo)) continue;
          const own =
            await db`SELECT id FROM crozon_uploads WHERE persona_id=${actor.id} AND path=${photo}`;
          assert(own[0], 400, "photo", "Cette photo ne vous appartient pas.");
        }
        const completedSteps = [...new Set([...old.completedSteps, step])];
        // Changed fields may invalidate previously completed sections.
        const validSteps = completedSteps.filter((s) => {
          try {
            completeStep(input, s);
            return true;
          } catch {
            return false;
          }
        });
        const slug =
          old.slug === old.id
            ? `${
                input.title
                  .normalize("NFD")
                  .replace(/[\u0300-\u036f]/g, "")
                  .toLowerCase()
                  .replace(/[^a-z0-9]+/g, "-")
                  .replace(/^-|-$/g, "")
                  .slice(0, 70) || "logement"
              }-${id}`
            : old.slug;
        const rental: Rental = {
          ...old,
          ...input,
          slug,
          completedSteps: validSteps,
          version: version + 1,
        };
        if (
          validSteps.length < rentalSteps.length &&
          rental.status === "published"
        )
          rental.status = "draft";
        await storeRental(db, rental);
        return rental;
      });
    },
    async publish(
      actor: Persona,
      id: string,
      version: number,
      published: boolean,
    ): Promise<Rental> {
      return sql.begin(async (db) => {
        const rental = await getRental(db, id, true);
        ownRental(actor, rental);
        assert(
          rental.version === version,
          409,
          "conflict",
          "L’annonce a changé. Rechargez avant de réessayer.",
        );
        if (published) {
          for (const step of rentalSteps) {
            assert(
              rental.completedSteps.includes(step),
              400,
              "incomplete",
              "Terminez toutes les étapes avant de publier.",
            );
            completeStep(rental, step);
          }
          if (
            !rental.subscriptionExpiresAt ||
            rental.subscriptionExpiresAt <= new Date().toISOString()
          ) {
            const paid =
              await db`SELECT id,data FROM crozon_subscriptions WHERE rental_id=${id} AND data->>'status'='paid' ORDER BY id LIMIT 1 FOR UPDATE`;
            assert(
              paid[0],
              402,
              "subscription_required",
              "Un abonnement payé est nécessaire pour publier.",
            );
            const subscription: Subscription = paid[0].data;
            const expiration = new Date();
            expiration.setUTCMonth(
              expiration.getUTCMonth() + subscription.months,
            );
            subscription.status = "consumed";
            subscription.expiresAt = expiration.toISOString();
            rental.subscriptionExpiresAt = subscription.expiresAt;
            await db`UPDATE crozon_subscriptions SET data=${subscription} WHERE id=${subscription.id}`;
          }
          rental.status = "published";
          await event(
            db,
            actor.id,
            "Votre annonce est publiée.",
            `/annonce/${rental.slug}`,
          );
        } else rental.status = "disabled";
        rental.version++;
        await storeRental(db, rental);
        return rental;
      });
    },
  };
}
