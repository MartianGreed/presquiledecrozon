import { mkdir } from "node:fs/promises";
import { join } from "node:path";
import type { SQL } from "bun";
import sharp from "sharp";
import type { Persona } from "../../../packages/contracts/src/models";
import { assert, invalid, Problem } from "./errors";
export async function upload(
  sql: SQL,
  actor: Persona,
  request: Request,
  directory: string,
): Promise<{ path: string }> {
  const type = request.headers.get("content-type") ?? "";
  assert(
    ["image/jpeg", "image/png", "image/webp"].includes(type),
    415,
    "image",
    "Utilisez une image JPEG, PNG ou WebP.",
  );
  const bytes = await readBounded(request, 8 * 1024 * 1024);
  let buffer: Buffer;
  try {
    buffer = await sharp(bytes, { limitInputPixels: 40_000_000 })
      .rotate()
      .resize({
        width: 1920,
        height: 1440,
        fit: "inside",
        withoutEnlargement: true,
      })
      .webp({ quality: 85 })
      .toBuffer();
  } catch {
    throw invalid(
      "Cette image est invalide ou dépasse les dimensions autorisées.",
    );
  }
  const id = crypto.randomUUID();
  const path = `/media/${id}.webp`;
  await mkdir(directory, { recursive: true });
  await Bun.write(join(directory, `${id}.webp`), new Uint8Array(buffer));
  await sql`INSERT INTO crozon_uploads(id,persona_id,path) VALUES(${id},${actor.id},${path})`;
  return { path };
}
export async function readBounded(
  request: Request,
  limit: number,
): Promise<Uint8Array> {
  assert(
    Number(request.headers.get("content-length") ?? 0) <= limit,
    413,
    "body_size",
    "Le fichier ou message est trop volumineux.",
  );
  const reader = request.body?.getReader();
  if (!reader) return new Uint8Array();
  const chunks: Uint8Array[] = [];
  let size = 0;
  const deadline = Date.now() + 15000;
  try {
    for (;;) {
      const remaining = deadline - Date.now();
      if (remaining <= 0)
        throw new Problem(408, "timeout", "La réception du fichier a expiré.");
      let timer: ReturnType<typeof setTimeout> | undefined;
      const result = await Promise.race([
        reader.read(),
        new Promise<never>((_, reject) => {
          timer = setTimeout(
            () =>
              reject(
                new Problem(
                  408,
                  "timeout",
                  "La réception du fichier a expiré.",
                ),
              ),
            remaining,
          );
        }),
      ]).finally(() => clearTimeout(timer));
      if (result.done) break;
      size += result.value.length;
      assert(
        size <= limit,
        413,
        "body_size",
        "Le fichier ou message est trop volumineux.",
      );
      chunks.push(result.value);
    }
  } finally {
    await reader.cancel();
  }
  const output = new Uint8Array(size);
  let offset = 0;
  for (const chunk of chunks) {
    output.set(chunk, offset);
    offset += chunk.length;
  }
  return output;
}
