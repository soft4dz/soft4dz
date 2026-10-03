"use client";

import { useRouter } from "next/navigation";
import { useRef, useState } from "react";
import { Icon } from "../Icon";
import { useCart, useI18n, useSettings } from "../providers";
import { useAnimatedNumber } from "../hooks";
import { finalPrice, type Product } from "@/lib/catalog";
import { formatNumber, currency } from "@/lib/format";
import { waLink } from "@/lib/site";
import { t } from "@/lib/i18n";

/** Galerie + bloc d'achat de la fiche produit (partie interactive). */
export function ProductBuy({ p, stockMap = {} }: { p: Product; stockMap?: Record<string, number> }) {
  const { locale, dict } = useI18n();
  const cart = useCart();
  const router = useRouter();
  const [opt, setOpt] = useState(p.options[0].id);
  const [qty, setQty] = useState(1);
  const [fav, setFav] = useState(false);
  const [done, setDone] = useState(false);
  const imRef = useRef<HTMLDivElement>(null);
  const settings = useSettings();
  const photos = [p.image, ...(p.gallery ?? [])].filter((x): x is string => !!x);
  const [photo, setPhoto] = useState(0);

  const unit = finalPrice(p, opt);
  const base = p.options.find((o) => o.id === opt)!.price;
  const quote = base === 0;
  const hasStock = (stockMap[opt] ?? 0) > 0;
  const now = useAnimatedNumber(unit * qty);
  const cur = currency(locale);
  const perkIcons = ["zap", "badge-check", "refresh-ccw", "credit-card"] as const;

  const add = () => {
    cart.add({ slug: p.slug, option: opt, qty }, imRef.current);
    setDone(true);
    setTimeout(() => setDone(false), 1600);
  };
  const buyNow = () => {
    cart.add({ slug: p.slug, option: opt, qty });
    router.push(`/${locale}/panier`);
  };
  const ask = waLink(settings.whatsapp, `${t(p.name, locale)} — ${t(p.options.find((o) => o.id === opt)!.label, locale)}`);

  return (
    <div className="pg">
      <div className="gal">
        <div ref={imRef} className={`main-im ${photos.length ? "has-img" : p.gradient}`}>
          {photos.length ? (
            // eslint-disable-next-line @next/next/no-img-element -- photo envoyée par l'admin
            <img key={photos[photo]} src={photos[photo]} alt={t(p.name, locale)} className="fade-in" />
          ) : (
            <><span className="ring" /><span className="ic"><Icon name={p.icon} /></span></>
          )}
          <div className="bd">
            {p.deal && <span className="o">-{p.deal.percent}%</span>}
            {!quote && (
              <span style={hasStock ? undefined : { background: "rgba(46, 100, 168, 0.9)" }}>
                <Icon name={hasStock ? "zap" : "clock"} />
                {hasStock ? dict.done.instantStock : dict.done.fastDelivery}
              </span>
            )}
          </div>
        </div>

        {photos.length > 1 && (
          <div className="thumbs">
            {photos.map((src, i) => (
              <button key={src} className={i === photo ? "on" : ""} onClick={() => setPhoto(i)} aria-label={`${i + 1} / ${photos.length}`} aria-pressed={i === photo}>
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={src} alt="" loading="lazy" />
              </button>
            ))}
          </div>
        )}
      </div>

      <div className="info">
        <span className="cat">{t(p.subtitle, locale)}</span>
        <h1>{t(p.name, locale)}</h1>
        <div className="rate">
          <span className="st">{[0, 1, 2, 3, 4].map((i) => <Icon key={i} name="star" />)}</span>
          <b>{p.rating}</b><span>· {p.reviews} {dict.product.reviews}</span>
        </div>

        <div className="price-box">
          {quote ? <span className="now">{dict.product.quote}</span> : (
            <>
              <span className="now">{formatNumber(now)} {cur}</span>
              {p.deal && <><s>{formatNumber(base * qty)} {cur}</s><span className="save" key={opt}>-{p.deal.percent}%</span></>}
            </>
          )}
        </div>

        {p.options.length > 1 && (
          <>
            <div className="lab">{dict.product.option}</div>
            <div className="opts" role="radiogroup" aria-label={dict.product.option}>
              {p.options.map((o) => (
                <button key={o.id} role="radio" aria-checked={o.id === opt} className={o.id === opt ? "on" : ""} onClick={() => setOpt(o.id)}>
                  <span>{t(o.label, locale)}</span>
                  {o.price > 0 && <small>{formatNumber(finalPrice(p, o.id))} {cur}</small>}
                </button>
              ))}
            </div>
          </>
        )}

        {quote ? (
          <div className="buy-row">
            <a className="btn b-or" href={ask} target="_blank" rel="noopener"><Icon name="message-circle" />{dict.product.askQuote}</a>
          </div>
        ) : (
          <>
            <div className="lab">{dict.product.quantity}</div>
            <div className="buy-row">
              <div className="qty">
                <button onClick={() => setQty(Math.max(1, qty - 1))} aria-label="-"><Icon name="minus" /></button>
                <span aria-live="polite">{qty}</span>
                <button onClick={() => setQty(Math.min(10, qty + 1))} aria-label="+"><Icon name="plus" /></button>
              </div>
              <button className="btn b-or" onClick={add} style={done ? { background: "var(--teal)" } : undefined}>
                <Icon name={done ? "check" : "shopping-cart"} />{done ? dict.product.added : dict.product.addToCart}
              </button>
              <button className={`qty fav-btn ${fav ? "on" : ""}`} style={{ width: 50, justifyContent: "center", color: fav ? "#e0245e" : undefined }} onClick={() => setFav(!fav)} aria-label={dict.product.favorite} aria-pressed={fav}>
                <Icon name="heart" style={fav ? { fill: "#e0245e" } : undefined} />
              </button>
            </div>
            <button className="btn b-pri" onClick={buyNow} style={{ width: "100%", marginTop: 10 }}>
              {dict.product.buyNow}<Icon name="arrow-right" className="ar flip-x" />
            </button>
          </>
        )}

        <div className="perks">
          {dict.product.perks.map(([t, s], i) => {
            if (i === 0 && !quote) {
              const perkTitle = hasStock ? dict.done.instantStock : dict.done.fastDelivery;
              const perkSub = hasStock
                ? (locale === "ar" ? "تسليم فوري للمفتاح على الشاشة" : locale === "en" ? "Key delivered immediately on screen" : "Clé délivrée immédiatement à l'écran")
                : (locale === "ar" ? "تجهيز سريع من طرف فريقنا (5-15 دقيقة)" : locale === "en" ? "Fast preparation by our team (5-15 min)" : "Préparation prioritaire par notre équipe (5-15 min)");
              return (
                <div key={t}>
                  <Icon name={hasStock ? "zap" : "clock"} />
                  <span><b>{perkTitle}</b><small>{perkSub}</small></span>
                </div>
              );
            }
            return (
              <div key={t}><Icon name={perkIcons[i]} /><span><b>{t}</b><small>{s}</small></span></div>
            );
          })}
        </div>
        <div className="wa-line"><Icon name="message-circle" />{dict.product.question} <a href={ask} target="_blank" rel="noopener">{dict.product.askWhatsapp}</a></div>
      </div>
    </div>
  );
}
