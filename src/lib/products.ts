import "server-only";
import { defaultProducts, withEnglish, type Product } from "./catalog";
import { readJson, updateJson } from "./store";

const FILE = "products.json";
const seed = () => structuredClone(defaultProducts);

/** Tous les produits, y compris masqués (admin). */
export const getAllProducts = async () => (await readJson<Product[]>(FILE, seed)).map(withEnglish);

/** Produits visibles en boutique. */
export const getProducts = async () => (await getAllProducts()).filter((p) => p.active !== false);

export const getProductBySlug = async (slug: string) =>
  (await getAllProducts()).find((p) => p.slug === slug);

/** Crée ou remplace un produit. `previousSlug` permet de renommer l'URL. */
export function saveProduct(product: Product, previousSlug?: string) {
  return updateJson<Product[]>(FILE, seed, (list) => {
    const key = previousSlug ?? product.slug;
    const i = list.findIndex((p) => p.slug === key);
    if (i < 0) return [...list, product];
    const next = [...list];
    next[i] = product;
    return next;
  });
}

export function setProductActive(slug: string, active: boolean) {
  return updateJson<Product[]>(FILE, seed, (list) => list.map((p) => (p.slug === slug ? { ...p, active } : p)));
}
