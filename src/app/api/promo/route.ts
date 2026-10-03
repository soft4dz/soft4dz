import { NextResponse } from "next/server";
import { finalPrice } from "@/lib/catalog";
import { getProducts } from "@/lib/products";
import { checkPromo } from "@/lib/promos";

/** Vérifie un code promo pour le panier donné (le sous-total est recalculé ici). */
export async function POST(req: Request) {
  let body: { code?: string; lines?: { slug?: string; option?: string; qty?: number }[] };
  try { body = await req.json(); } catch { return NextResponse.json({ ok: false, reason: "unknown" }, { status: 400 }); }
  const products = await getProducts();
  const subtotal = (Array.isArray(body.lines) ? body.lines.slice(0, 30) : []).reduce((s, l) => {
    const p = products.find((x) => x.slug === l.slug);
    const qty = Number(l.qty);
    if (!p || !p.options.some((o) => o.id === l.option) || !Number.isInteger(qty) || qty < 1 || qty > 10) return s;
    return s + finalPrice(p, String(l.option)) * qty;
  }, 0);
  return NextResponse.json(await checkPromo(String(body.code ?? "").slice(0, 40), subtotal));
}
