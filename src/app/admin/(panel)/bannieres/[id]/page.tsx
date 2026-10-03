import Link from "next/link";
import { notFound } from "next/navigation";
import { backgrounds, getAllSlides, type Slide } from "@/lib/slides";
import { getAllProducts } from "@/lib/products";
import { imageRules } from "@/lib/images";
import { Icon } from "@/components/Icon";
import { SlideForm } from "./SlideForm";

export const metadata = { title: "Bannière" };

const E = { fr: "", ar: "", en: "" };

export default async function SlideEdit({ params }: PageProps<"/admin/bannieres/[id]">) {
  const { id } = await params;
  const isNew = id === "nouveau";
  const [slides, products] = await Promise.all([getAllSlides(), getAllProducts()]);
  const slide: Slide | undefined = isNew
    ? { id: "", active: true, bg: backgrounds[0].css, tag: E, title: E, highlight: E, text: E, cta: { fr: "Découvrir", ar: "اكتشف", en: "Discover" }, href: "/", products: [] }
    : slides.find((s) => s.id === id);
  if (!slide) notFound();
  return (
    <>
      <div className="adm-h">
        <Link className="back" href="/admin/bannieres"><Icon name="arrow-left" />Bannières</Link>
        <h1>{isNew ? "Nouvelle bannière" : slide.title.fr}</h1>
        <a className="btn b-out" href="/fr" target="_blank" rel="noopener"><Icon name="external-link" />Voir l&apos;accueil</a>
      </div>
      <SlideForm
        slide={slide}
        rule={imageRules.banner}
        backgrounds={backgrounds.map((b) => ({ id: b.id, label: b.label, css: b.css }))}
        products={products.map((p) => ({ slug: p.slug, name: p.name.fr }))}
      />
    </>
  );
}
