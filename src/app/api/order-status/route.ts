import { NextResponse } from "next/server";
import { getOrder } from "@/lib/orders";

/*
 * Statut d'une commande pour l'assistant du site.
 * Ne renvoie aucune donnée personnelle (ni nom, ni e-mail, ni téléphone, ni clé).
 * Limite simple : 20 requêtes par minute et par adresse IP (évite de tester des numéros en rafale).
 */
const hits = new Map<string, number[]>();
const LIMIT = 20, WINDOW = 60_000;

export async function GET(req: Request) {
  const ip = req.headers.get("x-forwarded-for")?.split(",")[0].trim() || "local";
  const now = Date.now();
  const recent = (hits.get(ip) ?? []).filter((t) => now - t < WINDOW);
  if (recent.length >= LIMIT) return NextResponse.json({ error: "too_many" }, { status: 429 });
  recent.push(now);
  hits.set(ip, recent);
  if (hits.size > 5000) hits.clear(); // garde-fou mémoire

  const id = (new URL(req.url).searchParams.get("id") ?? "").trim().toUpperCase();
  if (!/^SD-[A-Z0-9]{8}$/.test(id)) return NextResponse.json({ found: false });
  const o = await getOrder(id);
  if (!o) return NextResponse.json({ found: false });
  return NextResponse.json({
    found: true,
    id: o.id,
    status: o.status,
    date: o.createdAt,
    locale: o.locale,
    items: o.lines.map((l) => `${l.name} × ${l.qty}`),
  });
}
