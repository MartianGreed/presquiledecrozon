# Data migration and rollback

The importer writes only the new `crozon_` and Structure `auth_` tables. It preserves the original snapshot in `crozon_legacy_archive`, including fields that are not part of a new read model. The archive contains personal data and password hashes. Restrict its database privileges and include it in the encrypted backup policy. Never commit a snapshot.

## Rehearse

1. Restore a recent legacy PostgreSQL backup into an isolated database. Keep outbound mail and payments disabled in that environment. Identify the original timestamp timezone and media root from the deployed configuration.
2. Create an empty destination database and run `bun run db:migrate` with its `DATABASE_URL`.
3. Export with a source credential restricted to `SELECT`:

```sh
export LEGACY_DATABASE_URL='postgres://.../isolated_legacy'
export LEGACY_TIME_ZONE='Europe/Paris'
export LEGACY_MEDIA_BASE_URL='https://your-cdn.example/rental/'
bun run legacy:export /secure/snapshots/crozon.json
```

The command reads a repeatable, read-only transaction. It refuses an existing output path and creates the file with mode 0600. It exports at most 250,000 rows, in 1,000-row batches. Larger databases require partitioning before a cutover.

4. Set `DATABASE_URL` to the empty destination and import:

```sh
bun run legacy:import /secure/snapshots/crozon.json
```

The import uses one transaction and an advisory lock. Foreign keys verify ownership and references. A successful snapshot records its checksum and source-table counts. Reimporting the same bytes is a no-op; a different snapshot is refused. The target must contain no personas. Rehearse again in a fresh database after changing a snapshot.

5. Compare account, rental, booking, subscription, favorite and message counts. Check `crozon_imports.counts` against the exported counts, and `crozon_legacy_archive` against all original rows. Inspect every import rejection, not just aggregate counts.
6. Verify a representative existing account login, rental URL, room/bed configuration, seasonal rate, photo, historical booking total, conversation and active subscription. Confirm that no unexpected listing has become public. Check cold-start access to the original CDN.
7. Dump and restore the new database into another isolated database and repeat the application checks. Back up and restore the upload volume too.

Dates with a time use the explicit source timezone. Historic booking totals stay unchanged. PostgreSQL's original `price` columns already contain cents; booking price JSON contains euro amounts, which are converted to cents. Media paths retain Vich's `Y/d/m` ordering, not `Y/m/d`.

Unsupported data causes the transaction to fail. The importer does not guess how to attach orphaned conversations, fulfill unpaid subscriptions or reconcile overlapping reservations. Original rows remain available in the source snapshot for a deliberate repair and another rehearsal.

## Switch traffic

- Disable legacy writes and drain Symfony Messenger, pending emails and payment confirmations. Old payment intents must settle on the legacy application before the final snapshot, or receive an explicit reconciliation.
- Take the final database and upload backups. Export and import into a fresh destination. Repeat count, media, login, subscription and reservation checks.
- Deploy the already-built container. Configure HTTPS origin, provider credentials, Stripe webhook and the persistent upload volume. Keep mail delivery disabled until smoke checks pass.
- Switch the proxy to the Bun listener and check `/health/ready`, public pages and authenticated journeys. Enable mail delivery. Monitor booking conflicts, errors and failed mail attempts.

## Rollback

Before the new application accepts writes, route traffic back to the unchanged legacy deployment and database. The previous code revision is `5c62d87`; use its existing deployment artifact rather than running the archive directory.

After the new application accepts writes, routing back would lose visibility of new bookings, messages, profiles and subscriptions. Freeze writes, preserve a new-system backup, and reconcile these records before returning to PHP. Prefer rolling forward with a repaired Bun artifact. There is no automatic reverse importer and no safe claim that a code rollback also rolls back business data.
