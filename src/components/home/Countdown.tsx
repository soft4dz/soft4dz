"use client";

import { useEffect, useRef, useState } from "react";

/** Temps restant jusqu'à minuit (heure locale) : les offres du jour se renouvellent chaque jour. */
const untilMidnight = () => {
  const now = new Date(), end = new Date(now);
  end.setHours(24, 0, 0, 0);
  return Math.max(0, Math.floor((end.getTime() - now.getTime()) / 1000));
};

function Digit({ v }: { v: string }) {
  const ref = useRef<HTMLSpanElement>(null);
  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    el.classList.remove("tick");
    void el.offsetWidth;
    el.classList.add("tick");
  }, [v]);
  return <span ref={ref}>{v}</span>;
}

export function Countdown() {
  const [left, setLeft] = useState<number | null>(null);
  useEffect(() => {
    const tick = () => setLeft(untilMidnight());
    const first = setTimeout(tick, 0);
    const id = setInterval(tick, 1000);
    return () => { clearTimeout(first); clearInterval(id); };
  }, []);
  if (left === null) return <div className="cd" aria-hidden="true"><span>--</span>:<span>--</span>:<span>--</span></div>;
  const pad = (n: number) => String(n).padStart(2, "0");
  return (
    <div className="cd" role="timer">
      <Digit v={pad(Math.floor(left / 3600))} />:<Digit v={pad(Math.floor((left / 60) % 60))} />:<Digit v={pad(left % 60)} />
    </div>
  );
}
