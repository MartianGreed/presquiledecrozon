# Migration contract

Epic #2 and tickets #3–#10 tracked the migration delivered in PR #12 to `develop`. The implementation started from `5c62d87`, which retains the original Symfony application in Git history.

## Application boundaries

One Bun process serves a Structure HTTP API and the Angular 22 application. Structure owns configuration, authentication and HTTP lifecycle. PostgreSQL owns durable state. Identity, rental catalog, booking, correspondence and billing have explicit application modules. Rental details are an owned JSON document with a version; booking rows and rental locks serialize availability decisions. Commands authorize the actor before writing. Queries return dedicated public or participant views. Notifications and outbound email are committed with business changes.

Keep the existing French page URLs. New client requests use `/api`. The API never invokes PHP. Money uses integer euro cents; dates are calendar dates with checkout excluded. Weekly rates use `floor(nights / 7)` plus residual nights. The PHP implementation used modulo for week counts and month-relative day differences; those bugs are corrected. Booking lead time must be in the future, within the owner's stated horizon.

## Acceptance and evidence

- Identity: registration, verification, login/logout, profile, password recovery and existing bcrypt/Argon hashes.
- Rentals: all editor sections, resume, images, address/map, seasonal pricing, owner-only edits and subscription-gated publishing.
- Booking: quotes, requests, capacity, calendar and lead time validation, owner decisions, participant isolation and concurrent overlap rejection.
- Correspondence: saved rentals, private conversations and notifications.
- Billing: server-calculated checkout, discounts, signed/deduplicated Stripe events and admin access.
- Client: French pages, responsive forms and explicit error/empty/loading feedback.
- Delivery: frozen dependencies, lint, strict types, domain/API integration tests, browser journeys, Angular build, migration integrity and operational instructions.

Legacy source remains available in Git at `5c62d87` for verification. Removing the live PHP entry point does not authorize deleting production data or cutting over an existing deployment. Production-data rehearsal and provider sandbox acceptance need their actual environments; report any unavailable evidence as a blocker, not a pass.
