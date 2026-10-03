import { isAdmin } from "@/lib/admin-auth";
import { listOrders } from "@/lib/orders";
import { methodLabel, statusLabel } from "@/lib/order-status";

/** Export des commandes au format CSV (ouvrable dans Excel). Réservé à l'admin. */
export async function GET() {
  if (!(await isAdmin())) return new Response("Accès refusé", { status: 403 });
  const orders = await listOrders();
  // Point-virgule : séparateur attendu par Excel en français
  const cell = (v: string | number | undefined) => {
    const s = String(v ?? "");
    // Neutralise les formules (=, +, -, @) pour éviter l'injection dans le tableur
    const safe = /^[=+\-@]/.test(s) ? `'${s}` : s;
    return `"${safe.replace(/"/g, '""')}"`;
  };
  const head = ["Commande", "Date", "Client", "Téléphone", "E-mail", "Langue", "Produits", "Paiement", "Code promo", "Remise (DA)", "Total (DA)", "Statut", "Démo"];
  const rows = orders.map((o) => [
    o.id,
    new Date(o.createdAt).toLocaleString("fr-FR"),
    o.customer.name,
    o.customer.phone,
    o.customer.email,
    o.locale,
    o.lines.map((l) => `${l.name} (${l.optionLabel}) x${l.qty}`).join(" | "),
    methodLabel[o.method],
    o.promo?.code,
    o.promo?.discount ?? 0,
    o.total,
    statusLabel[o.status],
    o.demo ? "oui" : "non",
  ].map(cell).join(";"));
  const csv = "﻿" + [head.map(cell).join(";"), ...rows].join("\r\n");
  const date = new Date().toISOString().slice(0, 10);
  return new Response(csv, {
    headers: {
      "Content-Type": "text/csv; charset=utf-8",
      "Content-Disposition": `attachment; filename="commandes-soft4dz-${date}.csv"`,
      "Cache-Control": "no-store",
    },
  });
}
