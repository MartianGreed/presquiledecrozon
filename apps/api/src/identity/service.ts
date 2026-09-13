import {
  AuthDependencyError,
  argon2id,
  makeAuth,
  makeAuthHandler,
  type OAuthHttpClient,
  RateLimitExceeded,
} from "@structure-ai/auth";
import { makeAuthStore } from "@structure-ai/auth-pg";
import type { SQL } from "bun";
import { Effect, Redacted } from "effect";
import type {
  Persona,
  Profile,
} from "../../../../packages/contracts/src/models";
import type { AppConfig } from "../config";
import { assert, Problem } from "../errors";
import { socialProviders } from "./oauth";
export const TENANT = "crozon";
export async function rateLimit(
  sql: SQL,
  key: string,
  limit = 20,
): Promise<void> {
  const rows =
    await sql`INSERT INTO crozon_rate_limits(key,points,expires_at) VALUES(${key},1,now()+interval '15 minutes') ON CONFLICT(key) DO UPDATE SET points=CASE WHEN crozon_rate_limits.expires_at<now() THEN 1 ELSE crozon_rate_limits.points+1 END, expires_at=CASE WHEN crozon_rate_limits.expires_at<now() THEN now()+interval '15 minutes' ELSE crozon_rate_limits.expires_at END RETURNING points`;
  assert(
    rows[0].points <= limit,
    429,
    "rate_limit",
    "Trop de tentatives. Réessayez dans quinze minutes.",
  );
}
export async function identity(
  sql: SQL,
  config: AppConfig,
  oauthHttpClient?: OAuthHttpClient,
) {
  const providers = socialProviders(config);
  const hasher = argon2id();
  const store = makeAuthStore(sql);
  const auth = makeAuth({
    store,
    oauthProviderResolver: providers.resolver,
    ...(oauthHttpClient ? { oauthHttpClient } : {}),
    accountLinkPolicy: {
      authorize: (request) =>
        Effect.succeed(request.requestedByUserId === request.existingUserId),
    },
    resolveTenant: () =>
      Effect.succeed({
        baseUrl: config.origin,
        oauth: providers.google ? { google: providers.google } : {},
        password: { minLength: 12, maxLength: 256 },
        passkey: {
          rpId: config.origin.hostname,
          rpName: "Presqu’île de Crozon",
          origins: [config.origin.origin],
          requireUserVerification: true,
        },
        session: {
          cookieName: "crozon_session",
          cookieSameSite: "Lax",
          ttlMillis: 7 * 24 * 60 * 60 * 1000,
        },
      }),
    passwordHasher: {
      hash: hasher.hash,
      verify: (password, hash) =>
        Effect.tryPromise({
          try: () =>
            Bun.password.verify(password, hash.replace(/^\$2y\$/, "$2b$")),
          catch: (cause) =>
            new AuthDependencyError({
              dependency: "password",
              operation: "verify",
              cause,
            }),
        }),
    },
    rateLimiter: {
      check: (request) =>
        Effect.tryPromise({
          try: () =>
            rateLimit(sql, `auth:${request.action}:${request.keyHash}`),
          catch: (cause) =>
            cause instanceof Problem
              ? new RateLimitExceeded({
                  action: request.action,
                  retryAfterSeconds: 900,
                })
              : new AuthDependencyError({
                  dependency: "rate-limit",
                  operation: "check",
                  cause,
                }),
        }),
    },
    emailSender: {
      send: (email) =>
        Effect.tryPromise({
          try: async () => {
            const page =
              email.kind === "password-reset"
                ? "/reinitialisation/mot-de-passe"
                : "/verification-email";
            const url = new URL(page, config.origin);
            url.searchParams.set("token", Redacted.value(email.token));
            await sql`INSERT INTO crozon_outbox(id,recipient,subject,body) VALUES(${crypto.randomUUID()},${email.to},${email.kind === "password-reset" ? "Réinitialisez votre mot de passe" : "Vérifiez votre adresse e-mail"},${url.toString()})`;
          },
          catch: (cause) =>
            new AuthDependencyError({
              dependency: "email-outbox",
              operation: "enqueue",
              cause,
            }),
        }),
    },
    audit: {
      record: (event) =>
        Effect.sync(() =>
          console.log(
            JSON.stringify({
              event: "auth.completed",
              action: event.action,
              outcome: event.outcome,
              time: new Date().toISOString(),
            }),
          ),
        ),
    },
  });
  const authHandler = await Effect.runPromise(
    makeAuthHandler(auth, {
      resolveTenant: () => Effect.succeed(TENANT),
      basePath: "/api/auth",
      oauthCallbackRedirect: "/mon-compte",
      allowOrigin: (_tenant, origin) =>
        Effect.succeed(origin === config.origin.origin),
    }),
  );
  async function handler(request: Request) {
    const url = new URL(request.url);
    const oauth =
      /^\/api\/auth\/oauth\/(google|facebook)\/(start|callback)$/.exec(
        url.pathname,
      );
    if (!oauth) return authHandler.handler(request);
    const cookieName = `crozon_oauth_${oauth[1]}`;
    const cookie = (value: string, age: number) =>
      `${cookieName}=${value}; Path=/api/auth/oauth/${oauth[1]}; Max-Age=${age}; HttpOnly; SameSite=Lax${config.origin.protocol === "https:" ? "; Secure" : ""}`;
    const digest = (value: string) =>
      new Bun.CryptoHasher("sha256").update(value).digest("hex");
    if (oauth[2] === "start") {
      const response = await authHandler.handler(request);
      if (response.ok) {
        const result = (await response.clone().json()) as {
          authorizationUrl: string;
        };
        const state = new URL(result.authorizationUrl).searchParams.get(
          "state",
        );
        if (state)
          response.headers.append("set-cookie", cookie(digest(state), 600));
      }
      return response;
    }
    const expected = request.headers
      .get("cookie")
      ?.split(";")
      .map((value) => value.trim())
      .find((value) => value.startsWith(`${cookieName}=`))
      ?.slice(cookieName.length + 1);
    const state = url.searchParams.get("state");
    let response: Response;
    if (
      !state ||
      !expected ||
      expected !== digest(state) ||
      url.searchParams.has("error")
    ) {
      response = new Response(null, {
        status: 303,
        headers: { location: "/login?oauth=failed" },
      });
    } else {
      response = await authHandler.handler(request);
      if (response.status !== 303)
        response = new Response(null, {
          status: 303,
          headers: { location: "/login?oauth=failed" },
        });
    }
    response.headers.append("set-cookie", cookie("", 0));
    return response;
  }
  async function persona(request: Request): Promise<Persona> {
    const token = await Effect.runPromise(
      auth.sessionTokenFromCookie(TENANT, request.headers.get("cookie")),
    );
    assert(token, 401, "unauthenticated", "Connectez-vous pour continuer.");
    const session = await Effect.runPromise(
      auth
        .getSession(TENANT, token)
        .pipe(Effect.catchAll(() => Effect.succeed(undefined))),
    );
    assert(session, 401, "unauthenticated", "Votre session a expiré.");
    await sql`INSERT INTO crozon_personas(id,email) VALUES(${session.user.id},${session.user.email ?? null}) ON CONFLICT(id) DO NOTHING`;
    const rows =
      await sql`SELECT id,email,admin,profile,disabled FROM crozon_personas WHERE id=${session.user.id}`;
    assert(
      rows[0] && !rows[0].disabled,
      403,
      "disabled",
      "Ce compte est désactivé.",
    );
    return {
      id: rows[0].id,
      email: rows[0].email ?? "",
      admin: rows[0].admin,
      profile: rows[0].profile,
    };
  }
  async function saveProfile(
    actor: Persona,
    profile: Profile,
  ): Promise<Persona> {
    const saved = {
      ...profile,
      ...(actor.profile?.avatarUrl
        ? { avatarUrl: actor.profile.avatarUrl }
        : {}),
    };
    await sql`UPDATE crozon_personas SET profile=${saved} WHERE id=${actor.id}`;
    return { ...actor, profile: saved };
  }
  async function preferences(actor: Persona) {
    const rows =
      await sql`SELECT preferences FROM crozon_personas WHERE id=${actor.id}`;
    return rows[0].preferences as { emailNotifications: boolean };
  }
  async function security(actor: Persona) {
    const credential = actor.email
      ? await Effect.runPromise(store.findPassword(TENANT, actor.email))
      : undefined;
    return { hasPassword: credential?.userId === actor.id, email: actor.email };
  }
  async function requestContactEmail(actor: Persona, email: string) {
    assert(
      !actor.email,
      409,
      "contact_email",
      "Une adresse e-mail est déjà associée à votre compte.",
    );
    await rateLimit(sql, `contact-email:${actor.id}`, 5);
    const token = crypto.randomUUID() + crypto.randomUUID();
    const hash = new Bun.CryptoHasher("sha256").update(token).digest("hex");
    const normalized = email.trim().toLowerCase();
    const url = new URL("/mon-compte/parametres", config.origin);
    url.searchParams.set("contact-token", token);
    await sql.begin(async (db) => {
      await db`DELETE FROM crozon_contact_tokens WHERE persona_id=${actor.id}`;
      await db`INSERT INTO crozon_contact_tokens(hash,persona_id,email,expires_at) VALUES(${hash},${actor.id},${normalized},now()+interval '30 minutes')`;
      await db`INSERT INTO crozon_outbox(id,recipient,subject,body) VALUES(${crypto.randomUUID()},${normalized},'Vérifiez votre adresse de contact',${url.toString()})`;
    });
    return { ok: true };
  }
  async function verifyContactEmail(actor: Persona, token: string) {
    const hash = new Bun.CryptoHasher("sha256").update(token).digest("hex");
    return sql.begin(async (db) => {
      const rows =
        await db`DELETE FROM crozon_contact_tokens WHERE hash=${hash} AND persona_id=${actor.id} AND expires_at>now() RETURNING email`;
      assert(
        rows[0],
        400,
        "contact_email",
        "Ce lien est invalide ou a expiré.",
      );
      const existing =
        await db`SELECT id FROM crozon_personas WHERE email=${rows[0].email}`;
      assert(
        !existing.length,
        409,
        "contact_email",
        "Cette adresse ne peut pas être associée à ce compte. Utilisez votre méthode de connexion habituelle.",
      );
      const updated =
        await db`UPDATE crozon_personas SET email=${rows[0].email} WHERE id=${actor.id} AND email IS NULL RETURNING email`;
      assert(
        updated.length,
        409,
        "contact_email",
        "Une adresse est déjà associée à ce compte.",
      );
      return { ...actor, email: rows[0].email as string };
    });
  }
  async function savePreferences(
    actor: Persona,
    value: { emailNotifications: boolean },
  ) {
    await sql`UPDATE crozon_personas SET preferences=${value} WHERE id=${actor.id}`;
    return value;
  }
  async function avatar(actor: Persona, path: string | null) {
    assert(
      actor.profile,
      400,
      "profile_required",
      "Enregistrez vos informations avant d’ajouter une photo.",
    );
    if (path) {
      const rows =
        await sql`SELECT id FROM crozon_uploads WHERE path=${path} AND persona_id=${actor.id}`;
      assert(rows[0], 403, "forbidden", "Cette photo ne vous appartient pas.");
    }
    const rows = path
      ? await sql`UPDATE crozon_personas SET profile=jsonb_set(profile,'{avatarUrl}',${JSON.stringify(path)}::jsonb) WHERE id=${actor.id} RETURNING profile`
      : await sql`UPDATE crozon_personas SET profile=profile-'avatarUrl' WHERE id=${actor.id} RETURNING profile`;
    return { ...actor, profile: rows[0].profile as Profile };
  }
  async function passkeys(request: Request) {
    const actor = await persona(request);
    const keys = await Effect.runPromise(store.listPasskeys(TENANT, actor.id));
    return keys.map((key) => ({
      id: key.credentialId,
      label: key.label ?? "Clé d’accès",
      createdAt: key.createdAt.toISOString(),
    }));
  }
  async function removePasskey(request: Request, id: string) {
    await persona(request);
    const token = await Effect.runPromise(
      auth.sessionTokenFromCookie(TENANT, request.headers.get("cookie")),
    );
    assert(token, 401, "unauthenticated", "Connectez-vous pour continuer.");
    const removed = await Effect.runPromise(
      auth.removePasskey(TENANT, token, id),
    );
    assert(removed, 404, "not_found", "Clé d’accès introuvable.");
    return { ok: true };
  }
  return {
    auth,
    handler,
    providers: providers.available,
    persona,
    saveProfile,
    passkeys,
    removePasskey,
    preferences,
    security,
    requestContactEmail,
    verifyContactEmail,
    savePreferences,
    avatar,
  };
}
export type Identity = Awaited<ReturnType<typeof identity>>;
