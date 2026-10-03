"use client";

import { startTransition, useActionState, useState } from "react";
import { addKeysAction } from "../../actions";
import { Icon } from "@/components/Icon";

/** Zone de saisie : recréée (donc vidée) après chaque ajout réussi, conservée en cas d'erreur. */
function KeysArea() {
  const [count, setCount] = useState(0);
  return (
    <>
      <label className="keys-lab" htmlFor="kz">Clés<span>{count} ligne(s)</span></label>
      <textarea id="kz" name="keys" className="ta" rows={8} spellCheck={false} placeholder={"XXXXX-XXXXX-XXXXX-XXXXX-XXXXX\nXXXXX-XXXXX-XXXXX-XXXXX-XXXXX"}
        onChange={(e) => setCount(e.target.value.split("\n").filter((l) => l.trim()).length)} />
    </>
  );
}

export function AddKeysForm({ targets, preset }: { targets: { value: string; label: string }[]; preset: string }) {
  const [state, action, pending] = useActionState(addKeysAction, undefined);
  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        const fd = new FormData(e.currentTarget);
        startTransition(() => action(fd));
      }}
    >
      <label className="keys-lab" htmlFor="tgt">Produit et option</label>
      <select id="tgt" name="target" className="sel" defaultValue={preset} required style={{ width: "100%" }}>
        <option value="" disabled>Choisir…</option>
        {targets.map((t) => <option key={t.value} value={t.value}>{t.label}</option>)}
      </select>
      <KeysArea key={state?.at ?? 0} />
      <button className="btn b-or" style={{ marginTop: 12, width: "100%" }} disabled={pending}>
        {pending ? <Icon name="loader" className="spin" /> : <Icon name="plus" />}Ajouter au stock
      </button>
      {state?.error && <div className="msg err" role="alert"><Icon name="info" />{state.error}</div>}
      {state?.ok && <div className="msg ok" role="status"><Icon name="check-circle-2" />{state.ok}</div>}
    </form>
  );
}
