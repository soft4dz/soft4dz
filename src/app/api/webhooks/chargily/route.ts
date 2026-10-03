import { NextResponse } from "next/server";
import { verifySignature } from "@/lib/chargily";
import { getOrder, updateOrder } from "@/lib/orders";
import { deliverFromStock } from "@/lib/fulfil";

type Event = { type?: string; data?: { id?: string; metadata?: { order_id?: string } } };

/** Chargily prévient ici quand un paiement réussit ou échoue. */
export async function POST(req: Request) {
  const raw = await req.text();
  if (!verifySignature(raw, req.headers.get("signature"))) {
    return NextResponse.json({ error: "invalid_signature" }, { status: 403 });
  }
  let event: Event;
  try { event = JSON.parse(raw); } catch { return NextResponse.json({ error: "invalid_json" }, { status: 400 }); }

  const orderId = event.data?.metadata?.order_id;
  const order = orderId ? await getOrder(orderId) : undefined;
  // On vérifie que l'événement correspond bien au paiement créé pour cette commande
  if (!order || order.chargilyCheckoutId !== event.data?.id) return NextResponse.json({ ok: true });

  if (event.type === "checkout.paid") {
    if (order.status === "pending") await updateOrder(order.id, { status: "paid" });
    await deliverFromStock(order.id);
  }
  else if ((event.type === "checkout.failed" || event.type === "checkout.canceled") && order.status === "pending") await updateOrder(order.id, { status: "failed" });

  return NextResponse.json({ ok: true });
}
