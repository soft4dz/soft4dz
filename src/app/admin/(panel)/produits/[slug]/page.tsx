import Link from "next/link";
import { notFound } from "next/navigation";
import { getProductBySlug } from "@/lib/products";
import type { Product } from "@/lib/catalog";
import { Icon } from "@/components/Icon";
import { ProductForm } from "./ProductForm";
import { imageRules } from "@/lib/images";

export const metadata = { title: "Produit" };

const blank: Product = {
  slug: "",
  category: "win",
  icon: "monitor",
  gradient: "g-win",
  name: { fr: "", ar: "" },
  subtitle: { fr: "", ar: "" },
  description: { fr: "", ar: "" },
  options: [{ id: "std", label: { fr: "", ar: "" }, price: 0 }],
  rating: 5,
  reviews: 0,
  active: true,
};

export default async function EditProduct({ params }: PageProps<"/admin/produits/[slug]">) {
  const { slug } = await params;
  const isNew = slug === "nouveau";
  const product = isNew ? blank : await getProductBySlug(slug);
  if (!product) notFound();
  return (
    <>
      <div className="adm-h">
        <Link className="back" href="/admin/produits"><Icon name="arrow-left" />Produits</Link>
        <h1>{isNew ? "Nouveau produit" : product.name.fr}</h1>
        {!isNew && <a className="btn b-out" href={`/fr/produit/${product.slug}`} target="_blank" rel="noopener"><Icon name="external-link" />Voir en boutique</a>}
      </div>
      <ProductForm product={product} isNew={isNew} rule={imageRules.product} />
    </>
  );
}
