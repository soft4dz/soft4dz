"use client";

import { useEffect, useState } from "react";
import { Icon, type IconName } from "../Icon";
import { reducedMotion, useI18n } from "../providers";

const icons: IconName[] = ["shopping-cart", "user", "credit-card", "check"];

/** Barre d'étapes : les étapes passées se valident une à une à l'arrivée sur la page. */
export function Stepper({ step }: { step: number }) {
  const { dict } = useI18n();
  const [shown, setShown] = useState(-1);
  useEffect(() => {
    if (reducedMotion()) { const t = setTimeout(() => setShown(step), 0); return () => clearTimeout(t); }
    let i = -1;
    const id = setInterval(() => { i++; setShown(i); if (i >= step) clearInterval(id); }, 220);
    return () => clearInterval(id);
  }, [step]);

  return (
    <div className="stepper" aria-label={dict.steps.join(" → ")}>
      {dict.steps.map((label, i) => {
        const state = i < step && i <= shown ? "done" : i === step && shown >= step ? "cur" : "";
        return (
          <div key={label} style={{ display: "contents" }}>
            <div className={`s ${state}`} aria-current={i === step ? "step" : undefined}>
              <i><Icon name={state === "done" ? "check" : icons[i]} /></i>{label}
            </div>
            {i < 3 && <span className={`ln ${i < step && i < shown ? "done" : ""}`} />}
          </div>
        );
      })}
    </div>
  );
}
