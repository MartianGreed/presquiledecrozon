import { execFileSync } from "node:child_process";
export default function setup() {
  execFileSync("bun", ["scripts/e2e-db.ts"], { stdio: "pipe" });
}
