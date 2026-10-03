"use client";

import { useActionState, useState } from "react";
import { saveSlideAction } from "../../../actions";
import { keepValues } from "../../keepValues";
import { ImageField } from "../../ImageField";
import { Icon } from "@/components/Icon";
import type { Slide } from "@/lib/slides";
import type { imageRules } from "@/lib/images";

const langs = [["fr", "Français"], ["ar", "Arabe"], ["en", "Anglais"]] as const;
const fields = [
  ["tag", "Étiquette", "Offre de la semaine", 40],
  ["title", "Titre", "Windows 11 Pro & Office 2021", 80],
  ["highlight", "Fin du titre (en doré)", "à prix mini", 40],
  ["text", "Texte", "Licences officielles à vie…", 200],
  ["cta", "Bouton", "J'en profite", 30],
] as const;

export function SlideForm({ slide: s, rule, backgrounds, products }: {
  slide: Slide;
  rule: (typeof imageRules)["banner"];
  backgrounds: { id: string; label: string; css: string }[];
  products: { slug: string; name: string }[];
}) {
  const [state, action, pending] = useActionState(saveSlideAction, undefined);
  const [lang, setLang] = useState<"fr" | "ar" | "en">("fr");
  const bgId = backgrounds.find((b) => b.css === s.bg)?.id ?? backgrounds[0].id;

  return (
    <form onSubmit={keepValues(action)} className="pf">
      <input type="hidden" name="id" value={s.id} />
      <div>
        <section className="card box">
          <h2>Textes</h2>
          <div className="seg" role="tablist" style={{ marginBottom: 14 }}>
            {langs.map(([l, n]) => (
              <label key={l}><input type="radio" name="_lang" checked={lang === l} onChange={() => setLang(l)} />{n}{l === "en" && " (facultatif)"}</label>
            ))}
          </div>
          {/* Tous les champs restent dans le formulaire ; seul l'onglet actif est visible */}
          {langs.map(([l]) => (
            <div key={l} className="pf-grid" hidden={lang !== l} dir={l === "ar" ? "rtl" : "ltr"}>
              {fields.map(([k, label, ph, max]) => (
                <div key={k} style={{ display: "contents" }}>
                  <label className="l" htmlFor={`${k}_${l}`}>{label}{(k === "title") && l !== "en" && " *"}</label>
                  {k === "text"
                    ? <textarea id={`${k}_${l}`} name={`${k}_${l}`} className="ta plain" rows={2} maxLength={max} defaultValue={s[k][l] ?? ""} placeholder={l === "fr" ? ph : ""} />
                    : <input id={`${k}_${l}`} name={`${k}_${l}`} className="in" maxLength={max} defaultValue={s[k][l] ?? ""} placeholder={l === "fr" ? ph : ""} />}
                </div>
              ))}
            </div>
          ))}
        </section>

        <section className="card box">
          <h2>Image de fond <small className="muted-s">· facultative</small></h2>
          <ImageField name="image" kind="banner" rule={rule} value={s.image ? [s.image] : []}
            hint="Gardez la moitié gauche calme (le texte s'y affiche, assombri automatiquement). Sur mobile, l'image est recadrée au centre. Avec une image, les produits en éventail ne s'affichent pas." />
        </section>
      </div>

      <aside className="prev">
        <section className="card box">
          <h2>Publication</h2>
          <label className="sw">Affichée sur l&apos;accueil<input type="checkbox" name="active" defaultChecked={s.active} /></label>
          <label className="l" htmlFor="href" style={{ marginTop: 8 }}>Lien du bouton</label>
          <input id="href" name="href" className="in mono" defaultValue={s.href} placeholder="/produit/windows-11-pro" required />
          <small className="muted-s">Ex. /produit/canva-pro ou ?cat=str#catalogue</small>

          <label className="l" style={{ marginTop: 14 }}>Couleur de fond</label>
          <div className="grads" style={{ gridTemplateColumns: "repeat(6,1fr)" }}>
            {backgrounds.map((b) => (
              <label key={b.id} title={b.label} style={{ background: b.css }}><input type="radio" name="bg" value={b.id} defaultChecked={b.id === bgId} /></label>
            ))}
          </div>

          <label className="l" style={{ marginTop: 14 }}>Produits en éventail (3 max)</label>
          {[0, 1, 2].map((i) => (
            <select key={i} name="products" className="sel" defaultValue={s.products[i] ?? ""} style={{ width: "100%", marginBottom: 6 }}>
              <option value="">— Aucun —</option>
              {products.map((p) => <option key={p.slug} value={p.slug}>{p.name}</option>)}
            </select>
          ))}
          <label className="l" htmlFor="pf" style={{ marginTop: 8 }}>Prix « Dès … » affiché</label>
          <select id="pf" name="priceFrom" className="sel" defaultValue={s.priceFrom ?? ""} style={{ width: "100%" }}>
            <option value="">— Pas de prix —</option>
            {products.map((p) => <option key={p.slug} value={p.slug}>{p.name}</option>)}
          </select>

          <button className="btn b-or" style={{ width: "100%", marginTop: 16 }} disabled={pending}>
            {pending ? <Icon name="loader" className="spin" /> : <Icon name="save" />}{s.id ? "Enregistrer" : "Créer la bannière"}
          </button>
          {state?.error && <div className="msg err" role="alert"><Icon name="info" />{state.error}</div>}
        </section>
      </aside>
    </form>
  );
}
