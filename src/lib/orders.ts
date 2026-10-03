import "server-only";
import { randomInt } from "crypto";
import type { Locale } from "./i18n";
import { readJson, updateJson } from "./store";

/*
 * Commandes. Stockées dans .data/orders.json en développement
 * (à remplacer par la table `orders` de Supabase, mêmes fonctions).
 */

export type PaymentMethod = "cib" | "edahabia" | "ccp";
export type OrderStatus = "pending" | "awaiting_transfer" | "paid" | "delivered" | "failed" | "cancelled";

export type OrderLine = {
  slug: string;
  option: string;
  qty: number;
  unitPrice: number;
  name: string;
  optionLabel: string;
  /** Clés ou identifiants livrés par l'admin (une par unité) */
  keys?: string[];
};

export type Order = {
  id: string;
  createdAt: string;
  locale: Locale;
  customer: { name: string; phone: string; email: string };
  method: PaymentMethod;
  lines: OrderLine[];
  /** Total payé, remise déduite */
  total: number;
  /** Code promo appliqué */
  promo?: { code: string; discount: number };
  status: OrderStatus;
  demo?: boolean;
  chargilyCheckoutId?: string;
  paidAt?: string;
  deliveredAt?: string;
  /** Note interne, jamais montrée au client */
  note?: string;
  /** Livrée automatiquement depuis le stock de clés */
  autoDelivered?: boolean;
};

const FILE = "orders.json";
const empty = (): Order[] => [];

/** Identifiant lisible et difficile à deviner : SD-XXXXXXXX (sert aussi de lien de suivi). */
const newId = () => {
  const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
  return "SD-" + Array.from({ length: 8 }, () => chars[randomInt(chars.length)]).join("");
};

export async function createOrder(data: Omit<Order, "id" | "createdAt">): Promise<Order> {
  const order: Order = { ...data, id: newId(), createdAt: new Date().toISOString() };
  if (order.status === "paid") order.paidAt = order.createdAt;
  await updateJson(FILE, empty, (list) => [...list, order]);
  return order;
}

export const listOrders = async () =>
  (await readJson(FILE, empty)).sort((a, b) => b.createdAt.localeCompare(a.createdAt));

export const getOrder = async (id: string) => (await readJson(FILE, empty)).find((o) => o.id === id);

export async function updateOrder(id: string, patch: Partial<Order>) {
  let result: Order | undefined;
  await updateJson(FILE, empty, (list) =>
    list.map((o) => {
      if (o.id !== id) return o;
      result = { ...o, ...patch };
      if (patch.status === "paid" && !o.paidAt) result.paidAt = new Date().toISOString();
      if (patch.status === "delivered" && !o.deliveredAt) result.deliveredAt = new Date().toISOString();
      return result;
    }),
  );
  return result;
}
