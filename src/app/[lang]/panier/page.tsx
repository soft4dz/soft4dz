"use client";

import Link from "next/link";
import { useState } from "react";
import { Icon } from "@/components/Icon";
import { useCart, useCatalog, useI18n } from "@/components/providers";
import { useAnimatedNumber } from "@/components/hooks";
import { Stepper } from "@/components/checkout/Stepper";
import { usePromoCheck } from "@/components/checkout/usePromoCheck";
import { finalPrice } from "@/lib/catalog";
import { formatNumber, currency } from "@/lib/format";
import { t } from "@/lib/i18n";

function LinePrice({ value }: { value: number }) {
  return <>{formatNumber(useAnimatedNumber(value))}</>;
}

export default function CartPage() {
  const { locale, dict } = useI18n();
  const cart = useCart();
  const { get: getProduct } = useCatalog();
  const [leaving, setLeaving] = useState<number | null>(null);
  const promo = usePromoCheck(cart.promo, cart.lines);
  const discount = promo?.ok ? promo.discount : 0;
  const sub = useAnimatedNumber(cart.subtotal);
  const total = useAnimatedNumber(cart.subtotal - discount);
  const [code, setCode] = useState("");
  const cur = currency(locale);
  const c = dict.cart;

  const remove = (i: number) => {
    setLeaving(i);
    setTimeout(() => { cart.remove(i); setLeaving(null); }, 300);
  };

  if (!cart.ready) return <Stepper step={0} />;

  return (
    <>
      <Stepper step={0} />
      <div className="co">
        <div className="card items">
          {cart.lines.length === 0 ? (
            <div className="empty">
              <Icon name="shopping-bag" />
              <p>{c.empty}</p>
              <Link className="btn b-pri" href={`/${locale}`} style={{ marginTop: 14 }}>{c.discover}</Link>
            </div>
          ) : cart.lines.map((l, i) => {
            const p = getProduct(l.slug)!;
            const opt = p.options.find((o) => o.id === l.option) ?? p.options[0];
            return (
              <div key={`${l.slug}-${l.option}`} className={`it ${leaving === i ? "bye" : ""}`} style={{ "--d": `${i * 0.08}s` } as React.CSSProperties}>
                <span className={`im ${p.gradient}`}><Icon name={p.icon} /></span>
                <div>
                  <h4><Link href={`/${locale}/produit/${p.slug}`}>{t(p.name, locale)}</Link></h4>
                  <small>{t(opt.label, locale)}</small>
                  <span className="ok"><Icon name="zap" />{c.instant}</span>
                </div>
                <div className="qty">
                  <button onClick={() => cart.setQty(i, l.qty - 1)} aria-label="-"><Icon name="minus" /></button>
                  <span>{l.qty}</span>
                  <button onClick={() => cart.setQty(i, l.qty + 1)} aria-label="+"><Icon name="plus" /></button>
                </div>
                <div style={{ display: "flex", alignItems: "center" }}>
                  <span className="lp"><LinePrice value={finalPrice(p, l.option) * l.qty} /> {cur}</span>
                  <button className="rmb" onClick={() => remove(i)} aria-label={c.remove}><Icon name="trash" /></button>
                </div>
              </div>
            );
          })}
        </div>

        <aside className="card sum">
          <h3>{c.summary}</h3>
          <div className="row"><span>{c.subtotal} ({cart.count} {c.items})</span><b>{formatNumber(sub)} {cur}</b></div>
          <div className="row"><span>{c.delivery}</span><b style={{ color: "var(--teal)" }}>{c.free}</b></div>
          {discount > 0 && <div className="row disc"><span>{c.discount} ({promo?.ok && promo.code})</span><b>-{formatNumber(discount)} {cur}</b></div>}
          <div className="row tot"><span>{c.total}</span><b>{formatNumber(total)} {cur}</b></div>
          {cart.lines.length > 0 && (
            <form className="promo" onSubmit={(e) => { e.preventDefault(); if (code.trim()) cart.setPromo(code.trim().toUpperCase()); }}>
              <input value={code} onChange={(e) => setCode(e.target.value)} placeholder={c.promo} aria-label={c.promo} className={promo && !promo.ok ? "err" : promo?.ok ? "ok" : ""} />
              <button>{c.apply}</button>
            </form>
          )}
          {promo && !promo.ok && (
            <div className="pmsg err" role="alert">
              {c.promoErr[promo.reason]}{promo.reason === "minimum" && ` ${formatNumber(promo.min ?? 0)} ${cur}`}{" "}
              <button type="button" className="lnk" onClick={() => { cart.setPromo(null); setCode(""); }}>✕</button>
            </div>
          )}
          {cart.lines.length > 0 && (
            <Link className="btn b-or" href={`/${locale}/paiement`}>{c.checkout}<Icon name="arrow-right" className="ar flip-x" /></Link>
          )}
          <div className="sec"><Icon name="lock" />{c.secure}</div>
        </aside>
      </div>
    </>
  );
}
