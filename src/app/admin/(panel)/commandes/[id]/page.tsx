import Link from "next/link";
import { notFound } from "next/navigation";
import { getOrder, type OrderStatus } from "@/lib/orders";
import { getAllProducts } from "@/lib/products";
import { methodLabel, statusLabel } from "@/lib/order-status";
import { formatPrice } from "@/lib/format";
import { Icon } from "@/components/Icon";
import { deliverFromStockAction, saveNote, setOrderStatus } from "../../../actions";
import { stockLevels } from "@/lib/keys";
import { DeliverForm } from "./DeliverForm";

export const metadata = { title: "Commande" };

const dt = (iso?: string) => (iso ? new Date(iso).toLocaleString("fr-FR", { dateStyle: "short", timeStyle: "short" }) : "—");
const statuses = Object.keys(statusLabel) as OrderStatus[];

export default async function OrderAdmin({ params, searchParams }: PageProps<"/admin/commandes/[id]">) {
  const { id } = await params;
  const sp = await searchParams;
  const order = await getOrder(id);
  if (!order) notFound();
  const [products, levels] = await Promise.all([getAllProducts(), stockLevels()]);
  const inStock = (slug: string, option: string) => levels.get(`${slug}|${option}`) ?? 0;
  const stockOk = order.lines.every((l) => inStock(l.slug, l.option) >= l.qty - (l.keys?.length ?? 0));
  const pic = (slug: string) => products.find((p) => p.slug === slug);
  const wa = `https://wa.me/213${order.customer.phone.replace(/\s/g, "").replace(/^0/, "")}`;

  const keysSummary = order.lines
    .map((l) => `${l.name} (${l.optionLabel}) :\n${(l.keys ?? []).join("\n")}`)
    .filter(Boolean)
    .join("\n\n");

  const waDeliveryMsg = encodeURIComponent(
    `Bonjour ${order.customer.name},\n\nVotre commande SOFT4DZ #${order.id} est prête ! 🎉\n\n${keysSummary ? `Vos accès / clés d'activation :\n${keysSummary}\n\n` : ""}Vous pouvez également retrouver vos clés à tout moment sur votre lien de commande :\nhttps://soft4dz.com/${order.locale}/commande/${order.id}\n\nMerci pour votre confiance !`
  );
  const waDeliveryLink = `https://wa.me/213${order.customer.phone.replace(/\s/g, "").replace(/^0/, "")}?text=${waDeliveryMsg}`;

  return (
    <>
      <div className="adm-h">
        <Link className="back" href="/admin/commandes"><Icon name="arrow-left" className="flip-x" />Commandes</Link>
        <h1>{order.id}</h1>
        <span className={`stt s-${order.status}`}>{statusLabel[order.status]}</span>
        {order.demo && <span className="stt s-cancelled">Démo</span>}
        <a className="btn b-out" href={`/${order.locale}/commande/${order.id}`} target="_blank" rel="noopener"><Icon name="external-link" />Page client</a>
      </div>

      <div className="od">
        <div>
          <section className="card box">
            <h2><Icon name="package" />Produits</h2>
            {order.lines.map((l, i) => {
              const p = pic(l.slug);
              return (
                <div className="line" key={i}>
                  <span className={`pic ${p?.gradient ?? "g-brand"}`}><Icon name={p?.icon ?? "key-round"} /></span>
                  <span><b>{l.name}</b><small>{l.optionLabel} · {formatPrice(l.unitPrice, "fr")} × {l.qty} · <span className={inStock(l.slug, l.option) >= l.qty ? "ok-t" : "ko-t"}>{inStock(l.slug, l.option)} en stock</span></small></span>
                  <b>{formatPrice(l.unitPrice * l.qty, "fr")}</b>
                </div>
              );
            })}
            {order.promo && <div className="line" style={{ gridTemplateColumns: "1fr auto" }}><span>Code promo <b className="mono">{order.promo.code}</b></span><b style={{ color: "var(--teal)" }}>-{formatPrice(order.promo.discount, "fr")}</b></div>}
            <div className="line" style={{ gridTemplateColumns: "1fr auto", fontSize: 16 }}><b>Total</b><b style={{ color: "var(--pri-700)" }}>{formatPrice(order.total, "fr")}</b></div>
          </section>

          <section className="card box">
            <h2><Icon name="key-round" />Livraison des clés</h2>
            {sp.stock === "ok" && <div className="msg ok" style={{ marginBottom: 12 }}><Icon name="check-circle-2" />Commande livrée avec les clés du stock.</div>}
            {sp.stock === "manque" && <div className="msg err" style={{ marginBottom: 12 }}><Icon name="info" />Stock insuffisant : ajoutez des clés dans « Stock de clés » ou saisissez-les ci-dessous.</div>}
            {order.autoDelivered && <p className="muted-s" style={{ marginBottom: 10 }}>Livrée automatiquement depuis le stock.</p>}

            {order.status === "paid" && !stockOk && (
              <div className="msg" style={{ background: "rgba(217, 127, 30, 0.09)", border: "1px solid rgba(217, 127, 30, 0.3)", color: "var(--fg)", marginBottom: 14 }}>
                <Icon name="clock" style={{ color: "var(--gold)" }} />
                <span style={{ fontSize: 13.5 }}>
                  <b>Commande payée sans stock immédiat</b> : Achetez la clé auprès de votre fournisseur et collez-la ci-dessous. Dès que vous cliquez sur « Livrer », le client verra ses clés s'afficher <b>en direct sur son écran</b> !
                </span>
              </div>
            )}

            {order.status === "paid" && (
              <form action={deliverFromStockAction} style={{ marginBottom: 14 }}>
                <input type="hidden" name="id" value={order.id} />
                <button className="btn b-pri" disabled={!stockOk} title={stockOk ? "" : "Stock insuffisant"}><Icon name="zap" />Livrer depuis le stock</button>
                <small className="muted-s" style={{ marginInlineStart: 10 }}>{stockOk ? "Les clés nécessaires sont disponibles." : "Pas assez de clés en stock (achat à la demande)."}</small>
              </form>
            )}

            {order.status === "paid" || order.status === "delivered" ? (
              <>
                <DeliverForm order={order} />
                {order.status === "delivered" && (
                  <div style={{ marginTop: 14, paddingTop: 14, borderTop: "1px solid var(--border)" }}>
                    <a className="btn b-or" href={waDeliveryLink} target="_blank" rel="noopener">
                      <Icon name="message-circle" />Envoyer les clés sur WhatsApp en 1 clic
                    </a>
                  </div>
                )}
              </>
            ) : (
              <p style={{ fontSize: 14, color: "var(--muted)" }}>
                {order.status === "awaiting_transfer"
                  ? "Vérifiez le reçu de versement CCP du client, puis passez la commande en « Payée » pour pouvoir livrer les clés."
                  : "La livraison est possible une fois la commande payée."}
              </p>
            )}
          </section>
        </div>

        <aside>
          <section className="card box">
            <h2><Icon name="user" />Client</h2>
            <dl className="kv">
              <dt>Nom</dt><dd>{order.customer.name}</dd>
              <dt>Téléphone</dt><dd><a href={wa} target="_blank" rel="noopener">{order.customer.phone}</a></dd>
              <dt>E-mail</dt><dd><a href={`mailto:${order.customer.email}`}>{order.customer.email}</a></dd>
              <dt>Langue</dt><dd>{{ fr: "Français", ar: "Arabe", en: "Anglais" }[order.locale]}</dd>
            </dl>
          </section>
          <section className="card box">
            <h2><Icon name="credit-card" />Paiement</h2>
            <dl className="kv">
              <dt>Moyen</dt><dd>{methodLabel[order.method]}</dd>
              <dt>Créée</dt><dd>{dt(order.createdAt)}</dd>
              <dt>Payée</dt><dd>{dt(order.paidAt)}</dd>
              <dt>Livrée</dt><dd>{dt(order.deliveredAt)}</dd>
              {order.chargilyCheckoutId && <><dt>Chargily</dt><dd style={{ wordBreak: "break-all", fontSize: 12 }}>{order.chargilyCheckoutId}</dd></>}
            </dl>
            <form action={setOrderStatus} className="st-form" style={{ marginTop: 14 }}>
              <input type="hidden" name="id" value={order.id} />
              <select key={order.status} name="status" defaultValue={order.status} aria-label="Statut">
                {statuses.map((s) => <option key={s} value={s}>{statusLabel[s]}</option>)}
              </select>
              <button className="btn b-pri" style={{ height: 44 }}>OK</button>
            </form>
          </section>
          <section className="card box">
            <h2><Icon name="pencil" />Note interne</h2>
            <form action={saveNote}>
              <input type="hidden" name="id" value={order.id} />
              <textarea className="ta plain" name="note" defaultValue={order.note} placeholder="Visible uniquement dans l'admin" />
              <button className="btn b-out" style={{ marginTop: 8, height: 40 }}><Icon name="save" />Enregistrer</button>
            </form>
          </section>
        </aside>
      </div>
    </>
  );
}
