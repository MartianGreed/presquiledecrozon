import { migrate as migrateAuth } from "@structure-ai/auth-pg";
import type { SQL } from "bun";
import { Effect } from "effect";
import { loadConfig } from "./config";
import { connect } from "./database";
export async function migrate(sql: SQL): Promise<void> {
  await Effect.runPromise(migrateAuth(sql));
  await sql.begin(async (db) => {
    await db`SELECT pg_advisory_xact_lock(720926)`;
    await db.unsafe(`
 CREATE TABLE IF NOT EXISTS crozon_schema (version integer PRIMARY KEY, applied_at timestamptz NOT NULL DEFAULT now());
 CREATE TABLE IF NOT EXISTS crozon_personas (id text PRIMARY KEY, email text NOT NULL UNIQUE, admin boolean NOT NULL DEFAULT false, profile jsonb, disabled boolean NOT NULL DEFAULT false);
 CREATE TABLE IF NOT EXISTS crozon_rentals (id text PRIMARY KEY, owner_id text NOT NULL REFERENCES crozon_personas(id), slug text NOT NULL UNIQUE, status text NOT NULL, version integer NOT NULL, data jsonb NOT NULL);
 CREATE INDEX IF NOT EXISTS crozon_rentals_owner ON crozon_rentals(owner_id);
 CREATE INDEX IF NOT EXISTS crozon_rentals_status ON crozon_rentals(status);
 CREATE TABLE IF NOT EXISTS crozon_favorites (persona_id text NOT NULL REFERENCES crozon_personas(id), rental_id text NOT NULL REFERENCES crozon_rentals(id), PRIMARY KEY(persona_id,rental_id));
 CREATE TABLE IF NOT EXISTS crozon_bookings (id text PRIMARY KEY, rental_id text NOT NULL REFERENCES crozon_rentals(id), persona_id text NOT NULL REFERENCES crozon_personas(id), start_date date NOT NULL, end_date date NOT NULL CHECK(end_date>start_date), status text NOT NULL, data jsonb NOT NULL);
 CREATE INDEX IF NOT EXISTS crozon_bookings_availability ON crozon_bookings(rental_id,start_date,end_date) WHERE status IN ('booked','confirmed');
 CREATE TABLE IF NOT EXISTS crozon_messages (id text PRIMARY KEY, booking_id text NOT NULL REFERENCES crozon_bookings(id), persona_id text NOT NULL REFERENCES crozon_personas(id), created_at timestamptz NOT NULL DEFAULT now(), data jsonb NOT NULL);
 CREATE INDEX IF NOT EXISTS crozon_messages_booking ON crozon_messages(booking_id,created_at);
 CREATE TABLE IF NOT EXISTS crozon_notifications (id text PRIMARY KEY, persona_id text NOT NULL REFERENCES crozon_personas(id), data jsonb NOT NULL, created_at timestamptz NOT NULL DEFAULT now());
 CREATE TABLE IF NOT EXISTS crozon_reference (kind text NOT NULL, id text NOT NULL, data jsonb NOT NULL, PRIMARY KEY(kind,id));
 CREATE TABLE IF NOT EXISTS crozon_subscriptions (id text PRIMARY KEY,rental_id text NOT NULL REFERENCES crozon_rentals(id),persona_id text NOT NULL REFERENCES crozon_personas(id),payment_intent_id text UNIQUE,data jsonb NOT NULL);
 CREATE TABLE IF NOT EXISTS crozon_payment_events (id text PRIMARY KEY,created_at timestamptz NOT NULL DEFAULT now());
 CREATE TABLE IF NOT EXISTS crozon_idempotency (persona_id text NOT NULL, key text NOT NULL, fingerprint text NOT NULL, result jsonb NOT NULL,created_at timestamptz NOT NULL DEFAULT now(), PRIMARY KEY(persona_id,key));
 CREATE TABLE IF NOT EXISTS crozon_uploads (id text PRIMARY KEY,persona_id text NOT NULL REFERENCES crozon_personas(id),path text NOT NULL UNIQUE,created_at timestamptz NOT NULL DEFAULT now());
 CREATE TABLE IF NOT EXISTS crozon_rate_limits (key text PRIMARY KEY,points integer NOT NULL,expires_at timestamptz NOT NULL);
 CREATE TABLE IF NOT EXISTS crozon_outbox (id text PRIMARY KEY,recipient text NOT NULL,subject text NOT NULL,body text NOT NULL, attempts integer NOT NULL DEFAULT 0,available_at timestamptz NOT NULL DEFAULT now(),sent_at timestamptz,created_at timestamptz NOT NULL DEFAULT now());
 CREATE TABLE IF NOT EXISTS crozon_legacy_archive(source_table text NOT NULL,source_key text NOT NULL,data jsonb NOT NULL,PRIMARY KEY(source_table,source_key));
 CREATE TABLE IF NOT EXISTS crozon_imports(id text PRIMARY KEY,checksum text NOT NULL,counts jsonb NOT NULL,created_at timestamptz NOT NULL DEFAULT now());
 INSERT INTO crozon_schema(version) VALUES(1) ON CONFLICT DO NOTHING;
 ALTER TABLE crozon_personas ADD COLUMN IF NOT EXISTS preferences jsonb NOT NULL DEFAULT '{"emailNotifications":true}';
 ALTER TABLE crozon_personas ALTER COLUMN email DROP NOT NULL;
 CREATE TABLE IF NOT EXISTS crozon_contact_tokens(hash text PRIMARY KEY,persona_id text NOT NULL REFERENCES crozon_personas(id),email text NOT NULL,expires_at timestamptz NOT NULL);
 INSERT INTO crozon_schema(version) VALUES(2) ON CONFLICT DO NOTHING;
 `);
  });
}
if (import.meta.main) {
  const sql = connect(await loadConfig());
  try {
    await migrate(sql);
    console.log("Database schema is current.");
  } finally {
    await sql.close();
  }
}
