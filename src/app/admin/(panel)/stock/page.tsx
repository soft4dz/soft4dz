import Link from "next/link";
import { stockTable } from "@/lib/admin-stats";
import { listKeys } from "@/lib/keys";
import { getSettings } from "@/lib/settings";
import { Icon } from "@/components/Icon";
import { deleteKeyAction } from "../../actions";
import { AddKeysForm } from "./AddKeysForm";

export const metadata = { title: "Stock de clés" };

const mask = (k: string) => (k.length > 10 ? `${k.slice(0, 5)}•••••${k.slice(-5)}` : "•••••");

export default async function StockPage({ searchParams }: PageProps<"/admin/stock">) {
  const sp = await searchParams;
  const [{ rows, threshold }, keys, settings] = await Promise.all([stockTable(), listKeys(), getSettings()]);
  const filter = typeof sp.p === "string" ? sp.p : "";
  const available = keys.filter((k) => !k.orderId && (!filter || `${k.slug}|${k.option}` === filter)).slice(-80).reverse();
  const total = rows.reduce((s, r) => s + r.available, 0);
  const label = (slug: string, option: string) => {
    const r = rows.find((x) => x.product.slug === slug && x.option.id === option);
    return r ? `${r.product.name.fr} · ${r.option.label.fr}` : `${slug} · ${option}`;
  };

  return (
    <>
      <div className="adm-h">
        <h1>Stock de clés</h1>
        <span className={`stt ${settings.autoDelivery ? "s-delivered" : "s-cancelled"}`}>Livraison automatique {settings.autoDelivery ? "activée" : "désactivée"}</span>
        <Link className="btn b-out" href="/admin/parametres"><Icon name="wrench" />Réglages</Link>
      </div>

      <div className="od">
        <div>
          <section className="card box">
            <h2><Icon name="key-round" />Niveaux de stock <small className="muted-s">· {total} clé(s) disponible(s) · alerte à {threshold} ou moins</small></h2>
            <div className="tbl-w">
              <table className="tbl">
                <thead><tr><th>Produit</th><th>Option</th><th>Disponibles</th><th>Livrées</th><th /></tr></thead>
                <tbody>
                  {rows.map((r) => (
                    <tr key={`${r.product.slug}|${r.option.id}`} className={r.product.active === false ? "off" : ""}>
                      <td><b>{r.product.name.fr}</b></td>
                      <td className="muted">{r.option.label.fr}</td>
                      <td>
                        <span className={`stt ${r.available === 0 ? "s-failed" : r.low ? "s-awaiting_transfer" : "s-delivered"}`}>{r.available}</span>
                      </td>
                      <td className="muted">{r.used}</td>
                      <td><Link className="row-link" href={`?p=${encodeURIComponent(`${r.product.slug}|${r.option.id}`)}#liste`}>Voir</Link></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </section>

          <section className="card box" id="liste">
            <h2><Icon name="eye" />Clés disponibles {filter && <><small className="muted-s">· {label(...(filter.split("|") as [string, string]))}</small><Link href="?#liste" className="row-link" style={{ fontSize: 13, marginInlineStart: "auto" }}>Tout afficher</Link></>}</h2>
            {available.length === 0 ? <p className="muted-s">Aucune clé disponible{filter ? " pour ce produit" : ""}.</p> : (
              <table className="tbl">
                <thead><tr><th>Clé</th><th>Produit</th><th>Ajoutée le</th><th /></tr></thead>
                <tbody>
                  {available.map((k) => (
                    <tr key={k.id}>
                      <td><code className="mono">{mask(k.key)}</code></td>
                      <td className="muted">{label(k.slug, k.option)}</td>
                      <td className="muted">{new Date(k.addedAt).toLocaleDateString("fr-FR")}</td>
                      <td>
                        <form action={deleteKeyAction}><input type="hidden" name="id" value={k.id} /><button className="rmb" aria-label="Retirer cette clé du stock" title="Retirer du stock"><Icon name="trash" /></button></form>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </section>
        </div>

        <aside>
          <section className="card box">
            <h2><Icon name="plus" />Ajouter des clés</h2>
            <AddKeysForm targets={rows.map((r) => ({ value: `${r.product.slug}|${r.option.id}`, label: `${r.product.name.fr} · ${r.option.label.fr}` }))} preset={filter} />
          </section>
          <section className="card box help">
            <h2><Icon name="info" />Comment ça marche</h2>
            <ol>
              <li>Collez vos clés ici, une par ligne, pour le bon produit et la bonne option.</li>
              <li>Dès qu&apos;un paiement CIB/Edahabia est confirmé (ou qu&apos;un CCP est validé), les clés sont attribuées et le client les voit sur sa page de commande.</li>
              <li>S&apos;il manque des clés, la commande reste « Payée · à livrer » et apparaît dans vos commandes à traiter.</li>
            </ol>
          </section>
        </aside>
      </div>
    </>
  );
}
