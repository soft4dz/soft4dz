"use client";

import { useActionState } from "react";
import { saveSettingsAction } from "../../actions";
import { keepValues } from "../keepValues";
import { Icon } from "@/components/Icon";
import type { Settings } from "@/lib/settings";

export function SettingsForm({ s }: { s: Settings }) {
  const [state, action, pending] = useActionState(saveSettingsAction, undefined);
  return (
    <form onSubmit={keepValues(action)} className="pf">
      <div>
        <section className="card box">
          <h2><Icon name="message-circle" />Contact</h2>
          <div className="pf-grid">
            <label className="l" htmlFor="wa">Numéro WhatsApp</label>
            <div><input id="wa" name="whatsapp" className="in mono" defaultValue={s.whatsapp} placeholder="213555123456" inputMode="numeric" /><small className="muted-s">Format international sans + ni espaces (213 puis le numéro sans le 0).</small></div>
            <label className="l" htmlFor="ph">Téléphone affiché</label>
            <input id="ph" name="phone" className="in" defaultValue={s.phone} placeholder="0555 12 34 56" />
            <label className="l" htmlFor="em">E-mail de contact</label>
            <input id="em" name="email" type="email" className="in" defaultValue={s.email} placeholder="contact@soft4dz.com" />
          </div>
        </section>

        <section className="card box">
          <h2><Icon name="globe" />Réseaux sociaux</h2>
          <div className="pf-grid">
            <label className="l" htmlFor="fb">Facebook</label>
            <input id="fb" name="facebook" className="in" defaultValue={s.facebook} placeholder="https://facebook.com/soft4dz" />
            <label className="l" htmlFor="ig">Instagram</label>
            <input id="ig" name="instagram" className="in" defaultValue={s.instagram} placeholder="https://instagram.com/soft4dz" />
            <label className="l" htmlFor="tt">TikTok</label>
            <input id="tt" name="tiktok" className="in" defaultValue={s.tiktok} placeholder="https://tiktok.com/@soft4dz" />
          </div>
        </section>

        <section className="card box">
          <h2><Icon name="sparkles" />Barre d&apos;annonces <small className="muted-s">· un message par ligne, 6 max. Vide = messages par défaut.</small></h2>
          <div className="grid3">
            <div><label className="l" htmlFor="af">Français</label><textarea id="af" name="announce_fr" className="ta plain" rows={5} defaultValue={s.announce.fr.join("\n")} placeholder={"Livraison instantanée par e-mail\n-20 % sur Office ce week-end"} /></div>
            <div><label className="l" htmlFor="aa">Arabe</label><textarea id="aa" name="announce_ar" className="ta plain" rows={5} dir="rtl" defaultValue={s.announce.ar.join("\n")} /></div>
            <div><label className="l" htmlFor="ae">Anglais</label><textarea id="ae" name="announce_en" className="ta plain" rows={5} defaultValue={s.announce.en.join("\n")} /></div>
          </div>
        </section>
      </div>

      <aside className="prev">
        <section className="card box">
          <h2><Icon name="key-round" />Livraison des clés</h2>
          <label className="sw">
            <span>Livraison automatique<small>Attribue les clés du stock dès que le paiement est confirmé.</small></span>
            <input type="checkbox" name="autoDelivery" defaultChecked={s.autoDelivery} />
          </label>
          <label className="l" htmlFor="ls" style={{ marginTop: 10 }}>Alerte de stock bas</label>
          <input id="ls" name="lowStock" type="number" min={0} max={100} className="in" defaultValue={s.lowStock} />
          <small className="muted-s">Un badge apparaît dans le menu quand il reste ce nombre de clés ou moins.</small>
        </section>

        <section className="card box" style={{ marginTop: 16 }}>
          <h2><Icon name="wrench" />Disponibilité du site</h2>
          <label className="sw">
            <span>
              Mode « En construction »
              <small>Affiche la page En construction aux visiteurs de la boutique. L&apos;accès /admin reste actif pour vous.</small>
            </span>
            <input type="checkbox" name="maintenanceMode" defaultChecked={s.maintenanceMode} />
          </label>
          <a
            href="/fr/en-construction"
            target="_blank"
            rel="noopener"
            className="btn b-out"
            style={{ width: "100%", marginTop: 12, display: "flex", justifyContent: "center", gap: 6, fontSize: 13 }}
          >
            <Icon name="external-link" />
            Voir la page En construction
          </a>
          <button className="btn b-or" style={{ width: "100%", marginTop: 16 }} disabled={pending}>
            {pending ? <Icon name="loader" className="spin" /> : <Icon name="save" />}Enregistrer
          </button>
          {state?.error && <div className="msg err" role="alert"><Icon name="info" />{state.error}</div>}
          {state?.ok && <div className="msg ok" role="status"><Icon name="check-circle-2" />Paramètres enregistrés et appliqués au site.</div>}
        </section>
      </aside>
    </form>
  );
}
