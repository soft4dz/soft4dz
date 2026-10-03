import "server-only";
import { getOrder, updateOrder } from "./orders";
import { takeKeysFor } from "./keys";
import { getSettings } from "./settings";
import { logAction } from "./audit";

/**
 * Livre une commande payée avec les clés du stock.
 * `force` : appel manuel depuis l'admin (ignore le réglage « livraison automatique »).
 * Renvoie true si la commande est livrée.
 */
export async function deliverFromStock(orderId: string, force = false) {
  const order = await getOrder(orderId);
  if (!order || order.status !== "paid") return false;
  if (!force && !(await getSettings()).autoDelivery) return false;
  // Les services sur devis n'ont pas de clé : jamais de livraison automatique
  const lines = await takeKeysFor(order);
  if (!lines) return false;
  await updateOrder(order.id, { lines, status: "delivered", autoDelivered: !force });
  await logAction(force ? "Commande livrée depuis le stock" : "Livraison automatique", order.id);
  return true;
}
