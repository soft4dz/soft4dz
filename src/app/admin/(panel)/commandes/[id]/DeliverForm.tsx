"use client";

import { startTransition, useActionState } from "react";
import { deliverOrder } from "../../../actions";
import { Icon } from "@/components/Icon";
import type { Order } from "@/lib/orders";


/** Envoie le formulaire sans que React le vide : en cas d'erreur, la saisie reste en place. */
const keepValues = (action: (fd: FormData) => void) => (e: React.FormEvent<HTMLFormElement>) => {
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  startTransition(() => action(fd));
};

export function DeliverForm({ order }: { order: Order }) {
  const [state, action, pending] = useActionState(deliverOrder, undefined);
  const delivered = order.status === "delivered";
  return (
    <form onSubmit={keepValues(action)}>
      <input type="hidden" name="id" value={order.id} />
      {order.lines.map((l, i) => (
        <div key={i}>
          <label className="keys-lab" htmlFor={`k${i}`}>{l.name} — {l.optionLabel}<span>{l.qty} clé(s), une par ligne</span></label>
          <textarea id={`k${i}`} name={`keys_${i}`} className="ta" rows={Math.max(2, l.qty)} defaultValue={(l.keys ?? []).join("\n")} placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX" spellCheck={false} />
        </div>
      ))}
      <button className="btn b-or" style={{ marginTop: 12 }} disabled={pending}>
        {pending ? <Icon name="loader" className="spin" /> : <Icon name="check" />}
        {delivered ? "Mettre à jour les clés" : "Livrer la commande"}
      </button>
      {state?.error && <div className="msg err" role="alert"><Icon name="info" />{state.error}</div>}
      {state?.ok && <div className="msg ok" role="status"><Icon name="check-circle-2" />Clés enregistrées : le client les voit sur sa page de commande.</div>}
    </form>
  );
}
