import Link from "next/link";
import { getAllProducts } from "@/lib/products";
import { categories, finalPrice } from "@/lib/catalog";
import { formatPrice } from "@/lib/format";
import { Icon } from "@/components/Icon";
import { toggleProduct } from "../../actions";

export const metadata = { title: "Produits" };

export default async function ProductsAdmin() {
  const products = await getAllProducts();
  return (
    <>
      <div className="adm-h">
        <h1>Produits</h1>
        <Link className="btn b-pri" href="/admin/produits/nouveau"><Icon name="plus" />Nouveau produit</Link>
      </div>
      <div className="card tbl-w">
        <table className="tbl">
          <thead><tr><th /><th>Produit</th><th>Catégorie</th><th>Prix</th><th>Remise</th><th>Boutique</th><th /></tr></thead>
          <tbody>
            {products.map((p) => {
              const prices = p.options.map((o) => finalPrice(p, o.id)).filter(Boolean);
              const on = p.active !== false;
              return (
                <tr key={p.slug} className={on ? "" : "off"}>
                  <td>{p.image
                    // eslint-disable-next-line @next/next/no-img-element
                    ? <img className="pic" src={p.image} alt="" style={{ objectFit: "cover" }} />
                    : <span className={`pic ${p.gradient}`} title="Pas de photo : affiche générée"><Icon name={p.icon} /></span>}</td>
                  <td>
                    <Link className="row-link" href={`/admin/produits/${p.slug}`}>{p.name.fr}</Link>
                    <div className="muted" dir="rtl" style={{ textAlign: "start" }}>{p.name.ar}</div>
                  </td>
                  <td className="muted">{categories.find((c) => c.id === p.category)?.name.fr}</td>
                  <td>{prices.length ? (Math.min(...prices) === Math.max(...prices) ? formatPrice(prices[0], "fr") : `${formatPrice(Math.min(...prices), "fr")} – ${formatPrice(Math.max(...prices), "fr")}`) : "Sur devis"}
                    <div className="muted">{p.options.length} option(s)</div></td>
                  <td>{p.deal ? <span className="stt s-awaiting_transfer">-{p.deal.percent}%</span> : <span className="muted">—</span>}{p.featured && <div className="muted">★ En avant</div>}</td>
                  <td>
                    <form action={toggleProduct}>
                      <input type="hidden" name="slug" value={p.slug} />
                      <input type="hidden" name="active" value={on ? "0" : "1"} />
                      <button className={`stt ${on ? "s-delivered" : "s-cancelled"}`} title={on ? "Cliquer pour masquer" : "Cliquer pour publier"}>{on ? "En ligne" : "Masqué"}</button>
                    </form>
                  </td>
                  <td><Link href={`/admin/produits/${p.slug}`} aria-label={`Modifier ${p.name.fr}`} className="rmb"><Icon name="pencil" /></Link></td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </>
  );
}
