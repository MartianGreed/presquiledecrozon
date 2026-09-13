import { CommonModule } from "@angular/common";
import { Component, inject, signal } from "@angular/core";
import { RouterLink } from "@angular/router";
import type {
  AccountSubscription,
  Page,
} from "../../../../packages/contracts/src/models";
import { Api } from "./api";

@Component({
  selector: "crozon-account-subscriptions",
  standalone: true,
  imports: [CommonModule, RouterLink],
  template: `<h1>Mes abonnements</h1>
    @if(error()){<p role="alert" class="alert error">{{error()}}</p>}
    @if(loading()){<p role="status">Chargement…</p>}@else{
    @for(subscription of subscriptions();track subscription.id){<article class="card subscription-summary"><h2>{{subscription.rentalTitle || 'Mon logement'}}</h2><p>{{subscription.amount/100 | currency:'EUR'}} · {{subscription.months}} mois</p><p>{{status(subscription)}}</p>@if(subscription.expiresAt){<p>Fin de publication : {{subscription.expiresAt | date:'dd/MM/yyyy'}}</p>}<a class="button secondary" routerLink="/abonnement" [queryParams]="{rental_id:subscription.rentalId}">{{subscription.status==='pending'?'Reprendre le paiement':'Choisir un abonnement'}}</a></article>}@empty{<p>Vous n’avez pas encore d’abonnement.</p><a class="button" routerLink="/mon-compte/annonces">Voir mes annonces</a>}
    @if(total()>25){<nav class="pagination" aria-label="Pages des abonnements"><button [disabled]="page===1" (click)="load(page-1)">Précédent</button><span>Page {{page}}</span><button [disabled]="page*25>=total()" (click)="load(page+1)">Suivant</button></nav>}}
  `,
})
export class AccountSubscriptions {
  private readonly api = inject(Api);
  readonly subscriptions = signal<AccountSubscription[]>([]);
  readonly total = signal(0);
  readonly loading = signal(false);
  readonly error = signal("");
  page = 1;
  constructor() {
    void this.load(1);
  }
  status(value: AccountSubscription) {
    if (value.status === "pending") return "Paiement en attente";
    if (value.status === "paid") return "Payé · prêt à publier";
    return value.expiresAt && Date.parse(value.expiresAt) < Date.now()
      ? "Publication expirée"
      : "Publication active";
  }
  async load(page: number) {
    this.loading.set(true);
    this.error.set("");
    try {
      const result = await this.api.request<Page<AccountSubscription>>(
        `/me/subscriptions?page=${page}`,
      );
      this.subscriptions.set(result.items);
      this.total.set(result.total);
      this.page = page;
    } catch (error) {
      this.error.set(
        error instanceof Error
          ? error.message
          : "Impossible de charger les abonnements.",
      );
    } finally {
      this.loading.set(false);
    }
  }
}
