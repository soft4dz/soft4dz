"use client";

import Link from "next/link";
import Image from "next/image";
import { usePathname, useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { Icon, type IconName } from "./Icon";
import { reducedMotion, useCart, useI18n, useSettings } from "./providers";
import { useAnimatedNumber } from "./hooks";
import { formatNumber, currency } from "@/lib/format";
import { categories } from "@/lib/catalog";
import { locales, localeNames, t } from "@/lib/i18n";

const annIcons: IconName[] = ["clock", "zap", "credit-card", "message-circle"];
const suggestions = ["Windows 11 Pro", "Office 2021", "Netflix 4K", "ChatGPT Plus", "Canva Pro"];

export function Header() {
  const { locale, dict } = useI18n();
  const cart = useCart();
  const settings = useSettings();
  // Messages réglés dans l'admin, sinon les messages par défaut
  const announce = settings.announce[locale]?.length ? settings.announce[locale] : dict.announce;
  const pathname = usePathname();
  const router = useRouter();
  const [ann, setAnn] = useState(0);
  const [scrolled, setScrolled] = useState(false);
  const [ph, setPh] = useState("");
  const [user, setUser] = useState<{ name: string; avatar?: string } | null>(null);
  const total = useAnimatedNumber(cart.subtotal);

  // Vérifier si le client est connecté
  useEffect(() => {
    fetch("/api/auth/me")
      .then((r) => r.json())
      .then((d) => {
        if (d.authenticated && d.user) setUser(d.user);
        else setUser(null);
      })
      .catch(() => {});
  }, [pathname]);

  // Barre d'annonces : les messages défilent verticalement
  useEffect(() => {
    if (reducedMotion()) return;
    const id = setInterval(() => setAnn((a) => (a + 1) % announce.length), 3500);
    return () => clearInterval(id);
  }, [announce.length]);

  useEffect(() => {
    let ticking = false;
    const onScroll = () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          const y = window.scrollY;
          setScrolled((prev) => {
            if (!prev && y > 60) return true;
            if (prev && y < 20) return false;
            return prev;
          });
          ticking = false;
        });
        ticking = true;
      }
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  // Suggestions qui s'écrivent dans la barre de recherche
  useEffect(() => {
    let i = 0, c = 0, del = false, t: ReturnType<typeof setTimeout>;
    if (reducedMotion()) { t = setTimeout(() => setPh(suggestions[0]), 0); return () => clearTimeout(t); }
    const step = () => {
      const w = suggestions[i];
      setPh(w.slice(0, c));
      if (!del) { c++; if (c > w.length) { del = true; t = setTimeout(step, 1400); return; } }
      else { c--; if (c < 0) { del = false; c = 0; i = (i + 1) % suggestions.length; } }
      t = setTimeout(step, del ? 40 : 90);
    };
    t = setTimeout(step, 0);
    return () => clearTimeout(t);
  }, []);

  const switchTo = (l: string) => pathname.replace(/^\/(fr|ar|en)(?=\/|$)/, `/${l}`);

  const onSearch = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const data = new FormData(e.currentTarget);
    const q = String(data.get("q") ?? "").trim();
    const cat = String(data.get("cat") ?? "");
    const params = new URLSearchParams();
    if (q) params.set("q", q);
    if (cat) params.set("cat", cat);
    router.push(`/${locale}?${params}#catalogue`);
  };

  return (
    <>
      <div className="util">
        <div className="w">
          <div className="ann" aria-live="polite">
            {announce.map((a, i) => (
              <div key={i} className={i === ann ? "on" : i === (ann - 1 + announce.length) % announce.length ? "out" : ""}>
                <Icon name={annIcons[i % annIcons.length]} />{a}
              </div>
            ))}
          </div>
          <nav className="langsw" aria-label="Langue">
            {locales.map((l) => (
              <Link key={l} href={switchTo(l)} className={locale === l ? "on" : ""} hrefLang={l} lang={l}>{localeNames[l]}</Link>
            ))}
          </nav>
        </div>
      </div>

      <header className={`hdr ${scrolled ? "scrolled" : ""}`}>
        <div className="w hd">
          <Link className="logo" href={`/${locale}`}>
            <Image src="/logo.png" alt="SOFT4DZ" width={40} height={50} priority style={{ width: "auto" }} />
            SOFT4DZ
          </Link>
          <form className="srch" role="search" onSubmit={onSearch}>
            <select name="cat" aria-label={dict.header.all} defaultValue="">
              <option value="">{dict.header.all}</option>
              {categories.map((c) => <option key={c.id} value={c.id}>{t(c.name, locale)}</option>)}
            </select>
            <input name="q" aria-label={dict.header.searchLabel} placeholder={dict.header.search + ph} />
            <button aria-label={dict.header.searchLabel}><Icon name="search" /></button>
          </form>
          <div className="hi">
            <Link className="hb" href={user ? `/${locale}/mon-compte` : `/${locale}/connexion`}>
              {user?.avatar ? (
                // eslint-disable-next-line @next/next/no-img-element
                <img
                  src={user.avatar}
                  alt={user.name}
                  style={{ width: 24, height: 24, borderRadius: "50%", objectFit: "cover" }}
                />
              ) : (
                <Icon name="user" />
              )}
              <span>
                <small>{user ? dict.header.hello : dict.header.account}</small>
                {user ? user.name.split(" ")[0] : dict.header.account}
              </span>
            </Link>
            <Link className="hb" href={`/${locale}/commande`}>
              <Icon name="package-search" /><span><small>{dict.header.track}</small>{dict.header.myOrder}</span>
            </Link>
            <Link className="hb cart" href={`/${locale}/panier`}>
              <span className="ct" id="cart-icon">
                <Icon name="shopping-cart" /><b id="cart-count">{cart.ready ? cart.count : 0}</b>
              </span>
              <span><small>{dict.header.cart}</small>{formatNumber(total)} {currency(locale)}</span>
            </Link>
          </div>
        </div>
      </header>
    </>
  );
}
