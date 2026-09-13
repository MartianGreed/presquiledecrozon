# Behavior inventory

The application source at `5c62d87` is the migration reference. Retrieve the original Symfony files through Git history, for example `git show 5c62d87:src/Kernel.php`. The archived source has been removed from the current tree.

| Legacy capability / page | Replacement | Verification |
| --- | --- | --- |
| `/`, `/annonces`, `/annonce/:slug` | Public catalog, search, pagination, detail and gallery | Browser discovery, refresh and mobile-width checks; unpublished listings hidden in API tests |
| `/login`, `/logout`, `/creer-mon-compte` | Structure password registration, verification, sessions and sign-out | Browser registration/login/logout; durable session and origin tests |
| `/reinitialisation-mot-de-passe`, `/reinitialisation/mot-de-passe` | Enumeration-safe request and single-use reset | API reset/replay/session-revocation test |
| `/mon-compte`, `/mon-compte/informations` | Profile and notifications | Browser profile edits; server rejects unvalidated fields |
| `/deposez-votre-annonce/*` | Eleven saved editor steps with owner authorization and optimistic version checks | API exercises every section; browser resumes and saves a draft |
| Configuration / bedrooms / beds | Typed rooms and bed counts | Saved editor round trip |
| Equipment / description / address | Validated fields and custom equipment | Saved editor round trip |
| Location | Bounded Google geocoding request, manual coordinates and external map | Coordinates validated; geocoding can fail without losing the draft |
| Gallery / cover / resizing | First photo is cover; bounded JPEG/PNG/WebP upload; EXIF rotation and WebP output | Real image upload in API suite |
| Preferences / unavailable dates / seasonal prices | Calendar dates, lead days, horizon, arrival/departure time and seasonal rates | Domain pricing and API booking checks |
| Taxes / linen / conditions | Cleaning and linen amounts, local-tax explanation, pets, smoking and custom rules | Saved editor round trip |
| `/previsualisation/annonce`, `/deposez-votre-annonce/termine` | Owner preview and completion page | Public detail authorizes preview; completion links to subscription |
| `/mon-compte/annonces`, publication toggle | Versioned editing and paid-entitlement publication | Unpaid publication refused; paid entitlement consumed once |
| `/booking/price/:id` client operation | `/api/quotes` | Weekly, cross-month and seasonal-price regressions |
| `/reservation/:id/confirmation` | Existing initialized requests can be submitted; new requests go through quote and request | Import preserves initialized state; API verifies submission/replay and cancellation releases dates |
| `/mon-profil/vacances`, `/mon-compte/reservations`, `/mon-compte/reservation/:id` | Traveler/owner listings and booking decisions | Browser request/confirmation and API ownership/concurrency checks |
| `/mon-compte/coups-de-coeur` | Idempotent add/remove and persona-owned list | API isolation/repetition and browser favorite |
| `/mon-compte/messages` | Booking conversations with participant authorization | Browser exchange and API intruder rejection |
| `/abonnement`, `/abonnement/confirm/:rentalId` | Hosted Stripe Checkout, discount validation, signed webhook settlement | Signed local Stripe events, replay and entitlement tests |
| `/admin` | Reference forms, account disable, rental editor, booking controls and subscription history | Role denial, reference management, account disable and self-disable rejection in API tests |
| Doctrine records and media paths | Read-only snapshot and transactional importer | Rehearsal checks IDs, cents, DST conversion, CDN paths, password login and repeat import |

## Deliberate changes

- The registration flow verifies new email addresses. Existing accounts retain login access through imported password hashes and verified status. Old sessions and reset links are invalidated at cutover.
- Booking rates use complete calendar-night counts and integer cents. The legacy modulo/week and month-relative date arithmetic were incorrect. Seasonal ranges include both rate boundary dates; a stay excludes its checkout date.
- New quotes display cleaning and linen fees once. Local tourist tax remains an explanation of any amount due separately. Historical booking totals are imported without recalculation.
- Booking requests reserve availability while awaiting an owner decision. Cancelled requests release it. A locked rental row serializes competing requests; a repeated idempotency key returns the original booking.
- Subscription settlement uses signed Stripe webhooks. Visiting a payment-return URL never grants an entitlement. The hosted checkout replaces the embedded Stripe payment form.
- Uploaded photos are converted to a bounded WebP image. Existing CDN URLs are preserved during import. New uploads use a persistent application volume; moving them to a separate object store requires an adapter and volume migration.
- The location editor supports coordinates and a map link. The former Google suggestion overlay is replaced by a geocode action and manual correction.
- Administration disables accounts instead of deleting related booking history. The admin rental editor retains the same subscription requirement for publication.
- Angular renders pages in the browser. The build includes static page metadata; it does not add server-side rendering for search engines.
- Existing PHP action URLs are not a supported external API. The new client uses `/api`; saved French page URLs remain valid. Old email links to state-changing actions need replacement during mail-queue drain before cutover.

## Cutover conditions

The test snapshot is representative, not a copy of production. Actual legacy data, paid Stripe sandbox checkout, Mailjet delivery, the deployed Google project and real CDN accessibility must pass the deployment rehearsal. The importer stops on unsupported hashes, ambiguous conversations, unknown notification targets, missing paid entitlement or overlapping active bookings. Resolve these records explicitly before switching traffic.
