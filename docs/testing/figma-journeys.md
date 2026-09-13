# Figma acceptance journeys

Tracking: [#17](https://github.com/MartianGreed/presquiledecrozon/issues/17). Design source: [UI_PRESQUILE 2](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-1), inspected on 2026-09-13.

The suite tests behavior represented by the designs against the built Angular client, Bun API and PostgreSQL. It uses real authentication, uploaded images, persisted messages and booking locks. It does not mock application API responses. Each scenario runs in desktop Chromium and mobile Chromium with Pixel 7 emulation. Mobile emulation checks usability and overflow; it does not certify Safari or a physical phone.

## Design mapping

Frame names and IDs came from the Figma layer tree. The login, rental detail, messaging, equipment and publication frames were also inspected as rendered prototypes. Some design images are blank in the shared prototype. The `📱 MOBILE` page exposes no frames in the shared file, so mobile checks use the same functional acceptance criteria at a narrow viewport.

| Figma frame | Browser acceptance | Test file |
| --- | --- | --- |
| [Home `0:2`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-2), Resultats annonces `0:3082` | Search from home, follow result, reload deep link, verify rendered photo and listing sections; recover from an empty search | `catalog.spec.ts` |
| [Connexion `0:134`, `0:324`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-324), Inscription `0:651` | Reject mismatched passwords and unverified login; verify email, edit profile, reload, logout; return to the requested page after sign-in | `account.spec.ts` |
| Connexion `0:324`, “Mot de passe oublié ?” | Request reset without revealing account existence; change password, revoke an existing session, reject old password and reused token | `account.spec.ts` |
| [Compte/Profil `0:3547`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-3547) | Persist names, phone and profile description; require profile before booking | `account.spec.ts`, `bookings.spec.ts` |
| [Coups de cœur `0:3112`, vide `0:3179`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-3112) | Empty state, save, retrieve after navigation/reload, remove, return to empty state; anonymous sign-in returns to the listing | `catalog.spec.ts`, `account.spec.ts` |
| [1 annonce `0:2889`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-2889), je réserve `0:2978`, Récap réservation `0:3074` | Date and capacity validation, exact weekly-plus-night amount, booking request and recap; preserve the selected stay during sign-in | `catalog.spec.ts`, `bookings.spec.ts` |
| [Compte/Réservations en cours `0:3770`, historique `0:3364`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-3770) | Traveler finds request; owner opens notification, confirms and cancels; another traveler cannot book occupied dates; cancellation releases them | `bookings.spec.ts` |
| [Compte/Messagerie `0:3226`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-3226) | Initial booking message, replies in both directions, persistence after reload, nonparticipant denial, empty conversation state | `bookings.spec.ts`, `account.spec.ts` |
| [Détails de la location `0:3833` through `0:4625`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-3833) | Create rooms/beds, equipment, title/description, address and coordinates; reject missing/invalid photos, upload four photos; resume the same draft and reject stale-tab overwrite | `rentals.spec.ts` |
| [Questions d’organisation `0:3873` through `0:4499`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-3873) | Save lead times, unavailable period, cleaning/linen fees, regular and seasonal rates, pets/rules; public quotes reflect these settings | `rentals.spec.ts` |
| [Félicitations `0:3896`, Publications `0:3935`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-3896), Compte/Abonnement `0:3720` | Complete wizard, choose plan, handle unavailable payment; visiting the return page does not grant publication; paid fixture enables publication | `rentals.spec.ts` |
| [Compte/Annonces `0:3462`, désactiver `0:3507`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-3462) | Publish, view as anonymous visitor, deactivate, hide cached detail on browser-back navigation, edit saved listing | `rentals.spec.ts` |
| [Page 404 `0:3188`](https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-3188) | Unknown URL offers a working return to home | `catalog.spec.ts` |
| Operational coverage beyond Figma | Ordinary account denied administration; administrator creates, edits, reloads and deletes an equipment reference | `admin.spec.ts` |

Every main test page is checked for uncaught JavaScript errors, a completed loading state and horizontal overflow. Successful scenarios attach full-page screenshots. Failures retain screenshots and Playwright traces. Screenshots are evidence for review, not pixel-diff baselines approved against Figma.

## Regressions found

- Anonymous favorite and reservation actions omitted the return URL when redirecting to login. Sign-in sent the visitor to `/mon-compte`. Both actions now return to the selected listing. In-app booking dates and message remain available after sign-in.
- A failed route load displayed the previously loaded content beneath its error. Disabling a listing and using browser Back reproduced this. Failed loads now hide the page content, and stale failed requests cannot replace a newer page's error state.

## Remaining design differences

The current client uses full authentication pages, an account grid, native date fields and an eleven-step editor. Figma shows modal authentication, an account sidebar, a calendar picker and a different editor presentation. The typography, colors, spacing, illustrations and page composition also differ. This work does not certify visual parity.

Figma includes Facebook/Google sign-in buttons, direct owner contact before booking, reviews, conversation search, profile avatars, tourism/events and legal/editorial pages. These capabilities are not present in the migrated application and are not reported as passing tests. Four-photo guidance appears in the photo design; the current API requires at least one photo. The test uploads four without changing that product rule. [Issue #18](https://github.com/MartianGreed/presquiledecrozon/issues/18) tracks these decisions.

## Test boundaries

- Email links are read from the real `crozon_outbox` table. This tests generation and consumption of verification/reset links, not Mailjet delivery or an email inbox. There is no Mailpit service.
- The publication test first proves that an unpaid listing and a forged payment-return visit cannot publish. It then inserts an explicitly labeled, test-only paid subscription fixture. No checkout payment is claimed. Signed Stripe webhook and replay checks remain in the API suite; real provider acceptance remains [#11](https://github.com/MartianGreed/presquiledecrozon/issues/11).
- Coordinates are entered manually. No Google geocoding request or external map is certified.
- Each test starts in a fresh browser context and creates its own account when needed. The shared catalog listing is used for search and booking; booking tests use distinct date ranges per project and cancel their request. Production data is never used.

## Run locally

Use Bun 1.3.14 and Node 24.18.0 on PATH. PostgreSQL must be running with two dedicated test databases.

```sh
export TEST_DATABASE_URL=postgres://localhost/crozon_test
export MIGRATION_TEST_DATABASE_URL=postgres://localhost/crozon_migration_test
bun install --frozen-lockfile
bun run lint
bun run typecheck
bun run test
bun run build
bun run test:e2e
```

Playwright refuses a database URL without the `_test` suffix before it starts a server or seeds data. It starts its own server and does not reuse a running development application. Test setup truncates the dedicated test database. Do not run API and browser suites concurrently against that database. Provider credentials are cleared in the browser-test server.

```sh
bun run test:e2e --project=chromium
bun run test:e2e --project=mobile-chromium rentals.spec.ts
node node_modules/@playwright/test/cli.js show-report playwright-report
```

GitHub Actions runs both projects and retains `browser-evidence` for 14 days. Contremaitre smoke remains a separate check of the packaged runtime and preserves its preview database:

```sh
contremaitre ensure --json
contremaitre verify --profile smoke --json
contremaitre report --json
```
