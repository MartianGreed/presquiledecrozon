import { SQL } from "bun";

const url = process.env.DATABASE_URL;
const email = process.argv[2]?.trim().toLowerCase();
if (!url || !email)
  throw new Error("Usage: DATABASE_URL=... bun scripts/promote-admin.ts email");
const sql = new SQL(url);
try {
  const rows =
    await sql`UPDATE crozon_personas SET admin=true WHERE email=${email} RETURNING id`;
  if (!rows.length)
    throw new Error(
      "The persona must register, verify and sign in before promotion.",
    );
  console.log("Administrator access granted.");
} finally {
  await sql.close();
}
