"use server";

import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { checkPassword, createSession, destroySession, requireAdmin } from "@/lib/admin-auth";
import { getOrder, updateOrder, type OrderStatus } from "@/lib/orders";
import { getAllProducts, saveProduct, setProductActive } from "@/lib/products";
import { categories, gradients, type CategoryId, type Product } from "@/lib/catalog";
import { iconNames, type IconName } from "@/components/Icon";
import { saveImage, type ImageKind } from "@/lib/images";
import { logAction } from "@/lib/audit";
import { addKeys, deleteKey } from "@/lib/keys";
import { deliverFromStock } from "@/lib/fulfil";
import { deletePromo, listPromos, savePromo, type Promo } from "@/lib/promos";
import { saveSettings } from "@/lib/settings";
import { backgrounds, deleteSlide, getAllSlides, moveSlide, newSlideId, saveSlide, type Slide } from "@/lib/slides";

const text = (form: FormData, k: string, max: number) => String(form.get(k) ?? "").trim().slice(0, max);
const num = (form: FormData, k: string) => Number(String(form.get(k) ?? "").replace(",", "."));
/** Seules les images envoyées via l'admin sont acceptées dans les fiches. */
const isUpload = (url: string, kind: ImageKind) => new RegExp(`^/media/${kind}/[a-f0-9]{16}\\.(jpg|png|webp)$`).test(url);

/* ---------------- Connexion ---------------- */
export async function login(_: { error?: string } | undefined, form: FormData) {
  const ok = checkPassword(String(form.get("password") ?? ""));
  if (!ok) {
    await new Promise((r) => setTimeout(r, 800)); // freine les essais en rafale
    await logAction("Échec de connexion");
    return { error: "Mot de passe incorrect." };
  }
  await createSession();
  await logAction("Connexion");
  redirect("/admin");
}

export async function logout() {
  await destroySession();
  redirect("/admin/login");
}

/* ---------------- Images ---------------- */
export async function uploadImage(kind: ImageKind, form: FormData) {
  await requireAdmin();
  const file = form.get("file");
  if (!(file instanceof File) || !file.size) return { error: "Aucun fichier reçu." };
  const res = await saveImage(file, kind);
  if (!("error" in res)) await logAction(`Image envoyée (${res.width}×${res.height})`, res.url);
  return res;
}

/* ---------------- Commandes ---------------- */
const statuses: OrderStatus[] = ["pending", "awaiting_transfer", "paid", "delivered", "failed", "cancelled"];

export async function setOrderStatus(form: FormData) {
  await requireAdmin();
  const id = String(form.get("id") ?? "");
  const status = String(form.get("status") ?? "") as OrderStatus;
  if (!statuses.includes(status)) return;
  await updateOrder(id, { status });
  await logAction(`Statut changé : ${status}`, id);
  // Paiement CCP validé : livraison automatique si le stock le permet
  if (status === "paid") await deliverFromStock(id);
  revalidatePath("/admin", "layout");
}

export async function saveNote(form: FormData) {
  await requireAdmin();
  const id = String(form.get("id") ?? "");
  await updateOrder(id, { note: String(form.get("note") ?? "").slice(0, 2000) });
  await logAction("Note modifiée", id);
  revalidatePath(`/admin/commandes/${id}`);
}

/** Enregistre les clés saisies (une par ligne) et passe la commande en « livrée ». */
export async function deliverOrder(_: { error?: string; ok?: boolean } | undefined, form: FormData) {
  await requireAdmin();
  const order = await getOrder(String(form.get("id") ?? ""));
  if (!order) return { error: "Commande introuvable." };
  if (order.status !== "paid" && order.status !== "delivered") return { error: "La commande doit d'abord être payée." };

  const lines = order.lines.map((l, i) => ({
    ...l,
    keys: String(form.get(`keys_${i}`) ?? "").split("\n").map((k) => k.trim()).filter(Boolean).slice(0, 50),
  }));
  const missing = lines.findIndex((l) => l.keys.length < l.qty);
  if (missing >= 0) return { error: `Il manque des clés pour « ${lines[missing].name} » (${lines[missing].qty} attendue(s)).` };

  await updateOrder(order.id, { lines, status: "delivered" });
  await logAction("Commande livrée (clés saisies)", order.id);
  revalidatePath("/admin", "layout");
  revalidatePath(`/${order.locale}/commande/${order.id}`);
  return { ok: true };
}

export async function deliverFromStockAction(form: FormData) {
  await requireAdmin();
  const id = String(form.get("id") ?? "");
  const ok = await deliverFromStock(id, true);
  revalidatePath("/admin", "layout");
  redirect(`/admin/commandes/${id}?stock=${ok ? "ok" : "manque"}`);
}

/* ---------------- Produits ---------------- */
export async function saveProductAction(_: { error?: string } | undefined, form: FormData) {
  await requireAdmin();
  const prevSlug = text(form, "prevSlug", 80) || undefined;
  const slug = text(form, "slug", 80).toLowerCase();
  if (!/^[a-z0-9]+(-[a-z0-9]+)*$/.test(slug)) return { error: "Adresse (slug) invalide : lettres minuscules, chiffres et tirets uniquement." };

  const all = await getAllProducts();
  if (slug !== prevSlug && all.some((p) => p.slug === slug)) return { error: "Un autre produit utilise déjà cette adresse." };

  const category = text(form, "category", 10) as CategoryId;
  const icon = text(form, "icon", 40) as IconName;
  const gradient = text(form, "gradient", 20);
  if (!categories.some((c) => c.id === category)) return { error: "Catégorie invalide." };
  if (!iconNames.includes(icon)) return { error: "Icône invalide." };
  if (!(gradients as readonly string[]).includes(gradient)) return { error: "Couleur invalide." };

  const nameFr = text(form, "name_fr", 100), nameAr = text(form, "name_ar", 100);
  if (!nameFr || !nameAr) return { error: "Le nom est obligatoire en français et en arabe." };

  const ids = form.getAll("opt_id").map(String), fr = form.getAll("opt_fr").map(String), ar = form.getAll("opt_ar").map(String), enL = form.getAll("opt_en").map(String), prices = form.getAll("opt_price").map(String);
  const options = ids.map((id, i) => ({
    id: (id.trim() || `o${i + 1}`).toLowerCase().replace(/[^a-z0-9-]/g, "").slice(0, 20) || `o${i + 1}`,
    label: { fr: (fr[i] ?? "").trim().slice(0, 30), ar: (ar[i] ?? "").trim().slice(0, 30), en: (enL[i] ?? "").trim().slice(0, 30) || undefined },
    price: Math.round(Number(prices[i])),
  })).filter((o) => o.label.fr || o.label.ar);
  if (!options.length || options.length > 8) return { error: "Ajoutez entre 1 et 8 options." };
  if (options.some((o) => !o.label.fr || !o.label.ar)) return { error: "Chaque option doit avoir un libellé FR et AR." };
  if (options.some((o) => !Number.isFinite(o.price) || o.price < 0 || o.price > 10_000_000)) return { error: "Prix invalide (0 = sur devis)." };
  if (new Set(options.map((o) => o.id)).size !== options.length) return { error: "Deux options ont le même identifiant." };

  const dealPercent = num(form, "deal_percent");
  const dealSold = num(form, "deal_sold");
  if (dealPercent && (dealPercent < 1 || dealPercent > 90)) return { error: "La remise doit être entre 1 et 90 %." };

  // Photos : la première est l'image principale, les suivantes la galerie
  const photos = form.getAll("photos").map(String).filter((u) => isUpload(u, "product")).slice(0, 5);
  const [image, ...gallery] = photos;

  const product: Product = {
    slug,
    category,
    icon,
    gradient,
    name: { fr: nameFr, ar: nameAr, en: text(form, "name_en", 100) || undefined },
    subtitle: { fr: text(form, "subtitle_fr", 80), ar: text(form, "subtitle_ar", 80), en: text(form, "subtitle_en", 80) || undefined },
    description: { fr: text(form, "desc_fr", 3000), ar: text(form, "desc_ar", 3000), en: text(form, "desc_en", 3000) || undefined },
    options,
    rating: Math.min(5, Math.max(0, num(form, "rating") || 5)),
    reviews: Math.max(0, Math.round(num(form, "reviews") || 0)),
    ...(dealPercent ? { deal: { percent: Math.round(dealPercent), sold: Math.min(1, Math.max(0, (dealSold || 0) / 100)) } } : {}),
    featured: form.get("featured") === "on",
    active: form.get("active") === "on",
    ...(image ? { image } : {}),
    ...(gallery.length ? { gallery } : {}),
  };

  await saveProduct(product, prevSlug);
  await logAction(prevSlug ? "Produit modifié" : "Produit créé", slug);
  revalidatePath("/", "layout");
  redirect("/admin/produits");
}

export async function toggleProduct(form: FormData) {
  await requireAdmin();
  const slug = String(form.get("slug") ?? ""), active = form.get("active") === "1";
  await setProductActive(slug, active);
  await logAction(active ? "Produit publié" : "Produit masqué", slug);
  revalidatePath("/", "layout");
}

/* ---------------- Stock de clés ---------------- */
export async function addKeysAction(_: { error?: string; ok?: string; at?: number } | undefined, form: FormData) {
  await requireAdmin();
  const [slug, option] = String(form.get("target") ?? "").split("|");
  const p = (await getAllProducts()).find((x) => x.slug === slug);
  if (!p || !p.options.some((o) => o.id === option)) return { error: "Choisissez un produit et une option." };
  const lines = String(form.get("keys") ?? "").split("\n").slice(0, 1000);
  if (!lines.some((l) => l.trim())) return { error: "Collez au moins une clé (une par ligne)." };
  const { added, duplicates } = await addKeys(slug, option, lines);
  await logAction(`${added} clé(s) ajoutée(s) au stock`, `${slug} · ${option}`);
  revalidatePath("/admin", "layout");
  return { ok: `${added} clé(s) ajoutée(s)${duplicates ? `, ${duplicates} doublon(s) ignoré(s)` : ""}.`, at: Date.now() };
}

export async function deleteKeyAction(form: FormData) {
  await requireAdmin();
  await deleteKey(String(form.get("id") ?? ""));
  await logAction("Clé retirée du stock");
  revalidatePath("/admin/stock");
}

/* ---------------- Codes promo ---------------- */
export async function savePromoAction(_: { error?: string } | undefined, form: FormData) {
  await requireAdmin();
  const code = text(form, "code", 30).toUpperCase().replace(/\s/g, "");
  const previous = text(form, "previous", 30) || undefined;
  if (!/^[A-Z0-9_-]{3,30}$/.test(code)) return { error: "Code invalide : 3 à 30 caractères (lettres, chiffres, - et _)." };
  if (code !== previous && (await listPromos()).some((p) => p.code === code)) return { error: "Ce code existe déjà." };
  const type = form.get("type") === "fixed" ? "fixed" : "percent";
  const value = Math.round(num(form, "value"));
  if (type === "percent" && (value < 1 || value > 90)) return { error: "Le pourcentage doit être entre 1 et 90." };
  if (type === "fixed" && (value < 10 || value > 1_000_000)) return { error: "Montant invalide." };
  const expires = text(form, "expires", 10);
  if (expires && !/^\d{4}-\d{2}-\d{2}$/.test(expires)) return { error: "Date invalide." };
  const old = (await listPromos()).find((p) => p.code === previous);
  const promo: Promo = {
    code, type, value,
    minTotal: Math.max(0, Math.round(num(form, "minTotal") || 0)),
    expires: expires || undefined,
    maxUses: Math.max(0, Math.round(num(form, "maxUses") || 0)),
    uses: old?.uses ?? 0,
    active: form.get("active") === "on",
  };
  await savePromo(promo, previous);
  await logAction(previous ? "Code promo modifié" : "Code promo créé", code);
  redirect("/admin/promos");
}

export async function deletePromoAction(form: FormData) {
  await requireAdmin();
  const code = String(form.get("code") ?? "");
  await deletePromo(code);
  await logAction("Code promo supprimé", code);
  revalidatePath("/admin/promos");
}

/* ---------------- Bannières (carrousel) ---------------- */
const L = (form: FormData, k: string, max: number) => ({ fr: text(form, `${k}_fr`, max), ar: text(form, `${k}_ar`, max), en: text(form, `${k}_en`, max) || undefined });

export async function saveSlideAction(_: { error?: string } | undefined, form: FormData) {
  await requireAdmin();
  const id = text(form, "id", 20) || newSlideId();
  const title = L(form, "title", 80);
  if (!title.fr || !title.ar) return { error: "Le titre est obligatoire en français et en arabe." };
  const href = text(form, "href", 200);
  if (!/^(\/[\w\-/]*|\?[\w=&#-]*)$/.test(href)) return { error: "Lien invalide : utilisez une adresse interne comme /produit/windows-11-pro ou ?cat=str#catalogue." };
  const image = text(form, "image", 200);
  if (image && !isUpload(image, "banner")) return { error: "Image invalide : envoyez-la depuis le formulaire." };
  const bg = backgrounds.find((b) => b.id === form.get("bg"))?.css ?? backgrounds[0].css;
  const slugs = new Set((await getAllProducts()).map((p) => p.slug));
  const products = form.getAll("products").map(String).filter((s) => slugs.has(s)).slice(0, 3);
  const priceFrom = text(form, "priceFrom", 80);
  const slide: Slide = {
    id, bg, active: form.get("active") === "on",
    ...(image ? { image } : {}),
    tag: L(form, "tag", 40), title, highlight: L(form, "highlight", 40), text: L(form, "text", 200), cta: L(form, "cta", 30),
    href, products, ...(priceFrom && slugs.has(priceFrom) ? { priceFrom } : {}),
  };
  const exists = (await getAllSlides()).some((s) => s.id === id);
  await saveSlide(slide);
  await logAction(exists ? "Bannière modifiée" : "Bannière créée", title.fr);
  revalidatePath("/", "layout");
  redirect("/admin/bannieres");
}

export async function slideAction(form: FormData) {
  await requireAdmin();
  const id = String(form.get("id") ?? ""), op = String(form.get("op") ?? "");
  if (op === "up" || op === "down") await moveSlide(id, op === "up" ? -1 : 1);
  if (op === "delete") { await deleteSlide(id); await logAction("Bannière supprimée", id); }
  if (op === "toggle") {
    const s = (await getAllSlides()).find((x) => x.id === id);
    if (s) { await saveSlide({ ...s, active: !s.active }); await logAction(s.active ? "Bannière masquée" : "Bannière publiée", t0(s)); }
  }
  revalidatePath("/", "layout");
}
const t0 = (s: Slide) => s.title.fr;

/* ---------------- Paramètres ---------------- */
export async function saveSettingsAction(_: { error?: string; ok?: boolean } | undefined, form: FormData) {
  await requireAdmin();
  const whatsapp = text(form, "whatsapp", 20).replace(/\D/g, "");
  if (whatsapp && !/^213[5-7]\d{8}$/.test(whatsapp)) return { error: "Numéro WhatsApp invalide : format international sans + (ex. 213555123456)." };
  const email = text(form, "email", 120);
  if (email && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) return { error: "Adresse e-mail invalide." };
  const url = (k: string) => { const v = text(form, k, 200); return v && /^https:\/\/[^\s]+$/.test(v) ? v : ""; };
  const lines = (k: string) => String(form.get(k) ?? "").split("\n").map((l) => l.trim()).filter(Boolean).slice(0, 6).map((l) => l.slice(0, 120));
  await saveSettings({
    whatsapp, email, phone: text(form, "phone", 30),
    facebook: url("facebook"), instagram: url("instagram"), tiktok: url("tiktok"),
    announce: { fr: lines("announce_fr"), ar: lines("announce_ar"), en: lines("announce_en") },
    autoDelivery: form.get("autoDelivery") === "on",
    lowStock: Math.max(0, Math.min(100, Math.round(num(form, "lowStock") || 0))),
    maintenanceMode: form.get("maintenanceMode") === "on",
  });
  await logAction("Paramètres modifiés");
  revalidatePath("/", "layout");
  return { ok: true };
}
