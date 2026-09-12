import {
  AuthDependencyError,
  argon2id,
  makeAuth,
  makeAuthHandler,
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
export async function identity(sql: SQL, config: AppConfig) {
  const hasher = argon2id();
  const auth = makeAuth({
    store: makeAuthStore(sql),
    resolveTenant: () =>
      Effect.succeed({
        baseUrl: config.origin,
        password: { minLength: 12, maxLength: 256 },
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
      allowOrigin: (_tenant, origin) =>
        Effect.succeed(origin === config.origin.origin),
    }),
  );
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
    await sql`INSERT INTO crozon_personas(id,email) VALUES(${session.user.id},${session.user.email ?? ""}) ON CONFLICT(id) DO NOTHING`;
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
      email: rows[0].email,
      admin: rows[0].admin,
      profile: rows[0].profile,
    };
  }
  async function saveProfile(
    actor: Persona,
    profile: Profile,
  ): Promise<Persona> {
    await sql`UPDATE crozon_personas SET profile=${profile} WHERE id=${actor.id}`;
    return { ...actor, profile };
  }
  return { auth, handler: authHandler.handler, persona, saveProfile };
}
export type Identity = Awaited<ReturnType<typeof identity>>;
