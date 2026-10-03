import Link from "next/link";
import { notFound } from "next/navigation";
import { hasLocale, t } from "@/lib/i18n";
import { getDictionary } from "@/lib/dictionaries";
import { catalogue, categories, deals } from "@/lib/catalog";
import { getProducts } from "@/lib/products";
import { getActiveSlides } from "@/lib/slides";
import { Icon } from "@/components/Icon";
import { HeroCarousel } from "@/components/home/HeroCarousel";
import { Brands, Catalogue, Deals, Newsletter, Services, TrustBar } from "@/components/home/Sections";

export default async function Home({ params, searchParams }: PageProps<"/[lang]">) {
  const { lang } = await params;
  if (!hasLocale(lang)) notFound();
  const sp = await searchParams;

  const cat = typeof sp.cat === "string" ? sp.cat : undefined;
  const q = typeof sp.q === "string" ? sp.q : undefined;
  const d = getDictionary(lang);
  const [products, slides] = await Promise.all([getProducts(), getActiveSlides()]);


  return (
    <>
      <section className="top">
        <nav className="side" aria-label={d.side.title}>
          <h3><Icon name="layout-grid" />{d.side.title}</h3>
          {categories.map((c, i) => (
            <Link key={c.id} href={c.id === "svc" ? `/${lang}/produit/site-vitrine` : `/${lang}?cat=${c.id}#catalogue`} className="in" style={{ "--d": `${0.2 + i * 0.05}s` } as React.CSSProperties}>
              <Icon name={c.icon} />{t(c.name, lang)}<Icon name="chevron-right" className="ch flip-x" />
            </Link>
          ))}
        </nav>
        {slides.length > 0 && <HeroCarousel slides={slides} />}
        <div className="mini">
          <Link href={`/${lang}?cat=str#catalogue`} className="mc m1 in" style={{ "--d": ".3s" } as React.CSSProperties}>
            <div><small>{d.home.subsTitle}</small><h4>{d.home.subsText}</h4></div>
            <span className="lk">{d.home.subsFrom}<Icon name="chevron-right" className="flip-x" /></span>
            <span className="ico"><Icon name="popcorn" /></span>
          </Link>
          <Link href={`/${lang}/produit/site-vitrine`} className="mc m2 in" style={{ "--d": ".42s" } as React.CSSProperties}>
            <div><small>{d.home.bizTitle}</small><h4>{d.home.bizText}</h4></div>
            <span className="lk">{d.home.bizCta}<Icon name="chevron-right" className="flip-x" /></span>
            <span className="ico"><Icon name="globe" /></span>
          </Link>
        </div>
      </section>

      <TrustBar />
      <Deals items={deals(products)} />
      <Catalogue items={catalogue(products)} initialCat={cat} query={q} key={`${cat}-${q}`} />
      <Services items={products.filter((p) => p.category === "svc")} />
      <Brands />
      <Newsletter />
    </>
  );
}
