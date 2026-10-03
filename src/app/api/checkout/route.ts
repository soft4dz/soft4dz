import { NextResponse } from "next/server";
import { finalPrice } from "@/lib/catalog";
import { getProducts } from "@/lib/products";
import { hasLocale, t } from "@/lib/i18n";
import { paymentMethods, rules } from "@/lib/validation";
import { createOrder, updateOrder, type OrderLine, type PaymentMethod } from "@/lib/orders";
import { chargilyEnabled, createCheckout } from "@/lib/chargily";
import { createSlickPayInvoice, slickpayEnabled } from "@/lib/slickpay";
import { checkPromo, countPromoUse } from "@/lib/promos";
import { deliverFromStock } from "@/lib/fulfil";

type Body = {
  locale?: string;
  customer?: { name?: string; phone?: string; email?: string };
  method?: string;
  lines?: { slug?: string; option?: string; qty?: number }[];
  promo?: string;
};

const bad = (error: string) => NextResponse.json({ error }, { status: 400 });

export async function POST(req: Request) {
  let body: Body;
  try { body = await req.json(); } catch { return bad("invalid_json"); }

  const locale = body.locale && hasLocale(body.locale) ? body.locale : "fr";
  const c = body.customer ?? {};
  const name = String(c.name ?? "").trim(), phone = String(c.phone ?? "").trim(), email = String(c.email ?? "").trim();
  if (!rules.name(name) || !rules.phone(phone) || !rules.email(email)) return bad("invalid_customer");
  if (!paymentMethods.includes(body.method as PaymentMethod)) return bad("invalid_method");
  const method = body.method as PaymentMethod;

  // Les prix sont TOUJOURS recalculés ici : on ne fait jamais confiance au navigateur.
  const raw = Array.isArray(body.lines) ? body.lines.slice(0, 30) : [];
  const products = await getProducts();
  const getProduct = (slug: string) => products.find((p) => p.slug === slug);
  const lines: OrderLine[] = [];
  for (const l of raw) {
    const p = getProduct(String(l.slug ?? ""));
    const opt = p?.options.find((o) => o.id === l.option);
    const qty = Number(l.qty);
    if (!p || !opt || !Number.isInteger(qty) || qty < 1 || qty > 10) return bad("invalid_line");
    const unitPrice = finalPrice(p, opt.id);
    if (unitPrice <= 0) return bad("quote_only"); // produit sur devis : pas de paiement en ligne
    lines.push({ slug: p.slug, option: opt.id, qty, unitPrice, name: t(p.name, locale), optionLabel: t(opt.label, locale) });
  }
  if (!lines.length) return bad("empty_cart");
  const subtotal = lines.reduce((s, l) => s + l.unitPrice * l.qty, 0);
  // Code promo : revérifié ici, jamais accepté tel quel depuis le navigateur
  const promoCheck = body.promo ? await checkPromo(String(body.promo).slice(0, 40), subtotal) : null;
  if (promoCheck && !promoCheck.ok) return bad(`promo_${promoCheck.reason}`);
  const promo = promoCheck?.ok ? { code: promoCheck.code, discount: promoCheck.discount } : undefined;
  const total = subtotal - (promo?.discount ?? 0);
  if (method !== "ccp" && total < 50) return bad("amount_too_low"); // minimum imposé par le paiement en ligne

  const origin = new URL(req.url).origin;
  const onlinePayConfigured = slickpayEnabled() || chargilyEnabled();
  const demo = method !== "ccp" && !onlinePayConfigured;

  const order = await createOrder({
    locale,
    customer: { name, phone, email: email.toLowerCase() },
    method,
    lines,
    total,
    promo,
    // Mode démo (sans passerelle de paiement en ligne configurée) : commande simulée payée
    status: method === "ccp" ? "awaiting_transfer" : demo ? "paid" : "pending",
    demo,
  });
  const confirmUrl = `${origin}/${locale}/commande/${order.id}`;
  if (promo) await countPromoUse(promo.code);
  if (order.status === "paid") await deliverFromStock(order.id);

  if (method === "ccp" || demo) return NextResponse.json({ redirect: confirmUrl, orderId: order.id });

  // 1. Priorité à Slick-Pay (passerelle directe SATIM CIB & Edahabia)
  if (slickpayEnabled()) {
    try {
      const parts = name.split(/\s+/);
      const firstname = parts[0] || "Client";
      const lastname = parts.slice(1).join(" ") || parts[0] || "Client";

      const invoiceRes = await createSlickPayInvoice({
        amount: total,
        firstname,
        lastname,
        email,
        phone: phone.replace(/\s+/g, ""),
        orderId: order.id,
        returnUrl: confirmUrl,
        items: lines.map((l) => ({
          name: `${l.name} (${l.optionLabel})`,
          price: l.unitPrice,
          quantity: l.qty,
        })),
      });

      await updateOrder(order.id, { slickpayInvoiceId: invoiceRes.id });
      return NextResponse.json({ redirect: invoiceRes.url, orderId: order.id });
    } catch (e) {
      console.error("SlickPay Error:", e);
      await updateOrder(order.id, { status: "failed" });
      return NextResponse.json({ error: "payment_unavailable" }, { status: 502 });
    }
  }

  // 2. Chargily Pay (si configuré)
  try {
    const isPublic = !/localhost|127\.0\.0\.1/.test(origin);
    const checkout = await createCheckout({
      amount: total,
      method,
      orderId: order.id,
      locale,
      successUrl: confirmUrl,
      failureUrl: `${confirmUrl}?echec=1`,
      webhookUrl: isPublic ? `${origin}/api/webhooks/chargily` : undefined,
    });
    await updateOrder(order.id, { chargilyCheckoutId: checkout.id });
    return NextResponse.json({ redirect: checkout.checkout_url, orderId: order.id });
  } catch (e) {
    console.error("Chargily Error:", e);
    await updateOrder(order.id, { status: "failed" });
    return NextResponse.json({ error: "payment_unavailable" }, { status: 502 });
  }
}
