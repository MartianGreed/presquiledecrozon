# Operations

The repository owner operates the service. Production infrastructure, dashboards and paging destinations must be connected by the deployment owner before public traffic is switched. The repository supplies the application and runbook; it does not provision a cloud account or modify the existing Lambda deployment.

## Configuration

All settings are loaded once with `@structure-ai/config`. Restart after changing them. Secrets must come from the deployment secret store or an untracked local file.

| Setting | Meaning / default |
| --- | --- |
| `DATABASE_URL` | Required PostgreSQL connection secret. Use a dedicated role and TLS for remote connections. Pool limit 10; connect timeout 5 seconds. |
| `APP_ORIGIN` | Public origin; defaults to `http://localhost:3000` for local use. Set the real HTTPS origin behind a proxy. Controls auth cookies, accepted mutation origins and email links. |
| `PORT` | Listening port, default 3000. |
| `WEB_DIRECTORY` | Built Angular directory, default `./dist/web/browser`. |
| `UPLOAD_DIRECTORY` | Writable persistent volume, default `./var/uploads`. Never use ephemeral storage for this directory. |
| `MAIL_MODE` | `outbox` by default. Use `mailjet` to send queued mail. |
| `EMAIL_SENDER` | Verified sender address. Default `bonjour@presquiledecrozon.fr`. |
| `MAILJET_API_KEY`, `MAILJET_SECRET_KEY` | Required secrets when Mailjet delivery is enabled. |
| `STRIPE_SECRET_KEY` | Payment secret. Missing configuration returns a visible unavailable response; it never grants free publication. |
| `STRIPE_WEBHOOK_SECRET` | Signing secret for `/api/stripe/webhook`. |
| `GOOGLE_MAPS_API_KEY` | Optional server-side geocoding key. Without it, owners use manual coordinates. |

Only register the public Stripe webhook for `checkout.session.completed`, `checkout.session.async_payment_succeeded` and `checkout.session.expired`. Checkout requests calculate the amount on the server, bind the rental and subscription, and use a stable provider idempotency key. Settlement verifies amount, currency, subscription and session binding. Replayed events are no-ops. Browser success pages cannot settle payments.

## Process and resource limits

- One Bun listener serves the Structure API, authenticated media and Angular assets. `/health/live` checks the process; `/health/ready` checks PostgreSQL within two seconds.
- The HTTP adapter admits at most 100 concurrent application requests and eight concurrent auth operations. Configure request and per-IP limits at the public reverse proxy as well. The application does not trust client-supplied forwarding headers.
- JSON requests are bounded to 256 KiB. Images are bounded to 8 MiB and 40 million pixels, then resized to at most 1920×1440. Body reads have a 15-second deadline. Structure auth has its own 64-KiB body bound.
- Geocoding times out after eight seconds. Stripe requests use a ten-second SDK timeout and at most one retry. Mailjet requests use five seconds and durable retry scheduling.
- Email delivery polls every ten seconds and locks up to ten rows. It retries up to five attempts with exponential delay. Delivery is at least once: a crash after Mailjet accepts an email but before the database commits can send a duplicate. Authentication tokens remain single-use.
- SIGTERM flips readiness, stops the listener and closes the database pool. Give the container 40 seconds to stop. Test termination on the deployed proxy before rollout.
- The upload volume is shared by all replicas. The supplied Compose deployment is one application replica. Do not add replicas with independent local upload directories.

## Monitoring and response

Structure records request duration, status, request/correlation IDs and bounded route labels. The application emits safe auth completion and failure events without emails, passwords, token URLs or message bodies. Database configuration and personal data are excluded from error responses.

For an initial deployment, track public/API availability over 30 days with a proposed 99.5% target and p95 interactive request latency below one second, excluding provider checkout. These targets require a real traffic baseline before they become production commitments. Page the deployment owner when readiness remains false for five minutes or API 5xx responses exceed 5% for five minutes. Configure the actual dashboard and destination in the hosting platform.

- **Readiness fails:** check database reachability, connection saturation and the `crozon_schema` table. Do not restart-loop on a database outage. Restore database connectivity, then verify readiness and a catalog query.
- **Bookings are rejected:** distinguish a 409 availability/version conflict from a 5xx failure. Check overlapping dates through authorized booking views; never bypass the rental lock by editing production rows.
- **Payment is pending:** inspect the provider's event delivery history and the subscription's session binding. Resend the same signed event after repairing delivery. Do not mark a subscription paid from its success-page visit.
- **Mail stops:** check credentials, provider quota and rows where `attempts=5 AND sent_at IS NULL`. Fix the cause and deliberately requeue affected rows with an operator-reviewed database operation. Do not expose mail bodies through an admin endpoint.
- **Photo fails:** check the upload mount, disk capacity and file ownership. For an imported photo, check the CDN root and original `Y/d/m` directory.

## Backups and retention

Back up PostgreSQL and the upload volume together. A proposed first deployment policy is daily encrypted backups, seven daily and four weekly copies, and a quarterly isolated restore drill. The owner must agree to recovery objectives based on the actual hosting service; the repository cannot certify an untested production backup.

Retain bookings and paid subscriptions under the platform's existing policy. Purge expired auth tokens/sessions and rate-limit rows through scheduled maintenance. Review archive retention after the migration is accepted. Email outbox rows contain recipient addresses and token links, so grant access only to the service and operators and purge sent rows after the agreed retention period. Do not log snapshot contents or copy them into issue attachments.

## Artifact verification

CI runs frozen installation, lint, strict API and Angular typechecks, domain/API/database migration tests, the Angular production build and browser journeys. A separate job builds the container. Runtime stages copy no PHP, Composer, Symfony, legacy configuration or migration snapshots. No deployment happens automatically on push.

Dependency resolution pins `@opentelemetry/core` to 2.11.0 to avoid the vulnerable transitive version selected by the initial Structure release. CI runs `bun audit` against the frozen lockfile. Reassess this override when upgrading Structure.
