import type { Localized } from "./i18n";
import type { IconName } from "@/components/Icon";

/*
 * Types et fonctions pures du catalogue (utilisables côté navigateur et serveur).
 * Les produits eux-mêmes sont stockés côté serveur (src/lib/products.ts) et gérés
 * depuis l'admin ; `defaultProducts` sert uniquement de contenu de départ.
 */

export type CategoryId = "win" | "off" | "str" | "ia" | "sec" | "svc";

export type Category = { id: CategoryId; icon: IconName; name: Localized };

export type ProductOption = {
  id: string;
  label: Localized;
  /** Prix en DA, avant remise. 0 = sur devis. */
  price: number;
};

export type Product = {
  slug: string;
  category: CategoryId;
  icon: IconName;
  /** Classe de dégradé définie dans globals.css (.g-*) */
  gradient: string;
  name: Localized;
  subtitle: Localized;
  description: Localized;
  options: ProductOption[];
  rating: number;
  reviews: number;
  /** Remise en % et part du stock vendue (0-1) pour les offres du jour */
  deal?: { percent: number; sold: number };
  featured?: boolean;
  /** false = masqué de la boutique (conservé pour l'historique des commandes) */
  active?: boolean;
  /** Image principale carrée (1000×1000 conseillé). Sans image : affiche générée. */
  image?: string;
  /** Photos supplémentaires pour la fiche produit (4 max) */
  gallery?: string[];
};

export const categories: Category[] = [
  { id: "win", icon: "monitor", name: { fr: "Windows", ar: "ويندوز", en: "Windows" } },
  { id: "off", icon: "file-spreadsheet", name: { fr: "Office", ar: "أوفيس", en: "Office" } },
  { id: "str", icon: "tv", name: { fr: "Streaming", ar: "بث", en: "Streaming" } },
  { id: "ia", icon: "sparkles", name: { fr: "IA & création", ar: "ذكاء وتصميم", en: "AI & design" } },
  { id: "sec", icon: "shield-check", name: { fr: "Sécurité", ar: "حماية", en: "Security" } },
  { id: "svc", icon: "globe", name: { fr: "Services", ar: "خدمات", en: "Services" } },
];

const o = (id: string, fr: string, ar: string, price: number): ProductOption => ({
  id,
  label: { fr, ar },
  price,
});

const licenceDesc: Localized = {
  fr: "Licence numérique officielle livrée par e-mail et disponible dans votre espace client. Activation en ligne en quelques minutes, garantie de remplacement en cas de problème.",
  ar: "ترخيص رقمي أصلي يرسل عبر البريد ويبقى متاحا في حسابك. تفعيل عبر الإنترنت في دقائق، مع ضمان الاستبدال عند أي مشكلة.",
};
const subDesc: Localized = {
  fr: "Abonnement activé rapidement après paiement. Les identifiants ou le lien d'activation vous sont envoyés par e-mail et WhatsApp.",
  ar: "اشتراك يفعل بسرعة بعد الدفع. ترسل بيانات الدخول أو رابط التفعيل عبر البريد وواتساب.",
};
const svcDesc: Localized = {
  fr: "Projet réalisé sur mesure par notre équipe : cadrage, maquette, développement, mise en ligne et accompagnement. Le prix final est confirmé après un échange sur vos besoins.",
  ar: "مشروع ينجز حسب الطلب من طرف فريقنا: دراسة، تصميم، برمجة، إطلاق ومرافقة. يؤكد السعر النهائي بعد مناقشة احتياجاتك.",
};

export const defaultProducts: Product[] = [
  { slug: "windows-11-pro", category: "win", icon: "monitor", gradient: "g-win", name: { fr: "Windows 11 Pro", ar: "ويندوز 11 برو" }, subtitle: { fr: "Licence à vie", ar: "ترخيص مدى الحياة" }, description: licenceDesc, options: [o("1pc", "1 PC", "1 جهاز", 4100), o("3pc", "3 PC", "3 أجهزة", 12300)], rating: 4.9, reviews: 312, deal: { percent: 30, sold: 0.72 }, featured: true },
  { slug: "office-2021-pro-plus", category: "off", icon: "file-spreadsheet", gradient: "g-off", name: { fr: "Office 2021 Pro Plus", ar: "أوفيس 2021 برو بلس" }, subtitle: { fr: "1 PC · À vie", ar: "جهاز واحد · مدى الحياة" }, description: licenceDesc, options: [o("1pc", "1 PC", "1 جهاز", 5000), o("2pc", "2 PC", "2 جهاز", 9300)], rating: 4.8, reviews: 201, deal: { percent: 30, sold: 0.55 }, featured: true },
  { slug: "windows-server-2022", category: "win", icon: "server", gradient: "g-srv", name: { fr: "Windows Server 2022", ar: "ويندوز سيرفر 2022" }, subtitle: { fr: "Standard / Datacenter", ar: "ستاندرد / داتاسنتر" }, description: licenceDesc, options: [o("std", "Std", "ستاندرد", 19900), o("dc", "DC", "داتاسنتر", 32000)], rating: 4.7, reviews: 24, deal: { percent: 25, sold: 0.3 } },
  { slug: "canva-pro", category: "ia", icon: "palette", gradient: "g-can", name: { fr: "Canva Pro — 1 an", ar: "كانفا برو — سنة" }, subtitle: { fr: "Sur votre compte", ar: "على حسابك" }, description: subDesc, options: [o("1y", "1 an", "سنة", 2200)], rating: 4.9, reviews: 156, deal: { percent: 30, sold: 0.84 } },
  { slug: "windows-10-pro", category: "win", icon: "key-round", gradient: "g-gray", name: { fr: "Windows 10 Pro", ar: "ويندوز 10 برو" }, subtitle: { fr: "Licence à vie", ar: "ترخيص مدى الحياة" }, description: licenceDesc, options: [o("1pc", "1 PC", "1 جهاز", 2900)], rating: 4.8, reviews: 98, deal: { percent: 35, sold: 0.61 } },
  { slug: "kaspersky-premium", category: "sec", icon: "shield-check", gradient: "g-green", name: { fr: "Kaspersky Premium", ar: "كاسبرسكي بريميوم" }, subtitle: { fr: "1 an", ar: "سنة" }, description: licenceDesc, options: [o("1", "1", "1", 2600), o("3", "3", "3", 4400), o("5", "5", "5", 6200)], rating: 4.6, reviews: 61, deal: { percent: 27, sold: 0.4 } },
  { slug: "netflix-premium", category: "str", icon: "clapperboard", gradient: "g-nfx", name: { fr: "Netflix Premium 4K", ar: "نتفليكس بريميوم 4K" }, subtitle: { fr: "Profil privé · 4K", ar: "ملف خاص · 4K" }, description: subDesc, options: [o("1m", "1 m", "شهر", 900), o("3m", "3 m", "3 أشهر", 2500), o("12m", "12 m", "سنة", 9500)], rating: 4.9, reviews: 540, featured: true },
  { slug: "chatgpt-plus", category: "ia", icon: "bot", gradient: "g-gpt", name: { fr: "ChatGPT Plus", ar: "شات جي بي تي بلس" }, subtitle: { fr: "Sur votre compte", ar: "على حسابك" }, description: subDesc, options: [o("1m", "1 m", "شهر", 4200), o("3m", "3 m", "3 أشهر", 11900)], rating: 4.8, reviews: 187, featured: true },
  { slug: "spotify-premium", category: "str", icon: "music", gradient: "g-spo", name: { fr: "Spotify Premium", ar: "سبوتيفاي بريميوم" }, subtitle: { fr: "Sur votre compte", ar: "على حسابك" }, description: subDesc, options: [o("1m", "1 m", "شهر", 600), o("6m", "6 m", "6 أشهر", 3200), o("12m", "12 m", "سنة", 5900)], rating: 4.8, reviews: 402 },
  { slug: "microsoft-365-famille", category: "off", icon: "cloud", gradient: "g-blue", name: { fr: "Microsoft 365 Famille", ar: "مايكروسوفت 365 العائلي" }, subtitle: { fr: "1 an · 6 utilisateurs", ar: "سنة · 6 مستخدمين" }, description: subDesc, options: [o("1y", "1 an", "سنة", 6900)], rating: 4.9, reviews: 133 },
  { slug: "adobe-creative-cloud", category: "ia", icon: "pen-tool", gradient: "g-pink", name: { fr: "Adobe Creative Cloud", ar: "أدوبي كريتيف كلاود" }, subtitle: { fr: "Toutes les apps", ar: "كل التطبيقات" }, description: subDesc, options: [o("1m", "1 m", "شهر", 5500), o("12m", "12 m", "سنة", 49000)], rating: 4.7, reviews: 58 },
  { slug: "shahid-vip", category: "str", icon: "play", gradient: "g-cyan", name: { fr: "Shahid VIP", ar: "شاهد VIP" }, subtitle: { fr: "Séries et sport", ar: "مسلسلات ورياضة" }, description: subDesc, options: [o("1m", "1 m", "شهر", 700), o("12m", "12 m", "سنة", 6900)], rating: 4.7, reviews: 221 },
  { slug: "office-2019-pro-plus", category: "off", icon: "file-text", gradient: "g-off", name: { fr: "Office 2019 Pro Plus", ar: "أوفيس 2019 برو بلس" }, subtitle: { fr: "1 PC · À vie", ar: "جهاز واحد · مدى الحياة" }, description: licenceDesc, options: [o("1pc", "1 PC", "1 جهاز", 2500)], rating: 4.7, reviews: 76 },
  { slug: "eset-internet-security", category: "sec", icon: "shield", gradient: "g-can", name: { fr: "ESET Internet Security", ar: "إيسيت إنترنت سكيوريتي" }, subtitle: { fr: "1 an", ar: "سنة" }, description: licenceDesc, options: [o("1", "1", "1", 1600), o("3", "3", "3", 2900)], rating: 4.6, reviews: 44 },
  { slug: "site-vitrine", category: "svc", icon: "globe", gradient: "g-brand", name: { fr: "Site web vitrine", ar: "موقع تعريفي" }, subtitle: { fr: "Bilingue · Adapté mobile", ar: "ثنائي اللغة · للهاتف" }, description: svcDesc, options: [o("ess", "Essentiel", "أساسي", 35000), o("pro", "Pro", "احترافي", 65000)], rating: 5, reviews: 32, featured: true },
  { slug: "application-web", category: "svc", icon: "layout-dashboard", gradient: "g-gpt", name: { fr: "Application web", ar: "تطبيق ويب" }, subtitle: { fr: "Portail, dashboard", ar: "بوابة، لوحة تحكم" }, description: svcDesc, options: [o("quote", "Sur devis", "حسب الطلب", 0)], rating: 5, reviews: 12 },
];

export const finalPrice = (p: Product, optionId: string) => {
  const opt = p.options.find((x) => x.id === optionId) ?? p.options[0];
  if (!p.deal || !opt.price) return opt.price;
  return Math.round((opt.price * (1 - p.deal.percent / 100)) / 100) * 100;
};

export const deals = (list: Product[]) => list.filter((p) => p.deal);
export const catalogue = (list: Product[]) => list.filter((p) => p.category !== "svc");
export const related = (list: Product[], p: Product) =>
  list.filter((x) => x.slug !== p.slug && x.category !== "svc").slice(0, 5);

export const gradients = ["g-win", "g-off", "g-srv", "g-can", "g-gray", "g-green", "g-nfx", "g-gpt", "g-spo", "g-blue", "g-pink", "g-cyan", "g-brand"] as const;

/* ---------------- Anglais ----------------
 * Traductions anglaises du catalogue de départ. `withEnglish` complète aussi
 * les produits créés dans l'admin sans version anglaise (sinon : repli sur le français).
 */
const en: Record<string, string> = {
  // noms
  "Canva Pro — 1 an": "Canva Pro — 1 year", "Microsoft 365 Famille": "Microsoft 365 Family",
  "Site web vitrine": "Showcase website", "Application web": "Web application",
  // sous-titres
  "Licence à vie": "Lifetime license", "1 PC · À vie": "1 PC · Lifetime", "Standard / Datacenter": "Standard / Datacenter",
  "Sur votre compte": "On your account", "1 an": "1 year", "Profil privé · 4K": "Private profile · 4K",
  "1 an · 6 utilisateurs": "1 year · 6 users", "Toutes les apps": "All apps", "Séries et sport": "Series & sports",
  "Bilingue · Adapté mobile": "Bilingual · Mobile-ready", "Portail, dashboard": "Portal, dashboard",
  // options
  "1 m": "1 mo", "3 m": "3 mo", "6 m": "6 mo", "12 m": "12 mo", "Essentiel": "Essential", "Sur devis": "On quote",
  // descriptions
  [licenceDesc.fr]: "Official digital license delivered by email and kept in your customer account. Online activation in minutes, with a replacement guarantee if anything goes wrong.",
  [subDesc.fr]: "Subscription activated quickly after payment. Login details or the activation link are sent by email and WhatsApp.",
  [svcDesc.fr]: "A custom project built by our team: scoping, design, development, launch and support. The final price is confirmed after discussing your needs.",
};

const fill = (l: Localized): Localized => (l.en ? l : { ...l, en: en[l.fr] ?? l.fr });

export const withEnglish = (p: Product): Product => ({
  ...p,
  name: fill(p.name),
  subtitle: fill(p.subtitle),
  description: fill(p.description),
  options: p.options.map((o) => ({ ...o, label: fill(o.label) })),
});
