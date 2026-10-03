import "server-only";
import { createHmac, timingSafeEqual } from "crypto";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";

/*
 * Accès admin par mot de passe unique (variable ADMIN_PASSWORD).
 * La session est un cookie httpOnly signé (HMAC) valable 12 h.
 * Quand Supabase sera branché, on pourra passer à des comptes admin nominatifs.
 */

const COOKIE = "s4dz_admin";
const TTL = 12 * 3600 * 1000;

export const adminConfigured = () => (process.env.ADMIN_PASSWORD ?? "").length >= 8;

// Secret de signature : ADMIN_SECRET si défini, sinon dérivé du mot de passe
const secret = () => process.env.ADMIN_SECRET || `s4dz:${process.env.ADMIN_PASSWORD}`;
const sign = (v: string) => createHmac("sha256", secret()).update(v).digest("hex");

const safeEqual = (a: string, b: string) => {
  const x = Buffer.from(a), y = Buffer.from(b);
  return x.length === y.length && timingSafeEqual(x, y);
};

export function checkPassword(input: string) {
  if (!adminConfigured()) return false;
  // Comparaison des empreintes : durée constante quelle que soit la longueur saisie
  return safeEqual(sign(`pw:${input}`), sign(`pw:${process.env.ADMIN_PASSWORD}`));
}

export async function createSession() {
  const exp = String(Date.now() + TTL);
  (await cookies()).set(COOKIE, `${exp}.${sign(exp)}`, {
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax",
    path: "/",
    maxAge: TTL / 1000,
  });
}

export async function destroySession() {
  (await cookies()).delete(COOKIE);
}

export async function isAdmin() {
  if (!adminConfigured()) return false;
  const raw = (await cookies()).get(COOKIE)?.value ?? "";
  const [exp, sig] = raw.split(".");
  return !!exp && !!sig && safeEqual(sig, sign(exp)) && Number(exp) > Date.now();
}

/** À appeler en tête de chaque page et de chaque action admin. */
export async function requireAdmin() {
  if (!(await isAdmin())) redirect("/admin/login");
}
