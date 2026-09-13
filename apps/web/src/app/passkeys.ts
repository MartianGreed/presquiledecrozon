import type {
  PasskeyAuthenticationOptions,
  PasskeyRegistrationOptions,
} from "@structure-ai/auth";
import type { Api } from "./api";

function decode(value: string): ArrayBuffer {
  const text = atob(value.replace(/-/g, "+").replace(/_/g, "/"));
  const bytes = new Uint8Array(text.length);
  for (let index = 0; index < text.length; index++)
    bytes[index] = text.charCodeAt(index);
  return bytes.buffer;
}
function encode(value: ArrayBuffer) {
  return btoa(String.fromCharCode(...new Uint8Array(value)))
    .replace(/\+/g, "-")
    .replace(/\//g, "_")
    .replace(/=+$/, "");
}
function supported() {
  if (!window.isSecureContext || !window.PublicKeyCredential)
    throw new Error(
      "Ce navigateur ne permet pas d’utiliser une clé d’accès ici.",
    );
}
export function passkeyError(error: unknown): string {
  if (
    error instanceof DOMException &&
    ["NotAllowedError", "AbortError"].includes(error.name)
  )
    return "La demande a été annulée ou aucune clé d’accès compatible n’a été sélectionnée.";
  if (error instanceof DOMException && error.name === "InvalidStateError")
    return "Cette clé d’accès est déjà enregistrée.";
  return error instanceof Error
    ? error.message
    : "Impossible d’utiliser cette clé d’accès.";
}
export async function registerPasskey(api: Api, label: string) {
  supported();
  const options = await api.request<PasskeyRegistrationOptions>(
    "/auth/passkeys/register/options",
    "POST",
    {},
  );
  const credential = (await navigator.credentials.create({
    publicKey: {
      ...options,
      challenge: decode(options.challenge),
      user: { ...options.user, id: decode(options.user.id) },
      pubKeyCredParams: options.pubKeyCredParams.map((value) => ({ ...value })),
      authenticatorSelection: {
        ...options.authenticatorSelection,
        residentKey: "required",
      },
      excludeCredentials: options.excludeCredentials.map((value) => ({
        type: value.type,
        id: decode(value.id),
      })),
    },
  })) as PublicKeyCredential | null;
  if (!credential) throw new Error("Aucune clé d’accès n’a été créée.");
  const response = credential.response as AuthenticatorAttestationResponse;
  await api.request("/auth/passkeys/register/verify", "POST", {
    credentialId: credential.id,
    label,
    response: {
      clientDataJSON: encode(response.clientDataJSON),
      attestationObject: encode(response.attestationObject),
      transports: response.getTransports(),
    },
  });
}
export async function signInWithPasskey(api: Api) {
  supported();
  const options = await api.request<PasskeyAuthenticationOptions>(
    "/auth/passkeys/authenticate/options",
    "POST",
    {},
  );
  const credential = (await navigator.credentials.get({
    publicKey: {
      ...options,
      challenge: decode(options.challenge),
      allowCredentials: options.allowCredentials?.map((value) => ({
        type: value.type,
        id: decode(value.id),
      })),
    },
  })) as PublicKeyCredential | null;
  if (!credential) throw new Error("Aucune clé d’accès n’a été sélectionnée.");
  const response = credential.response as AuthenticatorAssertionResponse;
  await api.request("/auth/passkeys/authenticate/verify", "POST", {
    credentialId: credential.id,
    response: {
      clientDataJSON: encode(response.clientDataJSON),
      authenticatorData: encode(response.authenticatorData),
      signature: encode(response.signature),
      ...(response.userHandle
        ? { userHandle: encode(response.userHandle) }
        : {}),
    },
  });
}
