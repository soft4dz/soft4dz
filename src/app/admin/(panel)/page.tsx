import Link from "next/link";
import { listOrders } from "@/lib/orders";
import { getAllProducts } from "@/lib/products";
import { stockTable } from "@/lib/admin-stats";
import { methodLabel } from "@/lib/order-status";
import { formatNumber, formatPrice } from "@/lib/format";
import { Icon, type IconName } from "@/components/Icon";
import { OrdersTable } from "./OrdersTable";

export const metadata = { title: "Tableau de bord" };

const day = (d: Date) => d.toISOString().slice(0, 10);

export default async function Dashboard() {
  const [orders, products, stock] = await Promise.all([listOrders(), getAllProducts(), stockTable()]);
  const real = orders.filter((o) => !o.demo);
  const cashed = real.filter((o) => o.status === "paid" || o.status === "delivered");

  const now = new Date();
  const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
  const prevStart = new Date(now.getFullYear(), now.getMonth() - 1, 1);
  const month = cashed.filter((o) => new Date(o.createdAt) >= monthStart).reduce((s, o) => s + o.total, 0);
  const prev = cashed.filter((o) => { const d = new Date(o.createdAt); return d >= prevStart && d < monthStart; }).reduce((s, o) => s + o.total, 0);
  const trend = prev ? Math.round(((month - prev) / prev) * 100) : null;
  const avg = cashed.length ? Math.round(cashed.reduce((s, o) => s + o.total, 0) / cashed.length) : 0;

  // Ventes des 30 derniers jours
  const days = Array.from({ length: 30 }, (_, i) => { const d = new Date(now); d.setDate(d.getDate() - 29 + i); return day(d); });
  const perDay = new Map(days.map((d) => [d, 0]));
  cashed.forEach((o) => { const d = o.createdAt.slice(0, 10); if (perDay.has(d)) perDay.set(d, perDay.get(d)! + o.total); });
  const values = days.map((d) => perDay.get(d)!);
  const maxV = Math.max(1, ...values);

  // Meilleurs produits (quantités vendues)
  const sold = new Map<string, { name: string; qty: number; revenue: number }>();
  cashed.forEach((o) => o.lines.forEach((l) => {
    const e = sold.get(l.slug) ?? { name: l.name, qty: 0, revenue: 0 };
    e.qty += l.qty; e.revenue += l.unitPrice * l.qty; sold.set(l.slug, e);
  }));
  const top = [...sold.values()].sort((a, b) => b.revenue - a.revenue).slice(0, 5);
  const topMax = Math.max(1, ...top.map((t) => t.revenue));

  // Répartition des moyens de paiement
  const methods = (["cib", "edahabia", "ccp"] as const).map((m) => ({ m, n: cashed.filter((o) => o.method === m).length }));
  const mTotal = Math.max(1, methods.reduce((s, x) => s + x.n, 0));

  const toDeliver = orders.filter((o) => o.status === "paid").length;
  const ccp = orders.filter((o) => o.status === "awaiting_transfer").length;
  const low = stock.rows.filter((r) => r.low && r.product.active !== false && r.available + r.used > 0);
  const noPhoto = products.filter((p) => p.active !== false && !p.image).length;

  const kpis: { label: string; value: string; sub?: string; icon: IconName; bg: string; fg: string }[] = [
    { label: "Chiffre d'affaires du mois", value: formatPrice(month, "fr"), sub: trend === null ? "—" : `${trend >= 0 ? "+" : ""}${trend} % vs mois dernier`, icon: "wallet", bg: "var(--pri-50)", fg: "var(--pri)" },
    { label: "Commandes payées", value: String(cashed.length), sub: `Panier moyen ${formatPrice(avg, "fr")}`, icon: "trending-up", bg: "var(--teal-50)", fg: "var(--teal)" },
    { label: "À livrer", value: String(toDeliver), sub: toDeliver ? "Clés à envoyer" : "Tout est livré", icon: "key-round", bg: "#fef3e2", fg: "var(--orange)" },
    { label: "CCP en attente", value: String(ccp), sub: ccp ? "Reçus à vérifier" : "Aucun", icon: "clock", bg: "#f1f3f6", fg: "var(--muted)" },
  ];

  return (
    <>
      <div className="adm-h">
        <h1>Tableau de bord</h1>
        <a className="btn b-out" href="/api/admin/export" download><Icon name="receipt" />Exporter (CSV)</a>
        <Link className="btn b-pri" href="/admin/produits/nouveau" style={{ marginInlineStart: 0 }}><Icon name="plus" />Nouveau produit</Link>
      </div>

      {(toDeliver > 0 || ccp > 0 || low.length > 0 || noPhoto > 0) && (
        <div className="alerts">
          {toDeliver > 0 && <Link href="/admin/commandes?statut=paid" className="alert a-or"><Icon name="key-round" /><b>{toDeliver} commande(s) payée(s)</b> attendent leurs clés</Link>}
          {ccp > 0 && <Link href="/admin/commandes?statut=awaiting_transfer" className="alert"><Icon name="clock" /><b>{ccp} versement(s) CCP</b> à vérifier</Link>}
          {low.length > 0 && <Link href="/admin/stock" className="alert a-warn"><Icon name="info" /><b>Stock bas</b> : {low.slice(0, 3).map((r) => `${r.product.name.fr} (${r.option.label.fr}) ${r.available}`).join(", ")}{low.length > 3 ? "…" : ""}</Link>}
          {noPhoto > 0 && <Link href="/admin/produits" className="alert"><Icon name="palette" /><b>{noPhoto} produit(s)</b> sans photo (affiche générée)</Link>}
        </div>
      )}

      <div className="kpis">
        {kpis.map((k, i) => (
          <div key={k.label} className="card kpi" style={{ "--d": `${i * 0.07}s` } as React.CSSProperties}>
            <span className="ic" style={{ background: k.bg, color: k.fg }}><Icon name={k.icon} /></span>
            <span><b>{k.value}</b><small>{k.label}</small>{k.sub && <em className={`sub ${trend !== null && i === 0 ? (trend >= 0 ? "up" : "down") : ""}`}>{k.sub}</em>}</span>
          </div>
        ))}
      </div>

      <div className="dash">
        <section className="card box">
          <h2><Icon name="trending-up" />Ventes des 30 derniers jours <small className="muted-s">· {formatPrice(values.reduce((a, b) => a + b, 0), "fr")}</small></h2>
          <div className="bars" role="img" aria-label="Ventes quotidiennes des 30 derniers jours">
            {values.map((v, i) => (
              <div key={days[i]} className="bar" style={{ "--h": `${(v / maxV) * 100}%`, "--i": i } as React.CSSProperties} title={`${new Date(days[i]).toLocaleDateString("fr-FR", { day: "2-digit", month: "short" })} : ${formatPrice(v, "fr")}`}>
                <i />
              </div>
            ))}
          </div>
          <div className="bars-x"><span>{new Date(days[0]).toLocaleDateString("fr-FR", { day: "2-digit", month: "short" })}</span><span>Aujourd&apos;hui</span></div>
        </section>

        <section className="card box">
          <h2><Icon name="store" />Meilleures ventes</h2>
          {top.length === 0 ? <p className="muted-s">Pas encore de vente payée.</p> : top.map((t) => (
            <div key={t.name} className="hbar">
              <div><b>{t.name}</b><small>{formatNumber(t.qty)} vendu(s) · {formatPrice(t.revenue, "fr")}</small></div>
              <span><i style={{ width: `${(t.revenue / topMax) * 100}%` }} /></span>
            </div>
          ))}
          <h2 style={{ marginTop: 18 }}><Icon name="credit-card" />Moyens de paiement</h2>
          <div className="split">
            {methods.map((x) => <i key={x.m} className={`m-${x.m}`} style={{ flex: x.n || 0.0001 }} title={`${methodLabel[x.m]} : ${x.n}`} />)}
          </div>
          <div className="legend">{methods.map((x) => <span key={x.m}><i className={`m-${x.m}`} />{methodLabel[x.m]} · {Math.round((x.n / mTotal) * 100)} %</span>)}</div>
        </section>
      </div>

      <div className="card">
        <div className="adm-h" style={{ padding: "16px 16px 0", marginBottom: 6 }}>
          <h2 style={{ fontSize: 16 }}>Dernières commandes</h2>
          <Link href="/admin/commandes" style={{ marginInlineStart: "auto", color: "var(--pri)", fontWeight: 600, fontSize: 14 }}>Tout voir</Link>
        </div>
        <OrdersTable orders={orders.slice(0, 8)} />
      </div>
      <p className="muted-s" style={{ marginTop: 14, fontSize: 12 }}>
        {products.filter((p) => p.active !== false).length} produits en ligne · les commandes « démo » ne comptent pas dans les statistiques.
      </p>
    </>
  );
}
