"use client";

import Link from "next/link";
import { useRef, useState } from "react";
import { Icon } from "./Icon";
import { reducedMotion, useCart, useI18n } from "./providers";
import { useAnimatedNumber } from "./hooks";
import { finalPrice, type Product } from "@/lib/catalog";
import { formatNumber, currency } from "@/lib/format";
import { t } from "@/lib/i18n";

export function ProductCard({ p, delay = 0, visible = true }: { p: Product; delay?: number; visible?: boolean }) {
  const { locale, dict } = useI18n();
  const cart = useCart();
  const [opt, setOpt] = useState(p.options[0].id);
  const [fav, setFav] = useState(false);
  const [done, setDone] = useState(false);
  const ref = useRef<HTMLElement>(null);
  const imRef = useRef<HTMLDivElement>(null);
  const price = useAnimatedNumber(finalPrice(p, opt));
  const base = p.options.find((o) => o.id === opt)!.price;
  const quote = base === 0;
  const href = `/${locale}/produit/${p.slug}`;

  // Inclinaison 3D + reflet qui suivent la souris
  const onMove = (e: React.PointerEvent) => {
    if (e.pointerType !== "mouse" || reducedMotion() || !ref.current) return;
    const el = ref.current, r = el.getBoundingClientRect();
    const px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
    el.classList.add("tilting");
    el.style.setProperty("--ry", `${(px - 0.5) * 12}deg`);
    el.style.setProperty("--rx", `${(0.5 - py) * 10}deg`);
    el.style.setProperty("--gx", `${px * 100}%`);
    el.style.setProperty("--gy", `${py * 100}%`);
  };
  const onLeave = () => {
    const el = ref.current;
    if (!el) return;
    el.classList.remove("tilting");
    el.style.setProperty("--rx", "0deg");
    el.style.setProperty("--ry", "0deg");
  };

  const onAdd = () => {
    if (done) return;
    cart.add({ slug: p.slug, option: opt, qty: 1 }, imRef.current);
    setDone(true);
    setTimeout(() => setDone(false), 1800);
  };

  return (
    <article
      ref={ref}
      className={`pc ${visible ? "" : "pre"}`}
      style={{ transitionDelay: visible ? `${delay}s` : undefined }}
      onPointerMove={onMove}
      onPointerLeave={onLeave}
    >
      <span className="glare" />
      <div ref={imRef} className={`im ${p.image ? "has-img" : p.gradient} ${p.options.length > 1 ? "has-qo" : ""}`}>
        {p.image
          // eslint-disable-next-line @next/next/no-img-element -- image envoyée par l'admin
          ? <img src={p.image} alt={t(p.name, locale)} loading="lazy" decoding="async" />
          : <Icon name={p.icon} />}
        {p.deal && <span className="off">-{p.deal.percent}%</span>}
        <button className={`fav ${fav ? "on" : ""}`} onClick={() => setFav(!fav)} aria-label={dict.product.favorite} aria-pressed={fav}>
          <Icon name="heart" />
        </button>
        {p.options.length > 1 && (
          <div className="qo" role="group" aria-label={dict.product.option}>
            {p.options.map((o) => (
              <button key={o.id} className={o.id === opt ? "on" : ""} onClick={() => setOpt(o.id)} aria-pressed={o.id === opt}>
                {t(o.label, locale)}
              </button>
            ))}
          </div>
        )}
      </div>
      <h4><Link href={href}>{t(p.name, locale)}</Link></h4>
      <div className="st" aria-label={`${p.rating}/5`}>
        {[0, 1, 2, 3, 4].map((i) => <Icon key={i} name="star" />)}
        <span>{p.rating} ({p.reviews})</span>
      </div>
      {p.deal && (
        <>
          <div className="stock"><i style={{ "--v": p.deal.sold } as React.CSSProperties} /></div>
          <div className="sl">{dict.product.sold} {Math.round(p.deal.sold * 100)}%</div>
        </>
      )}
      <div className="pz">
        {quote ? dict.product.quote : <>{formatNumber(price)} {currency(locale)}</>}
        {p.deal && !quote && <s>{formatNumber(base)} {currency(locale)}</s>}
      </div>
      {quote ? (
        <Link className="add" href={href}><Icon name="message-circle" />{dict.product.askQuote}</Link>
      ) : (
        <button className={`add ${done ? "done" : ""}`} onClick={onAdd}>
          <Icon name={done ? "check" : "shopping-cart"} />
          {done ? dict.product.added : dict.product.add}
        </button>
      )}
    </article>
  );
}
