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
import { LiveOrderPoller } from "@/components/checkout/LiveOrderPoller";

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
  const isPaid = order.status === "paid";
  const isPending = order.status === "pending" && !failed;

  const kind = isPaid || delivered ? "ok" : failed ? "ko" : "wait";

  // Titres & descriptions adaptés à chaque étape
  let title = d.done.title;
  let text = `${d.done.sent} ${mask(order.customer.email)}.`;

  if (delivered) {
    title = d.done.delivered;
    text = d.done.deliveredText;
  } else if (isPaid) {
    title = d.done.paidPreparingTitle;
    text = `${d.done.sent} ${mask(order.customer.email)}.`;
  } else if (isPending) {
    if (order.method === "ccp") {
      title = d.done.pending;
      text = d.done.pendingText;
    } else {
      title = d.done.verifying;
      text = d.done.verifyingText;
    }
  } else if (failed) {
    title = d.done.failed;
    text = d.done.failedText;
  }

  return (
    <>
      <Stepper step={delivered ? 3 : isPaid ? 2 : isPending ? 2 : 1} />
      <div className="done-w">
        <StatusIcon kind={kind} />
        <h1>{title}</h1>
        {text && <p>{text}</p>}
        <div className="ord"><Icon name="receipt" />{d.done.order} <b>#{order.id}</b></div>
        {order.demo && <div className="demo-note" style={{ justifyContent: "center" }}><Icon name="info" />{d.demoMode}</div>}

        {/* Suivi en direct par polling automatique */}
        {(isPending || isPaid) && (
          <LiveOrderPoller orderId={order.id} currentStatus={order.status} locale={lang} />
        )}

        {/* Encadré spécial quand le paiement est vérifié mais que la commande est en cours de préparation (stock à la demande) */}
        {isPaid && !delivered && (
          <div
            className="card"
            style={{
              background: "linear-gradient(135deg, rgba(31, 138, 112, 0.08) 0%, rgba(46, 100, 168, 0.09) 100%)",
              border: "1.5px solid rgba(31, 138, 112, 0.3)",
              padding: "20px 22px",
              borderRadius: "16px",
              margin: "18px 0 8px",
              textAlign: "center",
              boxShadow: "0 4px 20px rgba(31, 138, 112, 0.08)",
            }}
          >
            <div style={{ display: "inline-flex", alignItems: "center", gap: 8, padding: "5px 14px", background: "var(--teal)", color: "#fff", borderRadius: 999, fontSize: 13, fontWeight: 700, marginBottom: 10 }}>
              <Icon name="check-circle-2" style={{ width: 16, height: 16 }} />
              <span>{order.method === "cib" || order.method === "edahabia" ? "Paiement CIB / Edahabia validé" : "Paiement validé"}</span>
            </div>
            <h3 style={{ fontSize: 18, fontWeight: 700, margin: "6px 0 8px", color: "var(--fg)" }}>
              ⏱️ {d.done.paidPreparingSub}
            </h3>
            <p style={{ margin: "0 auto 12px", maxWidth: 520, fontSize: 14, color: "var(--muted)", lineHeight: 1.5 }}>
              {d.done.paidPreparingText}
            </p>
            <div style={{ display: "inline-block", background: "var(--card)", padding: "10px 18px", borderRadius: 10, border: "1px solid var(--border)", fontSize: 13, color: "var(--pri-700)", fontWeight: 600 }}>
              💡 {d.done.stayNotice}
            </div>
          </div>
        )}

        {/* Encadré spécial pendant la vérification du paiement SATIM / Slick-Pay */}
        {isPending && order.method !== "ccp" && (
          <div
            className="card"
            style={{
              background: "rgba(46, 100, 168, 0.06)",
              border: "1.5px solid rgba(46, 100, 168, 0.25)",
              padding: "18px 22px",
              borderRadius: "16px",
              margin: "18px 0 8px",
              textAlign: "center",
            }}
          >
            <div style={{ display: "inline-flex", alignItems: "center", gap: 8, color: "#2E64A8", fontWeight: 700, fontSize: 15, marginBottom: 6 }}>
              <Icon name="loader" className="spin" />
              <span>{d.done.verifying}</span>
            </div>
            <p style={{ margin: 0, fontSize: 13.5, color: "var(--muted)", lineHeight: 1.5 }}>
              {d.done.verifyingText}
            </p>
          </div>
        )}

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
          {isPaid && !delivered && (
            <div className="hint" style={{ background: "rgba(31, 138, 112, 0.08)", borderColor: "rgba(31, 138, 112, 0.25)", color: "var(--teal)" }}>
              <Icon name="clock" /> {d.done.paidPreparingSub} · {d.done.stayNotice}
            </div>
          )}
          {isPending && (
            <div className="hint"><Icon name="info" />{order.method === "ccp" ? d.done.pendingText : d.done.verifyingText}</div>
          )}
        </div>

        <div style={{ display: "flex", gap: 10, justifyContent: "center", marginTop: 22, flexWrap: "wrap" }}>
          {failed && <Link className="btn b-or" href={`/${lang}/panier`}>{d.done.retry}</Link>}
          {order.method === "ccp" && isPending && (
            <a className="btn b-or" href={waLink(settings.whatsapp, `${d.done.order} #${order.id}`)} target="_blank" rel="noopener"><Icon name="message-circle" />WhatsApp</a>
          )}
          {isPaid && !delivered && (
            <a className="btn b-out" href={waLink(settings.whatsapp, `Bonjour, j'ai une question sur ma commande #${order.id}`)} target="_blank" rel="noopener">
              <Icon name="message-circle" />Assistance WhatsApp
            </a>
          )}
          <Link className="btn b-out" href={`/${lang}`}>{d.done.continue}</Link>
        </div>
      </div>
    </>
  );
}

