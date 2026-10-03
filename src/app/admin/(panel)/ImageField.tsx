"use client";

import { useRef, useState } from "react";
import { Icon } from "@/components/Icon";
import { uploadImage } from "../actions";

type Rule = { label: string; ratio: number; recommended: readonly [number, number]; min: readonly [number, number]; maxMb: number };

/**
 * Champ image de l'admin : glisser-déposer, rappel des résolutions conseillées,
 * contrôle des dimensions avant envoi, aperçu et suppression.
 * `max` > 1 : galerie (plusieurs images, champ répété).
 */
export function ImageField({ name, kind, rule, value, max = 1, hint }: {
  name: string;
  kind: "product" | "banner";
  rule: Rule;
  value: string[];
  max?: number;
  hint?: string;
}) {
  const [urls, setUrls] = useState(value);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [warning, setWarning] = useState<string | null>(null);
  const [over, setOver] = useState(false);
  const input = useRef<HTMLInputElement>(null);
  const [rw, rh] = rule.recommended;

  const dims = (file: File) =>
    new Promise<{ w: number; h: number } | null>((res) => {
      const img = new Image();
      img.onload = () => { res({ w: img.naturalWidth, h: img.naturalHeight }); URL.revokeObjectURL(img.src); };
      img.onerror = () => res(null);
      img.src = URL.createObjectURL(file);
    });

  const send = async (files: FileList | File[]) => {
    setError(null); setWarning(null);
    for (const file of Array.from(files).slice(0, max - urls.length)) {
      if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) { setError("Format refusé : JPG, PNG ou WebP."); return; }
      if (file.size > rule.maxMb * 1024 * 1024) { setError(`« ${file.name} » dépasse ${rule.maxMb} Mo. Compressez-la (ex. squoosh.app) puis réessayez.`); return; }
      const d = await dims(file);
      if (d && (d.w < rule.min[0] || d.h < rule.min[1])) { setError(`« ${file.name} » fait ${d.w}×${d.h} px : minimum ${rule.min[0]}×${rule.min[1]} px.`); return; }
      setBusy(true);
      const fd = new FormData();
      fd.append("file", file);
      const res = await uploadImage(kind, fd);
      setBusy(false);
      if ("error" in res) { setError(res.error); return; }
      if (res.warning) setWarning(res.warning);
      setUrls((u) => (max === 1 ? [res.url] : [...u, res.url].slice(0, max)));
    }
  };

  const full = urls.length >= max;
  return (
    <div className="imgf">
      <div className="imgf-rules">
        <b><Icon name="info" />{rule.label}</b>
        <span>Idéal <strong>{rw}×{rh} px</strong> · minimum {rule.min[0]}×{rule.min[1]} · JPG, PNG ou WebP · {rule.maxMb} Mo max</span>
        {hint && <span>{hint}</span>}
      </div>

      <div className={`imgf-grid ${kind}`}>
        {urls.map((u, i) => (
          <div key={u} className="imgf-item">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={u} alt="" />
            <input type="hidden" name={name} value={u} />
            {max > 1 && i === 0 && <span className="imgf-badge">Principale</span>}
            <button type="button" className="imgf-rm" onClick={() => setUrls(urls.filter((x) => x !== u))} aria-label="Retirer l'image"><Icon name="trash" /></button>
          </div>
        ))}
        {!full && (
          <button
            type="button"
            className={`imgf-drop ${over ? "over" : ""}`}
            onClick={() => input.current?.click()}
            onDragOver={(e) => { e.preventDefault(); setOver(true); }}
            onDragLeave={() => setOver(false)}
            onDrop={(e) => { e.preventDefault(); setOver(false); send(e.dataTransfer.files); }}
            disabled={busy}
          >
            {busy ? <Icon name="loader" className="spin" /> : <Icon name="plus" />}
            <b>{busy ? "Envoi…" : "Ajouter une image"}</b>
            <small>Glissez-déposez ou cliquez</small>
            <small className="ratio">{kind === "product" ? "Format carré 1:1" : "Format large 5:2"}</small>
          </button>
        )}
      </div>
      <input ref={input} type="file" accept="image/jpeg,image/png,image/webp" multiple={max > 1} hidden onChange={(e) => { if (e.target.files) send(e.target.files); e.target.value = ""; }} />
      {error && <div className="msg err" role="alert"><Icon name="info" />{error}</div>}
      {warning && <div className="msg warn" role="status"><Icon name="info" />{warning}</div>}
    </div>
  );
}
