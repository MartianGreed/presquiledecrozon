# Presqu’île de Crozon

- Runtime: TypeScript, Bun 1.3.14, published `@structure-ai/*` 0.1.1, PostgreSQL 17.
- Client: Angular 22; its compiler uses Node 24.18. Bun runs the API and package scripts.
- `apps/api/src/` owns identity, rentals, bookings, correspondence and billing modules.
- `apps/web/` owns the French client; `packages/contracts/` contains shared models.
- Use `bun install --frozen-lockfile`, `bun run lint`, `bun run typecheck`, `bun run test`, `bun run build`.
- Integration tests require a dedicated `TEST_DATABASE_URL` ending in `_test`; they truncate it.
- Migration tests use a separate `MIGRATION_TEST_DATABASE_URL` ending in `_test`.
- `bun run test:e2e` seeds its own test database and exercises the built app.
- PostgreSQL migrations are explicit: `bun run db:migrate`. Serving never migrates.
- Preserve persona terminology, French URLs, integer cents, ownership checks and booking locks.
- `legacy/` is audit/rollback reference only and is excluded from runtime, CI and Docker images.
- Migration acceptance and operational limits live in `docs/migration/` and GitHub epic #2.
