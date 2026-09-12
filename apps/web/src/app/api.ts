import { Injectable, signal } from "@angular/core";
import type { Persona } from "../../../../packages/contracts/src/models";
export class ApiError extends Error {
  constructor(
    message: string,
    readonly status: number,
  ) {
    super(message);
  }
}
@Injectable({ providedIn: "root" })
export class Api {
  readonly persona = signal<Persona | null>(null);
  async request<T>(
    path: string,
    method = "GET",
    body?: unknown,
    headers: Record<string, string> = {},
  ): Promise<T> {
    const response = await fetch(`/api${path}`, {
      method,
      credentials: "same-origin",
      headers: {
        ...(body === undefined ? {} : { "content-type": "application/json" }),
        ...headers,
      },
      body: body === undefined ? undefined : JSON.stringify(body),
      signal: AbortSignal.timeout(20000),
    });
    const data = await response.json();
    if (!response.ok) {
      const messages: Record<string, string> = {
        InvalidCredentials:
          "L’adresse e-mail ou le mot de passe est incorrect.",
        EmailNotVerified:
          "Vérifiez votre adresse e-mail avant de vous connecter.",
        InvalidAuthToken: "Ce lien est invalide ou a expiré.",
        IdentityConflict: "Cette adresse e-mail est déjà utilisée.",
        RateLimitExceeded: "Trop de tentatives. Réessayez plus tard.",
      };
      throw new ApiError(
        messages[data.error] ??
          data.message ??
          "Impossible de terminer cette action.",
        response.status,
      );
    }
    return data as T;
  }
  async me(): Promise<Persona | null> {
    try {
      const persona = await this.request<Persona>("/me");
      this.persona.set(persona);
      return persona;
    } catch (error) {
      if (!(error instanceof ApiError) || error.status !== 401) throw error;
      this.persona.set(null);
      return null;
    }
  }
  async logout() {
    await this.request("/auth/sign-out", "POST", {});
    this.persona.set(null);
  }
}
