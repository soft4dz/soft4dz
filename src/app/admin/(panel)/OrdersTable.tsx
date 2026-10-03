import Link from "next/link";
import type { Order } from "@/lib/orders";
import { methodLabel, statusLabel } from "@/lib/order-status";
import { formatPrice } from "@/lib/format";

const date = (iso: string) =>
  new Date(iso).toLocaleString("fr-FR", { day: "2-digit", month: "2-digit", hour: "2-digit", minute: "2-digit" });

export function OrdersTable({ orders }: { orders: Order[] }) {
  return (
    <div className="tbl-w">
      <table className="tbl">
        <thead>
          <tr><th>Commande</th><th>Date</th><th>Client</th><th>Produits</th><th>Paiement</th><th>Total</th><th>Statut</th></tr>
        </thead>
        <tbody>
          {orders.length === 0 && <tr><td colSpan={7} className="empty-row">Aucune commande pour le moment.</td></tr>}
          {orders.map((o) => (
            <tr key={o.id}>
              <td><Link className="row-link" href={`/admin/commandes/${o.id}`}>{o.id}</Link>{o.demo && <div className="muted">démo</div>}</td>
              <td className="muted">{date(o.createdAt)}</td>
              <td>{o.customer.name}<div className="muted">{o.customer.phone}</div></td>
              <td>{o.lines.map((l) => `${l.name} ×${l.qty}`).join(", ")}</td>
              <td className="muted">{methodLabel[o.method]}</td>
              <td><b>{formatPrice(o.total, "fr")}</b></td>
              <td><span className={`stt s-${o.status}`}>{statusLabel[o.status]}</span></td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
