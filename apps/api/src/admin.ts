import type { SQL } from "bun";
import type { Persona } from "../../../packages/contracts/src/models";
import { assert } from "./errors";
export const referenceKinds = [
  "plans",
  "discounts",
  "beds",
  "equipment",
  "rental-types",
  "towns",
  "linens",
] as const;
export function admin(sql: SQL) {
  function requireAdmin(actor: Persona) {
    assert(actor.admin, 403, "forbidden", "Accès réservé à l’administration.");
  }
  return {
    async list(actor: Persona, kind: string, page: number) {
      requireAdmin(actor);
      if (referenceKinds.includes(kind as (typeof referenceKinds)[number])) {
        const rows =
          await sql`SELECT data FROM crozon_reference WHERE kind=${kind} ORDER BY id LIMIT 100 OFFSET ${(page - 1) * 100}`;
        return rows.map((r: { data: unknown }) => r.data);
      }
      if (kind === "personas")
        return sql`SELECT id,email,admin,disabled,profile FROM crozon_personas ORDER BY id LIMIT 100 OFFSET ${(page - 1) * 100}`;
      const table = {
        rentals: "crozon_rentals",
        bookings: "crozon_bookings",
        subscriptions: "crozon_subscriptions",
        notifications: "crozon_notifications",
      }[kind];
      assert(table, 404, "not_found", "Section introuvable.");
      const rows =
        await sql`SELECT data FROM ${sql(table)} ORDER BY id LIMIT 100 OFFSET ${(page - 1) * 100}`;
      return rows.map((r: { data: unknown }) => r.data);
    },
    async reference(actor: Persona, kind: string, input: { id: string }) {
      requireAdmin(actor);
      assert(
        referenceKinds.includes(kind as (typeof referenceKinds)[number]),
        404,
        "not_found",
        "Section introuvable.",
      );
      await sql`INSERT INTO crozon_reference(kind,id,data) VALUES(${kind},${input.id},${input}) ON CONFLICT(kind,id) DO UPDATE SET data=EXCLUDED.data`;
      return input;
    },
    async persona(actor: Persona, id: string, disabled: boolean) {
      requireAdmin(actor);
      assert(
        id !== actor.id,
        400,
        "self",
        "Vous ne pouvez pas désactiver votre propre compte.",
      );
      const rows =
        await sql`UPDATE crozon_personas SET disabled=${disabled} WHERE id=${id} RETURNING id`;
      assert(rows[0], 404, "not_found", "Compte introuvable.");
      return { ok: true };
    },
    async removeReference(actor: Persona, kind: string, id: string) {
      requireAdmin(actor);
      assert(
        referenceKinds.includes(kind as (typeof referenceKinds)[number]),
        404,
        "not_found",
        "Section introuvable.",
      );
      if (kind === "plans" || kind === "discounts") {
        const used =
          await sql`SELECT id FROM crozon_subscriptions WHERE data->>'planId'=${id} OR data->>'discountId'=${id} LIMIT 1`;
        assert(
          !used.length,
          409,
          "in_use",
          "Cette référence est utilisée par un abonnement.",
        );
      }
      await sql`DELETE FROM crozon_reference WHERE kind=${kind} AND id=${id}`;
      return { ok: true };
    },
  };
}
