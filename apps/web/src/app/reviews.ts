import { CommonModule } from "@angular/common";
import { Component, effect, inject, input, signal } from "@angular/core";
import { FormsModule } from "@angular/forms";
import { RouterLink } from "@angular/router";
import type {
  Page,
  Rental,
  Review,
} from "../../../../packages/contracts/src/models";
import { Api } from "./api";

@Component({
  selector: "crozon-reviews",
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  template: `<section class="reviews-section">
    @if(administrative()){<a routerLink="/admin">Retour à l’administration</a><h1>Modérer les avis</h1>}@else{<h2>Les avis des voyageurs</h2>}
    @if(error()){<p role="alert" class="alert error">{{error()}}</p>}
    @if(notice()){<p role="status" class="alert success">{{notice()}}</p>}
    @if(loading()){<p role="status">Chargement…</p>}@else{
    @if(total() && !administrative()){<p class="review-summary"><strong>{{average() | number:'1.1-1'}} / 5</strong> · {{total()}} avis après un séjour</p>}
    @for(review of reviews();track review.id){<article class="review-card"><div class="review-author">@if(review.author.avatarUrl){<img [src]="review.author.avatarUrl" alt="" width="44" height="44">}<strong>{{review.author.name}}</strong><time>{{review.createdAt | date:'dd/MM/yyyy'}}</time></div><p class="review-stars" [attr.aria-label]="review.rating+' sur 5'">{{'★'.repeat(review.rating)}}{{'☆'.repeat(5-review.rating)}}</p>@if(administrative()){<h2>{{review.rentalTitle}}</h2><span class="badge">{{review.published?'Publié':'Masqué'}}</span>}<p class="description">{{review.body}}</p>@if(review.reply){<blockquote><strong>Réponse du propriétaire</strong><p>{{review.reply}}</p></blockquote>}
    @if(api.persona()?.id===rental()?.ownerId){<form (ngSubmit)="reply(review)"><label>Votre réponse à {{review.author.name}}<textarea [name]="'reply-'+review.id" [(ngModel)]="replies[review.id]" required maxlength="5000"></textarea></label><button [disabled]="busy()">Enregistrer ma réponse</button></form>}
    @if(administrative()){<button class="secondary" [disabled]="busy()" (click)="moderate(review)">{{review.published?'Masquer cet avis':'Publier cet avis'}}</button>}</article>}@empty{<p>Aucun avis pour le moment.</p>}
    @if(total()>20){<nav class="pagination" aria-label="Pages des avis"><button [disabled]="page===1||busy()" (click)="load(page-1)">Précédent</button><span>Page {{page}}</span><button [disabled]="page*20>=total()||busy()" (click)="load(page+1)">Suivant</button></nav>}
    @if(eligible().length){<form class="review-form" (ngSubmit)="publish()"><h3>Racontez votre séjour</h3><p>Votre avis sera visible sur cette annonce.</p><label>Séjour à évaluer<select name="stay" [(ngModel)]="bookingId" required>@for(stay of eligible();track stay.id){<option [value]="stay.id">Du {{stay.start}} au {{stay.end}}</option>}</select></label><label>Note<select name="rating" [(ngModel)]="rating">@for(score of [5,4,3,2,1];track score){<option [ngValue]="score">{{score}} sur 5</option>}</select></label><label>Votre avis<textarea name="review" [(ngModel)]="body" required maxlength="5000" rows="4"></textarea></label><button [disabled]="busy()">Publier mon avis</button></form>}
    @if(!administrative()&&!eligible().length){<p class="muted">Les voyageurs peuvent laisser un avis à partir du lendemain de leur départ.</p>}}
  </section>`,
})
export class Reviews {
  readonly api = inject(Api);
  readonly rental = input<Rental>();
  readonly administrative = input(false);
  readonly reviews = signal<Review[]>([]);
  readonly eligible = signal<Array<{ id: string; start: string; end: string }>>(
    [],
  );
  readonly loading = signal(false);
  readonly busy = signal(false);
  readonly error = signal("");
  readonly notice = signal("");
  readonly average = signal(0);
  readonly total = signal(0);
  replies: Record<string, string> = {};
  page = 1;
  bookingId = "";
  rating = 5;
  body = "";
  constructor() {
    effect(() => {
      if (this.rental() || this.administrative()) void this.load(1);
    });
  }
  async load(page: number) {
    const rental = this.rental();
    this.loading.set(true);
    this.error.set("");
    try {
      const result = await this.api.request<
        Page<Review> & { average?: number }
      >(
        this.administrative()
          ? `/admin/reviews?page=${page}`
          : `/rentals/${rental!.id}/reviews?page=${page}`,
      );
      this.reviews.set(result.items);
      this.total.set(result.total);
      this.average.set(result.average ?? 0);
      this.page = page;
      for (const review of result.items) this.replies[review.id] = review.reply;
      if (rental && this.api.persona()) {
        const stays = await this.api.request<
          Array<{ id: string; start: string; end: string }>
        >(`/rentals/${rental.id}/reviewable`);
        this.eligible.set(stays);
        this.bookingId = stays[0]?.id ?? "";
      }
    } catch (error) {
      this.error.set(
        error instanceof Error
          ? error.message
          : "Impossible de charger les avis.",
      );
    } finally {
      this.loading.set(false);
    }
  }
  private async action(work: () => Promise<unknown>, notice: string) {
    if (this.busy()) return;
    this.busy.set(true);
    this.error.set("");
    this.notice.set("");
    try {
      await work();
      await this.load(1);
      this.notice.set(notice);
    } catch (error) {
      this.error.set(
        error instanceof Error
          ? error.message
          : "Impossible d’enregistrer cet avis.",
      );
    } finally {
      this.busy.set(false);
    }
  }
  publish() {
    return this.action(
      () =>
        this.api.request(`/bookings/${this.bookingId}/review`, "POST", {
          rating: this.rating,
          body: this.body,
        }),
      "Votre avis est publié.",
    );
  }
  reply(review: Review) {
    return this.action(
      () =>
        this.api.request(`/reviews/${review.id}/reply`, "PUT", {
          body: this.replies[review.id],
        }),
      "Votre réponse est enregistrée.",
    );
  }
  moderate(review: Review) {
    return this.action(
      () =>
        this.api.request(`/reviews/${review.id}/moderate`, "POST", {
          published: !review.published,
        }),
      "La visibilité de l’avis est mise à jour.",
    );
  }
}
