"use client";

import Link from "next/link";
import { useCallback, useEffect, useRef, useState } from "react";
import { Icon } from "../Icon";
import { ProductPoster } from "../ProductPoster";
import { reducedMotion, useCatalog, useI18n } from "../providers";
import { finalPrice } from "@/lib/catalog";
import { formatNumber, currency } from "@/lib/format";
import { t } from "@/lib/i18n";
import type { Slide } from "@/lib/slides";

const DURATION = 5000;

/** Carrousel d'accueil : les diapositives viennent d'Admin › Bannières. */
export function HeroCarousel({ slides }: { slides: Slide[] }) {
  const { locale, dict } = useI18n();
  const { get } = useCatalog();
  const [cur, setCur] = useState(0);
  const [paused, setPaused] = useState(false);
  const startX = useRef<number | null>(null);
  const go = useCallback((i: number) => setCur((i + slides.length) % slides.length), [slides.length]);
  const cur$ = currency(locale);

  useEffect(() => {
    if (paused || reducedMotion()) return;
    const id = setTimeout(() => go(cur + 1), DURATION);
    return () => clearTimeout(id);
  }, [cur, paused, go]);

  const rtl = locale === "ar";
  return (
    <div
      className={`slider ${paused ? "paused" : ""}`}
      aria-roledescription="carousel"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
      onPointerDown={(e) => (startX.current = e.clientX)}
      onPointerUp={(e) => {
        if (startX.current == null) return;
        const dx = e.clientX - startX.current;
        startX.current = null;
        if (Math.abs(dx) > 50) go(cur + ((dx < 0) !== rtl ? 1 : -1));
      }}
    >
      <div className="track" style={{ transform: `translateX(${(rtl ? 1 : -1) * cur * 100}%)` }}>
        {slides.map((s, i) => {
          const shown = s.products.map(get).filter((p) => p !== undefined);
          const ref = s.priceFrom ? get(s.priceFrom) : undefined;
          // Prix réels du catalogue (modifiables depuis l'admin)
          const now = ref ? Math.min(...ref.options.map((o) => finalPrice(ref, o.id)).filter(Boolean)) : 0;
          const base = ref?.deal ? ref.options.find((o) => finalPrice(ref, o.id) === now)?.price : undefined;
          return (
            <div key={s.id} className={`slide ${i === cur ? "act" : ""} ${s.image ? "has-img" : ""}`} style={{ background: s.image ? `linear-gradient(${rtl ? 270 : 90}deg,rgba(8,18,34,.82) 0%,rgba(8,18,34,.55) 45%,rgba(8,18,34,0) 75%), url(${s.image}) center/cover, ${s.bg}` : s.bg }} aria-hidden={i !== cur}>
              <div className="tx">
                {t(s.tag, locale) && <span className="tag"><Icon name="sparkles" />{t(s.tag, locale)}</span>}
                <h2>{t(s.title, locale)} <em>{t(s.highlight, locale)}</em></h2>
                <p>{t(s.text, locale)}</p>
                {now > 0 && (
                  <div className="price-line">
                    <small>{dict.product.from}</small>
                    <b>{formatNumber(now)} {cur$}</b>
                    {base && <><s>{formatNumber(base)} {cur$}</s><span>-{ref!.deal!.percent}%</span></>}
                  </div>
                )}
                <Link className="btn b-or" href={`/${locale}${s.href}`} tabIndex={i === cur ? 0 : -1}>
                  {t(s.cta, locale)}<Icon name="arrow-right" className="ar flip-x" />
                </Link>
              </div>
              {!s.image && <div className="comp" aria-hidden="true">
                {shown.map((p) => <ProductPoster key={p.slug} p={p} locale={locale} />)}
              </div>}
            </div>
          );
        })}
      </div>
      <button className="sarr sp" onClick={() => go(cur - 1)} aria-label={dict.carousel.prev}><Icon name="chevron-left" className="flip-x" /></button>
      <button className="sarr sn" onClick={() => go(cur + 1)} aria-label={dict.carousel.next}><Icon name="chevron-right" className="flip-x" /></button>
      <div className="dots" style={{ "--dur": `${DURATION}ms` } as React.CSSProperties}>
        {slides.map((_, i) => (
          <button key={i} className={i === cur ? "on" : ""} onClick={() => go(i)} aria-label={`${i + 1} / ${slides.length}`} aria-current={i === cur} />
        ))}
      </div>
    </div>
  );
}
