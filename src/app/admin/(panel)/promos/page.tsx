import Link from "next/link";
import { listPromos } from "@/lib/promos";
import { listOrders } from "@/lib/orders";
import { formatPrice } from "@/lib/format";
import { Icon } from "@/components/Icon";
import { deletePromoAction } from "../../actions";

export const metadata = { title: "Codes promo" };

export default async function PromosPage() {
  const [promos, orders] = await Promise.all([listPromos(), listOrders()]);
  const today = new Date().toISOString().slice(0, 10);
  return (
    <>
      <div className="adm-h">
        <h1>Codes promo</h1>
        <Link className="btn b-pri" href="/admin/promos/nouveau"><Icon name="plus" />Nouveau code</Link>
      </div>
      <div className="card tbl-w">
        <table className="tbl">
          <thead><tr><th>Code</th><th>Remise</th><th>Conditions</th><th>Utilisations</th><th>Remises accordées</th><th>État</th><th /></tr></thead>
          <tbody>
            {promos.length === 0 && <tr><td colSpan={7} className="empty-row">Aucun code promo. Créez-en un pour vos campagnes (réseaux sociaux, fêtes, clients fidèles…).</td></tr>}
            {promos.map((p) => {
              const expired = !!p.expires && p.expires < today;
              const exhausted = p.maxUses > 0 && p.uses >= p.maxUses;
              const given = orders.filter((o) => o.promo?.code === p.code && !o.demo).reduce((s, o) => s + (o.promo?.discount ?? 0), 0);
              const state = !p.active ? ["s-cancelled", "Désactivé"] : expired ? ["s-failed", "Expiré"] : exhausted ? ["s-awaiting_transfer", "Épuisé"] : ["s-delivered", "Actif"];
              const href = `/admin/promos/${encodeURIComponent(p.code)}`;
              return (
                <tr key={p.code}>
                  <td><Link className="row-link mono" href={href}>{p.code}</Link></td>
                  <td><b>{p.type === "percent" ? `-${p.value} %` : `-${formatPrice(p.value, "fr")}`}</b></td>
                  <td className="muted">{p.minTotal ? `Dès ${formatPrice(p.minTotal, "fr")}` : "Sans minimum"}{p.expires && <div>Jusqu&apos;au {new Date(p.expires).toLocaleDateString("fr-FR")}</div>}</td>
                  <td>{p.uses}{p.maxUses ? ` / ${p.maxUses}` : ""}</td>
                  <td className="muted">{formatPrice(given, "fr")}</td>
                  <td><span className={`stt ${state[0]}`}>{state[1]}</span></td>
                  <td style={{ display: "flex", gap: 4 }}>
                    <Link className="rmb" href={href} aria-label={`Modifier ${p.code}`}><Icon name="pencil" /></Link>
                    <form action={deletePromoAction}><input type="hidden" name="code" value={p.code} /><button className="rmb" aria-label={`Supprimer ${p.code}`}><Icon name="trash" /></button></form>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </>
  );
}
