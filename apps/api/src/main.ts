import { HttpApiBuilder } from "@effect/platform";
import { BunRuntime } from "@effect/platform-bun";
import { Api, Health, serve } from "@structure-ai/http";
import { Readiness } from "@structure-ai/runtime";
import { Effect, Layer } from "effect";
import { application } from "./app";
import { loadConfig, secret } from "./config";
import { connect } from "./database";
import { deliverOutbox } from "./outbox";

const config = await loadConfig();
if (
  config.mailMode === "mailjet" &&
  (!secret(config.mailjetKey) || !secret(config.mailjetSecret))
)
  throw new Error("Mailjet credentials are required for MAIL_MODE=mailjet.");
const sql = connect(config);
const versions = await sql`SELECT version FROM crozon_schema WHERE version=1`;
if (!versions.length)
  throw new Error("Run db:migrate before starting the application.");
const app = await application(sql, config);
const api = Api.make("crozon").add(Health.group);
const apiLayer = HttpApiBuilder.api(api).pipe(Layer.provide(Health.layer(api)));
const lifecycle = Layer.scopedDiscard(
  Effect.gen(function* () {
    const readiness = yield* Readiness;
    yield* readiness.register({
      name: "storage",
      run: Effect.tryPromise(() => sql`SELECT 1`).pipe(
        Effect.timeout("2 seconds"),
        Effect.match({ onFailure: () => false, onSuccess: () => true }),
      ),
    });
    yield* readiness.setReady;
    yield* Effect.forkScoped(
      Effect.forever(
        Effect.tryPromise((signal) => deliverOutbox(sql, config, signal)).pipe(
          Effect.catchAll(() => Effect.logError("outbox cycle failed")),
          Effect.zipRight(Effect.sleep("10 seconds")),
        ),
      ),
    );
    yield* Effect.addFinalizer(() =>
      Effect.promise(() => sql.close({ timeout: 5 })),
    );
  }),
);
BunRuntime.runMain(
  Layer.launch(
    serve({
      port: config.port,
      mounts: [
        { prefix: "/api", handler: app.handler },
        { prefix: "/media", handler: app.handler },
      ],
      static: { directory: config.webDirectory, spaFallback: "index.html" },
    }).pipe(
      Layer.provide(apiLayer),
      Layer.provide(lifecycle),
      Layer.provide(Readiness.layer),
    ),
  ),
);
