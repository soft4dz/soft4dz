import Link from "next/link";
import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { hasLocale, type Locale } from "@/lib/i18n";
import { getSettings } from "@/lib/settings";
import { waLink } from "@/lib/site";
import { Icon } from "@/components/Icon";
import { ConstructionForm } from "./ConstructionForm";

export async function generateMetadata({ params }: { params: Promise<{ lang: string }> }): Promise<Metadata> {
  const { lang } = await params;
  const titles = {
    fr: "Site en construction — SOFT4DZ",
    ar: "الموقع قيد الإنشاء — SOFT4DZ",
    en: "Under Construction — SOFT4DZ",
  };
  const title = titles[lang as Locale] ?? titles.fr;
  return {
    title,
    description: "SOFT4DZ arrive très bientôt ! Licences logicielles officielles et abonnements digitaux en Algérie avec paiement CIB et Edahabia.",
    robots: { index: false },
  };
}

export default async function ConstructionPage({ params }: { params: Promise<{ lang: string }> }) {
  const { lang } = await params;
  if (!hasLocale(lang)) notFound();

  const settings = await getSettings();
  const locale = lang as Locale;

  const content = {
    fr: {
      badge: "LANCEMENT IMMINENT",
      title: "Notre nouvelle plateforme arrive très bientôt !",
      subtitle:
        "Nous finalisons les derniers détails de la boutique SOFT4DZ pour vous proposer les meilleures licences logicielles officielles et abonnements digitaux au meilleur prix en DZD, avec livraison instantanée et paiement sécurisé par carte CIB et Edahabia.",
      vipTitle: "Besoin d'une licence ou d'un compte immédiatement ?",
      vipText:
        "Notre équipe commerciale reste à votre disposition 7j/7 pour traiter votre commande en direct et vous livrer en quelques minutes !",
      vipBtn: "Commander en direct sur WhatsApp",
      waPrefill: "Bonjour SOFT4DZ, je souhaite commander un produit pendant que le site est en construction.",
      servicesTitle: "Ce qui vous attend sur SOFT4DZ :",
      services: [
        {
          icon: "key-round" as const,
          title: "Licences Officielles",
          desc: "Windows 11/10 Pro, Office 2024/2021, Antivirus avec activation garantie à vie.",
        },
        {
          icon: "popcorn" as const,
          title: "Abonnements Premium",
          desc: "Netflix 4K, Canva Pro, Spotify, ChatGPT Plus et bien d'autres aux tarifs algériens.",
        },
        {
          icon: "credit-card" as const,
          title: "Paiement 100% Algérien",
          desc: "Paiement sécurisé par carte CIB, Edahabia SATIM ou versement CCP avec validation rapide.",
        },
        {
          icon: "zap" as const,
          title: "Livraison Instantanée",
          desc: "Réception de vos clés d'activation directement sur votre écran et par e-mail.",
        },
      ],
      adminLink: "Espace Administration",
      contactTitle: "Nous contacter",
    },
    ar: {
      badge: "افتتاح وشيك جداً",
      title: "منصتنا الرقمية الجديدة قادمة قريباً !",
      subtitle:
        "نضع اللمسات الأخيرة على متجر SOFT4DZ لنقدم لكم أفضل تراخيص البرامج الأصلية والاشتراكات الرقمية بأفضل الأسعار بالدينار الجزائري، مع تسليم فوري ودفع آمن عبر البطاقات البنكية CIB والذهبية.",
      vipTitle: "هل تحتاج إلى ترخيص أو حساب الآن ؟",
      vipText:
        "فريقنا التجاري في خدمتكم طيلة أيام الأسبوع لمعالجة طلبكم مباشرة عبر واتساب وتسليمكم في دقائق معدودة !",
      vipBtn: "الطلب المباشر عبر واتساب",
      waPrefill: "مرحباً SOFT4DZ، أود طلب ترخيص أو اشتراك مباشرة عبر واتساب.",
      servicesTitle: "ما ينتظركم على منصة SOFT4DZ :",
      services: [
        {
          icon: "key-round" as const,
          title: "تراخيص أصلية ومضمونة",
          desc: "ويندوز 11 و 10، أوفيس 2024 و 2021 ومضادات الفيروسات مع تفعيل رسمي مدى الحياة.",
        },
        {
          icon: "popcorn" as const,
          title: "اشتراكات بريميوم",
          desc: "نتفليكس 4K، كانفا برو، سبوتيفاي، شات جي بي تي بلس بأسعار مناسبة بالدينار الجزائري.",
        },
        {
          icon: "credit-card" as const,
          title: "دفع آمن بالدينار",
          desc: "دفع إلكتروني آمن ومباشر عبر بطاقة الذهبية أو CIB وشبكة ساتيم، أو عبر حساب بريدي CCP.",
        },
        {
          icon: "zap" as const,
          title: "تسليم فوري للمفاتيح",
          desc: "استلام المفاتيح وبيانات الحساب مباشرة على الشاشة وعبر البريد الإلكتروني.",
        },
      ],
      adminLink: "لوحة التحكم للمسؤول",
      contactTitle: "اتصل بنا",
    },
    en: {
      badge: "LAUNCHING VERY SOON",
      title: "Our new platform is coming very soon!",
      subtitle:
        "We are putting the final touches on SOFT4DZ to bring you genuine software licenses and premium digital subscriptions at the best prices in DZD, featuring instant delivery and secure Algerian CIB & Edahabia payments.",
      vipTitle: "Need a license or account right now?",
      vipText:
        "Our sales team is available 7 days a week to handle your order directly via WhatsApp with 5-minute delivery!",
      vipBtn: "Order directly on WhatsApp",
      waPrefill: "Hello SOFT4DZ, I would like to order a license while the site is under construction.",
      servicesTitle: "Coming soon on SOFT4DZ:",
      services: [
        {
          icon: "key-round" as const,
          title: "Genuine Licenses",
          desc: "Windows 11/10 Pro, Office 2024/2021, and Antivirus with lifetime guaranteed activation.",
        },
        {
          icon: "popcorn" as const,
          title: "Premium Subscriptions",
          desc: "Netflix 4K, Canva Pro, Spotify, ChatGPT Plus and more at Algerian local rates.",
        },
        {
          icon: "credit-card" as const,
          title: "100% Local Algerian Payment",
          desc: "Pay securely with your CIB, Edahabia SATIM card, or via postal CCP transfer.",
        },
        {
          icon: "zap" as const,
          title: "Instant Delivery",
          desc: "Get your activation keys delivered straight to your screen and by email.",
        },
      ],
      adminLink: "Admin Dashboard",
      contactTitle: "Contact Us",
    },
  }[locale];

  const waOrderUrl = waLink(settings.whatsapp, content.waPrefill);
  const waHelpUrl = waLink(settings.whatsapp, "Bonjour SOFT4DZ, j'ai une question.");

  return (
    <div style={{ maxWidth: 880, margin: "40px auto 60px", padding: "0 16px" }}>
      {/* Conteneur principal style Glassmorphism Sombre / Lumineux */}
      <div
        className="card"
        style={{
          background: "linear-gradient(145deg, #111b27 0%, #162436 50%, #0d1622 100%)",
          color: "#fff",
          borderRadius: 24,
          padding: "48px 32px",
          border: "1px solid rgba(255, 255, 255, 0.12)",
          boxShadow: "0 20px 60px rgba(0, 0, 0, 0.45)",
          textAlign: "center",
          position: "relative",
          overflow: "hidden",
        }}
      >
        {/* Lueur d'ambiance en arrière-plan */}
        <div
          style={{
            position: "absolute",
            top: -80,
            left: "50%",
            transform: "translateX(-50%)",
            width: 400,
            height: 240,
            background: "radial-gradient(circle, rgba(31, 138, 112, 0.35) 0%, rgba(46, 100, 168, 0.25) 50%, transparent 80%)",
            filter: "blur(50px)",
            pointerEvents: "none",
          }}
        />

        {/* Sélecteur de langue en haut */}
        <div style={{ display: "flex", justifyContent: "center", gap: 10, marginBottom: 28, position: "relative", zIndex: 2 }}>
          <Link
            href="/fr/en-construction"
            style={{
              padding: "4px 12px",
              borderRadius: 20,
              fontSize: 12,
              fontWeight: 600,
              background: locale === "fr" ? "rgba(255,255,255,0.2)" : "rgba(255,255,255,0.06)",
              color: "#fff",
              textDecoration: "none",
              border: "1px solid rgba(255,255,255,0.15)",
            }}
          >
            Français
          </Link>
          <Link
            href="/ar/en-construction"
            style={{
              padding: "4px 12px",
              borderRadius: 20,
              fontSize: 12,
              fontWeight: 600,
              background: locale === "ar" ? "rgba(255,255,255,0.2)" : "rgba(255,255,255,0.06)",
              color: "#fff",
              textDecoration: "none",
              border: "1px solid rgba(255,255,255,0.15)",
            }}
          >
            العربية
          </Link>
          <Link
            href="/en/en-construction"
            style={{
              padding: "4px 12px",
              borderRadius: 20,
              fontSize: 12,
              fontWeight: 600,
              background: locale === "en" ? "rgba(255,255,255,0.2)" : "rgba(255,255,255,0.06)",
              color: "#fff",
              textDecoration: "none",
              border: "1px solid rgba(255,255,255,0.15)",
            }}
          >
            English
          </Link>
        </div>

        {/* Badge animé */}
        <div
          style={{
            display: "inline-flex",
            alignItems: "center",
            gap: 8,
            padding: "8px 18px",
            background: "rgba(31, 138, 112, 0.2)",
            border: "1px solid rgba(31, 138, 112, 0.4)",
            color: "var(--teal)",
            borderRadius: 999,
            fontSize: 13,
            fontWeight: 800,
            letterSpacing: 1,
            marginBottom: 20,
            boxShadow: "0 0 20px rgba(31, 138, 112, 0.2)",
          }}
        >
          <Icon name="sparkles" style={{ width: 16, height: 16 }} />
          <span>{content.badge}</span>
        </div>

        {/* Titre Principal */}
        <h1
          style={{
            fontSize: "clamp(26px, 4vw, 38px)",
            fontWeight: 800,
            margin: "0 auto 16px",
            lineHeight: 1.25,
            maxWidth: 720,
            background: "linear-gradient(180deg, #ffffff 40%, rgba(255,255,255,0.75) 100%)",
            WebkitBackgroundClip: "text",
            WebkitTextFillColor: "transparent",
          }}
        >
          {content.title}
        </h1>

        {/* Sous-titre */}
        <p
          style={{
            fontSize: "clamp(14px, 2vw, 16px)",
            color: "rgba(255, 255, 255, 0.72)",
            maxWidth: 640,
            margin: "0 auto 12px",
            lineHeight: 1.6,
          }}
        >
          {content.subtitle}
        </p>

        {/* Formulaire interactif et barre d'avancement */}
        <ConstructionForm locale={locale} />

        {/* Boîte VIP Commande Immédiate WhatsApp */}
        <div
          style={{
            marginTop: 40,
            background: "linear-gradient(135deg, rgba(31, 138, 112, 0.18) 0%, rgba(46, 100, 168, 0.18) 100%)",
            border: "1.5px solid rgba(31, 138, 112, 0.35)",
            borderRadius: 18,
            padding: "24px 22px",
            textAlign: "center",
          }}
        >
          <div style={{ display: "inline-flex", alignItems: "center", gap: 8, color: "var(--gold)", fontWeight: 700, fontSize: 16, marginBottom: 8 }}>
            <Icon name="message-circle" style={{ width: 20, height: 20 }} />
            <span>{content.vipTitle}</span>
          </div>
          <p style={{ color: "rgba(255, 255, 255, 0.8)", fontSize: 14, margin: "0 auto 16px", maxWidth: 540, lineHeight: 1.5 }}>
            {content.vipText}
          </p>
          <a
            href={waOrderUrl}
            target="_blank"
            rel="noopener"
            className="btn b-or"
            style={{
              padding: "12px 28px",
              fontSize: 15,
              fontWeight: 700,
              display: "inline-flex",
              alignItems: "center",
              gap: 10,
              borderRadius: 12,
              boxShadow: "0 6px 20px rgba(217, 127, 30, 0.4)",
            }}
          >
            <Icon name="message-circle" style={{ width: 18, height: 18 }} />
            {content.vipBtn}
          </a>
        </div>

        {/* Aperçu des fonctionnalités prévues */}
        <div style={{ marginTop: 44, textAlign: "start" }}>
          <h3 style={{ fontSize: 16, fontWeight: 700, color: "rgba(255,255,255,0.9)", textAlign: "center", marginBottom: 20 }}>
            {content.servicesTitle}
          </h3>
          <div
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(auto-fit, minmax(240px, 1fr))",
              gap: 16,
            }}
          >
            {content.services.map((s, idx) => (
              <div
                key={idx}
                style={{
                  background: "rgba(255, 255, 255, 0.04)",
                  border: "1px solid rgba(255, 255, 255, 0.08)",
                  borderRadius: 16,
                  padding: "18px 16px",
                  display: "flex",
                  flexDirection: "column",
                  gap: 10,
                  transition: "transform .2s ease, border-color .2s ease",
                }}
              >
                <div
                  style={{
                    width: 38,
                    height: 38,
                    borderRadius: 10,
                    background: "rgba(31, 138, 112, 0.2)",
                    border: "1px solid rgba(31, 138, 112, 0.3)",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    color: "var(--teal)",
                  }}
                >
                  <Icon name={s.icon} style={{ width: 20, height: 20 }} />
                </div>
                <div>
                  <h4 style={{ fontSize: 15, fontWeight: 700, color: "#fff", marginBottom: 4 }}>{s.title}</h4>
                  <p style={{ fontSize: 12.5, color: "rgba(255, 255, 255, 0.65)", margin: 0, lineHeight: 1.45 }}>{s.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Coordonnées & réseaux sociaux */}
        <div style={{ marginTop: 36, paddingTop: 24, borderTop: "1px solid rgba(255, 255, 255, 0.08)", display: "flex", flexWrap: "wrap", justifyContent: "space-between", alignItems: "center", gap: 16, fontSize: 13 }}>
          <div style={{ display: "flex", gap: 16, flexWrap: "wrap", color: "rgba(255, 255, 255, 0.7)" }}>
            {settings.phone && (
              <a href={`tel:${settings.phone.replace(/\s/g, "")}`} style={{ color: "inherit", textDecoration: "none", display: "flex", alignItems: "center", gap: 6 }}>
                <Icon name="headset" style={{ width: 14, height: 14, color: "var(--teal)" }} />
                <span>{settings.phone}</span>
              </a>
            )}
            {settings.email && (
              <a href={`mailto:${settings.email}`} style={{ color: "inherit", textDecoration: "none", display: "flex", alignItems: "center", gap: 6 }}>
                <Icon name="mail" style={{ width: 14, height: 14, color: "var(--teal)" }} />
                <span>{settings.email}</span>
              </a>
            )}
            <a href={waHelpUrl} target="_blank" rel="noopener" style={{ color: "inherit", textDecoration: "none", display: "flex", alignItems: "center", gap: 6 }}>
              <Icon name="message-circle" style={{ width: 14, height: 14, color: "var(--teal)" }} />
              <span>WhatsApp 7j/7</span>
            </a>
          </div>

          {/* Lien Administration */}
          <Link
            href="/admin"
            style={{
              color: "rgba(255, 255, 255, 0.4)",
              textDecoration: "none",
              fontSize: 12,
              display: "flex",
              alignItems: "center",
              gap: 6,
            }}
          >
            <Icon name="lock" style={{ width: 12, height: 12 }} />
            <span>{content.adminLink}</span>
          </Link>
        </div>
      </div>
    </div>
  );
}
