import { CommonModule } from "@angular/common";
import { Component, DestroyRef, inject, signal } from "@angular/core";
import { takeUntilDestroyed } from "@angular/core/rxjs-interop";
import { FormsModule } from "@angular/forms";
import { NavigationEnd, Router, RouterLink } from "@angular/router";
import { filter } from "rxjs";
import {
  type Booking,
  emptyRental,
  type Message,
  type Notification,
  type Page,
  type Plan,
  type Profile,
  type Quote,
  type Rental,
  type RentalInput,
  rentalSteps,
} from "../../../../packages/contracts/src/models";
import { Api } from "./api";
import { AuthDialog } from "./auth-dialog";
import { emptySearch, RentalSearch, type SearchFields } from "./rental-search";
import { StayCalendar } from "./stay-calendar";
@Component({
  selector: "crozon-page",
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterLink,
    AuthDialog,
    StayCalendar,
    RentalSearch,
  ],
  templateUrl: "./page.html",
})
export class PageComponent {
  readonly api = inject(Api);
  readonly router = inject(Router);
  private readonly destroy = inject(DestroyRef);
  readonly page = signal("home");
  readonly loading = signal(true);
  readonly loadFailed = signal(false);
  readonly busy = signal(false);
  readonly error = signal("");
  readonly notice = signal("");
  readonly rentals = signal<Rental[]>([]);
  readonly rental = signal<Rental | null>(null);
  readonly bookings = signal<Booking[]>([]);
  readonly booking = signal<Booking | null>(null);
  readonly messages = signal<Message[]>([]);
  readonly notifications = signal<Notification[]>([]);
  readonly plans = signal<Plan[]>([]);
  readonly quote = signal<Quote | null>(null);
  readonly favorites = signal<string[]>([]);
  readonly adminRows = signal<Record<string, unknown>[]>([]);
  readonly steps = rentalSteps;
  readonly stepLabels: Record<string, string> = {
    configuration: "Le logement",
    equipements: "Équipements",
    description: "Description",
    adresse: "Adresse",
    carte: "Localisation",
    photos: "Photos",
    disponibilites: "Préférences",
    calendrier: "Calendrier",
    taxes: "Taxes et services",
    tarifs: "Tarifs",
    conditions: "Conditions",
  };
  readonly accountLinks = [
    { label: "Vue d’ensemble", path: "/mon-compte", pages: ["account"] },
    { label: "Messagerie", path: "/mon-compte/messages", pages: ["messages"] },
    { label: "Mes vacances", path: "/mon-profil/vacances", pages: [] },
    {
      label: "Réservations reçues",
      path: "/mon-compte/reservations",
      pages: [],
    },
    { label: "Annonces", path: "/mon-compte/annonces", pages: ["my-rentals"] },
    {
      label: "Coups de cœur",
      path: "/mon-compte/coups-de-coeur",
      pages: ["favorites"],
    },
    { label: "Profil", path: "/mon-compte/informations", pages: ["profile"] },
  ];
  readonly equipmentChoices = [
    "Wi-Fi",
    "Cheminée",
    "Lave-linge",
    "Climatisation",
    "Parking",
    "Lave-vaisselle",
    "Aspirateur",
    "Jardin",
    "Fer à repasser",
    "Barbecue",
    "Micro-ondes",
    "Sèche-cheveux",
  ];
  extraEquipment = "";
  get customEquipment() {
    return this.equipmentText
      .split("\n")
      .filter((item) => item && !this.equipmentChoices.includes(item));
  }
  addEquipment() {
    const value = this.extraEquipment.trim();
    if (value && !this.hasEquipment(value)) this.toggleEquipment(value);
    this.extraEquipment = "";
  }
  showPassword = false;
  get isAuth() {
    return ["login", "register", "forgot", "reset", "verify"].includes(
      this.page(),
    );
  }
  get isAccount() {
    return [
      "account",
      "profile",
      "favorites",
      "my-rentals",
      "bookings",
      "booking",
      "messages",
      "subscription",
      "payment-confirm",
    ].includes(this.page());
  }
  get progress() {
    return Math.round(
      ((this.rental()?.completedSteps.length ?? 0) / this.steps.length) * 100,
    );
  }
  get editorGroup() {
    return this.steps.indexOf(this.step as (typeof this.steps)[number]) < 6
      ? 1
      : 2;
  }
  get previousStep() {
    return this.steps[
      this.steps.indexOf(this.step as (typeof this.steps)[number]) - 1
    ];
  }
  closeAuth() {
    void this.router.navigateByUrl("/");
  }
  hasEquipment(value: string) {
    return this.equipmentText.split("\n").includes(value);
  }
  toggleEquipment(value: string) {
    const values = this.equipmentText.split("\n").filter(Boolean);
    this.equipmentText = (
      values.includes(value)
        ? values.filter((item) => item !== value)
        : [...values, value]
    ).join("\n");
  }
  coverPhoto(index: number) {
    const photo = this.editor.photos.splice(index, 1)[0];
    if (photo) this.editor.photos.unshift(photo);
  }
  email = "";
  password = "";
  confirmPassword = "";
  search = "";
  searchFields = emptySearch();
  message = "";
  start = "";
  end = "";
  peopleCount = 2;
  pageNumber = 1;
  total = 0;
  conversationId = "";
  conversationPage = 1;
  conversationTotal = 0;
  messageTotal = 0;
  step = "configuration";
  adminKind = "personas";
  referenceDraft = {
    id: crypto.randomUUID(),
    name: "",
    code: "",
    type: "percent",
    amount: 0,
    months: 12,
    active: true,
    expiresAt: "",
    maxUses: 100,
    uses: 0,
    payeeId: "",
  };
  discountCode = "";
  planId = "";
  rentalId = "";
  equipmentText = "";
  linensText = "";
  rulesText = "";
  bedroomsCount = 1;
  editor: RentalInput = emptyRental();
  profile: Profile = {
    firstname: "",
    lastname: "",
    cellphone: "",
    description: "",
    preferredLanguage: "fr_FR",
    birthdate: "",
    gender: "",
  };
  private generation = 0;
  private bookingKey = crypto.randomUUID();
  constructor() {
    this.router.events
      .pipe(
        filter((e) => e instanceof NavigationEnd),
        takeUntilDestroyed(this.destroy),
      )
      .subscribe(() => void this.load());
    void this.load();
  }
  private url() {
    return new URL(this.router.url, location.origin);
  }
  private async load() {
    const generation = ++this.generation;
    const url = this.url();
    const path = url.pathname;
    this.loading.set(true);
    this.loadFailed.set(false);
    this.error.set("");
    this.notice.set("");
    this.quote.set(null);
    this.pageNumber = Math.max(1, Number(url.searchParams.get("page") ?? 1));
    try {
      let page = "home";
      if (path === "/annonces") page = "catalog";
      else if (
        path.startsWith("/annonce/") ||
        path === "/previsualisation/annonce"
      )
        page = "detail";
      else if (path === "/login") page = "login";
      else if (path === "/creer-mon-compte") page = "register";
      else if (path === "/verification-email") page = "verify";
      else if (path === "/reinitialisation/mot-de-passe") page = "reset";
      else if (path.startsWith("/reinitialisation-mot-de-passe"))
        page = "forgot";
      else if (path.startsWith("/deposez-votre-annonce/"))
        page = path.endsWith("/termine") ? "rental-done" : "editor";
      else if (path === "/mon-compte/informations") page = "profile";
      else if (path === "/mon-compte/coups-de-coeur") page = "favorites";
      else if (path === "/mon-compte/annonces") page = "my-rentals";
      else if (
        path === "/mon-compte/reservations" ||
        path === "/mon-profil/vacances"
      )
        page = "bookings";
      else if (
        path.startsWith("/mon-compte/reservation/") ||
        path.startsWith("/reservation/")
      )
        page = "booking";
      else if (path === "/mon-compte/messages") page = "messages";
      else if (path.startsWith("/abonnement"))
        page = path.includes("/confirm/") ? "payment-confirm" : "subscription";
      else if (path === "/admin") page = "admin";
      else if (path === "/mon-compte") page = "account";
      else if (path !== "/") page = "not-found";
      this.page.set(page);
      const protectedPages = [
        "editor",
        "rental-done",
        "profile",
        "favorites",
        "my-rentals",
        "bookings",
        "booking",
        "messages",
        "subscription",
        "payment-confirm",
        "account",
        "admin",
      ];
      if (protectedPages.includes(page) && !(await this.api.me())) {
        await this.router.navigate(["/login"], {
          queryParams: { next: path + url.search },
        });
        return;
      }
      if (["home", "catalog", "detail"].includes(page)) await this.api.me();
      if (generation !== this.generation) return;
      if (["home", "catalog", "my-rentals"].includes(page)) {
        this.search = url.searchParams.get("q") ?? "";
        this.searchFields = {
          q: this.search,
          start: url.searchParams.get("start") ?? "",
          end: url.searchParams.get("end") ?? "",
          people: url.searchParams.get("people") ?? "",
          type: url.searchParams.get("type") ?? "",
          maxPrice: url.searchParams.has("maxPrice")
            ? String(Number(url.searchParams.get("maxPrice")) / 100)
            : "",
        };
        const result = await this.api.request<Page<Rental>>(
          `${page === "my-rentals" ? "/my" : ""}/rentals?page=${this.pageNumber}&q=${encodeURIComponent(this.search)}&${new URLSearchParams(Object.fromEntries(["start", "end", "people", "type", "maxPrice"].map((key) => [key, url.searchParams.get(key) ?? ""]))).toString()}`,
        );
        if (generation !== this.generation) return;
        this.rentals.set(result.items);
        this.total = result.total;
      }
      if (page === "home")
        this.plans.set(await this.api.request<Plan[]>("/plans"));
      if (page === "detail") {
        if (url.searchParams.has("start"))
          this.start = url.searchParams.get("start") ?? "";
        if (url.searchParams.has("end"))
          this.end = url.searchParams.get("end") ?? "";
        if (url.searchParams.has("people"))
          this.peopleCount = Number(url.searchParams.get("people")) || 2;
        const slug =
          path === "/previsualisation/annonce"
            ? url.searchParams.get("rental_id")
            : decodeURIComponent(path.split("/")[2]);
        const rental = await this.api.request<Rental>(`/rentals/${slug}`);
        if (generation !== this.generation) return;
        this.rental.set(rental);
        if (this.api.persona())
          this.favorites.set(
            (await this.api.request<Rental[]>("/favorites")).map((r) => r.id),
          );
      }
      if (
        ["home", "catalog", "favorites"].includes(page) &&
        this.api.persona()
      ) {
        const saved = await this.api.request<Rental[]>("/favorites");
        if (generation !== this.generation) return;
        this.favorites.set(saved.map((item) => item.id));
        if (page === "favorites") this.rentals.set(saved);
      }
      if (page === "profile")
        this.profile = { ...(this.api.persona()?.profile ?? this.profile) };
      if (page === "editor") {
        this.step = path.split("/")[2] ?? "configuration";
        if (!this.steps.includes(this.step as (typeof rentalSteps)[number]))
          this.step = "configuration";
        const id = url.searchParams.get("rental_id");
        let rental: Rental;
        if (id) rental = await this.api.request<Rental>(`/rentals/${id}`);
        else {
          const existing = await this.api.request<Page<Rental>>("/my/rentals");
          const draft = existing.items.find((r) => r.status === "draft");
          rental =
            draft ?? (await this.api.request<Rental>("/rentals", "POST", {}));
          const next =
            this.steps.find((s) => !rental.completedSteps.includes(s)) ??
            "configuration";
          await this.router.navigate(["/deposez-votre-annonce", next], {
            queryParams: { rental_id: rental.id },
          });
          return;
        }
        this.rental.set(rental);
        this.editor = this.inputOf(rental);
        this.equipmentText = rental.equipment.join("\n");
        this.linensText = rental.linens.join("\n");
        this.rulesText = rental.rules.join("\n");
      }
      if (page === "bookings") {
        const result = await this.api.request<Page<Booking>>(
          `/bookings?page=${this.pageNumber}&owner=${path === "/mon-compte/reservations"}`,
        );
        this.bookings.set(result.items);
        this.total = result.total;
      }
      if (page === "booking") {
        const id = path.startsWith("/reservation/")
          ? path.split("/")[2]
          : path.split("/")[3];
        this.booking.set(await this.api.request<Booking>(`/bookings/${id}`));
      }
      if (page === "messages") {
        this.conversationPage = Math.max(
          1,
          Number(url.searchParams.get("conversationsPage") ?? 1),
        );
        const result = await this.api.request<Page<Booking>>(
          `/conversations?page=${this.conversationPage}`,
        );
        this.bookings.set(result.items);
        this.conversationTotal = result.total;
        this.conversationId =
          url.searchParams.get("conversation") ?? result.items[0]?.id ?? "";
        this.messages.set([]);
        this.messageTotal = 0;
        if (this.conversationId) {
          const messages = await this.api.request<Page<Message>>(
            `/conversations/${this.conversationId}/messages?page=${this.pageNumber}`,
          );
          this.messages.set(messages.items);
          this.messageTotal = messages.total;
        }
      }
      if (page === "account")
        this.notifications.set(
          await this.api.request<Notification[]>("/notifications"),
        );
      if (page === "subscription") {
        this.rentalId = url.searchParams.get("rental_id") ?? "";
        this.plans.set(await this.api.request<Plan[]>("/plans"));
        this.planId = this.plans()[0]?.id ?? "";
        if (this.rentalId)
          this.rental.set(
            await this.api.request<Rental>(`/rentals/${this.rentalId}`),
          );
      }
      if (page === "admin") {
        const rows = await this.api.request<Record<string, unknown>[]>(
          `/admin/${this.adminKind}?page=${this.pageNumber}`,
        );
        if (generation !== this.generation) return;
        this.adminRows.set(rows);
      }
    } catch (error) {
      if (generation !== this.generation) return;
      this.loadFailed.set(true);
      this.error.set(
        error instanceof Error
          ? error.message
          : "Impossible de charger la page.",
      );
    } finally {
      if (generation === this.generation) this.loading.set(false);
    }
  }
  async action(work: () => Promise<void>) {
    if (this.busy()) return;
    this.busy.set(true);
    this.error.set("");
    this.notice.set("");
    try {
      await work();
    } catch (error) {
      this.error.set(
        error instanceof Error ? error.message : "Une erreur est survenue.",
      );
    } finally {
      this.busy.set(false);
    }
  }
  async auth() {
    await this.action(async () => {
      const page = this.page();
      if (page === "login") {
        await this.api.request("/auth/sign-in/password", "POST", {
          email: this.email,
          password: this.password,
        });
        await this.api.me();
        const next = this.url().searchParams.get("next");
        await this.router.navigateByUrl(
          next?.startsWith("/") && !next.startsWith("//")
            ? next
            : "/mon-compte",
        );
      } else if (page === "register") {
        if (this.password !== this.confirmPassword)
          throw new Error("Les mots de passe ne correspondent pas.");
        await this.api.request("/auth/register/password", "POST", {
          email: this.email,
          password: this.password,
        });
        this.notice.set(
          "Consultez votre boîte e-mail pour vérifier votre adresse avant de vous connecter.",
        );
      } else if (page === "forgot") {
        await this.api.request("/auth/password/reset/request", "POST", {
          email: this.email,
        });
        this.notice.set(
          "Si un compte correspond à cette adresse, vous recevrez un lien de réinitialisation.",
        );
      } else if (page === "verify") {
        await this.api.request("/auth/verify-email", "POST", {
          token: this.url().searchParams.get("token"),
        });
        this.notice.set(
          "Votre adresse est vérifiée. Vous pouvez vous connecter.",
        );
      } else if (page === "reset") {
        await this.api.request("/auth/password/reset/complete", "POST", {
          token: this.url().searchParams.get("token"),
          newPassword: this.password,
        });
        await this.api.me();
        await this.router.navigateByUrl("/mon-compte");
      }
    });
  }
  async saveProfile() {
    await this.action(async () => {
      this.api.persona.set(await this.api.request("/me", "PUT", this.profile));
      this.notice.set("Vos informations sont enregistrées.");
    });
  }
  searchRentals(fields: SearchFields) {
    void this.router.navigate(["/annonces"], {
      queryParams: {
        q: fields.q,
        start: fields.start || null,
        end: fields.end || null,
        people: fields.people || null,
        type: fields.type || null,
        maxPrice: fields.maxPrice
          ? Math.round(Number(fields.maxPrice) * 100)
          : null,
      },
    });
  }
  async favorite(rental: Rental) {
    await this.action(async () => {
      if (!this.api.persona()) {
        await this.router.navigate(["/login"], {
          queryParams: { next: this.router.url },
        });
        return;
      }
      const saved = this.favorites().includes(rental.id);
      await this.api.request(
        `/favorites/${rental.id}`,
        saved ? "DELETE" : "PUT",
        {},
      );
      this.favorites.update((ids) =>
        saved ? ids.filter((id) => id !== rental.id) : [...ids, rental.id],
      );
      if (saved && this.page() === "favorites")
        this.rentals.update((items) =>
          items.filter((item) => item.id !== rental.id),
        );
      this.notice.set(
        saved
          ? "Annonce retirée de vos coups de cœur."
          : "Annonce ajoutée à vos coups de cœur.",
      );
    });
  }
  chooseStay(range: { start: string; end: string }) {
    this.start = range.start;
    this.end = range.end;
    this.quote.set(null);
  }
  async calculate() {
    await this.action(async () => {
      const rental = this.rental();
      if (!rental) return;
      this.quote.set(
        await this.api.request<Quote>("/quotes", "POST", {
          rentalId: rental.id,
          start: this.start,
          end: this.end,
          peopleCount: this.peopleCount,
        }),
      );
    });
  }
  async reserve() {
    await this.action(async () => {
      const rental = this.rental();
      if (!rental) return;
      if (!this.api.persona()) {
        await this.router.navigate(["/login"], {
          queryParams: { next: this.router.url },
        });
        return;
      }
      const booking = await this.api.request<Booking>(
        "/bookings",
        "POST",
        {
          rentalId: rental.id,
          start: this.start,
          end: this.end,
          peopleCount: this.peopleCount,
          message:
            this.message || "Bonjour, nous souhaitons réserver votre logement.",
        },
        { "idempotency-key": this.bookingKey },
      );
      this.bookingKey = crypto.randomUUID();
      await this.router.navigate(["/mon-compte/reservation", booking.id]);
    });
  }
  inputOf(rental: Rental): RentalInput {
    const {
      id: _id,
      ownerId: _owner,
      slug: _slug,
      status: _status,
      version: _version,
      completedSteps: _steps,
      subscriptionExpiresAt: _expiry,
      ...input
    } = rental;
    return structuredClone(input);
  }
  lines(text: string) {
    return text
      .split("\n")
      .map((t) => t.trim())
      .filter(Boolean);
  }
  async saveStep() {
    await this.action(async () => {
      const old = this.rental();
      if (!old) return;
      this.editor.equipment = this.lines(this.equipmentText);
      this.editor.linens = this.lines(this.linensText);
      this.editor.rules = this.lines(this.rulesText);
      const updated = await this.api.request<Rental>(
        `/rentals/${old.id}`,
        "PUT",
        { version: old.version, step: this.step, rental: this.editor },
      );
      this.rental.set(updated);
      const next =
        this.steps[
          this.steps.indexOf(this.step as (typeof rentalSteps)[number]) + 1
        ] ?? "termine";
      await this.router.navigate(["/deposez-votre-annonce", next], {
        queryParams: { rental_id: old.id },
      });
    });
  }
  async upload(event: Event) {
    await this.action(async () => {
      const input = event.target as HTMLInputElement;
      const files = Array.from(input.files ?? []);
      if (this.editor.photos.length + files.length > 30)
        throw new Error("Vous pouvez ajouter au maximum 30 photos.");
      for (const file of files) {
        const response = await fetch("/api/media", {
          method: "POST",
          headers: { "content-type": file.type },
          body: file,
          signal: AbortSignal.timeout(30000),
        });
        const result = await response.json();
        if (!response.ok)
          throw new Error(
            result.message ?? "Impossible d’ajouter cette photo.",
          );
        this.editor.photos = [...this.editor.photos, result.path];
      }
      input.value = "";
    });
  }
  removePhoto(index: number) {
    this.editor.photos = this.editor.photos.filter((_, i) => i !== index);
  }
  addBedroom() {
    this.editor.bedrooms = [
      ...this.editor.bedrooms,
      {
        name: `Chambre ${this.editor.bedrooms.length + 1}`,
        beds: [{ type: "Double", count: 1 }],
      },
    ];
  }
  removeBedroom(index: number) {
    this.editor.bedrooms = this.editor.bedrooms.filter((_, i) => i !== index);
  }
  addRate() {
    this.editor.rates = [
      ...this.editor.rates,
      { start: "", end: "", daily: 0, weekly: 0 },
    ];
  }
  addUnavailable() {
    this.editor.unavailable = [
      ...this.editor.unavailable,
      { start: "", end: "" },
    ];
  }
  async geocode() {
    await this.action(async () => {
      const address = Object.values(this.editor.address).join(", ");
      const location = await this.api.request<{ lat: number; lng: number }>(
        "/geocode",
        "POST",
        { address },
      );
      this.editor.latitude = location.lat;
      this.editor.longitude = location.lng;
    });
  }
  async publish(rental: Rental) {
    await this.action(async () => {
      const updated = await this.api.request<Rental>(
        `/rentals/${rental.id}/publish`,
        "POST",
        { version: rental.version, published: rental.status !== "published" },
      );
      this.rentals.update((list) =>
        list.map((r) => (r.id === updated.id ? updated : r)),
      );
      this.notice.set(
        updated.status === "published"
          ? "Votre annonce est publiée."
          : "Votre annonce est désactivée.",
      );
    });
  }
  async transition(status: "confirm" | "cancel") {
    await this.action(async () => {
      const booking = this.booking();
      if (!booking) return;
      this.booking.set(
        await this.api.request<Booking>(
          `/bookings/${booking.id}/${status}`,
          "POST",
          {},
        ),
      );
    });
  }
  async changeConversationPage(delta: number) {
    await this.router.navigate([], {
      queryParams: {
        conversationsPage: this.conversationPage + delta,
        conversation: null,
        page: 1,
      },
      queryParamsHandling: "merge",
    });
  }
  async send() {
    await this.action(async () => {
      const message = await this.api.request<Message>(
        `/conversations/${this.conversationId}/messages`,
        "POST",
        { body: this.message },
      );
      this.messages.update((list) => [...list, message]);
      this.message = "";
    });
  }
  async checkout() {
    await this.action(async () => {
      const result = await this.api.request<{ url: string }>(
        "/subscriptions/checkout",
        "POST",
        {
          rentalId: this.rentalId,
          planId: this.planId,
          discountCode: this.discountCode,
        },
      );
      location.assign(result.url);
    });
  }
  async readNotification(item: Notification) {
    await this.api.request(`/notifications/${item.id}/read`, "POST", {});
    await this.router.navigateByUrl(item.href);
  }
  async loadAdmin() {
    await this.action(async () => {
      this.adminRows.set(
        await this.api.request(
          `/admin/${this.adminKind}?page=${this.pageNumber}`,
        ),
      );
    });
  }
  editReference(item: Record<string, unknown>) {
    this.referenceDraft = {
      ...this.referenceDraft,
      ...item,
    } as typeof this.referenceDraft;
  }
  newReference() {
    this.referenceDraft = {
      id: crypto.randomUUID(),
      name: "",
      code: "",
      type: "percent",
      amount: 0,
      months: 12,
      active: true,
      expiresAt: "",
      maxUses: 100,
      uses: 0,
      payeeId: "",
    };
  }
  adminLabel(item: Record<string, unknown>) {
    return String(
      item.name ??
        item.title ??
        item.rentalTitle ??
        item.email ??
        item.code ??
        item.message ??
        "Abonnement",
    );
  }
  async submitInitial() {
    await this.action(async () => {
      const item = this.booking();
      if (item)
        this.booking.set(
          await this.api.request<Booking>(
            `/bookings/${item.id}/request`,
            "POST",
            {
              body:
                this.message ||
                "Bonjour, je souhaite confirmer ma demande de réservation.",
            },
          ),
        );
    });
  }
  async saveAdmin() {
    await this.action(async () => {
      await this.api.request(
        `/admin/${this.adminKind}`,
        "PUT",
        this.adminKind === "plans"
          ? {
              id: this.referenceDraft.id,
              name: this.referenceDraft.name,
              amount: this.referenceDraft.amount,
              months: this.referenceDraft.months,
              active: this.referenceDraft.active,
            }
          : this.adminKind === "discounts"
            ? {
                id: this.referenceDraft.id,
                code: this.referenceDraft.code,
                type: this.referenceDraft.type,
                amount: this.referenceDraft.amount,
                expiresAt: this.referenceDraft.expiresAt,
                maxUses: this.referenceDraft.maxUses,
                uses: this.referenceDraft.uses,
                payeeId: this.referenceDraft.payeeId || null,
              }
            : { id: this.referenceDraft.id, name: this.referenceDraft.name },
      );
      this.adminRows.set(await this.api.request(`/admin/${this.adminKind}`));
      this.notice.set("La référence est enregistrée.");
    });
  }
  async disablePersona(id: unknown, disabled: boolean) {
    await this.action(async () => {
      await this.api.request(`/admin/personas/${id}`, "POST", { disabled });
      this.adminRows.set(await this.api.request("/admin/personas"));
    });
  }
  async deleteReference(id: unknown) {
    await this.action(async () => {
      await this.api.request(`/admin/${this.adminKind}/${id}`, "DELETE", {});
      this.adminRows.set(await this.api.request(`/admin/${this.adminKind}`));
    });
  }
  money(cents: number) {
    return new Intl.NumberFormat("fr-FR", {
      style: "currency",
      currency: "EUR",
    }).format(cents / 100);
  }
  status(value: string) {
    return (
      (
        {
          initialised: "À confirmer",
          draft: "Brouillon",
          published: "Publiée",
          disabled: "Désactivée",
          expired: "Expirée",
          booked: "Demande envoyée",
          confirmed: "Confirmée",
          cancelled: "Annulée",
          done: "Passée",
        } as Record<string, string>
      )[value] ?? value
    );
  }
  euro(value: string) {
    return Math.round(Number(value) * 100);
  }
  get currentId() {
    return this.url().searchParams.get("rental_id") ?? "";
  }
  get isReference() {
    return [
      "plans",
      "discounts",
      "beds",
      "equipment",
      "rental-types",
      "towns",
      "linens",
    ].includes(this.adminKind);
  }
  changePage(delta: number) {
    void this.router.navigate([], {
      queryParams: { page: Math.max(1, this.pageNumber + delta) },
      queryParamsHandling: "merge",
    });
  }
}
