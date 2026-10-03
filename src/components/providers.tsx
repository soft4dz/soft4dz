"use client";

import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from "react";
import { t, type Locale } from "@/lib/i18n";
import type { Dictionary } from "@/lib/dictionaries";
import { finalPrice, type Product } from "@/lib/catalog";
import type { PublicSettings } from "@/lib/settings";
import { Icon, type IconName } from "./Icon";

/* ---------------- Langue ---------------- */
type I18n = { locale: Locale; dict: Dictionary };
const I18nCtx = createContext<I18n | null>(null);
export const useI18n = () => {
  const v = useContext(I18nCtx);
  if (!v) throw new Error("useI18n hors de <Providers>");
  return v;
};

/* ---------------- Paramètres publics de la boutique ---------------- */
const SettingsCtx = createContext<PublicSettings | null>(null);
export const useSettings = () => {
  const v = useContext(SettingsCtx);
  if (!v) throw new Error("useSettings hors de <Providers>");
  return v;
};

/* ---------------- Catalogue (fourni par le serveur) ---------------- */
type Catalog = { products: Product[]; get: (slug: string) => Product | undefined };
const CatalogCtx = createContext<Catalog | null>(null);
export const useCatalog = () => {
  const v = useContext(CatalogCtx);
  if (!v) throw new Error("useCatalog hors de <Providers>");
  return v;
};

/* ---------------- Panier ---------------- */
export type CartLine = { slug: string; option: string; qty: number };
type Cart = {
  lines: CartLine[];
  count: number;
  subtotal: number;
  ready: boolean;
  add: (line: CartLine, from?: HTMLElement | null) => void;
  setQty: (i: number, qty: number) => void;
  remove: (i: number) => void;
  clear: () => void;
  /** Code promo saisi (vérifié par le serveur) */
  promo: string | null;
  setPromo: (code: string | null) => void;
};
const CartCtx = createContext<Cart | null>(null);
export const useCart = () => {
  const v = useContext(CartCtx);
  if (!v) throw new Error("useCart hors de <Providers>");
  return v;
};

const KEY = "soft4dz-cart";
const PROMO_KEY = "soft4dz-promo";
export const reducedMotion = () =>
  typeof window !== "undefined" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/* ---------------- Toast ---------------- */
type ToastData = { title: string; text: string; icon: IconName; gradient: string };

export function Providers({ locale, dict, products, settings, children }: I18n & { products: Product[]; settings: PublicSettings; children: React.ReactNode }) {
  const catalog = useMemo<Catalog>(() => {
    const bySlug = new Map(products.map((p) => [p.slug, p]));
    return { products, get: (slug) => bySlug.get(slug) };
  }, [products]);
  const getProduct = catalog.get;
  const [lines, setLines] = useState<CartLine[]>([]);
  const [ready, setReady] = useState(false);
  const [promo, setPromoState] = useState<string | null>(null);
  const [toast, setToast] = useState<ToastData | null>(null);
  const toastTimer = useRef<ReturnType<typeof setTimeout>>(undefined);

  // Le panier vit dans le navigateur : lecture au chargement et synchro entre onglets
  useEffect(() => {
    const load = () => {
      try {
        const saved = JSON.parse(localStorage.getItem(KEY) || "[]") as CartLine[];
        // On ne garde que les produits qui existent encore au catalogue
        setLines(saved.filter((l) => getProduct(l.slug)));
        setPromoState(localStorage.getItem(PROMO_KEY));
      } catch {}
      setReady(true);
    };
    const onStorage = (e: StorageEvent) => { if (e.key === KEY) load(); };
    const raf = requestAnimationFrame(load);
    window.addEventListener("storage", onStorage);
    return () => { cancelAnimationFrame(raf); window.removeEventListener("storage", onStorage); };
  }, [getProduct]);

  useEffect(() => {
    if (!ready) return;
    try { localStorage.setItem(KEY, JSON.stringify(lines)); } catch {}
  }, [lines, ready]);

  const showToast = useCallback((d: ToastData) => {
    setToast(d);
    clearTimeout(toastTimer.current);
    toastTimer.current = setTimeout(() => setToast(null), 2600);
  }, []);

  const bumpCart = () => {
    ["cart-count", "cart-icon"].forEach((id, k) => {
      const el = document.getElementById(id);
      if (!el) return;
      const cls = k ? "shake" : "bump";
      el.classList.remove(cls);
      void el.offsetWidth;
      el.classList.add(cls);
    });
  };

  const add = useCallback<Cart["add"]>((line, from) => {
    const p = getProduct(line.slug);
    if (!p) return;
    const commit = () => {
      setLines((prev) => {
        const i = prev.findIndex((l) => l.slug === line.slug && l.option === line.option);
        if (i < 0) return [...prev, line];
        const next = [...prev];
        next[i] = { ...next[i], qty: Math.min(10, next[i].qty + line.qty) };
        return next;
      });
      bumpCart();
      showToast({ title: dict.product.addedToCart, text: t(p.name, locale), icon: p.icon, gradient: p.gradient });
    };
    const target = document.getElementById("cart-icon");
    if (!from || !target || reducedMotion() || document.hidden) return commit();

    // L'icône du produit s'envole en arc jusqu'au panier
    const a = from.getBoundingClientRect(), b = target.getBoundingClientRect();
    const fly = document.createElement("div");
    fly.className = `fly ${p.gradient}`;
    fly.innerHTML = (from.querySelector("img") ?? from.querySelector("svg"))?.outerHTML ?? "";
    document.body.appendChild(fly);
    const x0 = a.left + a.width / 2 - 22, y0 = a.top + a.height / 2 - 22;
    const x1 = b.left + b.width / 2 - 22, y1 = b.top + b.height / 2 - 22;
    const r = locale === "ar" ? -1 : 1;
    fly.animate(
      [
        { transform: `translate(${x0}px,${y0}px) scale(1.4)` },
        { transform: `translate(${(x0 + x1) / 2}px,${Math.min(y0, y1) - 120}px) rotate(${180 * r}deg)`, offset: 0.55 },
        { transform: `translate(${x1}px,${y1}px) scale(.3) rotate(${360 * r}deg)`, opacity: 0.6 },
      ],
      { duration: 850, easing: "cubic-bezier(.5,0,.3,1)" },
    ).onfinish = () => { fly.remove(); commit(); };
  }, [dict, locale, showToast, getProduct]);

  const cart = useMemo<Cart>(() => ({
    lines,
    ready,
    count: lines.reduce((s, l) => s + l.qty, 0),
    subtotal: lines.reduce((s, l) => {
      const p = getProduct(l.slug);
      return p ? s + finalPrice(p, l.option) * l.qty : s;
    }, 0),
    add,
    setQty: (i, qty) => setLines((prev) => prev.map((l, k) => (k === i ? { ...l, qty: Math.max(1, Math.min(10, qty)) } : l))),
    remove: (i) => setLines((prev) => prev.filter((_, k) => k !== i)),
    clear: () => { setLines([]); setPromoState(null); try { localStorage.removeItem(PROMO_KEY); } catch {} },
    promo,
    setPromo: (code) => {
      setPromoState(code);
      try { if (code) localStorage.setItem(PROMO_KEY, code); else localStorage.removeItem(PROMO_KEY); } catch {}
    },
  }), [lines, ready, add, getProduct, promo]);

  return (
    <I18nCtx.Provider value={{ locale, dict }}>
      <SettingsCtx.Provider value={settings}>
      <CatalogCtx.Provider value={catalog}>
      <CartCtx.Provider value={cart}>
        {children}
        <div className={`toast ${toast ? "on" : ""}`} role="status" aria-live="polite">
          {toast && (
            <>
              <span className={`ti ${toast.gradient}`}><Icon name={toast.icon} /></span>
              <span><b>{toast.title}</b><small>{toast.text}</small></span>
            </>
          )}
        </div>
      </CartCtx.Provider>
      </CatalogCtx.Provider>
      </SettingsCtx.Provider>
    </I18nCtx.Provider>
  );
}
