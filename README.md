# Presqu’île de Crozon

Vacation rentals on the Crozon peninsula. The application runs on TypeScript, Bun, Structure and PostgreSQL, with an Angular 22 client in French.

## Run locally

Use Bun 1.3.14 and Node 24.18.0. Angular's compiler requires Node; the server runs on Bun.

```sh
bun install --frozen-lockfile
cp .env.example .env.local
# Set DATABASE_URL to your local PostgreSQL 17 database.
bun run db:migrate
bun run build
bun run dev
```

Open `http://localhost:3000`. `bun run dev` watches the server. Re-run `bun run build` after changing the client. The same Bun listener serves the API and built Angular files.

`MAIL_MODE=outbox` stores emails in `crozon_outbox` without delivering them. For local verification, inspect that table in your own database and open the verification link. `MAIL_MODE=mailjet` enables delivery and requires both Mailjet credentials. There is no public endpoint that exposes mail or tokens.

After registering, verifying and signing in, grant your first administrator access:

```sh
bun run admin:promote your-address@example.com
```

Administrators manage subscription plans, discounts, reference data, rentals, reservations and account access at `/admin`. Create an active subscription plan before accepting listing payments. Configure Stripe and its signed webhook before accepting real payments.

## Local preview with Contremaitre

From this checkout, with Contremaitre and its native runtime installed:

```sh
bun install --frozen-lockfile
bun x playwright install chromium
contremaitre ensure --json
contremaitre verify --profile smoke --json
contremaitre report --json
```

Open the returned `web` URL. Contremaitre manages a separate PostgreSQL database and persistent uploads for this workspace. It builds `Dockerfile.contremaitre`, initializes the schema and demo data, then runs the application. Use `ensure` after editing source to rebuild it. This image does not enable hot reload.

The preview contains **La maison des embruns** and two verified accounts:

| Role | Email | Password |
| --- | --- | --- |
| Owner and administrator | `browser-owner@example.test` | `Crozon browser test 2026!` |
| Traveler | `browser-guest@example.test` | `Crozon browser test 2026!` |

Use the traveler to request a stay, then the owner to confirm it and exchange messages. The owner's `/admin` page manages reference data. The sample listing has a synthetic paid publication entitlement; no payment provider is contacted. Stripe checkout and automatic geocoding require separate provider configuration and are unavailable in this preview. Email is stored in `crozon_outbox` without delivery.

Preview initialization preserves existing rows and edits. It requires `CONTREMAITRE_PREVIEW=1`, `MAIL_MODE=outbox` and no Stripe or Mailjet key. The existing end-to-end test command still resets only dedicated `_test` databases; do not point it at the preview. The Contremaitre smoke profile reads the catalog, checks desktop/mobile pages and signs both demo users in and out without changing listings or bookings. Screenshots appear in the returned review page.

`contremaitre down` stops the workspace environment and retains its data. These shared demo credentials are intended only for the local preview.

## Checks

The integration and migration suites **truncate their test databases**. Use two dedicated databases with names ending in `_test` and provide their URLs through your shell or a local env file. Do not use a production database or a shared development database.

```sh
export TEST_DATABASE_URL=postgres://crozon:crozon@localhost:5432/crozon_test
export MIGRATION_TEST_DATABASE_URL=postgres://crozon:crozon@localhost:5432/crozon_migration_test
bun run lint
bun run typecheck
bun run test
bun run build
bun x playwright install chromium
bun run test:e2e
```

The browser suite resets `TEST_DATABASE_URL` and starts the built application when no test server is running. It never contacts Stripe, Google or Mailjet. `bun run test:unit` runs pricing checks without PostgreSQL. The full `test` command fails if either database URL is missing, so CI cannot silently skip persistence verification.

## Repository

| Path | Responsibility |
| --- | --- |
| `apps/api/src/identity` | Structure authentication and persona profiles |
| `apps/api/src/rentals` | Catalog, owner editing, availability and publishing |
| `apps/api/src/bookings` | Quotes, requests and owner decisions |
| `apps/api/src/correspondence` | Favorites, messages and notifications |
| `apps/api/src/billing` | Subscription checkout and signed Stripe events |
| `apps/api/src/migration` | Legacy record conversion and transactional import |
| `apps/web` | Angular client, styles and browser journeys |
| `packages/contracts` | Shared TypeScript models |
| `legacy` | Original Symfony source, excluded from builds and runtime |

The public French page URLs remain available. State-changing requests use the new `/api` endpoints and require an exact same-origin header. Booking requests also require an `Idempotency-Key`.

## Migration and deployment

The [migration epic](https://github.com/MartianGreed/presquiledecrozon/issues/2) tracks the delivery. Read [the behavior inventory](docs/migration/parity.md), [data migration](docs/migration/data.md), and [operations](docs/migration/operations.md) before deploying.

The container uses Bun at runtime and contains no PHP or legacy source. Build with `docker build -t crozon:VERSION .`. `compose.yaml` provides PostgreSQL, a separate schema-migration job and the application. Set `POSTGRES_PASSWORD`, `DATABASE_URL` (host `database`, URL-encoded password), `APP_ORIGIN` and provider credentials through your deployment environment. Terminate HTTPS at your reverse proxy and forward to the loopback-only port 3000.

Deployment does not migrate the legacy data automatically. Rehearse export, import, media access and backup restoration against an isolated copy before switching traffic. The new schema uses `crozon_` and `auth_` tables and does not mutate the legacy tables.
