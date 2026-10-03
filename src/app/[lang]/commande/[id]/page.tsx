import Link from "next/link";
import { notFound } from "next/navigation";
import { hasLocale } from "@/lib/i18n";
import { getDictionary } from "@/lib/dictionaries";
import { getAllProducts } from "@/lib/products";
import { getOrder, updateOrder } from "@/lib/orders";
import { chargilyEnabled, getCheckout } from "@/lib/chargily";
import { getSlickPayInvoice, slickpayEnabled } from "@/lib/slickpay";
import { formatPrice } from "@/lib/format";
import { waLink } from "@/lib/site";
import { getSettings } from "@/lib/settings";
import { deliverFromStock } from "@/lib/fulfil";
import { Icon } from "@/components/Icon";
import { Stepper } from "@/components/checkout/Stepper";
import { StatusIcon } from "@/components/checkout/StatusIcon";
import { CopyKey } from "@/components/checkout/CopyKey";

export const metadata = { robots: { index: false } };

/** a***@exemple.com : on n'affiche pas l'adresse complète sur une page accessible par lien. */
const mask = (email: string) => email.replace(/^(.)[^@]*/, "$1***");

export default async function OrderPage({ params, searchParams }: PageProps<"/[lang]/commande/[id]">) {
  const { lang, id } = await params;
  if (!hasLocale(lang)) notFound();
  const d = getDictionary(lang);
  const sp = await searchParams;
  let order = await getOrder(id.toUpperCase());

  if (!order) {
    return (
      <div className="done-w">
        <StatusIcon kind="ko" />
        <h1>{d.done.notFound}</h1>
        <Link className="btn b-pri" href={`/${lang}/commande`} style={{ marginTop: 20 }}>{d.footer.track}</Link>
      </div>
    );
  }

  // 1. Au retour de Slick-Pay, on revérifie le statut de la facture SATIM
  if (order.status === "pending" && order.slickpayInvoiceId && slickpayEnabled()) {
    try {
      const inv = await getSlickPayInvoice(order.slickpayInvoiceId);
      const isPaid = inv.completed === 1 || inv.data?.completed === 1;
      if (isPaid) {
        await updateOrder(order.id, { status: "paid" });
        await deliverFromStock(order.id);
        order = (await getOrder(order.id)) ?? order;
      }
    } catch (e) {
      console.error("SlickPay verification error:", e);
    }
  }

  // 2. Au retour de Chargily
  if (order.status === "pending" && order.chargilyCheckoutId && chargilyEnabled()) {
    try {
      const c = await getCheckout(order.chargilyCheckoutId);
      if (c.status === "paid") {
        await updateOrder(order.id, { status: "paid" });
        await deliverFromStock(order.id);
        order = (await getOrder(order.id)) ?? order;
      }
      else if (c.status === "failed" || c.status === "canceled" || c.status === "expired") order = (await updateOrder(order.id, { status: "failed" })) ?? order;
    } catch (e) { console.error(e); }
  }

  const [all, settings] = await Promise.all([getAllProducts(), getSettings()]);
  const getProduct = (slug: string) => all.find((p) => p.slug === slug);
  const failed = order.status === "failed" || order.status === "cancelled" || (order.status === "pending" && sp.echec === "1");
  const delivered = order.status === "delivered";
  const kind = order.status === "paid" || delivered ? "ok" : failed ? "ko" : "wait";
  const title = delivered ? d.done.delivered : kind === "ok" ? d.done.title : kind === "ko" ? d.done.failed : d.done.pending;
  const text = delivered ? d.done.deliveredText : kind === "ok" ? `${d.done.sent} ${mask(order.customer.email)}.` : kind === "ko" ? d.done.failedText : order.method === "ccp" ? d.done.pendingText : "";

  return (
    <>
      <Stepper step={kind === "ok" ? 3 : 2} />
      <div className="done-w">
        <StatusIcon kind={kind} />
        <h1>{title}</h1>
        {text && <p>{text}</p>}
        <div className="ord"><Icon name="receipt" />{d.done.order} <b>#{order.id}</b></div>
        {order.demo && <div className="demo-note" style={{ justifyContent: "center" }}><Icon name="info" />{d.demoMode}</div>}

        <div className="card keys">
          <h3><Icon name="key-round" />{d.done.keys}</h3>
          {order.lines.map((l, i) => {
            const p = getProduct(l.slug);
            return (
              <div className="key" key={i} style={{ "--d": `${0.4 + i * 0.12}s` } as React.CSSProperties}>
                <span className={`im ${p?.gradient ?? "g-brand"}`}><Icon name={p?.icon ?? "key-round"} /></span>
                <span><b>{l.name}</b><small>{l.optionLabel} × {l.qty}</small></span>
                <span className="pz">{formatPrice(l.unitPrice * l.qty, lang)}</span>
                {delivered && !!l.keys?.length && (
                  <div className="kl">{l.keys.map((k, j) => <CopyKey key={j} value={k} copy={d.done.copy} copied={d.done.copied} />)}</div>
                )}
              </div>
            );
          })}
          <div className="row" style={{ display: "flex", justifyContent: "space-between", fontWeight: 700, marginTop: 10 }}>
            <span>{d.cart.total}</span><span style={{ color: "var(--pri-700)" }}>{formatPrice(order.total, lang)}</span>
          </div>
          {kind === "ok" && !delivered && <div className="hint"><Icon name="info" />{d.done.keysHint}</div>}
        </div>

        <div style={{ display: "flex", gap: 10, justifyContent: "center", marginTop: 22, flexWrap: "wrap" }}>
          {kind === "ko" && <Link className="btn b-or" href={`/${lang}/panier`}>{d.done.retry}</Link>}
          {order.method === "ccp" && kind === "wait" && (
            <a className="btn b-or" href={waLink(settings.whatsapp, `${d.done.order} #${order.id}`)} target="_blank" rel="noopener"><Icon name="message-circle" />WhatsApp</a>
          )}
          <Link className="btn b-out" href={`/${lang}`}>{d.done.continue}</Link>
        </div>
      </div>
    </>
  );
}
