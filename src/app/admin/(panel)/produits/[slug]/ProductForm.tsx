"use client";

import { startTransition, useActionState, useState } from "react";
import { saveProductAction } from "../../../actions";
import { Icon, iconNames } from "@/components/Icon";
import { categories, gradients, type Product } from "@/lib/catalog";
import { ImageField } from "../../ImageField";
import type { imageRules } from "@/lib/images";


/** Envoie le formulaire sans que React le vide : en cas d'erreur, la saisie reste en place. */
const keepValues = (action: (fd: FormData) => void) => (e: React.FormEvent<HTMLFormElement>) => {
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  startTransition(() => action(fd));
};

export function ProductForm({ product: p, isNew, rule }: { product: Product; isNew: boolean; rule: (typeof imageRules)["product"] }) {
  const [state, action, pending] = useActionState(saveProductAction, undefined);
  const [opts, setOpts] = useState(p.options.map((o, i) => ({ ...o, key: i })));
  const [next, setNext] = useState(p.options.length);

  const addOpt = () => { setOpts([...opts, { id: `o${next + 1}`, label: { fr: "", ar: "" }, price: 0, key: next }]); setNext(next + 1); };

  return (
    <form onSubmit={keepValues(action)} className="pf">
      <input type="hidden" name="prevSlug" value={isNew ? "" : p.slug} />
      <div>
        <section className="card box">
          <h2>Photos</h2>
          <ImageField name="photos" kind="product" rule={rule} max={5} value={[p.image, ...(p.gallery ?? [])].filter((x): x is string => !!x)}
            hint="La 1re photo sert de vignette partout sur le site ; les suivantes s'affichent dans la galerie de la fiche produit. Sans photo, une affiche est générée automatiquement." />
        </section>

        <section className="card box">
          <h2>Informations</h2>
          <div className="grid2">
            <div><label className="l" htmlFor="nf">Nom (français)</label><input id="nf" className="in" name="name_fr" defaultValue={p.name.fr} required /></div>
            <div><label className="l" htmlFor="na">Nom (arabe)</label><input id="na" className="in" name="name_ar" defaultValue={p.name.ar} dir="rtl" required /></div>
          </div>
          <div><label className="l" htmlFor="ne">Nom (anglais, facultatif)</label><input id="ne" className="in" name="name_en" defaultValue={p.name.en} style={{ marginBottom: 12 }} /></div>
          <div className="grid2">
            <div><label className="l" htmlFor="sf">Sous-titre (FR)</label><input id="sf" className="in" name="subtitle_fr" defaultValue={p.subtitle.fr} placeholder="Licence à vie" /></div>
            <div><label className="l" htmlFor="sa">Sous-titre (AR)</label><input id="sa" className="in" name="subtitle_ar" defaultValue={p.subtitle.ar} dir="rtl" /></div>
          </div>
          <div><label className="l" htmlFor="se">Sous-titre (EN)</label><input id="se" className="in" name="subtitle_en" defaultValue={p.subtitle.en} style={{ marginBottom: 12 }} /></div>
          <div className="grid2">
            <div><label className="l" htmlFor="df">Description (FR)</label><textarea id="df" className="ta plain" name="desc_fr" defaultValue={p.description.fr} rows={4} /></div>
            <div><label className="l" htmlFor="da">Description (AR)</label><textarea id="da" className="ta plain" name="desc_ar" defaultValue={p.description.ar} rows={4} dir="rtl" /></div>
          </div>
          <div><label className="l" htmlFor="de">Description (EN)</label><textarea id="de" className="ta plain" name="desc_en" defaultValue={p.description.en} rows={3} style={{ marginBottom: 12 }} /></div>
          <div className="grid2">
            <div><label className="l" htmlFor="sl">Adresse de la page</label><input id="sl" className="in" name="slug" defaultValue={p.slug} placeholder="windows-11-pro" required pattern="[a-z0-9]+(-[a-z0-9]+)*" /></div>
            <div><label className="l" htmlFor="ct">Catégorie</label>
              <select id="ct" className="sel in" name="category" defaultValue={p.category}>
                {categories.map((c) => <option key={c.id} value={c.id}>{c.name.fr}</option>)}
              </select></div>
          </div>
        </section>

        <section className="card box">
          <h2>Options et prix <small style={{ fontWeight: 400, color: "var(--muted)", fontSize: 12 }}>· prix en DA avant remise, 0 = sur devis</small></h2>
          <div className="opt-h"><span>Code</span><span>Libellé FR</span><span>Libellé AR</span><span>Libellé EN</span><span>Prix (DA)</span><span /></div>
          {opts.map((o) => (
            <div className="opt-row" key={o.key}>
              <input className="in" name="opt_id" defaultValue={o.id} aria-label="Code" />
              <input className="in" name="opt_fr" defaultValue={o.label.fr} placeholder="1 PC" aria-label="Libellé FR" />
              <input className="in" name="opt_ar" defaultValue={o.label.ar} dir="rtl" aria-label="Libellé AR" />
              <input className="in" name="opt_en" defaultValue={o.label.en} aria-label="Libellé EN" />
              <input className="in" name="opt_price" type="number" min={0} step={100} defaultValue={o.price} aria-label="Prix" />
              <button type="button" className="rmb" onClick={() => setOpts(opts.filter((x) => x.key !== o.key))} disabled={opts.length === 1} aria-label="Supprimer l'option"><Icon name="trash" /></button>
            </div>
          ))}
          {opts.length < 8 && <button type="button" className="btn b-out" style={{ height: 40 }} onClick={addOpt}><Icon name="plus" />Ajouter une option</button>}
        </section>

        <section className="card box">
          <h2>Apparence</h2>
          <label className="l">Icône</label>
          <div className="icons" style={{ marginBottom: 14 }}>
            {iconNames.map((n) => <label key={n} title={n}><input type="radio" name="icon" value={n} defaultChecked={n === p.icon} /><Icon name={n} /></label>)}
          </div>
          <label className="l">Couleur</label>
          <div className="grads">
            {gradients.map((g) => <label key={g} className={g} title={g}><input type="radio" name="gradient" value={g} defaultChecked={g === p.gradient} /></label>)}
          </div>
        </section>
      </div>

      <aside className="prev">
        <section className="card box">
          <h2>Publication</h2>
          <label className="sw">Visible en boutique<input type="checkbox" name="active" defaultChecked={p.active !== false} /></label>
          <label className="sw">Mis en avant<input type="checkbox" name="featured" defaultChecked={!!p.featured} /></label>
          <div className="grid2" style={{ marginTop: 10 }}>
            <div><label className="l" htmlFor="dp">Remise (%)</label><input id="dp" className="in" name="deal_percent" type="number" min={0} max={90} defaultValue={p.deal?.percent ?? ""} placeholder="Aucune" /></div>
            <div><label className="l" htmlFor="ds">Vendu (%)</label><input id="ds" className="in" name="deal_sold" type="number" min={0} max={100} defaultValue={p.deal ? Math.round(p.deal.sold * 100) : ""} /></div>
          </div>
          <div className="grid2">
            <div><label className="l" htmlFor="rt">Note /5</label><input id="rt" className="in" name="rating" type="number" min={0} max={5} step={0.1} defaultValue={p.rating} /></div>
            <div><label className="l" htmlFor="rv">Nb d&apos;avis</label><input id="rv" className="in" name="reviews" type="number" min={0} defaultValue={p.reviews} /></div>
          </div>
          <button className="btn b-or" style={{ width: "100%" }} disabled={pending}>
            {pending ? <Icon name="loader" className="spin" /> : <Icon name="save" />}{isNew ? "Créer le produit" : "Enregistrer"}
          </button>
          {state?.error && <div className="msg err" role="alert"><Icon name="info" />{state.error}</div>}
        </section>
      </aside>
    </form>
  );
}
