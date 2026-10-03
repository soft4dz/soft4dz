"use client";

import { useActionState, useState } from "react";
import { savePromoAction } from "../../../actions";
import { keepValues } from "../../keepValues";
import { Icon } from "@/components/Icon";
import type { Promo } from "@/lib/promos";

export function PromoForm({ promo: p, isNew }: { promo: Promo; isNew: boolean }) {
  const [state, action, pending] = useActionState(savePromoAction, undefined);
  const [type, setType] = useState(p.type);
  return (
    <form onSubmit={keepValues(action)} className="card box" style={{ maxWidth: 640 }}>
      <input type="hidden" name="previous" value={isNew ? "" : p.code} />
      <div className="pf-grid">
        <label className="l" htmlFor="c">Code</label>
        <input id="c" name="code" className="in mono" defaultValue={p.code} placeholder="RAMADAN20" required style={{ textTransform: "uppercase" }} />
        <span className="l">Type de remise</span>
        <div className="seg">
          <label><input type="radio" name="type" value="percent" checked={type === "percent"} onChange={() => setType("percent")} />Pourcentage</label>
          <label><input type="radio" name="type" value="fixed" checked={type === "fixed"} onChange={() => setType("fixed")} />Montant fixe</label>
        </div>
        <label className="l" htmlFor="v">{type === "percent" ? "Remise (%)" : "Remise (DA)"}</label>
        <input id="v" name="value" type="number" className="in" defaultValue={p.value} min={type === "percent" ? 1 : 10} max={type === "percent" ? 90 : 1000000} required />
        <label className="l" htmlFor="m">Panier minimum (DA)</label>
        <input id="m" name="minTotal" type="number" className="in" defaultValue={p.minTotal || ""} min={0} placeholder="Aucun" />
        <label className="l" htmlFor="e">Valable jusqu&apos;au</label>
        <input id="e" name="expires" type="date" className="in" defaultValue={p.expires ?? ""} />
        <label className="l" htmlFor="u">Utilisations max</label>
        <input id="u" name="maxUses" type="number" className="in" defaultValue={p.maxUses || ""} min={0} placeholder="Illimité" />
      </div>
      <label className="sw" style={{ marginTop: 6 }}>Code actif<input type="checkbox" name="active" defaultChecked={p.active} /></label>
      {!isNew && <p className="muted-s">Déjà utilisé {p.uses} fois.</p>}
      <button className="btn b-or" style={{ marginTop: 12 }} disabled={pending}>
        {pending ? <Icon name="loader" className="spin" /> : <Icon name="save" />}{isNew ? "Créer le code" : "Enregistrer"}
      </button>
      {state?.error && <div className="msg err" role="alert"><Icon name="info" />{state.error}</div>}
    </form>
  );
}
