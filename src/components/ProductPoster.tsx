import { Icon } from "./Icon";
import type { Product } from "@/lib/catalog";
import { t, type Locale } from "@/lib/i18n";

/** Visuel d'un produit : sa photo si elle existe, sinon une « affiche » générée (dégradé, pastille, nom). */
export function ProductPoster({ p, locale, className = "" }: { p: Product; locale: Locale; className?: string }) {
  if (p.image) {
    return (
      <div className={`pt has-img ${className}`}>
        {/* eslint-disable-next-line @next/next/no-img-element -- image envoyée par l'admin, déjà dimensionnée */}
        <img src={p.image} alt={t(p.name, locale)} loading="lazy" decoding="async" />
      </div>
    );
  }
  return (
    <div className={`pt ${p.gradient} ${className}`}>
      <span className="gm"><Icon name={p.icon} /></span>
      <span className="nm">{t(p.name, locale)}<span>{t(p.subtitle, locale)}</span></span>
    </div>
  );
}
