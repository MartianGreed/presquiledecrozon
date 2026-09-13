# Migration verification

Verified locally on 2026-09-12 with Bun 1.3.14, Node 24.18.0 and PostgreSQL 17.11 locally / 17.10 in the container. All database work used isolated test databases and synthetic accounts.

| Check | Result |
| --- | --- |
| Frozen Bun installation | Passed on macOS and Linux container build |
| Dependency audit | No known vulnerabilities reported by `bun audit` |
| Biome | Passed for application, contracts, scripts and browser tests |
| Strict TypeScript and Angular template compilation | Passed |
| Domain, PostgreSQL API and legacy import tests | 21 passed, 93 assertions |
| Built-client Chromium journeys | 4 passed: discovery/mobile, identity/profile, request/confirmation/messages, editor resume |
| Angular production build | Passed; initial estimated transfer 94.53 kB |
| Linux application image | Built successfully; no PHP executable or legacy source; UID 1000; writable upload volume with read-only root filesystem |
| Container HTTP smoke | Liveness, readiness, catalog and browser deep link returned 200; cross-origin mutation returned 403 |
| Database outage | Readiness returned 503 in 2.03 seconds while liveness stayed available; readiness recovered after PostgreSQL resumed |
| Shutdown | SIGTERM exited with code 0 in 0.41 seconds, without OOM or forced termination |
| Backup restoration | Representative imported database dumped and restored into a separate database; all 31 table counts matched |

The API acceptance suite includes every saved rental section, private drafts, stale versions, unpaid publication, signed payment amount/session binding and replay, conflicting concurrent booking requests, conversation isolation, administrator references/account disable, initialized legacy requests, cancellation, reset token replay and session revocation. The migration suite checks identifiers, historical cents, relationships, source timezone conversion, CDN path construction and existing Symfony bcrypt login.

The migration's new runtime files passed `git diff --check`.

## External rollout evidence still required

No production database or credentials were used. Actual production snapshot integrity, CDN availability, a Stripe sandbox checkout through the provider, Mailjet delivery, the deployed Google project, hosting monitoring and proxy-level termination must be rehearsed before traffic switches. See the behavior inventory and operations runbook. The implementation PR and [GitHub rollout ticket #11](https://github.com/MartianGreed/presquiledecrozon/issues/11) track these limits separately from the local test results.
