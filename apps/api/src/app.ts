import { join } from "node:path";
import type { OAuthHttpClient } from "@structure-ai/auth";
import type { SQL } from "bun";
import { Schema } from "effect";
import { admin, referenceKinds } from "./admin";
import { billing } from "./billing/service";
import { bookings } from "./bookings/service";
import { type AppConfig, secret } from "./config";
import { ContentWriteSchema, ProposalSchema } from "./content/schema";
import { content } from "./content/service";
import { reviews } from "./correspondence/reviews";
import { correspondence } from "./correspondence/service";
import { assert, invalid, Problem } from "./errors";
import { identity, rateLimit } from "./identity/service";
import { readBounded, upload } from "./media";
import { rentalFilters } from "./rentals/search";
import { rentals } from "./rentals/service";
import * as S from "./schemas";

type Mutable<T> = T extends readonly (infer U)[]
  ? Mutable<U>[]
  : T extends object
    ? { -readonly [K in keyof T]: Mutable<T[K]> }
    : T;
async function body<A, I>(
  request: Request,
  schema: Schema.Schema<A, I>,
): Promise<Mutable<A>> {
  assert(
    request.headers.get("content-type")?.split(";")[0] === "application/json",
    415,
    "media_type",
    "Envoyez des données JSON.",
  );
  try {
    const value = JSON.parse(
      new TextDecoder().decode(await readBounded(request, 256 * 1024)),
    );
    return Schema.decodeUnknownSync(schema, { onExcessProperty: "error" })(
      value,
    ) as Mutable<A>;
  } catch (error) {
    if (error instanceof Problem) throw error;
    throw invalid("Les informations transmises sont invalides.");
  }
}
function page(url: URL): number {
  const value = Number(url.searchParams.get("page") ?? 1);
  assert(
    Number.isInteger(value) && value >= 1 && value <= 10000,
    400,
    "page",
    "Page invalide.",
  );
  return value;
}
interface Context {
  request: Request;
  url: URL;
  params: Record<string, string>;
}
type Handler = (context: Context) => Promise<unknown>;
export async function application(
  sql: SQL,
  config: AppConfig,
  oauthHttpClient?: OAuthHttpClient,
) {
  const accounts = await identity(sql, config, oauthHttpClient);
  const catalog = rentals(sql);
  const reservations = bookings(sql);
  const publishing = content(sql);
  const feedback = reviews(sql);
  const messages = correspondence(sql);
  const payments = billing(sql, config);
  const administration = admin(sql);
  const routes: Array<{
    method: string;
    path: string;
    regex: RegExp;
    names: string[];
    handler: Handler;
  }> = [];
  const route = (method: string, path: string, handler: Handler) => {
    const names: string[] = [];
    const pattern = path.replace(/:([A-Za-z]+)/g, (_, name: string) => {
      names.push(name);
      return "([^/]+)";
    });
    routes.push({
      method,
      path,
      regex: new RegExp(`^${pattern}$`),
      names,
      handler,
    });
  };
  route("GET", "/api/me/subscriptions", async ({ request, url }) =>
    payments.accountSubscriptions(await accounts.persona(request), page(url)),
  );
  route("GET", "/api/sign-in/providers", async () => accounts.providers);
  route("GET", "/api/me/preferences", async ({ request }) =>
    accounts.preferences(await accounts.persona(request)),
  );
  route("PUT", "/api/me/preferences", async ({ request }) =>
    accounts.savePreferences(
      await accounts.persona(request),
      await body(request, S.PreferencesSchema),
    ),
  );
  route("POST", "/api/me/avatar", async ({ request }) => {
    const actor = await accounts.persona(request);
    assert(
      actor.profile,
      400,
      "profile_required",
      "Enregistrez vos informations avant d’ajouter une photo.",
    );
    const image = await upload(sql, actor, request, config.uploadDirectory);
    return accounts.avatar(actor, image.path);
  });
  route("DELETE", "/api/me/avatar", async ({ request }) =>
    accounts.avatar(await accounts.persona(request), null),
  );
  route("GET", "/api/me", async ({ request }) => accounts.persona(request));
  route("GET", "/api/me/security", async ({ request }) =>
    accounts.security(await accounts.persona(request)),
  );
  route("POST", "/api/me/contact-email", async ({ request }) =>
    accounts.requestContactEmail(
      await accounts.persona(request),
      (
        await body(
          request,
          Schema.Struct({
            email: Schema.String.pipe(
              Schema.maxLength(254),
              Schema.pattern(/^[^\s@]+@[^\s@]+\.[^\s@]+$/),
            ),
          }),
        )
      ).email,
    ),
  );
  route("POST", "/api/me/contact-email/verify", async ({ request }) =>
    accounts.verifyContactEmail(
      await accounts.persona(request),
      (
        await body(
          request,
          Schema.Struct({
            token: Schema.String.pipe(
              Schema.minLength(1),
              Schema.maxLength(200),
            ),
          }),
        )
      ).token,
    ),
  );
  route("GET", "/api/me/passkeys", ({ request }) => accounts.passkeys(request));
  route("DELETE", "/api/me/passkeys/:id", ({ request, params }) =>
    accounts.removePasskey(request, params.id!),
  );
  route("PUT", "/api/me", async ({ request }) =>
    accounts.saveProfile(
      await accounts.persona(request),
      await body(request, S.ProfileSchema),
    ),
  );
  route("GET", "/api/rentals", async ({ url }) =>
    catalog.list(
      page(url),
      (url.searchParams.get("q") ?? "").slice(0, 100),
      undefined,
      rentalFilters(url.searchParams),
    ),
  );
  route("GET", "/api/my/rentals", async ({ url, request }) =>
    catalog.list(page(url), "", await accounts.persona(request)),
  );
  route("POST", "/api/rentals", async ({ request }) =>
    catalog.create(await accounts.persona(request)),
  );
  route("GET", "/api/rentals/:id", async ({ params, request }) =>
    catalog.detail(
      params.id!,
      request.headers.has("cookie")
        ? await accounts.persona(request).catch((error: unknown) => {
            if (error instanceof Problem && error.status === 401)
              return undefined;
            throw error;
          })
        : undefined,
    ),
  );
  route("PUT", "/api/rentals/:id", async ({ params, request }) => {
    const actor = await accounts.persona(request);
    const input = await body(request, S.SaveRental);
    return catalog.save(
      actor,
      params.id!,
      input.version,
      input.step,
      input.rental,
    );
  });
  route("POST", "/api/rentals/:id/publish", async ({ params, request }) => {
    const actor = await accounts.persona(request);
    const input = await body(request, S.PublishRequest);
    return catalog.publish(actor, params.id!, input.version, input.published);
  });
  route("POST", "/api/media", async ({ request }) =>
    upload(
      sql,
      await accounts.persona(request),
      request,
      config.uploadDirectory,
    ),
  );
  route("POST", "/api/geocode", async ({ request }) => {
    const actor = await accounts.persona(request);
    await rateLimit(sql, `geocode:${actor.id}`, 50);
    const input = await body(
      request,
      Schema.Struct({
        address: Schema.String.pipe(Schema.minLength(5), Schema.maxLength(500)),
      }),
    );
    const key = secret(config.googleMapsKey);
    assert(
      key,
      503,
      "geocode_unavailable",
      "Saisissez les coordonnées du logement pour continuer.",
    );
    const url = new URL("https://maps.googleapis.com/maps/api/geocode/json");
    url.searchParams.set("address", input.address);
    url.searchParams.set("key", key);
    const response = await fetch(url, { signal: AbortSignal.timeout(8000) });
    assert(
      response.ok,
      502,
      "geocode",
      "Le service de localisation est indisponible.",
    );
    const data = (await response.json()) as {
      status: string;
      results: Array<{ geometry: { location: { lat: number; lng: number } } }>;
    };
    assert(
      data.status === "OK" && data.results[0],
      400,
      "address",
      "Adresse introuvable.",
    );
    return data.results[0].geometry.location;
  });
  route("POST", "/api/quotes", async ({ request }) =>
    reservations.quote(await body(request, S.QuoteRequest)),
  );
  route("POST", "/api/bookings", async ({ request }) => {
    const actor = await accounts.persona(request);
    return reservations.request(
      actor,
      await body(request, S.BookingRequest),
      request.headers.get("idempotency-key") ?? "",
    );
  });
  route("POST", "/api/bookings/:id/request", async ({ request, params }) =>
    reservations.submitInitial(
      await accounts.persona(request),
      params.id!,
      (await body(request, S.MessageRequest)).body,
    ),
  );
  route("GET", "/api/bookings", async ({ request, url }) =>
    reservations.list(
      await accounts.persona(request),
      page(url),
      url.searchParams.get("owner") === "true",
    ),
  );
  route("GET", "/api/bookings/:id", async ({ request, params }) =>
    reservations.detail(await accounts.persona(request), params.id!),
  );
  route("POST", "/api/bookings/:id/confirm", async ({ request, params }) =>
    reservations.transition(
      await accounts.persona(request),
      params.id!,
      "confirmed",
    ),
  );
  route("POST", "/api/bookings/:id/cancel", async ({ request, params }) =>
    reservations.transition(
      await accounts.persona(request),
      params.id!,
      "cancelled",
    ),
  );
  route("GET", "/api/favorites", async ({ request }) =>
    messages.favorites(await accounts.persona(request)),
  );
  route("PUT", "/api/favorites/:id", async ({ request, params }) =>
    messages.favorite(await accounts.persona(request), params.id!, true),
  );
  route("DELETE", "/api/favorites/:id", async ({ request, params }) =>
    messages.favorite(await accounts.persona(request), params.id!, false),
  );
  route(
    "POST",
    "/api/rentals/:id/conversations",
    async ({ request, params }) => {
      const actor = await accounts.persona(request);
      await rateLimit(sql, `contact:${actor.id}`, 50);
      return messages.contact(actor, params.id!);
    },
  );
  route("GET", "/api/rentals/:id/reviews", async ({ request, params, url }) =>
    feedback.list(
      params.id!,
      page(url),
      await accounts.persona(request).catch((error) => {
        if (error instanceof Problem && error.status === 401) return undefined;
        throw error;
      }),
    ),
  );
  route("GET", "/api/rentals/:id/reviewable", async ({ request, params }) =>
    feedback.eligible(await accounts.persona(request), params.id!),
  );
  route("POST", "/api/bookings/:id/review", async ({ request, params }) =>
    feedback.create(
      await accounts.persona(request),
      params.id!,
      await body(
        request,
        Schema.Struct({
          rating: Schema.Number.pipe(Schema.int(), Schema.between(1, 5)),
          body: Schema.String.pipe(Schema.minLength(1), Schema.maxLength(5000)),
        }),
      ),
    ),
  );
  route("PUT", "/api/reviews/:id/reply", async ({ request, params }) =>
    feedback.reply(
      await accounts.persona(request),
      params.id!,
      (await body(request, S.MessageRequest)).body,
    ),
  );
  route("POST", "/api/reviews/:id/moderate", async ({ request, params }) =>
    feedback.moderate(
      await accounts.persona(request),
      params.id!,
      (await body(request, Schema.Struct({ published: Schema.Boolean })))
        .published,
    ),
  );
  route("GET", "/api/admin/reviews", async ({ request, url }) =>
    feedback.administration(await accounts.persona(request), page(url)),
  );
  const contentFilters = (url: URL) => ({
    kind: (url.searchParams.get("kind") ?? "").slice(0, 20),
    q: (url.searchParams.get("q") ?? "").slice(0, 100),
    category: (url.searchParams.get("category") ?? "").slice(0, 100),
    town: (url.searchParams.get("town") ?? "").slice(0, 100),
    period: url.searchParams.get("period") ?? "all",
  });
  route("GET", "/api/content", async ({ url }) =>
    publishing.list(page(url), contentFilters(url)),
  );
  route("GET", "/api/content/:slug", async ({ params }) =>
    publishing.detail(params.slug!),
  );
  route("GET", "/api/admin/content", async ({ url, request }) =>
    publishing.list(
      page(url),
      contentFilters(url),
      await accounts.persona(request),
    ),
  );
  route("POST", "/api/events/proposals", async ({ request }) => {
    const actor = await accounts.persona(request);
    await rateLimit(sql, `proposal:${actor.id}`, 10);
    const input = await body(request, ProposalSchema);
    return publishing.propose(actor, input.content, input.submitter);
  });
  const saveContent: Handler = async ({ request, params }) => {
    const actor = await accounts.persona(request);
    const input = await body(request, ContentWriteSchema);
    return publishing.save(
      actor,
      params.id,
      input.content,
      input.status,
      input.version,
    );
  };
  route("POST", "/api/admin/content", saveContent);
  route("PUT", "/api/admin/content/:id", saveContent);
  route("GET", "/api/conversations", async ({ request, url }) =>
    messages.conversations(
      await accounts.persona(request),
      page(url),
      (url.searchParams.get("q") ?? "").slice(0, 100),
    ),
  );
  route(
    "GET",
    "/api/conversations/:id/messages",
    async ({ request, params, url }) =>
      messages.messages(await accounts.persona(request), params.id!, page(url)),
  );
  route(
    "POST",
    "/api/conversations/:id/messages",
    async ({ request, params }) => {
      const actor = await accounts.persona(request);
      await rateLimit(sql, `message:${actor.id}`, 100);
      return messages.send(
        actor,
        params.id!,
        (await body(request, S.MessageRequest)).body,
      );
    },
  );
  route("GET", "/api/notifications", async ({ request }) =>
    messages.notifications(await accounts.persona(request)),
  );
  route("POST", "/api/notifications/:id/read", async ({ request, params }) =>
    messages.read(await accounts.persona(request), params.id!),
  );
  route("GET", "/api/plans", () => payments.plans());
  route("GET", "/api/rentals/:id/subscriptions", async ({ request, params }) =>
    payments.subscriptions(await accounts.persona(request), params.id!),
  );
  route("POST", "/api/subscriptions/checkout", async ({ request }) =>
    payments.checkout(
      await accounts.persona(request),
      await body(request, S.SubscriptionRequest),
    ),
  );
  route("GET", "/api/reference/:kind", async ({ params }) => {
    assert(
      referenceKinds.includes(params.kind as (typeof referenceKinds)[number]) &&
        !["plans", "discounts"].includes(params.kind!),
      404,
      "not_found",
      "Section introuvable.",
    );
    const rows =
      await sql`SELECT data FROM crozon_reference WHERE kind=${params.kind!} ORDER BY id LIMIT 500`;
    return rows.map((r: { data: unknown }) => r.data);
  });
  route("GET", "/api/admin/:kind", async ({ request, url, params }) =>
    administration.list(
      await accounts.persona(request),
      params.kind!,
      page(url),
    ),
  );
  route("PUT", "/api/admin/:kind", async ({ request, params }) => {
    const actor = await accounts.persona(request);
    const kind = params.kind!;
    let value: { id: string };
    if (kind === "plans") value = await body(request, S.PlanSchema);
    else if (kind === "discounts") {
      const discount = await body(request, S.DiscountSchema);
      assert(
        discount.type !== "percent" || discount.amount <= 100,
        400,
        "discount",
        "Remise invalide.",
      );
      value = discount;
    } else value = await body(request, S.ReferenceSchema);
    return administration.reference(actor, kind, value);
  });
  route("DELETE", "/api/admin/:kind/:id", async ({ request, params }) =>
    administration.removeReference(
      await accounts.persona(request),
      params.kind!,
      params.id!,
    ),
  );
  route("POST", "/api/admin/personas/:id", async ({ request, params }) =>
    administration.persona(
      await accounts.persona(request),
      params.id!,
      (await body(request, Schema.Struct({ disabled: Schema.Boolean })))
        .disabled,
    ),
  );
  let active = 0;
  let activeAuth = 0;
  async function handler(request: Request): Promise<Response> {
    const requestId = crypto.randomUUID();
    let counted = false;
    let countedAuth = false;
    try {
      assert(
        active < 100,
        503,
        "overload",
        "Le service est occupé. Réessayez dans un instant.",
      );
      active++;
      counted = true;
      const url = new URL(request.url);
      const path = url.pathname;
      if (path === "/api/stripe/webhook" && request.method === "POST") {
        try {
          return Response.json(
            await payments.webhook(
              new TextDecoder().decode(await readBounded(request, 256 * 1024)),
              request.headers.get("stripe-signature") ?? "",
            ),
          );
        } catch (error) {
          if (
            error instanceof Error &&
            error.message === "invalid-stripe-signature"
          )
            throw invalid("Signature de paiement invalide.");
          throw error;
        }
      }
      if (!["GET", "HEAD"].includes(request.method))
        assert(
          request.headers.get("origin") === config.origin.origin,
          403,
          "origin",
          "Origine de requête refusée.",
        );
      if (path.startsWith("/api/auth/")) {
        assert(
          activeAuth < 8,
          503,
          "overload",
          "Le service de connexion est occupé. Réessayez dans un instant.",
        );
        activeAuth++;
        countedAuth = true;
        if (path.startsWith("/api/auth/passkeys/register/"))
          await accounts.persona(request);
        return await accounts.handler(request);
      }
      if (path.startsWith("/media/")) {
        assert(
          request.method === "GET" || request.method === "HEAD",
          405,
          "method",
          "Méthode refusée.",
        );
        assert(
          /^\/media\/[a-f0-9-]{36}\.webp$/.test(path),
          404,
          "not_found",
          "Image introuvable.",
        );
        const publicRows =
          await sql`SELECT id FROM crozon_rentals WHERE status='published' AND data->>'subscriptionExpiresAt'>${new Date().toISOString()} AND data->'photos' @> ${[path]}::jsonb UNION ALL SELECT id FROM crozon_personas WHERE NOT disabled AND profile->>'avatarUrl'=${path} UNION ALL SELECT id FROM crozon_content WHERE status='published' AND data->'images' @> ${[path]}::jsonb LIMIT 1`;
        if (!publicRows.length) {
          const actor = await accounts.persona(request);
          const rows =
            await sql`SELECT id FROM crozon_uploads WHERE path=${path} AND persona_id=${actor.id}`;
          assert(rows[0], 404, "not_found", "Image introuvable.");
        }
        const file = Bun.file(
          join(config.uploadDirectory, path.slice("/media/".length)),
        );
        assert(await file.exists(), 404, "not_found", "Image introuvable.");
        return new Response(request.method === "HEAD" ? null : file, {
          headers: {
            "content-type": "image/webp",
            "cache-control": "private, max-age=300",
            "x-content-type-options": "nosniff",
          },
        });
      }
      for (const item of routes) {
        const match = item.regex.exec(path);
        if (!match || item.method !== request.method) continue;
        const params: Record<string, string> = {};
        item.names.forEach((name, i) => {
          const value = decodeURIComponent(match[i + 1]!);
          assert(
            /^[A-Za-z0-9_-]{1,200}$/.test(value),
            400,
            "path",
            "Identifiant invalide.",
          );
          params[name] = value;
        });
        const result = await item.handler({ request, url, params });
        return Response.json(result, {
          headers: {
            "cache-control": "no-store",
            "x-request-id": requestId,
            "x-content-type-options": "nosniff",
          },
        });
      }
      throw new Problem(404, "not_found", "Page introuvable.");
    } catch (error) {
      const known = error instanceof Problem;
      const status = known ? error.status : 500;
      if (!known)
        console.error(
          JSON.stringify({
            event: "request.failed",
            requestId,
            time: new Date().toISOString(),
            errorType: error instanceof Error ? error.name : "unknown",
          }),
        );
      return Response.json(
        {
          error: known ? error.code : "internal",
          message: known
            ? error.message
            : "Une erreur est survenue. Réessayez dans un instant.",
          requestId,
        },
        {
          status,
          headers: {
            "cache-control": "no-store",
            "x-request-id": requestId,
            ...(status === 429 ? { "retry-after": "900" } : {}),
          },
        },
      );
    } finally {
      if (counted) active--;
      if (countedAuth) activeAuth--;
    }
  }
  return {
    handler,
    accounts,
    routes: routes.map(({ method, path }) => ({ method, path })),
  };
}
