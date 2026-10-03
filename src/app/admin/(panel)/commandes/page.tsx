import Link from "next/link";
import { listOrders, type OrderStatus } from "@/lib/orders";
import { statusLabel } from "@/lib/order-status";
import { OrdersTable } from "../OrdersTable";
import { Icon } from "@/components/Icon";

export const metadata = { title: "Commandes" };

const filters: { id: string; label: string; match: (s: OrderStatus) => boolean }[] = [
  { id: "", label: "Toutes", match: () => true },
  { id: "a-traiter", label: "À traiter", match: (s) => s === "paid" || s === "awaiting_transfer" },
  { id: "paid", label: statusLabel.paid, match: (s) => s === "paid" },
  { id: "awaiting_transfer", label: statusLabel.awaiting_transfer, match: (s) => s === "awaiting_transfer" },
  { id: "delivered", label: statusLabel.delivered, match: (s) => s === "delivered" },
  { id: "failed", label: "Échouées / annulées", match: (s) => s === "failed" || s === "cancelled" || s === "pending" },
];

export default async function OrdersPage({ searchParams }: PageProps<"/admin/commandes">) {
  const sp = await searchParams;
  const current = typeof sp.statut === "string" ? sp.statut : "";
  const q = typeof sp.q === "string" ? sp.q.trim().toLowerCase() : "";
  const all = await listOrders();
  const f = filters.find((x) => x.id === current) ?? filters[0];
  const shown = all.filter((o) => f.match(o.status) && (!q ||
    o.id.toLowerCase().includes(q) || o.customer.name.toLowerCase().includes(q) ||
    o.customer.email.includes(q) || o.customer.phone.replace(/\s/g, "").includes(q.replace(/\s/g, ""))));

  return (
    <>
      <div className="adm-h">
        <h1>Commandes</h1>
        <a className="btn b-out" href="/api/admin/export" download style={{ marginInlineStart: "auto" }}><Icon name="receipt" />Exporter (CSV)</a>
        <form style={{ display: "flex", gap: 8 }}>
          {current && <input type="hidden" name="statut" value={current} />}
          <input className="sel" name="q" defaultValue={q} placeholder="N°, nom, téléphone, e-mail" aria-label="Rechercher une commande" style={{ minWidth: 260 }} />
        </form>
      </div>
      <nav className="chips" aria-label="Filtrer par statut">
        {filters.map((x) => (
          <Link key={x.id} href={x.id ? `?statut=${x.id}` : "?"} className={x.id === f.id ? "on" : ""}>
            {x.label}<b>{all.filter((o) => x.match(o.status)).length}</b>
          </Link>
        ))}
      </nav>
      <div className="card"><OrdersTable orders={shown} /></div>
    </>
  );
}
