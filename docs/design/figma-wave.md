# Figma implementation wave

Tracking: #18, with delivery tickets #20 through #23. The user requested both visual parity and missing functionality on 13 September 2026.

Reference: https://www.figma.com/design/WikwPpWtcfnZg68OZjWqPD/UI_PRESQUILE-2?node-id=0-1

## Layout and search, #20

Acceptance:
- Original brand assets, locally hosted Circular/AvantGarde fonts, blue `#006287`, pink `#f89da4`, warm white `#f7f5f2`, yellow `#d8a42e`.
- Home hero, rounded segmented search, property cards, wave sections and owner call to action follow frame 0:2.
- Authentication uses a keyboard-accessible modal with continuation after login. Account pages use the sidebar from 0:3547. The editor uses the split layout from 0:3833.
- Date selection populates the booking form. Cover selection persists the photo order. Existing booking locks, prices, ownership and publication rules remain intact.
- Search filters run before pagination. Dates exclude manual unavailability, booked/confirmed stays and dates outside owner preferences. Price filtering is explicitly the base nightly price, in integer cents, excluding seasonal rates and fees.
- All existing browser journeys and new controls pass on desktop and mobile. Inspect actual screenshots against Figma before recording regression baselines.

Reproduction before implementation: the preview displayed DM Sans/serif typography, brown controls and a dashboard grid. The new home and sidebar assertions failed on the old build. A five-person search returned a four-person rental; its regression test also failed before the filter implementation.

The Figma MOBILE page exposed no frames during inspection. Mobile layouts adapt the supplied desktop composition and are verified at phone widths; they are not claimed to match an unavailable mobile specification. Accessible labels remain visible on forms. Pink buttons use dark text for contrast. Current subscription amounts come from the database, without reproducing the expired 2022 promotion.

## Identity and settings, #21

Use the installed `@structure-ai/auth` provider resolver and OAuth HTTP handler. Google uses its built-in provider. Facebook needs a provider adapter. Persisted, one-use state and PKCE remain owned by Structure. Never link an existing account solely from an unverified provider email. Provider callbacks use application paths and preserve the requested continuation.

Avatar uploads reuse the validated media pipeline and enforce upload ownership. Add explicit notification preferences without suppressing transactional authentication emails. Add settings and subscription pages to the account navigation. Live OAuth verification needs client credentials and registered callback URLs. The user confirmed these are not ready and will supply configuration later.

Provider configuration is optional: `GOOGLE_OAUTH_CLIENT_ID`, `GOOGLE_OAUTH_CLIENT_SECRET`, `FACEBOOK_OAUTH_CLIENT_ID`, `FACEBOOK_OAUTH_CLIENT_SECRET`, and `FACEBOOK_GRAPH_VERSION` (default `v25.0`). Register `${APP_ORIGIN}/api/auth/oauth/google/callback` and `${APP_ORIGIN}/api/auth/oauth/facebook/callback` with their providers. Obtain Google credentials in the [Google Auth platform](https://console.cloud.google.com/auth/clients) and Facebook credentials in the [Meta app dashboard](https://developers.facebook.com/apps/). No existing vault location was supplied.

Facebook only requests public profile, because its account verification is not proof of email ownership. Such accounts initially have no contact email. Settings provides a separate, authenticated, single-use email verification flow with a 30-minute expiry. It does not silently merge accounts or create a password credential. Existing accounts can explicitly associate a social identity from Settings while signed in. OAuth state is bound to a SameSite HttpOnly browser cookie in addition to Structure's one-use state and PKCE. Callback failures return to a French login message.

The user also requested passkeys. WebAuthn registration, discoverable sign-in and credential removal are implemented using Structure, with virtual-authenticator browser coverage including replay and cross-account removal rejection. Passkeys use the hostname and origin of `APP_ORIGIN`; a passkey registered on one preview hostname cannot sign in on another. HTTPS is required except for localhost browser development. No provider credential is needed for passkeys.

## Conversations and reviews, #22

Preserve booking conversations and add a separate direct-contact conversation record. Direct contact does not create or lock a booking. Only participants may read or send messages. The unified conversation list must search on the server, paginate, and display safe counterpart names and avatars rather than private email addresses.

Reviews belong to completed stays. Enforce one review per booking, traveler ownership, an elapsed checkout date and an eligible booking status. Owners can respond, administrators can moderate, and public listings expose only published reviews. The UI provides ratings, written reviews and the owner's response.

## Tourism, events and editorial content, #23

Add a content module with public listing/detail queries and authorized editing commands. Events have dates, category, location, summary, body, image and organizer contact fields. Proposals collect explicit publication consent and enter moderation. Public queries exclude drafts and pending proposals. Keep submitter identity private; publish only consented contact information.

Use the same content module for restaurants, activities and legal/editorial pages, with a restricted kind and stable French routes. Render text safely, validate external URLs and enforce image ownership. The user will supply legal/editorial content later. Provide publishing controls and drafts; do not fabricate policies. Draft/unavailable content must be clear in the interface.

## Verification

Use a dedicated `_test` database for backend and browser suites. Never truncate the user's Contremaitre preview. Capture desktop and phone evidence for home, authentication, account, editor, listing, conversations and new content flows. Verify the final source through Contremaitre and open reviewable pull requests for the delivery tickets.
