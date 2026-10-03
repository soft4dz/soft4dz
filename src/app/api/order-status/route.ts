import { NextResponse } from "next/server";
import { getOrder, updateOrder } from "@/lib/orders";
import { getSlickPayInvoice, slickpayEnabled } from "@/lib/slickpay";
import { deliverFromStock } from "@/lib/fulfil";

/*
 * Statut d'une commande pour l'assistant du site et le suivi en direct du client.
 * Ne renvoie aucune donnée personnelle sensible (ni nom, ni e-mail, ni téléphone, ni clé brute).
 */
const hits = new Map<string, number[]>();
const LIMIT = 90, WINDOW = 60_000;

export async function GET(req: Request) {
  const ip = req.headers.get("x-forwarded-for")?.split(",")[0].trim() || "local";
  const now = Date.now();
  const recent = (hits.get(ip) ?? []).filter((t) => now - t < WINDOW);
  if (recent.length >= LIMIT) return NextResponse.json({ error: "too_many" }, { status: 429 });
  recent.push(now);
  hits.set(ip, recent);
  if (hits.size > 5000) hits.clear();

  const id = (new URL(req.url).searchParams.get("id") ?? "").trim().toUpperCase();
  if (!/^SD-[A-Z0-9]{8}$/.test(id)) return NextResponse.json({ found: false });
  let o = await getOrder(id);
  if (!o) return NextResponse.json({ found: false });

  // Si la commande est en attente avec Slick-Pay, on revérifie l'état de la facture SATIM
  if (o.status === "pending" && o.slickpayInvoiceId && slickpayEnabled()) {
    try {
      const inv = await getSlickPayInvoice(o.slickpayInvoiceId);
      const isPaid = inv.completed === 1 || inv.data?.completed === 1;
      if (isPaid) {
        await updateOrder(o.id, { status: "paid" });
        await deliverFromStock(o.id);
        const fresh = await getOrder(o.id);
        if (fresh) o = fresh;
      }
    } catch (e) {
      console.error("SlickPay polling verification error:", e);
    }
  }

  return NextResponse.json({
    found: true,
    id: o.id,
    status: o.status,
    delivered: o.status === "delivered",
    paid: o.status === "paid" || o.status === "delivered",
    date: o.createdAt,
    locale: o.locale,
    items: o.lines.map((l) => `${l.name} × ${l.qty}`),
  });
}

