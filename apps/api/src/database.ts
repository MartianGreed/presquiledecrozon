import { SQL, type TransactionSQL } from "bun";
import { Redacted } from "effect";
import type { AppConfig } from "./config";
export type Transaction = TransactionSQL;
export type Connection = SQL | Transaction;
export function connect(config: AppConfig): SQL {
  return new SQL({
    url: Redacted.value(config.databaseUrl),
    max: 10,
    idleTimeout: 20,
    connectionTimeout: 5,
    maxLifetime: 3600,
  });
}
export async function event(
  db: Connection,
  personaId: string,
  message: string,
  href: string,
): Promise<void> {
  const id = crypto.randomUUID();
  await db`INSERT INTO crozon_notifications (id,persona_id,data) VALUES (${id},${personaId},${{ id, personaId, message, href, read: false, createdAt: new Date().toISOString() }})`;
  await db`INSERT INTO crozon_outbox (id,recipient,subject,body) SELECT ${crypto.randomUUID()},email,${"Presqu’île de Crozon"},${message} FROM crozon_personas WHERE id=${personaId} AND email IS NOT NULL AND email<>'' AND coalesce((preferences->>'emailNotifications')::boolean,true)`;
}
