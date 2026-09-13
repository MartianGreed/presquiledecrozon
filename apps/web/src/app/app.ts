import { Component, inject, signal } from "@angular/core";
import {
  NavigationEnd,
  Router,
  RouterLink,
  RouterOutlet,
} from "@angular/router";
import { Api } from "./api";
@Component({
  selector: "crozon-app",
  standalone: true,
  imports: [RouterOutlet, RouterLink],
  template: `
<a class="skip-link" href="#main">Aller au contenu</a>
<header class="site-header">
  <a routerLink="/" class="brand" aria-label="Presqu’île de Crozon, accueil"><img src="/images/logo.png" alt="Presqu’île de Crozon" width="166" height="62"></a>
  <button type="button" class="menu-toggle secondary" (click)="menuOpen.set(!menuOpen())" [attr.aria-expanded]="menuOpen()" aria-controls="main-navigation">Menu <span aria-hidden="true">☰</span></button>
  <nav id="main-navigation" aria-label="Navigation principale" [class.open]="menuOpen()">
    <a class="header-search" routerLink="/annonces" aria-label="Rechercher une location"><img src="/images/icons/search.svg" alt="" width="18" height="18"></a>
    <a routerLink="/annonces">Se loger</a>
    <details class="discover-menu" #discover><summary>Se divertir</summary><div><a routerLink="/evenements" (click)="discover.open=false">Événements</a><a routerLink="/activites" (click)="discover.open=false">Activités</a><a routerLink="/restaurants" (click)="discover.open=false">Restaurants</a></div></details>
    <a class="button" routerLink="/deposez-votre-annonce/configuration">Déposer mon annonce</a>
    @if(api.persona()){
      <a class="header-favorite" routerLink="/mon-compte/coups-de-coeur" aria-label="Mes coups de cœur">♥</a>
      <a routerLink="/mon-compte">{{api.persona()?.profile?.firstname || 'Mon compte'}}</a>
      <button class="text-button" (click)="logout()">Déconnexion</button>
    }@else{<a routerLink="/login">Se connecter</a><a class="button secondary" routerLink="/creer-mon-compte">S’inscrire</a>}
  </nav>
</header>
@if(error()){<p class="error" role="alert">{{error()}}</p>}
<main id="main"><router-outlet /></main>
<footer class="site-footer"><nav aria-label="Navigation de pied de page"><a routerLink="/annonces">Se loger</a><a routerLink="/mon-compte/coups-de-coeur">Mes coups de cœur</a><a routerLink="/mon-compte">Mon compte</a><a routerLink="/deposez-votre-annonce/configuration">Proposer mon logement</a></nav><nav aria-label="Informations du site"><a routerLink="/conditions-generales">Conditions générales</a><a routerLink="/mentions-legales">Mentions légales</a><a routerLink="/confidentialite">Confidentialité</a><a routerLink="/cookies">Cookies</a><a routerLink="/plan-du-site">Plan du site</a></nav><small>Presqu’île de Crozon · Locations de particuliers à particuliers</small></footer>`,
})
export class AppComponent {
  readonly api = inject(Api);
  private readonly router = inject(Router);
  readonly error = signal("");
  readonly menuOpen = signal(false);
  constructor() {
    this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) this.menuOpen.set(false);
    });
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
