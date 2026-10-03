"use client";

import { useEffect, useRef, useState } from "react";
import { reducedMotion } from "./providers";

/** Fait défiler un nombre jusqu'à sa nouvelle valeur (prix, totaux, compteurs). */
export function useAnimatedNumber(value: number, duration = 500) {
  const [shown, setShown] = useState(value);
  const from = useRef(value);
  useEffect(() => {
    if (reducedMotion() || document.hidden) {
      const raf = requestAnimationFrame(() => { from.current = value; setShown(value); });
      return () => cancelAnimationFrame(raf);
    }
    const start = from.current, t0 = performance.now();
    let raf = 0;
    const step = (t: number) => {
      const k = Math.min((t - t0) / duration, 1);
      const v = start + (value - start) * (1 - Math.pow(1 - k, 3));
      setShown(v);
      from.current = v;
      if (k < 1) raf = requestAnimationFrame(step);
    };
    raf = requestAnimationFrame(step);
    return () => cancelAnimationFrame(raf);
  }, [value, duration]);
  return Math.round(shown);
}

/** Ajoute la classe `on` quand l'élément entre dans l'écran (apparition au scroll). */
export function useReveal<T extends HTMLElement>() {
  const ref = useRef<T>(null);
  const [on, setOn] = useState(false);
  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    if (reducedMotion()) { const raf = requestAnimationFrame(() => setOn(true)); return () => cancelAnimationFrame(raf); }
    const io = new IntersectionObserver(([e]) => {
      if (e.isIntersecting) { setOn(true); io.disconnect(); }
    }, { threshold: 0.12 });
    io.observe(el);
    return () => io.disconnect();
  }, []);
  return [ref, on] as const;
}
