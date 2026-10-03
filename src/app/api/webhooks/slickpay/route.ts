import { NextResponse } from "next/server";
import { getOrders, updateOrder } from "@/lib/orders";
import { deliverFromStock } from "@/lib/fulfil";

/*
 * Webhook Slick-Pay : notification asynchrone lors du paiement SATIM réussi.
 * Endpoint : POST /api/webhooks/slickpay
 */
export async function POST(req: Request) {
  try {
    const body = await req.json();
    const invoice = body.invoice ?? body.data ?? body;
    const invoiceId = invoice.id;
    const isCompleted = invoice.completed === 1 || String(invoice.status).toLowerCase().includes("pay");

    if (!invoiceId) {
      return NextResponse.json({ error: "missing_invoice_id" }, { status: 400 });
    }

    if (!isCompleted) {
      return NextResponse.json({ received: true, status: "pending" });
    }

    // Retrouver la commande associée par son slickpayInvoiceId
    const allOrders = await getOrders();
    const order = allOrders.find((o) => String(o.slickpayInvoiceId) === String(invoiceId));

    if (order && order.status !== "paid" && order.status !== "delivered") {
      await updateOrder(order.id, { status: "paid" });
      await deliverFromStock(order.id);
    }

    return NextResponse.json({ success: true, orderId: order?.id });
  } catch (e) {
    console.error("Webhook SlickPay error:", e);
    return NextResponse.json({ error: "internal_error" }, { status: 500 });
  }
}
