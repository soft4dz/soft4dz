import "server-only";
import { randomBytes } from "crypto";
import type { Localized } from "./i18n";
import { readJson, updateJson } from "./store";

/** Une diapositive du carrousel d'accueil, gérée dans Admin › Bannières. */
export type Slide = {
  id: string;
  active: boolean;
  /** Dégradé de fond (utilisé si aucune image, ou en surcouche) */
  bg: string;
  /** Image de fond 1600×640 (facultative) */
  image?: string;
  tag: Localized;
  title: Localized;
  highlight: Localized;
  text: Localized;
  cta: Localized;
  href: string;
  /** Jusqu'à 3 produits affichés en éventail (ignoré si image) */
  products: string[];
  /** Produit dont le prix « Dès … » est affiché */
  priceFrom?: string;
};

export const backgrounds = [
  { id: "blue", label: "Bleu", css: "linear-gradient(115deg,#0F2B52 0%,#1D4577 40%,#2E64A8 75%,#3C8FB0 100%)" },
  { id: "violet", label: "Violet", css: "linear-gradient(115deg,#0E1426 0%,#23205A 45%,#4B2E8A 80%,#6A3FA8 100%)" },
  { id: "indigo", label: "Indigo", css: "linear-gradient(115deg,#1C0F45 0%,#3B2396 45%,#2E64A8 100%)" },
  { id: "teal", label: "Vert d'eau", css: "linear-gradient(115deg,#062E26 0%,#0F6B57 45%,#1F8A70 75%,#2E64A8 100%)" },
  { id: "night", label: "Nuit", css: "linear-gradient(115deg,#070D18 0%,#10213A 55%,#1D4577 100%)" },
  { id: "amber", label: "Ambre", css: "linear-gradient(115deg,#3A1A04 0%,#8A4A0E 50%,#E08A24 100%)" },
];

const L = (fr: string, ar: string, en: string): Localized => ({ fr, ar, en });

export const defaultSlides = (): Slide[] => [
  { id: "s1", active: true, bg: backgrounds[0].css, tag: L("Offre de la semaine", "عرض الأسبوع", "Deal of the week"), title: L("Windows 11 Pro & Office 2021", "ويندوز 11 برو وأوفيس 2021", "Windows 11 Pro & Office 2021"), highlight: L("à prix mini", "بسعر خاص", "at low prices"), text: L("Licences officielles à vie, livrées par e-mail en quelques minutes.", "تراخيص أصلية مدى الحياة، تسليم عبر البريد في دقائق.", "Official lifetime licenses, delivered by email in minutes."), cta: L("J’en profite", "اطلب الآن", "Get the deal"), href: "/produit/windows-11-pro", products: ["windows-11-pro", "office-2021-pro-plus", "microsoft-365-famille"], priceFrom: "windows-11-pro" },
  { id: "s2", active: true, bg: backgrounds[1].css, tag: L("Streaming", "بث", "Streaming"), title: L("Vos séries et votre musique", "مسلسلاتك وموسيقاك", "Your shows and your music"), highlight: L("sans limite", "بلا حدود", "unlimited"), text: L("Netflix 4K, Shahid VIP, Spotify Premium : activés en quelques minutes.", "نتفليكس 4K، شاهد VIP، سبوتيفاي بريميوم: تفعيل في دقائق.", "Netflix 4K, Shahid VIP, Spotify Premium: activated in minutes."), cta: L("Voir les abonnements", "شاهد الاشتراكات", "See subscriptions"), href: "?cat=str#catalogue", products: ["shahid-vip", "netflix-premium", "spotify-premium"], priceFrom: "spotify-premium" },
  { id: "s3", active: true, bg: backgrounds[2].css, tag: L("Intelligence artificielle", "ذكاء اصطناعي", "Artificial intelligence"), title: L("ChatGPT Plus, Canva Pro", "شات جي بي تي، كانفا برو", "ChatGPT Plus, Canva Pro"), highlight: L("et outils IA", "وأدوات الذكاء", "and AI tools"), text: L("Boostez votre productivité avec les meilleurs outils du moment.", "ضاعف إنتاجيتك بأفضل الأدوات الحالية.", "Boost your productivity with today's best tools."), cta: L("Découvrir", "اكتشف", "Discover"), href: "?cat=ia#catalogue", products: ["canva-pro", "chatgpt-plus", "adobe-creative-cloud"], priceFrom: "canva-pro" },
  { id: "s4", active: true, bg: backgrounds[3].css, tag: L("Pour les entreprises", "للشركات", "For businesses"), title: L("Votre site web", "موقعك الإلكتروني", "Your website,"), highlight: L("clé en main", "جاهز", "turnkey"), text: L("Vitrine, e-commerce ou application : on s’occupe de tout.", "موقع تعريفي، متجر أو تطبيق: نتكفل بكل شيء.", "Showcase site, e-commerce or app: we handle everything."), cta: L("Demander un devis", "اطلب عرض سعر", "Request a quote"), href: "/produit/site-vitrine", products: ["windows-server-2022", "site-vitrine", "application-web"], priceFrom: "site-vitrine" },
];

const FILE = "slides.json";
export const getAllSlides = () => readJson<Slide[]>(FILE, defaultSlides);
export const getActiveSlides = async () => (await getAllSlides()).filter((s) => s.active);
export const newSlideId = () => "s" + randomBytes(4).toString("hex");

export const saveSlide = (slide: Slide) =>
  updateJson<Slide[]>(FILE, defaultSlides, (list) => {
    const i = list.findIndex((s) => s.id === slide.id);
    if (i < 0) return [...list, slide];
    const next = [...list];
    next[i] = slide;
    return next;
  });

export const deleteSlide = (id: string) => updateJson<Slide[]>(FILE, defaultSlides, (list) => list.filter((s) => s.id !== id));

export const moveSlide = (id: string, dir: -1 | 1) =>
  updateJson<Slide[]>(FILE, defaultSlides, (list) => {
    const i = list.findIndex((s) => s.id === id), j = i + dir;
    if (i < 0 || j < 0 || j >= list.length) return list;
    const next = [...list];
    [next[i], next[j]] = [next[j], next[i]];
    return next;
  });
