"use client";

import { useActionState } from "react";
import { login } from "../actions";
import { Icon } from "@/components/Icon";

export function LoginForm() {
  const [state, action, pending] = useActionState(login, undefined);
  return (
    <form action={action}>
      <div className={`fld ${state?.error ? "err" : ""}`}>
        <input id="pw" name="password" type="password" placeholder=" " required autoFocus autoComplete="current-password" />
        <label htmlFor="pw">Mot de passe</label>
      </div>
      {state?.error && <div className="msg err" role="alert"><Icon name="info" />{state.error}</div>}
      <button className="btn b-pri" style={{ width: "100%", marginTop: 14 }} disabled={pending}>
        {pending ? <Icon name="loader" className="spin" /> : <Icon name="lock" />}Se connecter
      </button>
    </form>
  );
}
