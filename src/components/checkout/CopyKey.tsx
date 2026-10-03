"use client";

import { useState } from "react";
import { Icon } from "../Icon";

/** Une clé de licence livrée, avec bouton « Copier ». */
export function CopyKey({ value, copy, copied }: { value: string; copy: string; copied: string }) {
  const [ok, setOk] = useState(false);
  const onCopy = async () => {
    try { await navigator.clipboard.writeText(value); } catch { return; }
    setOk(true);
    setTimeout(() => setOk(false), 1500);
  };
  return (
    <div className="ck">
      <code>{value}</code>
      <button type="button" className={ok ? "ok" : ""} onClick={onCopy}>
        <Icon name={ok ? "check" : "copy"} />{ok ? copied : copy}
      </button>
    </div>
  );
}
