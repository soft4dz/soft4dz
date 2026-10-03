"use client";

import { useEffect, useRef } from "react";
import { reducedMotion } from "../providers";

const colors = ["#2E64A8", "#1F8A70", "#D97F1E", "#7FDDB9", "#4A7DB8"];

/** Coche qui se dessine + confettis aux couleurs du logo (ou icône d'attente / d'échec). */
export function StatusIcon({ kind }: { kind: "ok" | "wait" | "ko" }) {
  const ref = useRef<HTMLDivElement>(null);
  useEffect(() => {
    const el = ref.current;
    if (!el || kind !== "ok" || reducedMotion()) return;
    const bits = Array.from({ length: 26 }, (_, i) => {
      const s = document.createElement("span");
      const a = Math.random() * Math.PI * 2, r = 70 + Math.random() * 90;
      s.className = "cf";
      s.style.cssText = `background:${colors[i % 5]};--x:${Math.cos(a) * r}px;--y:${Math.sin(a) * r}px;--r:${Math.random() * 540}deg;animation-delay:${0.25 + Math.random() * 0.15}s`;
      el.appendChild(s);
      return s;
    });
    return () => bits.forEach((b) => b.remove());
  }, [kind]);

  return (
    <div ref={ref} className={`ok-ic ${kind === "ok" ? "" : kind}`}>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round">
        {kind === "ok" && <path className="dr" d="M5 12.5l4.5 4.5L19 7.5" />}
        {kind === "wait" && <><circle cx="12" cy="12" r="8" /><path d="M12 8v4l3 2" /></>}
        {kind === "ko" && <path d="M7 7l10 10M17 7L7 17" />}
      </svg>
    </div>
  );
}
