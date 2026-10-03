"use client";

import { useState } from "react";
import { Icon } from "@/components/Icon";
import type { Locale } from "@/lib/i18n";

export function ConstructionForm({ locale }: { locale: Locale }) {
  const [email, setEmail] = useState("");
  const [submitted, setSubmitted] = useState(false);

  const t = {
    fr: {
      placeholder: "Votre adresse e-mail…",
      btn: "M'avertir du lancement",
      success: "🎉 C'est noté ! Vous recevrez une invitation prioritaire et une offre exclusive.",
      badge: "Lancement imminent · 95% prêt",
    },
    ar: {
      placeholder: "بريدك الإلكتروني…",
      btn: "أعلمني عند الإطلاق",
      success: "🎉 تم التسجيل بنجاح ! ستتلقى إشعاراً وعرضاً حصرياً فور الافتتاح.",
      badge: "افتتاح وشيك · 95% جاهز",
    },
    en: {
      placeholder: "Your email address…",
      btn: "Notify me on launch",
      success: "🎉 You're on the list! You will receive priority access and an exclusive discount.",
      badge: "Launching soon · 95% ready",
    },
  }[locale];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!email.trim() || !email.includes("@")) return;
    setSubmitted(true);
  };

  return (
    <div style={{ marginTop: 24, maxWidth: 520, marginInline: "auto" }}>
      {/* Barre de progression */}
      <div style={{ marginBottom: 20 }}>
        <div style={{ display: "flex", justifyContent: "space-between", fontSize: 13, fontWeight: 600, marginBottom: 8, color: "var(--fg)" }}>
          <span>{t.badge}</span>
          <span style={{ color: "var(--teal)" }}>95%</span>
        </div>
        <div style={{ height: 8, background: "rgba(255,255,255,0.12)", borderRadius: 999, overflow: "hidden", border: "1px solid rgba(255,255,255,0.1)" }}>
          <div
            style={{
              height: "100%",
              width: "95%",
              background: "linear-gradient(90deg, var(--pri) 0%, var(--teal) 100%)",
              borderRadius: 999,
              boxShadow: "0 0 12px rgba(31, 138, 112, 0.5)",
            }}
          />
        </div>
      </div>

      {submitted ? (
        <div
          style={{
            padding: "16px 20px",
            background: "rgba(31, 138, 112, 0.12)",
            border: "1.5px solid rgba(31, 138, 112, 0.35)",
            borderRadius: 14,
            color: "var(--teal)",
            fontSize: 14,
            fontWeight: 600,
            display: "flex",
            alignItems: "center",
            gap: 10,
            animation: "fadeIn .3s ease",
          }}
        >
          <Icon name="check-circle-2" style={{ flexShrink: 0, width: 20, height: 20 }} />
          <span>{t.success}</span>
        </div>
      ) : (
        <form onSubmit={handleSubmit} style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
          <input
            type="email"
            required
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder={t.placeholder}
            style={{
              flex: "1 1 240px",
              height: 48,
              padding: "0 16px",
              borderRadius: 12,
              border: "1.5px solid rgba(255,255,255,0.18)",
              background: "rgba(255, 255, 255, 0.08)",
              color: "#fff",
              fontSize: 14,
              outline: "none",
              backdropFilter: "blur(8px)",
            }}
          />
          <button
            type="submit"
            className="btn b-or"
            style={{ height: 48, padding: "0 22px", borderRadius: 12, fontSize: 14, fontWeight: 700 }}
          >
            <Icon name="send" style={{ width: 16, height: 16 }} />
            {t.btn}
          </button>
        </form>
      )}
    </div>
  );
}
