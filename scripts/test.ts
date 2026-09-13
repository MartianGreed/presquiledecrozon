for (const key of ["TEST_DATABASE_URL", "MIGRATION_TEST_DATABASE_URL"]) {
  const value = process.env[key];
  if (!value || !new URL(value).pathname.endsWith("_test"))
    throw new Error(
      `${key} must point to a dedicated database ending in _test. The tests truncate it.`,
    );
}
const child = Bun.spawn([process.execPath, "test", "apps/api/test"], {
  stdout: "inherit",
  stderr: "inherit",
  env: process.env,
});
process.exit(await child.exited);

export {};
