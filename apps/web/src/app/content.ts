import { CommonModule } from "@angular/common";
import { Component, effect, inject, input, signal } from "@angular/core";
import { FormsModule } from "@angular/forms";
import { RouterLink } from "@angular/router";
import {
  type ContentEntry,
  type ContentKind,
  contentPath,
  emptyContent,
} from "../../../../packages/contracts/src/content";
import type { Page } from "../../../../packages/contracts/src/models";
import { Api, ApiError } from "./api";

@Component({
  selector: "crozon-content",
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: "./content.html",
})
export class ContentPages {
  readonly api = inject(Api);
  readonly mode = input<"list" | "detail" | "proposal" | "admin">("list");
  readonly kind = input<ContentKind>("event");
  readonly slug = input("");
  readonly items = signal<ContentEntry[]>([]);
  readonly entry = signal<ContentEntry | null>(null);
  readonly loading = signal(false);
  readonly busy = signal(false);
  readonly error = signal("");
  readonly notice = signal("");
  readonly unavailable = signal(false);
  readonly submitted = signal(false);
  readonly total = signal(0);
  readonly path = contentPath;
  readonly kinds: Array<{ value: ContentKind; label: string }> = [
    { value: "event", label: "Événement" },
    { value: "activity", label: "Activité" },
    { value: "restaurant", label: "Restaurant" },
    { value: "page", label: "Page d’information" },
  ];
  draft = emptyContent();
  submitter = { firstname: "", lastname: "", email: "", phone: "" };
  id = "";
  version = 0;
  status: ContentEntry["status"] = "draft";
  page = 1;
  query = "";
  category = "";
  town = "";
  period = "all";
  filterKind = "";
  private generation = 0;
  get heading() {
    return {
      event: "Dernières actualités",
      activity: "Activités sur la presqu’île",
      restaurant: "Où se restaurer ?",
      page: "Informations pratiques",
    }[this.kind()];
  }
  constructor() {
    effect(() => {
      this.mode();
      this.kind();
      this.slug();
      this.draft = emptyContent(this.kind());
      void this.load(1);
    });
  }
  async load(page: number) {
    const generation = ++this.generation;
    this.loading.set(true);
    this.error.set("");
    this.unavailable.set(false);
    try {
      if (this.mode() === "detail") {
        const entry = await this.api.request<ContentEntry>(
          `/content/${encodeURIComponent(this.slug())}`,
        );
        if (generation === this.generation) this.entry.set(entry);
      } else if (this.mode() === "proposal") {
        const actor = this.api.persona();
        this.submitter = {
          firstname: actor?.profile?.firstname ?? "",
          lastname: actor?.profile?.lastname ?? "",
          email: actor?.email ?? "",
          phone: actor?.profile?.cellphone ?? "",
        };
      } else {
        const params = new URLSearchParams({
          page: String(page),
          kind: this.mode() === "admin" ? this.filterKind : this.kind(),
          q: this.query,
          category: this.category,
          town: this.town,
          period: this.period,
        });
        const result = await this.api.request<Page<ContentEntry>>(
          `${this.mode() === "admin" ? "/admin" : ""}/content?${params}`,
        );
        if (generation === this.generation) {
          this.items.set(result.items);
          this.total.set(result.total);
          this.page = page;
        }
      }
    } catch (error) {
      if (
        error instanceof ApiError &&
        error.status === 404 &&
        this.mode() === "detail"
      ) {
        this.unavailable.set(true);
        this.entry.set(null);
      } else
        this.error.set(
          error instanceof Error
            ? error.message
            : "Impossible de charger cette page.",
        );
    } finally {
      if (generation === this.generation) this.loading.set(false);
    }
  }
  edit(entry: ContentEntry) {
    this.id = entry.id;
    this.version = entry.version;
    this.status = entry.status;
    this.draft = structuredClone(entry.content);
    this.submitter = entry.submitter ?? this.submitter;
    this.notice.set("");
    document
      .getElementById("content-form")
      ?.scrollIntoView({ behavior: "smooth" });
  }
  newEntry() {
    this.id = "";
    this.version = 0;
    this.status = "draft";
    this.draft = emptyContent("page");
    this.submitter = { firstname: "", lastname: "", email: "", phone: "" };
  }
  async save() {
    if (this.busy()) return;
    this.busy.set(true);
    this.error.set("");
    this.notice.set("");
    try {
      if (this.mode() === "proposal") {
        await this.api.request("/events/proposals", "POST", {
          content: this.draft,
          submitter: this.submitter,
        });
        this.submitted.set(true);
        this.notice.set(
          "Votre proposition a été envoyée. Elle sera examinée avant publication.",
        );
      } else {
        const entry = await this.api.request<ContentEntry>(
          `/admin/content${this.id ? `/${this.id}` : ""}`,
          this.id ? "PUT" : "POST",
          { content: this.draft, status: this.status, version: this.version },
        );
        this.id = entry.id;
        this.version = entry.version;
        this.draft = structuredClone(entry.content);
        await this.load(1);
        this.notice.set(
          this.status === "published"
            ? "La publication est en ligne."
            : "Le brouillon est enregistré.",
        );
      }
    } catch (error) {
      this.error.set(
        error instanceof Error
          ? error.message
          : "Impossible d’enregistrer cette publication.",
      );
    } finally {
      this.busy.set(false);
    }
  }
  async upload(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length || this.busy()) return;
    this.busy.set(true);
    this.error.set("");
    try {
      if (input.files.length + this.draft.images.length > 12)
        throw new Error("Vous pouvez ajouter jusqu’à douze images.");
      for (const file of Array.from(input.files)) {
        const result = await this.api.upload<{ path: string }>("/media", file);
        this.draft.images.push(result.path);
      }
    } catch (error) {
      this.error.set(
        error instanceof Error
          ? error.message
          : "Impossible d’ajouter cette image.",
      );
    } finally {
      this.busy.set(false);
      input.value = "";
    }
  }
}
