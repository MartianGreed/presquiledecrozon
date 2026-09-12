import type { SQL } from "bun";
import type { AppConfig } from "./config";
import { secret } from "./config";
export async function deliverOutbox(
  sql: SQL,
  config: AppConfig,
  signal?: AbortSignal,
): Promise<void> {
  if (config.mailMode === "outbox") return;
  const key = secret(config.mailjetKey);
  const password = secret(config.mailjetSecret);
  if (!key || !password)
    throw new Error("Mailjet configuration is incomplete.");
  await sql.begin(async (db) => {
    const rows =
      await db`SELECT * FROM crozon_outbox WHERE sent_at IS NULL AND attempts<5 AND available_at<=now() ORDER BY created_at LIMIT 10 FOR UPDATE SKIP LOCKED`;
    for (const row of rows) {
      signal?.throwIfAborted();
      try {
        const response = await fetch("https://api.mailjet.com/v3.1/send", {
          method: "POST",
          headers: {
            authorization: `Basic ${btoa(`${key}:${password}`)}`,
            "content-type": "application/json",
          },
          body: JSON.stringify({
            Messages: [
              {
                From: {
                  Email: config.emailSender,
                  Name: "Presqu’île de Crozon",
                },
                To: [{ Email: row.recipient }],
                Subject: row.subject,
                TextPart: row.body,
                CustomID: row.id,
              },
            ],
          }),
          signal: signal
            ? AbortSignal.any([signal, AbortSignal.timeout(5000)])
            : AbortSignal.timeout(5000),
        });
        if (!response.ok) throw new Error("Mail delivery refused.");
        await db`UPDATE crozon_outbox SET sent_at=now(),attempts=attempts+1 WHERE id=${row.id}`;
      } catch {
        signal?.throwIfAborted();
        await db`UPDATE crozon_outbox SET attempts=attempts+1,available_at=now()+interval '1 minute'*power(2,attempts) WHERE id=${row.id}`;
        console.error(
          JSON.stringify({ event: "email.retry", attempt: row.attempts + 1 }),
        );
      }
    }
  });
}
