import { createHmac } from "node:crypto";
import {
  AuthDependencyError,
  builtInOAuthProvider,
  type OAuthProvider,
  type OAuthProviderResolver,
} from "@structure-ai/auth";
import { Effect, Redacted } from "effect";
import { type AppConfig, secret } from "../config";
import { readBounded } from "../media";

export function socialProviders(config: AppConfig) {
  const googleId = secret(config.googleOAuthClientId);
  const googleSecret = secret(config.googleOAuthClientSecret);
  const facebookId = secret(config.facebookOAuthClientId);
  const facebookSecret = secret(config.facebookOAuthClientSecret);
  const google =
    googleId && googleSecret
      ? { clientId: googleId, clientSecret: Redacted.make(googleSecret) }
      : undefined;
  let facebook: OAuthProvider | undefined;
  if (facebookId && facebookSecret) {
    if (!/^v\d+\.\d+$/.test(config.facebookGraphVersion))
      throw new Error("FACEBOOK_GRAPH_VERSION must be a version such as v25.0");
    const graph = `https://graph.facebook.com/${config.facebookGraphVersion}`;
    facebook = {
      id: "facebook",
      credentials: {
        clientId: facebookId,
        clientSecret: Redacted.make(facebookSecret),
      },
      authorizationEndpoint: `https://www.facebook.com/${config.facebookGraphVersion}/dialog/oauth`,
      tokenEndpoint: `${graph}/oauth/access_token`,
      scopes: ["public_profile"],
      tokenAuthMethod: "client-secret-post",
      fetchProfile: (tokens, client) =>
        Effect.gen(function* () {
          const url = new URL(`${graph}/me`);
          url.searchParams.set("fields", "id,name");
          url.searchParams.set(
            "appsecret_proof",
            createHmac("sha256", facebookSecret)
              .update(Redacted.value(tokens.accessToken))
              .digest("hex"),
          );
          const response = yield* client.execute(
            new Request(url, {
              headers: {
                authorization: `Bearer ${Redacted.value(tokens.accessToken)}`,
              },
            }),
          );
          const profile = yield* Effect.tryPromise({
            try: async () => {
              if (!response.ok) throw new Error("Facebook profile unavailable");
              const bytes = await readBounded(
                new Request("https://provider-response.invalid", {
                  method: "POST",
                  body: response.body,
                  duplex: "half",
                } as RequestInit),
                64 * 1024,
              );
              const data: unknown = JSON.parse(new TextDecoder().decode(bytes));
              if (
                !data ||
                typeof data !== "object" ||
                !("id" in data) ||
                typeof data.id !== "string" ||
                !/^\d{1,100}$/.test(data.id)
              )
                throw new Error("Invalid Facebook subject");
              return {
                subject: data.id,
                emailVerified: false,
                ...("name" in data && typeof data.name === "string"
                  ? { displayName: data.name.slice(0, 200) }
                  : {}),
              };
            },
            catch: () =>
              new AuthDependencyError({
                dependency: "facebook",
                operation: "profile",
              }),
          });
          // Facebook's account verification is not proof of ownership of an email address.
          return profile;
        }),
    };
  }
  const resolver: OAuthProviderResolver = {
    resolve: (_tenant, provider, tenant) =>
      Effect.succeed(
        provider === "facebook"
          ? facebook
          : builtInOAuthProvider(tenant, provider),
      ),
  };
  return {
    google,
    resolver,
    available: [
      ...(google ? ["google"] : []),
      ...(facebook ? ["facebook"] : []),
    ],
  };
}
