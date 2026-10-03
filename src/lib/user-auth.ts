import "server-only";
import { createHmac, randomBytes, timingSafeEqual } from "crypto";
import { cookies } from "next/headers";
import { readJson, updateJson } from "./store";

/*
 * Gestion de l'authentification des clients (Espace Client).
 * Supporte Google OAuth 2.0 et sessions sécurisées par cookie httpOnly signé.
 */

export type CustomerUser = {
  id: string;
  name: string;
  email: string;
  avatar?: string;
  googleId?: string;
  facebookId?: string;
  phone?: string;
  createdAt: string;
  lastLoginAt: string;
};

const FILE = "users.json";
const COOKIE = "s4dz_client_session";
const STATE_COOKIE = "s4dz_oauth_state";
const TTL = 30 * 24 * 3600 * 1000; // 30 jours

const empty = (): CustomerUser[] => [];

export const googleConfigured = () =>
  Boolean(process.env.GOOGLE_CLIENT_ID && process.env.GOOGLE_CLIENT_SECRET);

const secret = () =>
  process.env.ADMIN_SECRET || process.env.APP_SECRET || "s4dz_default_super_secret_auth_key";

const sign = (v: string) => createHmac("sha256", secret()).update(v).digest("hex");

const safeEqual = (a: string, b: string) => {
  const x = Buffer.from(a), y = Buffer.from(b);
  return x.length === y.length && timingSafeEqual(x, y);
};

export const getCustomers = () => readJson<CustomerUser[]>(FILE, empty);

export async function findCustomerById(id: string): Promise<CustomerUser | null> {
  const users = await getCustomers();
  return users.find((u) => u.id === id) ?? null;
}

export async function findOrCreateGoogleCustomer(profile: {
  googleId: string;
  email: string;
  name: string;
  avatar?: string;
}): Promise<CustomerUser> {
  const now = new Date().toISOString();
  let user: CustomerUser | null = null;

  await updateJson<CustomerUser[]>(FILE, empty, (users) => {
    // 1. Chercher par googleId ou par email
    const idx = users.findIndex(
      (u) => u.googleId === profile.googleId || u.email.toLowerCase() === profile.email.toLowerCase()
    );

    if (idx >= 0) {
      user = {
        ...users[idx],
        googleId: profile.googleId,
        name: profile.name || users[idx].name,
        avatar: profile.avatar || users[idx].avatar,
        lastLoginAt: now,
      };
      const next = [...users];
      next[idx] = user;
      return next;
    }

    user = {
      id: "usr_" + randomBytes(8).toString("hex"),
      googleId: profile.googleId,
      name: profile.name,
      email: profile.email.toLowerCase(),
      avatar: profile.avatar,
      createdAt: now,
      lastLoginAt: now,
    };
    return [...users, user];
  });

  return user!;
}

/** Crée la session du client par cookie sécurisé signé */
export async function createCustomerSession(userId: string) {
  const exp = String(Date.now() + TTL);
  const sig = sign(`${userId}.${exp}`);
  const val = `${userId}.${exp}.${sig}`;

  (await cookies()).set(COOKIE, val, {
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax",
    path: "/",
    maxAge: TTL / 1000,
  });
}

/** Supprime la session */
export async function destroyCustomerSession() {
  (await cookies()).delete(COOKIE);
}

/** Récupère le client connecté actuel */
export async function getCurrentCustomer(): Promise<CustomerUser | null> {
  const raw = (await cookies()).get(COOKIE)?.value ?? "";
  if (!raw) return null;

  const parts = raw.split(".");
  if (parts.length !== 3) return null;

  const [userId, exp, sig] = parts;
  if (Number(exp) < Date.now()) return null;

  const expected = sign(`${userId}.${exp}`);
  if (!safeEqual(sig, expected)) return null;

  return findCustomerById(userId);
}

/** Génère l'URL de connexion Google OAuth 2.0 */
export async function getGoogleAuthUrl(origin: string): Promise<string> {
  const clientId = process.env.GOOGLE_CLIENT_ID ?? "";
  const redirectUri = `${origin}/api/auth/callback/google`;
  const state = randomBytes(16).toString("hex");

  // Stocker l'état CSRF dans un cookie temporaire valable 10 minutes
  (await cookies()).set(STATE_COOKIE, state, {
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax",
    path: "/",
    maxAge: 600,
  });

  const params = new URLSearchParams({
    client_id: clientId,
    redirect_uri: redirectUri,
    response_type: "code",
    scope: "openid email profile",
    access_type: "offline",
    prompt: "select_account",
    state,
  });

  return `https://accounts.google.com/o/oauth2/v2/auth?${params.toString()}`;
}

/** Vérifie l'état CSRF OAuth */
export async function verifyOAuthState(state: string | null): Promise<boolean> {
  if (!state) return false;
  const cookieStore = await cookies();
  const savedState = cookieStore.get(STATE_COOKIE)?.value;
  cookieStore.delete(STATE_COOKIE);
  return Boolean(savedState && savedState === state);
}

/** Échange le code d'autorisation contre les informations de profil Google */
export async function exchangeGoogleCode(code: string, origin: string) {
  const clientId = process.env.GOOGLE_CLIENT_ID ?? "";
  const clientSecret = process.env.GOOGLE_CLIENT_SECRET ?? "";
  const redirectUri = `${origin}/api/auth/callback/google`;

  // 1. Échanger le code contre un access token
  const tokenRes = await fetch("https://oauth2.googleapis.com/token", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      code,
      client_id: clientId,
      client_secret: clientSecret,
      redirect_uri: redirectUri,
      grant_type: "authorization_code",
    }),
  });

  if (!tokenRes.ok) {
    const errorBody = await tokenRes.text();
    throw new Error(`Google token error (${tokenRes.status}): ${errorBody}`);
  }

  const tokens = await tokenRes.json();
  const accessToken = tokens.access_token;

  // 2. Récupérer les informations de profil utilisateur
  const userRes = await fetch("https://www.googleapis.com/oauth2/v2/userinfo", {
    headers: { Authorization: `Bearer ${accessToken}` },
  });

  if (!userRes.ok) {
    throw new Error("Impossible de récupérer les informations du compte Google");
  }

  const profile = await userRes.json();
  return {
    googleId: String(profile.id),
    email: String(profile.email),
    name: String(profile.name || profile.given_name || "Client Google"),
    avatar: profile.picture as string | undefined,
  };
}
