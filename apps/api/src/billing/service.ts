import type { SQL } from "bun";
import Stripe from "stripe";
import type {
  AccountSubscription,
  Discount,
  Page,
  Persona,
  Plan,
  Subscription,
} from "../../../../packages/contracts/src/models";
import type { AppConfig } from "../config";
import { secret } from "../config";
import { event } from "../database";
import { assert } from "../errors";
import { getRental, ownRental } from "../rentals/service";
export function discounted(amount: number, discount?: Discount): number {
  if (!discount) return amount;
  assert(
    discount.type !== "percent" || discount.amount <= 100,
    400,
    "discount",
    "Remise invalide.",
  );
  return Math.max(
    0,
    amount -
      (discount.type === "percent"
        ? Math.round((amount * discount.amount) / 100)
        : discount.amount),
  );
}
export function billing(sql: SQL, config: AppConfig) {
  const key = secret(config.stripeSecret);
  const stripe = key
    ? new Stripe(key, { timeout: 10000, maxNetworkRetries: 1 })
    : undefined;
  return {
    async plans(): Promise<Plan[]> {
      const rows =
        await sql`SELECT data FROM crozon_reference WHERE kind='plans' AND data->>'active'='true' ORDER BY id`;
      return rows.map((r: { data: Plan }) => r.data);
    },
    async accountSubscriptions(
      actor: Persona,
      page: number,
    ): Promise<Page<AccountSubscription>> {
      const rows =
        await sql`SELECT s.data, r.data->>'title' AS title FROM crozon_subscriptions s JOIN crozon_rentals r ON r.id=s.rental_id WHERE s.persona_id=${actor.id} ORDER BY s.id DESC LIMIT 25 OFFSET ${(page - 1) * 25}`;
      const count =
        await sql`SELECT count(*) AS total FROM crozon_subscriptions WHERE persona_id=${actor.id}`;
      return {
        items: rows.map((row: { data: Subscription; title: string }) => ({
          ...row.data,
          rentalTitle: row.title,
        })),
        page,
        total: Number(count[0].total),
      };
    },
    async subscriptions(
      actor: Persona,
      rentalId: string,
    ): Promise<Subscription[]> {
      ownRental(actor, await getRental(sql, rentalId));
      const rows =
        await sql`SELECT data FROM crozon_subscriptions WHERE rental_id=${rentalId} ORDER BY id`;
      return rows.map((r: { data: Subscription }) => r.data);
    },
    async checkout(
      actor: Persona,
      input: { rentalId: string; planId: string; discountCode: string },
    ): Promise<{ url: string }> {
      assert(
        stripe,
        503,
        "payments_unavailable",
        "Le paiement est temporairement indisponible.",
      );
      const sub = await sql.begin(async (db) => {
        const rental = await getRental(db, input.rentalId, true);
        ownRental(actor, rental);
        const plans =
          await db`SELECT data FROM crozon_reference WHERE kind='plans' AND id=${input.planId}`;
        const plan: Plan | undefined = plans[0]?.data;
        assert(plan?.active, 400, "plan", "Abonnement indisponible.");
        const pending =
          await db`SELECT data FROM crozon_subscriptions WHERE rental_id=${rental.id} AND data->>'status'='pending' ORDER BY id LIMIT 1 FOR UPDATE`;
        if (pending[0]) {
          const existing: Subscription = pending[0].data;
          assert(
            existing.planId === plan.id,
            409,
            "checkout_pending",
            "Un paiement est déjà en cours pour cette annonce.",
          );
          const discounts = existing.discountId
            ? await db`SELECT data FROM crozon_reference WHERE kind='discounts' AND id=${existing.discountId}`
            : [];
          assert(
            (discounts[0]?.data.code ?? "").toLowerCase() ===
              input.discountCode.toLowerCase(),
            409,
            "checkout_pending",
            "Un paiement avec une autre remise est déjà en cours.",
          );
          return existing;
        }
        let discount: Discount | undefined;
        if (input.discountCode) {
          const rows =
            await db`SELECT data FROM crozon_reference WHERE kind='discounts' AND lower(data->>'code')=lower(${input.discountCode}) FOR UPDATE`;
          discount = rows[0]?.data;
          assert(
            discount &&
              discount.expiresAt >= new Date().toISOString().slice(0, 10),
            400,
            "discount",
            "Code de réduction invalide ou expiré.",
          );
          assert(
            !discount.payeeId || discount.payeeId === actor.id,
            400,
            "discount",
            "Ce code est réservé à un autre compte.",
          );
          const reserved =
            await db`SELECT count(*) AS total FROM crozon_subscriptions WHERE data->>'discountId'=${discount.id} AND data->>'status'='pending'`;
          assert(
            discount.uses + Number(reserved[0].total) < discount.maxUses,
            400,
            "discount",
            "Ce code a atteint sa limite d’utilisation.",
          );
        }
        const subscription: Subscription = {
          id: crypto.randomUUID(),
          rentalId: rental.id,
          personaId: actor.id,
          planId: plan.id,
          amount: discounted(plan.amount, discount),
          months: plan.months,
          status: "pending",
          paymentIntentId: null,
          discountId: discount?.id ?? null,
          expiresAt: null,
        };
        await db`INSERT INTO crozon_subscriptions(id,rental_id,persona_id,data) VALUES(${subscription.id},${rental.id},${actor.id},${subscription})`;
        return subscription;
      });
      const session = await stripe.checkout.sessions.create(
        {
          mode: "payment",
          client_reference_id: sub.id,
          metadata: { subscriptionId: sub.id, rentalId: sub.rentalId },
          line_items: [
            {
              price_data: {
                currency: "eur",
                unit_amount: sub.amount,
                product_data: { name: "Publication de votre annonce" },
              },
              quantity: 1,
            },
          ],
          success_url: new URL(
            `/abonnement/confirm/${sub.rentalId}`,
            config.origin,
          ).toString(),
          cancel_url: new URL(
            `/abonnement?rental_id=${sub.rentalId}`,
            config.origin,
          ).toString(),
        },
        { idempotencyKey: `crozon-subscription-${sub.id}` },
      );
      assert(session.url, 502, "payment", "Impossible d’ouvrir le paiement.");
      await sql.begin(async (db) => {
        const rows =
          await db`SELECT data FROM crozon_subscriptions WHERE id=${sub.id} FOR UPDATE`;
        const current: Subscription = rows[0].data;
        assert(
          !current.paymentIntentId || current.paymentIntentId === session.id,
          409,
          "payment",
          "Le paiement a changé.",
        );
        current.paymentIntentId = session.id;
        await db`UPDATE crozon_subscriptions SET payment_intent_id=${session.id},data=${current} WHERE id=${sub.id}`;
      });
      return { url: session.url };
    },
    async webhook(
      body: string,
      signature: string,
    ): Promise<{ received: true }> {
      const webhookKey = secret(config.stripeWebhookSecret);
      assert(
        stripe && webhookKey,
        503,
        "payments_unavailable",
        "Paiement indisponible.",
      );
      let paymentEvent: Stripe.Event;
      try {
        paymentEvent = await stripe.webhooks.constructEventAsync(
          body,
          signature,
          webhookKey,
        );
      } catch {
        throw new Error("invalid-stripe-signature");
      }
      if (
        ![
          "checkout.session.completed",
          "checkout.session.async_payment_succeeded",
          "checkout.session.expired",
        ].includes(paymentEvent.type)
      )
        return { received: true };
      const session = paymentEvent.data.object as Stripe.Checkout.Session;
      await sql.begin(async (db) => {
        const inserted =
          await db`INSERT INTO crozon_payment_events(id) VALUES(${paymentEvent.id}) ON CONFLICT DO NOTHING RETURNING id`;
        if (!inserted.length) return;
        const id = session.metadata?.subscriptionId;
        assert(id, 400, "payment", "Référence de paiement absente.");
        const rows =
          await db`SELECT data FROM crozon_subscriptions WHERE id=${id} FOR UPDATE`;
        const sub: Subscription | undefined = rows[0]?.data;
        assert(
          sub &&
            session.client_reference_id === sub.id &&
            session.metadata?.rentalId === sub.rentalId &&
            session.amount_total === sub.amount &&
            session.currency === "eur" &&
            (!sub.paymentIntentId || sub.paymentIntentId === session.id),
          400,
          "payment",
          "Le paiement ne correspond pas à l’abonnement.",
        );
        if (paymentEvent.type === "checkout.session.expired") {
          if (sub.status === "pending")
            await db`DELETE FROM crozon_subscriptions WHERE id=${id}`;
          return;
        }
        if (
          session.payment_status !== "paid" &&
          !(
            sub.amount === 0 && session.payment_status === "no_payment_required"
          )
        )
          return;
        if (sub.status !== "pending") return;
        sub.status = "paid";
        sub.paymentIntentId = session.id;
        await db`UPDATE crozon_subscriptions SET payment_intent_id=${session.id},data=${sub} WHERE id=${id}`;
        if (sub.discountId)
          await db`UPDATE crozon_reference SET data=jsonb_set(data,'{uses}',to_jsonb((data->>'uses')::int+1)) WHERE kind='discounts' AND id=${sub.discountId}`;
        await event(
          db,
          sub.personaId,
          "Votre abonnement est payé. Vous pouvez publier votre annonce.",
          `/mon-compte/annonces`,
        );
      });
      return { received: true };
    },
  };
}
