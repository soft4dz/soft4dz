import Link from "next/link";
import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { hasLocale, t } from "@/lib/i18n";
import { getDictionary } from "@/lib/dictionaries";
import { categories, related } from "@/lib/catalog";
import { getProductBySlug, getProducts } from "@/lib/products";
import { Icon } from "@/components/Icon";
import { ProductBuy } from "@/components/product/ProductBuy";
import { ProductCard } from "@/components/ProductCard";

export async function generateMetadata({ params }: PageProps<"/[lang]/produit/[slug]">): Promise<Metadata> {
  const { lang, slug } = await params;
  const p = await getProductBySlug(slug);
  if (!p || !hasLocale(lang)) return {};
  return { title: t(p.name, lang), description: t(p.description, lang) };
}

export default async function ProductPage({ params }: PageProps<"/[lang]/produit/[slug]">) {
  const { lang, slug } = await params;
  const p = await getProductBySlug(slug);
  if (!hasLocale(lang) || !p || p.active === false) notFound();
  const d = getDictionary(lang);
  const service = p.category === "svc";

  return (
    <>
      <nav className="crumb" aria-label="Fil d'Ariane">
        <Link href={`/${lang}`}>{d.product.home}</Link><Icon name="chevron-right" className="flip-x" />
        <Link href={`/${lang}?cat=${p.category}#catalogue`}>{t(categories.find((c) => c.id === p.category)!.name, lang)}</Link><Icon name="chevron-right" className="flip-x" />
        <span>{t(p.name, lang)}</span>
      </nav>

      <ProductBuy p={p} />

      <section className="card desc">
        <h2>{d.product.description}</h2>
        <p>{t(p.description, lang)}</p>
        {!service && (
          <>
            <h2 style={{ marginTop: 20 }}>{d.product.activation}</h2>
            <div className="steps4">
              {d.product.steps.map((s, i) => <div key={s}><b>{i + 1}</b>{s}</div>)}
            </div>
          </>
        )}
      </section>

      {!service && (
        <>
          <h2 className="sec-t">{d.product.related}</h2>
          <div className="pr">
            {related(await getProducts(), p).map((r) => <ProductCard key={r.slug} p={r} />)}
          </div>
        </>
      )}
    </>
  );
}
