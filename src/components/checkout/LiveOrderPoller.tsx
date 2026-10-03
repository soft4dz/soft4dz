"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { Icon } from "@/components/Icon";
import type { Locale } from "@/lib/i18n";

interface Props {
  orderId: string;
  currentStatus: string;
  locale: Locale;
}

export function LiveOrderPoller({ orderId, currentStatus, locale }: Props) {
  const router = useRouter();
  const [pulse, setPulse] = useState(false);
  const isFinal = currentStatus === "delivered" || currentStatus === "failed" || currentStatus === "cancelled";

  useEffect(() => {
    if (isFinal) return;

    const interval = setInterval(async () => {
      try {
        setPulse(true);
        setTimeout(() => setPulse(false), 800);

        const res = await fetch(`/api/order-status?id=${orderId}`, { cache: "no-store" });
        if (!res.ok) return;
        const data = await res.json();

        if (data.found && data.status && data.status !== currentStatus) {
          // Le statut a changé : on recharge immédiatement la vue serveur
          router.refresh();
        }
      } catch (err) {
        console.error("Erreur de suivi en direct :", err);
      }
    }, 3800);

    return () => clearInterval(interval);
  }, [orderId, currentStatus, isFinal, router]);

  if (isFinal) return null;

  const labels = {
    fr: {
      tracking: "Suivi en direct",
      desc: "Actualisation automatique dès validation",
    },
    ar: {
      tracking: "متابعة حية",
      desc: "تحديث تلقائي فور التأكيد",
    },
    en: {
      tracking: "Live tracking",
      desc: "Auto-refresh as soon as updated",
    },
  }[locale];

  return (
    <div
      style={{
        display: "inline-flex",
        alignItems: "center",
        gap: 8,
        padding: "6px 14px",
        borderRadius: "999px",
        background: "rgba(31, 138, 112, 0.08)",
        border: "1px solid rgba(31, 138, 112, 0.22)",
        fontSize: "12px",
        color: "var(--teal)",
        fontWeight: 600,
        margin: "12px auto 0",
        boxShadow: "0 2px 8px rgba(0,0,0,0.02)",
      }}
    >
      <span
        style={{
          display: "inline-block",
          width: 8,
          height: 8,
          borderRadius: "50%",
          backgroundColor: pulse ? "var(--teal)" : "#2E64A8",
          boxShadow: pulse ? "0 0 10px var(--teal)" : "0 0 4px #2E64A8",
          transition: "all 0.3s ease",
          transform: pulse ? "scale(1.25)" : "scale(1)",
        }}
      />
      <span>{labels.tracking}</span>
      <span style={{ opacity: 0.6 }}>•</span>
      <span style={{ fontWeight: 400, opacity: 0.85 }}>{labels.desc}</span>
    </div>
  );
}
