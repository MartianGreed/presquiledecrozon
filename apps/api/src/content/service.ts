import type { SQL } from "bun";
import type {
  ContentEntry,
  ContentInput,
} from "../../../../packages/contracts/src/content";
import type { Page, Persona } from "../../../../packages/contracts/src/models";
import type { Connection } from "../database";
import { assert } from "../errors";

interface Row {
  id: string;
  version: number;
  status: ContentEntry["status"];
  author_id: string;
  data: ContentInput;
  submitter: ContentEntry["submitter"];
}
const present = (row: Row, admin = false): ContentEntry => ({
  id: row.id,
  version: row.version,
  status: row.status,
  content: {
    ...row.data,
    organizer:
      admin || row.data.contactConsent
        ? row.data.organizer
        : { name: "", email: "", phone: "", website: "" },
  },
  ...(admin ? { submitter: row.submitter } : {}),
});
function validDate(value: string) {
  return (
    /^\d{4}-\d{2}-\d{2}$/.test(value) &&
    Number.isFinite(Date.parse(value)) &&
    new Date(value).toISOString().slice(0, 10) === value
  );
}
export function content(sql: SQL) {
  async function validate(
    db: Connection,
    actor: Persona,
    input: ContentInput,
    authorId = actor.id,
  ) {
    assert(input.title.trim(), 400, "content", "Le titre est obligatoire.");
    if (input.organizer.website) {
      let valid = false;
      try {
        const url = new URL(input.organizer.website);
        valid =
          ["http:", "https:"].includes(url.protocol) &&
          !url.username &&
          !url.password;
      } catch {}
      assert(
        valid,
        400,
        "content",
        "Le site internet doit utiliser une adresse HTTP ou HTTPS.",
      );
    }
    assert(
      !input.organizer.email ||
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.organizer.email),
      400,
      "content",
      "L’adresse e-mail de l’organisateur est invalide.",
    );
    if (input.kind === "event") {
      assert(
        validDate(input.start) &&
          validDate(input.end) &&
          input.start <= input.end,
        400,
        "content",
        "Les dates de l’événement sont invalides.",
      );
      assert(
        [input.startTime, input.endTime].every((time) =>
          /^([01]\d|2[0-3]):[0-5]\d$/.test(time),
        ) &&
          (input.start !== input.end || input.startTime < input.endTime),
        400,
        "content",
        "Les horaires de l’événement sont invalides.",
      );
      assert(
        input.category.trim() && input.town.trim() && input.address.trim(),
        400,
        "content",
        "Précisez le thème, la commune et l’adresse.",
      );
    }
    for (const path of input.images) {
      assert(
        /^\/media\/[a-f0-9-]{36}\.webp$/.test(path),
        400,
        "image",
        "Ajoutez une image depuis le formulaire.",
      );
      const owned =
        await db`SELECT id FROM crozon_uploads WHERE path=${path} AND (persona_id=${actor.id} OR persona_id=${authorId})`;
      assert(
        owned.length,
        403,
        "forbidden",
        "Cette image ne vous appartient pas.",
      );
    }
  }
  function slug(input: ContentInput) {
    return (
      input.slug ||
      `${
        input.title
          .normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "")
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, "-")
          .replace(/^-|-$/g, "")
          .slice(0, 100) || "publication"
      }-${crypto.randomUUID().slice(0, 8)}`
    );
  }
  return {
    async list(
      page: number,
      filters: {
        kind: string;
        q: string;
        category: string;
        town: string;
        period: string;
      },
      actor?: Persona,
    ): Promise<Page<ContentEntry>> {
      if (actor)
        assert(
          actor.admin,
          403,
          "forbidden",
          "Accès réservé à l’administration.",
        );
      assert(
        ["event", "activity", "restaurant", "page", ""].includes(
          filters.kind,
        ) && ["all", "upcoming", "past"].includes(filters.period),
        400,
        "content",
        "Filtre invalide.",
      );
      const rows =
        await sql`SELECT *,count(*) OVER() AS total FROM crozon_content WHERE (${!!actor} OR status='published') AND (${filters.kind}='' OR kind=${filters.kind}) AND concat_ws(' ',data->>'title',data->>'summary',data->>'town') ILIKE ${`%${filters.q}%`} AND (${filters.category}='' OR data->>'category'=${filters.category}) AND (${filters.town}='' OR data->>'town'=${filters.town}) AND (${filters.period}='all' OR kind<>'event' OR (${filters.period}='upcoming' AND data->>'end'>=${new Date().toISOString().slice(0, 10)}) OR (${filters.period}='past' AND data->>'end'<${new Date().toISOString().slice(0, 10)})) ORDER BY updated_at DESC,id LIMIT 20 OFFSET ${(page - 1) * 20}`;
      return {
        items: rows.map((row: Row) => present(row, !!actor)),
        page,
        total: Number(rows[0]?.total ?? 0),
      };
    },
    async detail(slug: string) {
      const rows =
        await sql`SELECT * FROM crozon_content WHERE slug=${slug} AND status='published'`;
      assert(
        rows[0],
        404,
        "not_found",
        "Cette page n’est pas encore disponible.",
      );
      return present(rows[0]);
    },
    async propose(
      actor: Persona,
      input: ContentInput,
      submitter: NonNullable<ContentEntry["submitter"]>,
    ) {
      assert(
        input.kind === "event" && input.publicationConsent,
        400,
        "content",
        "Votre accord de publication est nécessaire.",
      );
      assert(
        input.end >= new Date().toISOString().slice(0, 10),
        400,
        "content",
        "Proposez un événement à venir.",
      );
      await validate(sql, actor, input);
      const data = { ...input, slug: slug({ ...input, slug: "" }) };
      const rows =
        await sql`INSERT INTO crozon_content(id,author_id,kind,slug,status,version,data,submitter) VALUES(${crypto.randomUUID()},${actor.id},'event',${data.slug},'pending',1,${data},${submitter}) RETURNING *`;
      return present(rows[0]);
    },
    async save(
      actor: Persona,
      id: string | undefined,
      input: ContentInput,
      status: ContentEntry["status"],
      version: number,
    ) {
      assert(
        actor.admin,
        403,
        "forbidden",
        "Accès réservé à l’administration.",
      );
      return sql.begin(async (db) => {
        const existing = id
          ? ((
              await db`SELECT * FROM crozon_content WHERE id=${id} FOR UPDATE`
            )[0] as Row | undefined)
          : undefined;
        assert(!id || existing, 404, "not_found", "Publication introuvable.");
        assert(
          existing ? version === existing.version : version === 0,
          409,
          "version",
          "Cette publication a changé. Rechargez-la avant d’enregistrer.",
        );
        await validate(db, actor, input, existing?.author_id);
        assert(
          status !== "published" ||
            (input.publicationConsent && input.body.trim()),
          400,
          "content",
          "Confirmez l’autorisation de publication et ajoutez le contenu.",
        );
        const data = { ...input, slug: slug(input) };
        const conflicts =
          await db`SELECT id FROM crozon_content WHERE slug=${data.slug} AND id<>${id ?? ""}`;
        assert(
          !conflicts.length,
          409,
          "slug",
          "Cette adresse de page est déjà utilisée.",
        );
        const rows = existing
          ? await db`UPDATE crozon_content SET kind=${data.kind},slug=${data.slug},status=${status},version=version+1,data=${data},updated_at=now() WHERE id=${existing.id} RETURNING *`
          : await db`INSERT INTO crozon_content(id,author_id,kind,slug,status,version,data,submitter) VALUES(${crypto.randomUUID()},${actor.id},${data.kind},${data.slug},${status},1,${data},${{ firstname: actor.profile?.firstname ?? "", lastname: actor.profile?.lastname ?? "", email: actor.email, phone: actor.profile?.cellphone ?? "" }}) RETURNING *`;
        return present(rows[0], true);
      });
    },
  };
}
