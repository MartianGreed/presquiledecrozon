import { writeFile } from "node:fs/promises";
import { SQL } from "bun";
import { importLegacy, type Snapshot } from "../apps/api/src/migration/legacy";

const [action, file] = process.argv.slice(2);
if (!file || !["export", "import"].includes(action ?? ""))
  throw new Error(
    "Usage: bun scripts/legacy-data.ts export|import /secure/path/snapshot.json",
  );
if (action === "export") {
  const url = process.env.LEGACY_DATABASE_URL;
  const mediaBaseUrl = process.env.LEGACY_MEDIA_BASE_URL;
  const sourceTimeZone = process.env.LEGACY_TIME_ZONE;
  if (!url || !mediaBaseUrl || !sourceTimeZone)
    throw new Error(
      "LEGACY_DATABASE_URL, LEGACY_MEDIA_BASE_URL and LEGACY_TIME_ZONE are required.",
    );
  const sql = new SQL(url);
  try {
    const tables: Snapshot["tables"] = {};
    await sql.begin("ISOLATION LEVEL REPEATABLE READ READ ONLY", async (db) => {
      const names =
        await db`SELECT tablename FROM pg_tables WHERE schemaname='public' AND tablename NOT LIKE 'crozon_%' AND tablename NOT LIKE 'auth_%' ORDER BY tablename`;
      let total = 0;
      for (const { tablename } of names) {
        tables[tablename] = [];
        for (let offset = 0; ; offset += 1000) {
          const rows =
            await db`SELECT to_jsonb(t) AS data FROM ${db(tablename)} t ORDER BY to_jsonb(t)::text LIMIT 1000 OFFSET ${offset}`;
          total += rows.length;
          if (total > 250000)
            throw new Error(
              "Snapshot exceeds 250,000 rows; use a partitioned migration.",
            );
          tables[tablename]!.push(
            ...rows.map((r: { data: Record<string, unknown> }) => r.data),
          );
          if (rows.length < 1000) break;
        }
      }
    });
    const snapshot: Snapshot = {
      version: 1,
      sourceTimeZone,
      mediaBaseUrl,
      tables,
    };
    await writeFile(file, JSON.stringify(snapshot), {
      mode: 0o600,
      flag: "wx",
    });
    console.log(
      JSON.stringify({
        exported: Object.fromEntries(
          Object.entries(tables).map(([name, rows]) => [name, rows.length]),
        ),
      }),
    );
  } finally {
    await sql.close();
  }
} else {
  const url = process.env.DATABASE_URL;
  if (!url) throw new Error("DATABASE_URL is required.");
  const snapshot = (await Bun.file(file).json()) as Snapshot;
  const sql = new SQL(url);
  try {
    console.log(
      JSON.stringify({ imported: await importLegacy(sql, snapshot) }),
    );
  } finally {
    await sql.close();
  }
}
