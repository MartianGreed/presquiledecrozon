import { Component, inject, signal } from "@angular/core";
import { FormsModule } from "@angular/forms";
import { Api } from "./api";
import { passkeyError, registerPasskey } from "./passkeys";

interface Passkey {
  id: string;
  label: string;
  createdAt: string;
}

@Component({
  selector: "crozon-account-settings",
  standalone: true,
  imports: [FormsModule],
  template: `<h1>Paramètres du compte</h1>
    @if(error()){<p class="alert error" role="alert">{{error()}}</p>}
    @if(notice()){<p class="alert success" role="status">{{notice()}}</p>}
    @if(!loading() && !contactEmail()){<section class="settings-section"><h2>Adresse de contact</h2><p>Ajoutez une adresse vérifiée pour recevoir vos notifications. Votre connexion reste gérée par votre compte social ou vos clés d’accès.</p>@if(contactToken){<button [disabled]="busy()" (click)="verifyEmail()">Vérifier mon adresse de contact</button>}@else{<form (ngSubmit)="requestEmail()"><label>Adresse e-mail de contact<input type="email" name="contactEmail" [(ngModel)]="email" required maxlength="254"></label><button [disabled]="busy()">Recevoir le lien de vérification</button></form>}</section>}
    @if(providers().length){<section class="settings-section"><h2>Connexion sociale</h2><p>Associez votre compte pour vous connecter avec le fournisseur de votre choix.</p><div class="social-login">@for(provider of providers();track provider){<button class="secondary" [disabled]="busy()" (click)="linkProvider(provider)">Associer {{provider==='google'?'Google':'Facebook'}}</button>}</div></section>}
    <section class="settings-section"><h2>Notifications</h2><form (ngSubmit)="savePreferences()"><label class="checkbox"><input type="checkbox" name="emailNotifications" [(ngModel)]="emailNotifications" [disabled]="loading()">Recevoir les notifications par e-mail</label><p>Vos messages restent disponibles dans votre compte. Les e-mails de vérification et de sécurité restent actifs.</p><button [disabled]="busy()">Enregistrer mes préférences</button></form></section>
    <section class="settings-section"><h2>Clés d’accès</h2><p>Connectez-vous avec votre empreinte, la reconnaissance faciale ou le code de votre appareil.</p>
    @if(loading()){<p role="status">Chargement…</p>}@else{@for(key of keys();track key.id){<div class="passkey-row"><strong>{{key.label}}</strong><button type="button" class="text-button" [attr.aria-label]="'Supprimer '+key.label" [disabled]="busy()" (click)="remove(key)">Supprimer</button></div>}@empty{<p>Aucune clé d’accès enregistrée.</p>}}
    <form (ngSubmit)="register()"><label>Nom de la clé d’accès<input name="label" [(ngModel)]="label" required maxlength="100" placeholder="Mon téléphone, mon ordinateur…"></label><button [disabled]="busy()">Ajouter une clé d’accès</button></form></section>
    @if(hasPassword()){<section class="settings-section"><h2>Mot de passe</h2><form (ngSubmit)="changePassword()"><label>Mot de passe actuel<input type="password" name="currentPassword" [(ngModel)]="currentPassword" required autocomplete="current-password"></label><label>Nouveau mot de passe<input type="password" name="newPassword" [(ngModel)]="newPassword" required minlength="12" maxlength="256" autocomplete="new-password"></label><button [disabled]="busy()">Modifier le mot de passe</button></form></section>}`,
})
export class AccountSettings {
  private readonly api = inject(Api);
  readonly keys = signal<Passkey[]>([]);
  readonly loading = signal(true);
  readonly busy = signal(false);
  readonly error = signal("");
  readonly notice = signal("");
  readonly hasPassword = signal(false);
  readonly contactEmail = signal("");
  readonly providers = signal<string[]>([]);
  readonly contactToken = new URL(location.href).searchParams.get(
    "contact-token",
  );
  email = "";
  emailNotifications = true;
  label = "";
  currentPassword = "";
  newPassword = "";
  constructor() {
    void this.action(async () => {
      try {
        const [, preferences, security, providers] = await Promise.all([
          this.loadKeys(),
          this.api.request<{ emailNotifications: boolean }>("/me/preferences"),
          this.api.request<{ hasPassword: boolean; email: string }>(
            "/me/security",
          ),
          this.api.request<string[]>("/sign-in/providers"),
        ]);
        this.emailNotifications = preferences.emailNotifications;
        this.hasPassword.set(security.hasPassword);
        this.contactEmail.set(security.email);
        this.providers.set(providers);
      } finally {
        this.loading.set(false);
      }
    });
  }
  private async loadKeys() {
    this.keys.set(await this.api.request<Passkey[]>("/me/passkeys"));
  }
  private async action(work: () => Promise<void>) {
    if (this.busy()) return;
    this.busy.set(true);
    this.error.set("");
    this.notice.set("");
    try {
      await work();
    } catch (error) {
      this.error.set(passkeyError(error));
    } finally {
      this.busy.set(false);
    }
  }
  linkProvider(provider: string) {
    return this.action(async () => {
      const response = await this.api.request<{ authorizationUrl: string }>(
        `/auth/oauth/${provider}/start`,
        "POST",
        { returnTo: "/mon-compte/parametres" },
      );
      location.assign(response.authorizationUrl);
    });
  }
  requestEmail() {
    return this.action(async () => {
      await this.api.request("/me/contact-email", "POST", {
        email: this.email,
      });
      this.notice.set(
        "Un lien de vérification a été envoyé. Il expire dans trente minutes.",
      );
    });
  }
  verifyEmail() {
    return this.action(async () => {
      await this.api.request("/me/contact-email/verify", "POST", {
        token: this.contactToken,
      });
      await this.api.me();
      this.contactEmail.set(this.api.persona()?.email ?? "");
      history.replaceState(null, "", "/mon-compte/parametres");
      this.notice.set("Votre adresse de contact est vérifiée.");
    });
  }
  savePreferences() {
    return this.action(async () => {
      await this.api.request("/me/preferences", "PUT", {
        emailNotifications: this.emailNotifications,
      });
      this.notice.set("Vos préférences sont enregistrées.");
    });
  }
  register() {
    return this.action(async () => {
      await registerPasskey(this.api, this.label.trim());
      await this.loadKeys();
      this.label = "";
      this.notice.set("Votre clé d’accès est enregistrée.");
    });
  }
  remove(key: Passkey) {
    return this.action(async () => {
      await this.api.request(
        `/me/passkeys/${encodeURIComponent(key.id)}`,
        "DELETE",
      );
      await this.loadKeys();
      this.notice.set("La clé d’accès a été supprimée.");
    });
  }
  changePassword() {
    return this.action(async () => {
      await this.api.request("/auth/password/change", "POST", {
        currentPassword: this.currentPassword,
        newPassword: this.newPassword,
      });
      this.currentPassword = "";
      this.newPassword = "";
      this.notice.set(
        "Votre mot de passe a été modifié. Les autres sessions sont déconnectées.",
      );
    });
  }
}
