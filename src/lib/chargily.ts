import "server-only";
import { createHmac, timingSafeEqual } from "crypto";

/*
 * Chargily Pay V2 — paiement CIB / Edahabia.
 * Variables d'environnement :
 *   CHARGILY_SECRET_KEY  clé secrète (test_sk_… ou live_sk_…)
 *   CHARGILY_MODE        "test" (défaut) ou "live"
 * Sans clé, le site fonctionne en mode démo (aucun paiement réel).
 */

const key = () => process.env.CHARGILY_SECRET_KEY ?? "";
export const chargilyEnabled = () => key().length > 0;
const base = () =>
  process.env.CHARGILY_MODE === "live" ? "https://pay.chargily.net/api/v2" : "https://pay.chargily.net/test/api/v2";

type Checkout = { id: string; checkout_url: string; status: string };

async function call<T>(path: string, init?: RequestInit): Promise<T> {
  const res = await fetch(base() + path, {
    ...init,
    headers: { Authorization: `Bearer ${key()}`, "Content-Type": "application/json", Accept: "application/json" },
    cache: "no-store",
  });
  if (!res.ok) throw new Error(`Chargily ${res.status}: ${await res.text()}`);
  return res.json() as Promise<T>;
}

export function createCheckout(input: {
  amount: number;
  method: "cib" | "edahabia";
  orderId: string;
  locale: "fr" | "ar" | "en";
  successUrl: string;
  failureUrl: string;
  webhookUrl?: string;
}) {
  return call<Checkout>("/checkouts", {
    method: "POST",
    body: JSON.stringify({
      amount: input.amount,
      currency: "dzd",
      payment_method: input.method,
      success_url: input.successUrl,
      failure_url: input.failureUrl,
      // Chargily n'accepte qu'une URL publique : on ne l'envoie pas en local
      ...(input.webhookUrl ? { webhook_endpoint: input.webhookUrl } : {}),
      locale: input.locale,
      metadata: { order_id: input.orderId },
    }),
  });
}

export const getCheckout = (id: string) => call<Checkout>(`/checkouts/${id}`);

/** Vérifie la signature HMAC-SHA256 envoyée par Chargily dans l'en-tête `signature`. */
export function verifySignature(rawBody: string, signature: string | null) {
  if (!signature || !chargilyEnabled()) return false;
  const expected = createHmac("sha256", key()).update(rawBody).digest("hex");
  const a = Buffer.from(expected), b = Buffer.from(signature);
  return a.length === b.length && timingSafeEqual(a, b);
}
