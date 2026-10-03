"use client";

import { useEffect, useState } from "react";
import type { CartLine } from "../providers";

export type PromoResult = { ok: true; code: string; discount: number } | { ok: false; reason: "unknown" | "expired" | "exhausted" | "minimum"; min?: number };

/** Revérifie le code promo auprès du serveur à chaque changement du panier. */
export function usePromoCheck(code: string | null, lines: CartLine[]) {
  const [result, setResult] = useState<PromoResult | null>(null);
  const key = JSON.stringify(lines);
  useEffect(() => {
    if (!code) { const t = setTimeout(() => setResult(null), 0); return () => clearTimeout(t); }
    const ctrl = new AbortController();
    fetch("/api/promo", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ code, lines: JSON.parse(key) }), signal: ctrl.signal })
      .then((r) => r.json())
      .then(setResult)
      .catch(() => {});
    return () => ctrl.abort();
  }, [code, key]);
  return result;
}
