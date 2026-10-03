import Link from "next/link";
import { getAllSlides } from "@/lib/slides";
import { Icon } from "@/components/Icon";
import { slideAction } from "../../actions";

export const metadata = { title: "Bannières" };

function Op({ id, op, label, icon, disabled }: { id: string; op: string; label: string; icon: Parameters<typeof Icon>[0]["name"]; disabled?: boolean }) {
  return (
    <form action={slideAction}>
      <input type="hidden" name="id" value={id} /><input type="hidden" name="op" value={op} />
      <button className="rmb" aria-label={label} title={label} disabled={disabled} style={disabled ? { opacity: .3 } : undefined}><Icon name={icon} /></button>
    </form>
  );
}

export default async function BannersPage() {
  const slides = await getAllSlides();
  return (
    <>
      <div className="adm-h">
        <h1>Bannières du carrousel</h1>
        <Link className="btn b-pri" href="/admin/bannieres/nouveau"><Icon name="plus" />Nouvelle bannière</Link>
      </div>
      <p className="muted-s" style={{ marginBottom: 14 }}>Affichées dans cet ordre sur la page d&apos;accueil, une toutes les 5 secondes. Image conseillée : 1600×640 px.</p>
      <div className="banners">
        {slides.map((s, i) => (
          <div key={s.id} className={`card bn ${s.active ? "" : "off"}`}>
            <div className="bn-prev" style={{ background: s.image ? `linear-gradient(90deg,rgba(8,18,34,.8),rgba(8,18,34,.1)), url(${s.image}) center/cover` : s.bg }}>
              {s.tag.fr && <span className="bn-tag">{s.tag.fr}</span>}
              <b>{s.title.fr} <em>{s.highlight.fr}</em></b>
              <small>{s.cta.fr} →</small>
            </div>
            <div className="bn-meta">
              <span className="bn-n">{i + 1}</span>
              <div>
                <b>{s.title.fr}</b>
                <small className="muted-s">{s.image ? "Image de fond" : `${s.products.length} produit(s) en éventail`} · lien {s.href}</small>
              </div>
              <span className={`stt ${s.active ? "s-delivered" : "s-cancelled"}`}>{s.active ? "Affichée" : "Masquée"}</span>
              <div className="bn-ops">
                <Op id={s.id} op="up" label="Monter" icon="arrow-up" disabled={i === 0} />
                <Op id={s.id} op="down" label="Descendre" icon="arrow-down" disabled={i === slides.length - 1} />
                <Op id={s.id} op="toggle" label={s.active ? "Masquer" : "Afficher"} icon={s.active ? "eye-off" : "eye"} />
                <Link className="rmb" href={`/admin/bannieres/${s.id}`} aria-label="Modifier" title="Modifier"><Icon name="pencil" /></Link>
                <Op id={s.id} op="delete" label="Supprimer" icon="trash" />
              </div>
            </div>
          </div>
        ))}
        {slides.length === 0 && <div className="card box empty-row">Aucune bannière : le carrousel est masqué sur l&apos;accueil.</div>}
      </div>
    </>
  );
}
