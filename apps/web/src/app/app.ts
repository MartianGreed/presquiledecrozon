import { Component, inject, signal } from "@angular/core";
import { Router, RouterLink, RouterOutlet } from "@angular/router";
import { Api } from "./api";
@Component({
  selector: "crozon-app",
  standalone: true,
  imports: [RouterOutlet, RouterLink],
  template: `
<a class="skip-link" href="#main">Aller au contenu</a>
<header class="site-header"><a routerLink="/" class="brand"><img src="/images/logo.png" alt="" width="44" height="44"><span>Presqu'île<br><strong>de Crozon</strong></span></a><nav aria-label="Navigation principale"><a routerLink="/annonces">Les locations</a><a routerLink="/deposez-votre-annonce/configuration">Déposer une annonce</a>@if(api.persona()){<a routerLink="/mon-compte">Mon compte</a><button class="text-button" (click)="logout()">Déconnexion</button>}@else{<a routerLink="/login">Se connecter</a>}</nav></header>
@if(error()){<p class="error" role="alert">{{error()}}</p>}
<main id="main"><router-outlet /></main>
<footer><div><strong>Presqu'île de Crozon</strong><p>Vos vacances au bout du monde, tout près de chez nous.</p></div><div><a routerLink="/annonces">Trouver un logement</a><a routerLink="/deposez-votre-annonce/configuration">Proposer mon logement</a></div><small>Les locations entre particuliers sur la presqu'île.</small></footer>`,
})
export class AppComponent {
  readonly api = inject(Api);
  private readonly router = inject(Router);
  readonly error = signal("");
  constructor() {
    void this.api.me().catch((error: Error) => this.error.set(error.message));
  }
  async logout() {
    try {
      await this.api.logout();
      this.error.set("");
      await this.router.navigateByUrl("/login");
    } catch (error) {
      this.error.set(
        error instanceof Error
          ? error.message
          : "Impossible de vous déconnecter.",
      );
    }
  }
}
