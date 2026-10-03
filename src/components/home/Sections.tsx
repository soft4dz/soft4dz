"use client";

import Link from "next/link";
import { useLayoutEffect, useMemo, useRef, useState } from "react";
import { Icon } from "../Icon";
import { ProductCard } from "../ProductCard";
import { reducedMotion, useI18n } from "../providers";
import { useAnimatedNumber, useReveal } from "../hooks";
import { Countdown } from "./Countdown";
import { categories, type CategoryId, type Product } from "@/lib/catalog";
import { formatNumber, currency } from "@/lib/format";
import { t } from "@/lib/i18n";

/* ---------------- Bandeau de confiance ---------------- */
export function TrustBar() {
  const { dict } = useI18n();
  const [ref, on] = useReveal<HTMLElement>();
  const n = useAnimatedNumber(on ? 12480 : 0, 1600);
  const t = dict.trust;
  return (
    <section ref={ref} className={`trust rv ${on ? "on" : ""}`}>
      <div><span className="ti"><Icon name="badge-check" /></span><span>{t.authentic}<small>{formatNumber(n)} {t.customers}</small></span></div>
      <div><span className="ti"><Icon name="zap" /></span><span>{t.instant}<small>{t.instantSub}</small></span></div>
      <div><span className="ti"><Icon name="credit-card" /></span><span>{t.secure}<small>CIB · Edahabia</small></span></div>
      <div><span className="ti"><Icon name="headset" /></span><span>{t.support}<small>{t.supportSub}</small></span></div>
    </section>
  );
}

/* ---------------- Offres du jour ---------------- */
export function Deals({ items }: { items: Product[] }) {
  const { dict, locale } = useI18n();
  const [ref, on] = useReveal<HTMLElement>();
  return (
    <section ref={ref} className={`blk rv ${on ? "on" : ""}`}>
      <div className="bh">
        <h2><Icon name="flame" style={{ color: "var(--orange)" }} />{dict.home.deals}</h2>
        <Countdown />
        <Link className="all" href={`/${locale}#catalogue`}>{dict.home.seeAll}<Icon name="chevron-right" className="flip-x" /></Link>
      </div>
      <div className="pr">
        {items.map((p, i) => <ProductCard key={p.slug} p={p} delay={i * 0.06} visible={on} />)}
      </div>
    </section>
  );
}

/* ---------------- Catalogue filtrable (réarrangement animé) ---------------- */
export function Catalogue({ items, initialCat, query }: { items: Product[]; initialCat?: string; query?: string }) {
  const { dict, locale } = useI18n();
  const [ref, on] = useReveal<HTMLElement>();
  const valid = categories.some((c) => c.id === initialCat) ? (initialCat as CategoryId) : "all";
  const [cat, setCat] = useState<CategoryId | "all">(valid);
  const cards = useRef(new Map<string, HTMLDivElement>());
  const before = useRef<Map<string, DOMRect> | null>(null);

  const q = (query ?? "").toLowerCase();
  const shown = useMemo(
    () => items.filter((p) =>
      (cat === "all" || p.category === cat) &&
      (!q || p.name.fr.toLowerCase().includes(q) || p.name.ar.includes(q))),
    [items, cat, q],
  );

  const pick = (c: CategoryId | "all") => {
    if (c === cat) return;
    // FLIP : on mémorise les positions avant de changer le filtre
    before.current = new Map([...cards.current].map(([k, el]) => [k, el.getBoundingClientRect()]));
    setCat(c);
  };

  useLayoutEffect(() => {
    const prev = before.current;
    before.current = null;
    if (!prev || reducedMotion()) return;
    cards.current.forEach((el, k) => {
      const a = prev.get(k), b = el.getBoundingClientRect();
      if (!a) el.animate([{ opacity: 0, transform: "scale(.7)" }, { opacity: 1, transform: "scale(1)" }], { duration: 450, easing: "cubic-bezier(.34,1.56,.64,1)" });
      else el.animate([{ translate: `${a.left - b.left}px ${a.top - b.top}px` }, { translate: "0 0" }], { duration: 500, easing: "cubic-bezier(.22,1,.36,1)" });
    });
  }, [cat]);

  const tabs: { id: CategoryId | "all"; icon: Parameters<typeof Icon>[0]["name"]; label: string }[] = [
    { id: "all", icon: "layout-grid", label: dict.home.all },
    ...categories.filter((c) => c.id !== "svc").map((c) => ({ id: c.id, icon: c.icon, label: t(c.name, locale) })),
  ];

  return (
    <section ref={ref} id="catalogue" className={`blk rv ${on ? "on" : ""}`} style={{ scrollMarginTop: 90 }}>
      <div className="bh">
        <h2><Icon name="store" style={{ color: "var(--teal)" }} />{dict.home.catalogue}{q && <small style={{ fontWeight: 500, color: "var(--muted)", fontSize: 14 }}> · « {query} »</small>}</h2>
        <div className="ftabs" role="tablist">
          {tabs.map((t) => (
            <button key={t.id} role="tab" aria-selected={cat === t.id} className={cat === t.id ? "on" : ""} onClick={() => pick(t.id)}>
              <Icon name={t.icon} />{t.label}
            </button>
          ))}
        </div>
      </div>
      <div className="pr">
        {shown.map((p, i) => (
          <div key={p.slug} ref={(el) => { if (el) cards.current.set(p.slug, el); else cards.current.delete(p.slug); }} style={{ display: "flex" }}>
            <div style={{ flex: 1, display: "flex" }}><ProductCard p={p} delay={(i % 6) * 0.06} visible={on} /></div>
          </div>
        ))}
      </div>
      {!shown.length && <p className="empty">{dict.home.noResults}</p>}
    </section>
  );
}

/* ---------------- Services ---------------- */
export function Services({ items }: { items: Product[] }) {
  const { dict, locale } = useI18n();
  const [ref, on] = useReveal<HTMLElement>();
  const enter = (e: React.PointerEvent<HTMLAnchorElement>) => {
    const r = e.currentTarget.getBoundingClientRect();
    e.currentTarget.style.setProperty("--cx", `${e.clientX - r.left}px`);
    e.currentTarget.style.setProperty("--cy", `${e.clientY - r.top}px`);
  };
  const extra = [{ icon: "wrench" as const, name: { fr: "Maintenance", ar: "الصيانة", en: "Maintenance" }, sub: { fr: "Hébergement, mises à jour", ar: "استضافة، تحديثات", en: "Hosting, updates" }, price: { fr: "Dès 3 000 DA/mois", ar: "ابتداء من 3 000 دج/شهر", en: "From 3,000 DA/month" } }];
  return (
    <section ref={ref} className={`svc rv ${on ? "on" : ""}`}>
      <div className="sv0">
        <h2>{dict.home.servicesTitle}</h2>
        <p>{dict.home.servicesText}</p>
        <Link className="btn b-wh" href={`/${locale}/produit/site-vitrine`} style={{ alignSelf: "flex-start", marginTop: "auto" }}>
          {dict.home.quote}<Icon name="arrow-right" className="ar flip-x" />
        </Link>
      </div>
      {items.map((p) => (
        <Link key={p.slug} href={`/${locale}/produit/${p.slug}`} className="sv" onPointerEnter={enter}>
          <span className="ic"><Icon name={p.icon} /></span>
          <h4>{t(p.name, locale)}</h4>
          <p>{t(p.subtitle, locale)}</p>
          <b>{p.options[0].price ? `${dict.product.from} ${formatNumber(p.options[0].price)} ${currency(locale)}` : dict.product.quote}</b>
        </Link>
      ))}
      {extra.map((x) => (
        <a key={x.icon} href="#" className="sv" onPointerEnter={enter}>
          <span className="ic"><Icon name={x.icon} /></span>
          <h4>{t(x.name, locale)}</h4><p>{t(x.sub, locale)}</p><b>{t(x.price, locale)}</b>
        </a>
      ))}
    </section>
  );
}

/* ---------------- Marques ---------------- */
export function Brands() {
  const { dict } = useI18n();
  const [ref, on] = useReveal<HTMLElement>();
  const list = ["MICROSOFT", "NETFLIX", "SPOTIFY", "CANVA", "OPENAI", "ADOBE", "KASPERSKY", "SHAHID"];
  return (
    <section ref={ref} className={`blk rv ${on ? "on" : ""}`}>
      <div className="bh"><h2>{dict.home.brands}</h2></div>
      <div className="brands">{list.map((b, i) => <span key={b} style={{ "--i": i } as React.CSSProperties}>{b}</span>)}</div>
    </section>
  );
}

/* ---------------- Newsletter ---------------- */
export function Newsletter() {
  const { dict } = useI18n();
  const [ref, on] = useReveal<HTMLElement>();
  const [ok, setOk] = useState(false);
  return (
    <section
      ref={ref}
      className={`news rv ${on ? "on" : ""}`}
      onPointerMove={(e) => {
        const r = e.currentTarget.getBoundingClientRect();
        e.currentTarget.style.setProperty("--mx", `${((e.clientX - r.left) / r.width) * 100}%`);
      }}
    >
      <div><h3>{dict.home.newsTitle}</h3><p>{dict.home.newsText}</p></div>
      <form onSubmit={(e) => { e.preventDefault(); setOk(true); }}>
        <input type="email" required placeholder="votre@email.com" aria-label="E-mail" />
        <button className={`btn b-or ${ok ? "ok" : ""}`}>
          {ok && <Icon name="check" />}{ok ? dict.home.newsOk : dict.home.newsBtn}
        </button>
      </form>
    </section>
  );
}
