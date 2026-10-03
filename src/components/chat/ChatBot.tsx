"use client";

import Link from "next/link";
import { useEffect, useRef, useState } from "react";
import { Icon, type IconName } from "../Icon";
import { ProductPoster } from "../ProductPoster";
import { reducedMotion, useCart, useCatalog, useI18n, useSettings } from "../providers";
import { byCategory, detectIntent, ORDER_RE, searchProducts, wantsToBuy, type Intent } from "./brain";
import { categories, finalPrice, type CategoryId } from "@/lib/catalog";
import { formatNumber, currency } from "@/lib/format";
import { t } from "@/lib/i18n";
import { waLink } from "@/lib/site";

type Chip = "products" | "track" | "payment" | "activation" | "delivery" | "contact" | "human";
type OrderInfo = { id: string; status: keyof ReturnType<typeof useI18n>["dict"]["bot"]["orderStatus"]; items: string[] };
type Msg = {
  from: "bot" | "me";
  text?: string;
  products?: string[];
  cats?: boolean;
  order?: OrderInfo;
  chips?: Chip[];
  whatsapp?: boolean;
};

const KEY = "soft4dz-chat";
const ALL_CHIPS: Chip[] = ["products", "track", "payment", "activation", "delivery", "contact", "human"];
const chipIcon: Record<Chip, IconName> = { products: "search", track: "package-search", payment: "credit-card", activation: "key-round", delivery: "zap", contact: "clock", human: "message-circle" };

/** Assistant FAQ du site : réponses prêtes, recherche produit, suivi de commande, relais WhatsApp. */
export function ChatBot() {
  const { locale, dict } = useI18n();
  const b = dict.bot;
  const { products, get } = useCatalog();
  const cart = useCart();
  const settings = useSettings();
  const [open, setOpen] = useState(false);
  const [msgs, setMsgs] = useState<Msg[]>([]);
  const [typing, setTyping] = useState(false);
  const [input, setInput] = useState("");
  const [tip, setTip] = useState(false);
  const [added, setAdded] = useState<string | null>(null);
  const [awaitOrder, setAwaitOrder] = useState(false);
  const list = useRef<HTMLDivElement>(null);
  const field = useRef<HTMLInputElement>(null);

  // Conversation conservée le temps de la visite (changement de page compris)
  useEffect(() => {
    const raf = requestAnimationFrame(() => {
      try { const saved = JSON.parse(sessionStorage.getItem(KEY) || "null"); if (Array.isArray(saved)) setMsgs(saved); } catch {}
    });
    const a = setTimeout(() => setTip(true), 4000), c = setTimeout(() => setTip(false), 9000);
    return () => { cancelAnimationFrame(raf); clearTimeout(a); clearTimeout(c); };
  }, []);
  useEffect(() => { try { sessionStorage.setItem(KEY, JSON.stringify(msgs.slice(-40))); } catch {} }, [msgs]);
  useEffect(() => { list.current?.scrollTo({ top: list.current.scrollHeight, behavior: reducedMotion() ? "auto" : "smooth" }); }, [msgs, typing, open]);
  useEffect(() => {
    if (!open) return;
    const onKey = (e: KeyboardEvent) => { if (e.key === "Escape") setOpen(false); };
    window.addEventListener("keydown", onKey);
    const tm = setTimeout(() => field.current?.focus(), 250);
    return () => { window.removeEventListener("keydown", onKey); clearTimeout(tm); };
  }, [open]);

  const reply = (m: Msg | Msg[], delay = 550) => {
    setTyping(true);
    setTimeout(() => { setTyping(false); setMsgs((prev) => [...prev, ...(Array.isArray(m) ? m : [m])]); }, reducedMotion() ? 0 : delay);
  };

  const openChat = () => {
    setOpen(true);
    setTip(false);
    if (!msgs.length) reply({ from: "bot", text: b.hello, chips: ALL_CHIPS }, 350);
  };

  const trackOrder = async (raw: string) => {
    const id = raw.toUpperCase().replace(/^SD-?/, "SD-");
    setAwaitOrder(false);
    setTyping(true);
    try {
      const r = await fetch(`/api/order-status?id=${encodeURIComponent(id)}`);
      const d = await r.json();
      setTyping(false);
      if (!d.found) setMsgs((p) => [...p, { from: "bot", text: b.orderNotFound, whatsapp: true }]);
      else setMsgs((p) => [...p, { from: "bot", order: { id: d.id, status: d.status, items: d.items }, chips: ["human"] }]);
    } catch {
      setTyping(false);
      setMsgs((p) => [...p, { from: "bot", text: b.orderNotFound, whatsapp: true }]);
    }
  };

  const answer = (intent: Intent | Chip): Msg => {
    switch (intent) {
      case "products": return { from: "bot", text: b.askCategory, cats: true };
      case "track": setAwaitOrder(true); return { from: "bot", text: b.askOrder };
      case "payment": return { from: "bot", text: b.payment, chips: ["delivery", "human"] };
      case "activation": return { from: "bot", text: b.activation, chips: ["human"] };
      case "delivery": return { from: "bot", text: b.delivery, chips: ["payment", "track"] };
      case "contact": return { from: "bot", text: `${b.contact}\n📞 ${settings.phone} · ✉️ ${settings.email}`, whatsapp: true };
      case "human": return { from: "bot", text: b.human, whatsapp: true };
      case "warranty": return { from: "bot", text: b.warranty, whatsapp: true };
      case "thanks": return { from: "bot", text: b.thanks, chips: ALL_CHIPS };
      case "greet": return { from: "bot", text: b.hello, chips: ALL_CHIPS };
    }
  };

  const productsMsg = (slugs: string[], budget?: number | null): Msg =>
    slugs.length
      ? { from: "bot", text: budget ? b.foundBudget : b.found, products: slugs }
      : { from: "bot", text: b.notFound, cats: true, whatsapp: true };

  const onChip = (c: Chip) => {
    setMsgs((p) => [...p, { from: "me", text: b.chips[c] }]);
    reply(answer(c));
  };

  const onCategory = (c: CategoryId) => {
    setMsgs((p) => [...p, { from: "me", text: t(categories.find((x) => x.id === c)!.name, locale) }]);
    reply(productsMsg(byCategory(c, products).map((p) => p.slug)));
  };

  const onSend = (e: React.FormEvent) => {
    e.preventDefault();
    const text = input.trim().slice(0, 300);
    if (!text) return;
    setInput("");
    setMsgs((p) => [...p, { from: "me", text }]);

    // 1. Numéro de commande
    const order = text.match(ORDER_RE);
    if (order) { trackOrder(order[0]); return; }
    if (awaitOrder && /^[a-z0-9]{8}$/i.test(text.replace(/\s/g, ""))) { trackOrder(text.replace(/\s/g, "")); return; }
    // 2. Demandes prioritaires : parler à un humain, signaler un problème, suivre une commande
    const intent = detectIntent(text);
    if (intent === "human" || intent === "warranty" || intent === "track") { reply(answer(intent)); return; }
    // 3. Intention d'achat ou budget → produits
    const search = searchProducts(text, products);
    if (wantsToBuy(text) || search.budget) { reply(productsMsg(search.list.map((p) => p.slug), search.budget)); return; }
    // 4. Autre sujet d'aide reconnu
    if (intent) { reply(answer(intent)); return; }
    // 5. Sinon, peut-être un nom de produit
    if (search.list.length) { reply(productsMsg(search.list.map((p) => p.slug))); return; }
    reply({ from: "bot", text: b.fallback, chips: ALL_CHIPS });
  };

  const summary = () => {
    const mine = msgs.filter((m) => m.from === "me" && m.text).slice(-3).map((m) => `• ${m.text}`);
    return [b.summary, ...mine].join("\n");
  };

  const cur = currency(locale);
  return (
    <>
      <div className={`chat-tip ${tip && !open ? "on" : ""}`} aria-hidden="true">{dict.whatsapp}</div>
      <button className={`chat-fab ${open ? "is-open" : ""}`} onClick={() => (open ? setOpen(false) : openChat())} aria-label={open ? b.close : b.open} aria-expanded={open}>
        <span className="i1"><Icon name="chat" /></span>
        <span className="i2"><Icon name="x" /></span>
      </button>

      <section className={`chat ${open ? "on" : ""}`} role="dialog" aria-label={b.title} aria-hidden={!open}>
        <header className="chat-h">
          <span className="av"><Icon name="bot" /></span>
          <span><b>{b.title}</b><small><i />{b.status}</small></span>
          <button onClick={() => setOpen(false)} aria-label={b.close} tabIndex={open ? 0 : -1}><Icon name="x" /></button>
        </header>

        <div className="chat-list" ref={list} aria-live="polite">
          {msgs.map((m, i) => (
            <div key={i} className={`cm ${m.from}`}>
              {m.text && <div className="bub">{m.text}</div>}

              {m.order && (
                <div className="bub ord-c">
                  <span className="lab">{b.orderIntro} <b>#{m.order.id}</b></span>
                  <span className={`st st-${m.order.status}`}>{b.orderStatus[m.order.status]}</span>
                  <small>{m.order.items.join(" · ")}</small>
                  <Link href={`/${locale}/commande/${m.order.id}`} onClick={() => setOpen(false)}>{b.seeOrder} →</Link>
                </div>
              )}

              {m.products && (
                <div className="cprods">
                  {m.products.map((slug) => {
                    const p = get(slug);
                    if (!p) return null;
                    const prices = p.options.map((o) => finalPrice(p, o.id)).filter(Boolean);
                    const opt = p.options.find((o) => finalPrice(p, o.id) === Math.min(...prices)) ?? p.options[0];
                    const quote = !prices.length;
                    return (
                      <div key={slug} className="cprod">
                        <ProductPoster p={p} locale={locale} />
                        <div>
                          <b>{t(p.name, locale)}</b>
                          <small>{quote ? dict.product.quote : `${dict.product.from} ${formatNumber(Math.min(...prices))} ${cur}`}</small>
                          <div className="cacts">
                            {!quote && (
                              <button onClick={() => { cart.add({ slug, option: opt.id, qty: 1 }); setAdded(slug); setTimeout(() => setAdded(null), 1500); }}>
                                <Icon name={added === slug ? "check" : "shopping-cart"} />{added === slug ? b.added : b.add}
                              </button>
                            )}
                            <Link href={`/${locale}/produit/${slug}`} onClick={() => setOpen(false)}>{b.see}</Link>
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}

              {m.cats && i === msgs.length - 1 && (
                <div className="chips">
                  {categories.map((c) => <button key={c.id} onClick={() => onCategory(c.id)}><Icon name={c.icon} />{t(c.name, locale)}</button>)}
                </div>
              )}
              {m.chips && i === msgs.length - 1 && (
                <div className="chips">
                  {m.chips.map((c) => <button key={c} onClick={() => onChip(c)}><Icon name={chipIcon[c]} />{b.chips[c]}</button>)}
                </div>
              )}
              {m.whatsapp && (
                <a className="wa-btn" href={waLink(settings.whatsapp, summary())} target="_blank" rel="noopener"><Icon name="message-circle" />{b.openWhatsapp}</a>
              )}
            </div>
          ))}
          {typing && <div className="cm bot"><div className="bub typing" aria-label="…"><i /><i /><i /></div></div>}
        </div>

        <form className="chat-in" onSubmit={onSend}>
          <input ref={field} value={input} onChange={(e) => setInput(e.target.value)} placeholder={b.placeholder} aria-label={b.placeholder} maxLength={300} tabIndex={open ? 0 : -1} />
          <button aria-label={b.send} disabled={!input.trim()} tabIndex={open ? 0 : -1}><Icon name="send" className="flip-x" /></button>
        </form>
      </section>
    </>
  );
}
