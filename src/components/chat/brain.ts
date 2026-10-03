import { finalPrice, type CategoryId, type Product } from "@/lib/catalog";

/*
 * « Cerveau » de l'assistant FAQ : reconnaissance d'intention par mots-clés
 * (français, arabe, anglais, un peu de darija) et recherche dans le catalogue.
 * Aucune IA, aucun appel externe : tout se passe dans le navigateur.
 */

export type Intent = "track" | "payment" | "activation" | "delivery" | "contact" | "human" | "warranty" | "thanks" | "greet" | "products";

/** Minuscules, sans accents, alef/ya/ta marbouta arabes unifiés. */
export const norm = (s: string) =>
  s.toLowerCase()
    .normalize("NFD").replace(/[̀-ͯ]/g, "")
    .replace(/[إأآا]/g, "ا").replace(/ى/g, "ي").replace(/ة/g, "ه").replace(/[ً-ْ]/g, "")
    .replace(/[^\p{L}\p{N}\s-]/gu, " ").replace(/\s+/g, " ").trim();

const KW: Record<Exclude<Intent, "products">, string[]> = {
  track: ["ma commande", "mon achat", "suivi", "suivre", "statut", "ou en est", "my order", "order status", "track", "تتبع", "طلبي", "الكوموند تاعي"],
  payment: ["paiement", "payer", "paye", "cib", "edahabia", "dahabia", "ccp", "baridimob", "carte", "virement", "versement", "pay", "payment", "card", "دفع", "الدفع", "بطاقه", "الذهبيه", "خلاص", "نخلص"],
  activation: ["activer", "activation", "installer", "installation", "comment utiliser", "product key", "activate", "install", "تفعيل", "فعل", "تثبيت"],
  delivery: ["livraison", "livre", "delai", "quand", "recevoir", "recu", "combien de temps", "delivery", "deliver", "receive", "how long", "تسليم", "متي", "استلام", "وقتاش"],
  contact: ["horaire", "ouvert", "heure", "contact", "telephone", "numero", "adresse", "hours", "open", "phone", "ساعات", "مفتوح", "هاتف", "رقم", "اتصال"],
  human: ["humain", "conseiller", "whatsapp", "agent", "parler", "quelqu un", "human", "talk", "advisor", "person", "شخص", "واتساب", "مستشار", "نتكلم", "نهدر"],
  warranty: ["probleme", "marche pas", "fonctionne pas", "rembourse", "garantie", "invalide", "bloque", "refund", "not working", "warranty", "broken", "مشكل", "مشكله", "استرجاع", "ضمان", "ماخدمتش", "ماخدمش", "مايخدمش", "ما يخدمش", "ماتخدمش", "مخدمش", "خطا", "غلط", "erreur", "error", "ne marche", "marche plus", "refuse", "رفض"],
  thanks: ["merci", "parfait", "super", "top", "thanks", "thank you", "great", "شكرا", "يعطيك الصحه", "بارك الله"],
  greet: ["bonjour", "salut", "bonsoir", "salam", "cc", "hello", "hi", "hey", "مرحبا", "السلام", "اهلا", "سلام"],
};

/** Mots qui désignent une catégorie (en plus des noms de produits). */
const CAT_KW: Record<CategoryId, string[]> = {
  win: ["windows", "win", "systeme", "ويندوز"],
  off: ["office", "word", "excel", "powerpoint", "365", "اوفيس", "وورد"],
  str: ["netflix", "spotify", "shahid", "streaming", "film", "serie", "musique", "music", "series", "نتفليكس", "شاهد", "افلام", "مسلسلات"],
  ia: ["ia", "ai", "chatgpt", "gpt", "canva", "adobe", "photoshop", "design", "ذكاء", "كانفا"],
  sec: ["antivirus", "virus", "kaspersky", "eset", "securite", "security", "حمايه", "فيروس"],
  svc: ["site", "web", "application", "logiciel", "devis", "website", "app", "software", "موقع", "تطبيق", "برنامج"],
};

/** Mots d'achat : la question porte sur un produit à acheter plutôt que sur un sujet d'aide. */
const BUY = ["acheter", "achat", "prix", "combien", "tarif", "je veux", "je cherche", "cherche", "besoin", "pas cher", "moins cher", "commander", "buy", "price", "how much", "cheap", "want", "looking for", "نشري", "شراء", "سعر", "ثمن", "بكم", "نحب", "حاب"];
export const wantsToBuy = (text: string) => { const t = ` ${norm(text)} `; return BUY.some((k) => t.includes(` ${norm(k)}`)); };

export const ORDER_RE = /\bSD-?[A-Z0-9]{8}\b/i;

export function detectIntent(text: string): Intent | null {
  const t = ` ${norm(text)} `;
  // Arabe : les mots se conjuguent avec préfixes/suffixes (نفعل، تفعيل…) → on cherche aussi à l'intérieur des mots
  const arabic = (k: string) => /[؀-ۿ]/.test(k);
  const hit = (k: string) => t.includes(` ${norm(k)} `) || ((k.length > 4 || (arabic(k) && k.length >= 3)) && t.includes(norm(k)));
  // Ordre de priorité : un numéro de commande l'emporte sur tout le reste
  if (ORDER_RE.test(text)) return "track";
  for (const i of ["human", "warranty", "track", "payment", "activation", "delivery", "contact"] as const) if (KW[i].some(hit)) return i;
  if (KW.thanks.some(hit) && t.trim().split(" ").length <= 4) return "thanks";
  if (KW.greet.some(hit) && t.trim().split(" ").length <= 3) return "greet";
  return null;
}

/** Budget exprimé dans la question (« moins de 2000 », « < 3000 da », « 1500 دج »). */
export function detectBudget(text: string): number | null {
  const m = norm(text).match(/(\d[\d\s]{2,})/);
  if (!m) return null;
  const n = Number(m[1].replace(/\s/g, ""));
  return n >= 100 && n <= 1_000_000 ? n : null;
}

/** Recherche produits : catégorie, nom (toutes langues) et budget. */
export function searchProducts(text: string, products: Product[]): { list: Product[]; budget: number | null } {
  const t = norm(text);
  const words = t.split(" ").filter((w) => w.length > 1);
  const budget = detectBudget(text);
  const cats = (Object.keys(CAT_KW) as CategoryId[]).filter((c) => CAT_KW[c].some((k) => ` ${t} `.includes(` ${norm(k)} `)));

  const scored = products.map((p) => {
    const hay = norm([p.name.fr, p.name.ar, p.name.en, p.subtitle.fr, p.slug.replace(/-/g, " ")].join(" "));
    let score = words.reduce((s, w) => s + (w.length > 2 && hay.includes(w) ? 2 : 0), 0);
    if (cats.includes(p.category)) score += 3;
    if (p.featured) score += 0.5;
    return { p, score };
  });
  let list = scored.filter((x) => x.score >= 2).sort((a, b) => b.score - a.score).map((x) => x.p);
  if (budget) {
    const base = list.length ? list : cats.length ? products.filter((p) => cats.includes(p.category)) : products;
    list = base
      .filter((p) => { const prices = p.options.map((o) => finalPrice(p, o.id)).filter(Boolean); return prices.length && Math.min(...prices) <= budget; })
      .sort((a, b) => Math.min(...a.options.map((o) => finalPrice(a, o.id) || Infinity)) - Math.min(...b.options.map((o) => finalPrice(b, o.id) || Infinity)));
  }
  return { list: list.slice(0, 3), budget };
}

export const byCategory = (cat: CategoryId, products: Product[]) =>
  products.filter((p) => p.category === cat).sort((a, b) => Number(!!b.featured) - Number(!!a.featured)).slice(0, 3);
