const translations = {
  fr: {
    dir: "ltr",
    badge: "LANCEMENT IMMINENT",
    title: "Notre nouvelle plateforme arrive très bientôt !",
    desc: "Nous finalisons les derniers détails de la boutique SOFT4DZ pour vous proposer les meilleures licences logicielles officielles et abonnements digitaux au meilleur prix en DZD, avec livraison instantanée et paiement sécurisé par carte CIB et Edahabia (SATIM).",
    progressLabel: "Lancement imminent · 95% prêt",
    emailPlaceholder: "Votre adresse e-mail…",
    notifyBtn: "M'avertir du lancement",
    notifySuccess: "🎉 C'est noté ! Vous recevrez une invitation prioritaire et un code promo exclusif.",
    vipTitle: "Besoin d'une licence ou d'un compte immédiatement ?",
    vipDesc: "Notre équipe commerciale reste à votre disposition 7j/7 pour traiter votre commande en direct et vous livrer en quelques minutes !",
    vipBtn: "Commander en direct sur WhatsApp",
    waMsg: "Bonjour SOFT4DZ, je souhaite commander un produit pendant que le site est en construction.",
    servicesTitle: "Ce qui vous attend sur SOFT4DZ :",
    srv1Title: "Licences Officielles",
    srv1Desc: "Windows 11/10 Pro, Office 2024/2021 et Antivirus avec activation garantie à vie.",
    srv2Title: "Abonnements Premium",
    srv2Desc: "Netflix 4K, Canva Pro, Spotify, ChatGPT Plus et bien d'autres aux tarifs algériens.",
    srv3Title: "Paiement 100% Algérien",
    srv3Desc: "Paiement sécurisé par carte CIB, Edahabia SATIM ou versement CCP avec validation rapide.",
    srv4Title: "Livraison Instantanée",
    srv4Desc: "Réception de vos clés d'activation directement sur votre écran et par e-mail.",
    waHelp: "WhatsApp 7j/7",
    rights: "Tous droits réservés."
  },
  ar: {
    dir: "rtl",
    badge: "افتتاح وشيك جداً",
    title: "منصتنا الرقمية الجديدة قادمة قريباً !",
    desc: "نضع اللمسات الأخيرة على متجر SOFT4DZ لنقدم لكم أفضل تراخيص البرامج الأصلية والاشتراكات الرقمية بأفضل الأسعار بالدينار الجزائري، مع تسليم فوري ودفع آمن عبر البطاقات البنكية CIB والذهبية.",
    progressLabel: "افتتاح وشيك · 95% جاهز",
    emailPlaceholder: "بريدك الإلكتروني…",
    notifyBtn: "أعلمني عند الإطلاق",
    notifySuccess: "🎉 تم التسجيل بنجاح ! ستتلقى إشعاراً فور الافتتاح وكود تخفيض حصري.",
    vipTitle: "هل تحتاج إلى ترخيص أو حساب الآن ؟",
    vipDesc: "فريقنا التجاري في خدمتكم طيلة أيام الأسبوع لمعالجة طلبكم مباشرة عبر واتساب وتسليمكم في دقائق معدودة !",
    vipBtn: "الطلب المباشر عبر واتساب",
    waMsg: "مرحباً SOFT4DZ، أود طلب ترخيص أو اشتراك مباشرة عبر واتساب.",
    servicesTitle: "ما ينتظركم على منصة SOFT4DZ :",
    srv1Title: "تراخيص أصلية ومضمونة",
    srv1Desc: "ويندوز 11 و 10، أوفيس 2024 و 2021 ومضادات الفيروسات مع تفعيل رسمي مدى الحياة.",
    srv2Title: "اشتراكات بريميوم",
    srv2Desc: "نتفليكس 4K، كانفا برو، سبوتيفاي، شات جي بي تي بلس بأسعار مناسبة بالدينار الجزائري.",
    srv3Title: "دفع آمن بالدينار",
    srv3Desc: "دفع إلكتروني آمن ومباشر عبر بطاقة الذهبية أو CIB وشبكة ساتيم، أو عبر حساب بريدي CCP.",
    srv4Title: "تسليم فوري للمفاتيح",
    srv4Desc: "استلام المفاتيح وبيانات الحساب مباشرة على الشاشة وعبر البريد الإلكتروني.",
    waHelp: "واتساب 7/7",
    rights: "جميع الحقوق محفوظة."
  },
  en: {
    dir: "ltr",
    badge: "LAUNCHING VERY SOON",
    title: "Our new platform is coming very soon!",
    desc: "We are putting the final touches on SOFT4DZ to bring you genuine software licenses and premium digital subscriptions at the best prices in DZD, featuring instant delivery and secure Algerian CIB & Edahabia payments.",
    progressLabel: "Launching soon · 95% ready",
    emailPlaceholder: "Your email address…",
    notifyBtn: "Notify me on launch",
    notifySuccess: "🎉 You're on the list! You will receive priority access and an exclusive discount.",
    vipTitle: "Need a license or account right now?",
    vipDesc: "Our sales team is available 7 days a week to handle your order directly via WhatsApp with 5-minute delivery!",
    vipBtn: "Order directly on WhatsApp",
    waMsg: "Hello SOFT4DZ, I would like to order a license while the site is under construction.",
    servicesTitle: "Coming soon on SOFT4DZ:",
    srv1Title: "Genuine Licenses",
    srv1Desc: "Windows 11/10 Pro, Office 2024/2021, and Antivirus with lifetime guaranteed activation.",
    srv2Title: "Premium Subscriptions",
    srv2Desc: "Netflix 4K, Canva Pro, Spotify, ChatGPT Plus and more at Algerian local rates.",
    srv3Title: "100% Local Payment",
    srv3Desc: "Pay securely with your CIB, Edahabia SATIM card, or via postal CCP transfer.",
    srv4Title: "Instant Delivery",
    srv4Desc: "Get your activation keys delivered straight to your screen and by email.",
    waHelp: "WhatsApp 7/7",
    rights: "All rights reserved."
  }
};

let currentLang = "fr";
const whatsappPhone = "213550312783"; // Numéro WhatsApp de contact

function setLanguage(lang) {
  if (!translations[lang]) return;
  currentLang = lang;

  const t = translations[lang];
  document.body.setAttribute("dir", t.dir);
  document.documentElement.setAttribute("lang", lang);

  // Mettre à jour les boutons actifs
  document.querySelectorAll(".lang-btn").forEach(btn => {
    btn.classList.toggle("active", btn.getAttribute("data-lang") === lang);
  });

  // Mettre à jour tous les éléments textuels
  document.getElementById("txt-badge").textContent = t.badge;
  document.getElementById("txt-title").textContent = t.title;
  document.getElementById("txt-desc").textContent = t.desc;
  document.getElementById("txt-progress-label").textContent = t.progressLabel;
  document.getElementById("notify-email").placeholder = t.emailPlaceholder;
  document.getElementById("txt-notify-btn").textContent = t.notifyBtn;
  document.getElementById("txt-notify-success").textContent = t.notifySuccess;
  document.getElementById("txt-vip-title").textContent = t.vipTitle;
  document.getElementById("txt-vip-desc").textContent = t.vipDesc;
  document.getElementById("txt-vip-btn").textContent = t.vipBtn;
  document.getElementById("txt-services-title").textContent = t.servicesTitle;

  document.getElementById("txt-srv1-title").textContent = t.srv1Title;
  document.getElementById("txt-srv1-desc").textContent = t.srv1Desc;
  document.getElementById("txt-srv2-title").textContent = t.srv2Title;
  document.getElementById("txt-srv2-desc").textContent = t.srv2Desc;
  document.getElementById("txt-srv3-title").textContent = t.srv3Title;
  document.getElementById("txt-srv3-desc").textContent = t.srv3Desc;
  document.getElementById("txt-srv4-title").textContent = t.srv4Title;
  document.getElementById("txt-srv4-desc").textContent = t.srv4Desc;

  document.getElementById("txt-wa-help").textContent = t.waHelp;
  document.getElementById("txt-rights").textContent = t.rights;

  // Lien WhatsApp
  const waUrl = `https://wa.me/${whatsappPhone}?text=${encodeURIComponent(t.waMsg)}`;
  document.getElementById("vip-wa-link").setAttribute("href", waUrl);
}

document.addEventListener("DOMContentLoaded", () => {
  // Détection langue préférée du navigateur
  const navLang = (navigator.language || navigator.userLanguage || "fr").slice(0, 2).toLowerCase();
  const initialLang = ["ar", "en", "fr"].includes(navLang) ? navLang : "fr";
  setLanguage(initialLang);

  // Événements sur les boutons de langues
  document.querySelectorAll(".lang-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      setLanguage(btn.getAttribute("data-lang"));
    });
  });

  // Gestion formulaire notification
  const form = document.getElementById("notify-form");
  const successBox = document.getElementById("notify-success");

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const email = document.getElementById("notify-email").value.trim();
    if (!email || !email.includes("@")) return;

    form.style.display = "none";
    successBox.style.display = "flex";
  });
});
