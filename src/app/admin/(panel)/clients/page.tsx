import Link from "next/link";
import { listOrders } from "@/lib/orders";
import { formatPrice } from "@/lib/format";

export const metadata = { title: "Clients" };

/** Clients reconstitués à partir des commandes (regroupés par téléphone). */
export default async function ClientsPage({ searchParams }: PageProps<"/admin/clients">) {
  const sp = await searchParams;
  const q = typeof sp.q === "string" ? sp.q.trim().toLowerCase() : "";
  const orders = (await listOrders()).filter((o) => !o.demo);
  const map = new Map<string, { name: string; phone: string; email: string; locale: string; orders: number; paid: number; spent: number; last: string; ids: string[] }>();
  for (const o of orders) {
    const key = o.customer.phone.replace(/\s/g, "");
    const c = map.get(key) ?? { name: o.customer.name, phone: o.customer.phone, email: o.customer.email, locale: o.locale, orders: 0, paid: 0, spent: 0, last: o.createdAt, ids: [] };
    c.orders++;
    c.ids.push(o.id);
    if (o.status === "paid" || o.status === "delivered") { c.paid++; c.spent += o.total; }
    if (o.createdAt > c.last) c.last = o.createdAt;
    map.set(key, c);
  }
  const clients = [...map.values()]
    .filter((c) => !q || c.name.toLowerCase().includes(q) || c.email.includes(q) || c.phone.replace(/\s/g, "").includes(q.replace(/\s/g, "")))
    .sort((a, b) => b.spent - a.spent);
  const wa = (phone: string) => `https://wa.me/213${phone.replace(/\s/g, "").replace(/^0/, "")}`;

  return (
    <>
      <div className="adm-h">
        <h1>Clients <small className="muted-s">· {map.size}</small></h1>
        <form style={{ marginInlineStart: "auto" }}>
          <input className="sel" name="q" defaultValue={q} placeholder="Nom, téléphone, e-mail" aria-label="Rechercher un client" style={{ minWidth: 260 }} />
        </form>
      </div>
      <div className="card tbl-w">
        <table className="tbl">
          <thead><tr><th>Client</th><th>Contact</th><th>Commandes</th><th>Total dépensé</th><th>Dernière commande</th><th>Langue</th></tr></thead>
          <tbody>
            {clients.length === 0 && <tr><td colSpan={6} className="empty-row">Aucun client pour le moment (les commandes « démo » ne sont pas comptées).</td></tr>}
            {clients.map((c) => (
              <tr key={c.phone}>
                <td><b>{c.name}</b>{c.paid >= 3 && <div className="muted">★ Client fidèle</div>}</td>
                <td><a className="row-link" href={wa(c.phone)} target="_blank" rel="noopener">{c.phone}</a><div className="muted"><a href={`mailto:${c.email}`}>{c.email}</a></div></td>
                <td>{c.orders}<div className="muted">{c.paid} payée(s)</div></td>
                <td><b>{formatPrice(c.spent, "fr")}</b></td>
                <td className="muted"><Link className="row-link" href={`/admin/commandes/${c.ids[c.ids.length - 1]}`}>{new Date(c.last).toLocaleDateString("fr-FR")}</Link></td>
                <td className="muted">{c.locale.toUpperCase()}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  );
}
