import "server-only";
import { getAllProducts } from "./products";
import { listKeys } from "./keys";
import { getSettings } from "./settings";
import { finalPrice } from "./catalog";

/** Niveaux de stock de clés par produit/option (hors services sur devis). */
export async function stockTable() {
  const [products, keys, settings] = await Promise.all([getAllProducts(), listKeys(), getSettings()]);
  const rows = products.filter((p) => p.category !== "svc").flatMap((p) =>
    p.options.filter((o) => finalPrice(p, o.id) > 0).map((o) => {
      const mine = keys.filter((k) => k.slug === p.slug && k.option === o.id);
      const available = mine.filter((k) => !k.orderId).length;
      return { product: p, option: o, available, used: mine.length - available, low: available <= settings.lowStock };
    }));
  return { rows, threshold: settings.lowStock };
}

/** Nombre de produits en ligne dont le stock est sous le seuil (et qui ont déjà eu des clés). */
export async function lowStockCount() {
  const { rows } = await stockTable();
  return rows.filter((r) => r.low && r.product.active !== false && r.available + r.used > 0).length;
}
