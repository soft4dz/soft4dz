import "server-only";
import { randomBytes } from "crypto";
import { readJson, updateJson } from "./store";
import type { Order, OrderLine } from "./orders";

/*
 * Stock de clés de licence : l'admin les dépose à l'avance,
 * elles sont attribuées aux commandes (manuellement ou automatiquement).
 */

export type StockKey = {
  id: string;
  slug: string;
  option: string;
  key: string;
  addedAt: string;
  orderId?: string;
  usedAt?: string;
};

const FILE = "keys.json";
const empty = (): StockKey[] => [];

export const listKeys = () => readJson(FILE, empty);

/** Ajoute des clés (une par ligne) ; ignore les doublons déjà en stock. */
export function addKeys(slug: string, option: string, raw: string[]) {
  let added = 0, duplicates = 0;
  return updateJson(FILE, empty, (list) => {
    const known = new Set(list.map((k) => k.key));
    const now = new Date().toISOString();
    const fresh: StockKey[] = [];
    for (const key of raw.map((k) => k.trim()).filter(Boolean)) {
      if (known.has(key)) { duplicates++; continue; }
      known.add(key);
      fresh.push({ id: randomBytes(6).toString("hex"), slug, option, key: key.slice(0, 200), addedAt: now });
      added++;
    }
    return [...list, ...fresh];
  }).then(() => ({ added, duplicates }));
}

export const deleteKey = (id: string) =>
  updateJson(FILE, empty, (list) => list.filter((k) => k.id !== id || k.orderId));

/** Stock disponible par produit/option. */
export async function stockLevels() {
  const map = new Map<string, number>();
  for (const k of await listKeys()) if (!k.orderId) map.set(`${k.slug}|${k.option}`, (map.get(`${k.slug}|${k.option}`) ?? 0) + 1);
  return map;
}

/**
 * Réserve dans le stock les clés nécessaires à une commande.
 * Tout ou rien : si une ligne manque de clés, rien n'est pris.
 */
export async function takeKeysFor(order: Order): Promise<OrderLine[] | null> {
  let result: OrderLine[] | null = null;
  await updateJson(FILE, empty, (list) => {
    const now = new Date().toISOString();
    const next = list.map((k) => ({ ...k }));
    const lines: OrderLine[] = [];
    for (const l of order.lines) {
      const already = l.keys?.length ?? 0;
      const need = Math.max(0, l.qty - already);
      const free = next.filter((k) => !k.orderId && k.slug === l.slug && k.option === l.option).slice(0, need);
      if (free.length < need) return list; // stock insuffisant : on ne touche à rien
      free.forEach((k) => { k.orderId = order.id; k.usedAt = now; });
      lines.push({ ...l, keys: [...(l.keys ?? []), ...free.map((k) => k.key)] });
    }
    result = lines;
    return next;
  });
  return result;
}
