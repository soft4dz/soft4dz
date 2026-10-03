import "server-only";
import { readJson, updateJson } from "./store";

/** Code promo géré dans Admin › Codes promo. */
export type Promo = {
  code: string;
  type: "percent" | "fixed";
  /** % (1-90) ou montant en DA */
  value: number;
  /** Montant minimum du panier en DA (0 = aucun) */
  minTotal: number;
  /** Date limite AAAA-MM-JJ incluse (vide = sans limite) */
  expires?: string;
  /** Nombre d'utilisations max (0 = illimité) */
  maxUses: number;
  uses: number;
  active: boolean;
};

const FILE = "promos.json";
const empty = (): Promo[] => [];

export const listPromos = () => readJson(FILE, empty);

export const savePromo = (p: Promo, previous?: string) =>
  updateJson(FILE, empty, (list) => {
    const i = list.findIndex((x) => x.code === (previous ?? p.code));
    if (i < 0) return [...list, p];
    const next = [...list];
    next[i] = p;
    return next;
  });

export const deletePromo = (code: string) => updateJson(FILE, empty, (list) => list.filter((p) => p.code !== code));

export type PromoCheck = { ok: true; code: string; discount: number } | { ok: false; reason: "unknown" | "expired" | "exhausted" | "minimum"; min?: number };

/** Vérifie un code pour un sous-total donné et calcule la remise (arrondie à 10 DA). */
export async function checkPromo(raw: string, subtotal: number): Promise<PromoCheck> {
  const code = raw.trim().toUpperCase();
  const p = (await listPromos()).find((x) => x.code === code && x.active);
  if (!p) return { ok: false, reason: "unknown" };
  if (p.expires && new Date(`${p.expires}T23:59:59`) < new Date()) return { ok: false, reason: "expired" };
  if (p.maxUses > 0 && p.uses >= p.maxUses) return { ok: false, reason: "exhausted" };
  if (subtotal < p.minTotal) return { ok: false, reason: "minimum", min: p.minTotal };
  const raw$ = p.type === "percent" ? (subtotal * p.value) / 100 : p.value;
  const discount = Math.min(subtotal, Math.round(raw$ / 10) * 10);
  return { ok: true, code, discount };
}

export const countPromoUse = (code: string) =>
  updateJson(FILE, empty, (list) => list.map((p) => (p.code === code ? { ...p, uses: p.uses + 1 } : p)));
