import Link from "next/link";
import { redirect } from "next/navigation";
import { hasLocale } from "@/lib/i18n";
import { getCurrentCustomer } from "@/lib/user-auth";
import { listOrders } from "@/lib/orders";
import { getAllProducts } from "@/lib/products";
import { formatPrice } from "@/lib/format";
import { statusLabel } from "@/lib/order-status";
import { Icon } from "@/components/Icon";
import { CopyKey } from "@/components/checkout/CopyKey";

export const metadata = {
  title: "Mon Compte — SOFT4DZ",
  robots: { index: false },
};

export default async function MonComptePage({
  params,
}: {
  params: Promise<{ lang: string }>;
}) {
  const { lang } = await params;
  if (!hasLocale(lang)) redirect("/fr/mon-compte");

  const customer = await getCurrentCustomer();
  if (!customer) {
    redirect(`/${lang}/connexion`);
  }

  const [allOrders, products] = await Promise.all([listOrders(), getAllProducts()]);

  // Filtrer les commandes appartenant à ce client
  const myOrders = allOrders
    .filter((o) => o.customer.email.toLowerCase() === customer.email.toLowerCase())
    .sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());

  const getProduct = (slug: string) => products.find((p) => p.slug === slug);

  // Total des clés possédées
  const totalKeys = myOrders.reduce(
    (sum, o) => sum + o.lines.reduce((s, l) => s + (l.keys?.length ?? 0), 0),
    0
  );

  return (
    <div className="w" style={{ maxWidth: 960, margin: "32px auto", padding: "0 16px" }}>
      {/* En-tête profil client */}
      <div
        className="card box"
        style={{
          padding: 24,
          display: "flex",
          alignItems: "center",
          gap: 20,
          flexWrap: "wrap",
          borderRadius: 18,
          marginBottom: 24,
        }}
      >
        <div style={{ position: "relative" }}>
          {customer.avatar ? (
            // eslint-disable-next-line @next/next/no-img-element
            <img
              src={customer.avatar}
              alt={customer.name}
              style={{ width: 68, height: 68, borderRadius: "50%", objectFit: "cover", border: "3px solid var(--pri-50)" }}
            />
          ) : (
            <div
              style={{
                width: 68,
                height: 68,
                borderRadius: "50%",
                background: "var(--grad)",
                color: "#fff",
                display: "grid",
                placeItems: "center",
                fontSize: 24,
                fontWeight: 700,
              }}
            >
              {customer.name.charAt(0).toUpperCase()}
            </div>
          )}
        </div>

        <div style={{ flex: 1, minWidth: 200 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 10, flexWrap: "wrap" }}>
            <h1 style={{ fontSize: 22, fontWeight: 800 }}>{customer.name}</h1>
            <span className="stt s-delivered" style={{ display: "inline-flex", alignItems: "center", gap: 4 }}>
              <Icon name="badge-check" style={{ width: 14, height: 14 }} />
              Compte Google Vérifié
            </span>
          </div>
          <p style={{ color: "var(--muted)", fontSize: 14, marginTop: 4 }}>{customer.email}</p>
        </div>

        <div style={{ display: "flex", gap: 10, alignItems: "center" }}>
          <form action="/api/auth/logout" method="POST">
            <button className="btn b-out" style={{ height: 42, padding: "0 16px", fontSize: 13, gap: 6 }}>
              <Icon name="log-out" />
              Se déconnecter
            </button>
          </form>
        </div>
      </div>

      {/* Résumé des statistiques client */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))", gap: 14, marginBottom: 24 }}>
        <div className="card box" style={{ padding: 18, display: "flex", alignItems: "center", gap: 14 }}>
          <div style={{ padding: 12, borderRadius: 12, background: "var(--pri-50)", color: "var(--pri)" }}>
            <Icon name="shopping-bag" style={{ width: 22, height: 22 }} />
          </div>
          <div>
            <div style={{ fontSize: 20, fontWeight: 800 }}>{myOrders.length}</div>
            <div style={{ fontSize: 12, color: "var(--muted)" }}>Commandes passées</div>
          </div>
        </div>

        <div className="card box" style={{ padding: 18, display: "flex", alignItems: "center", gap: 14 }}>
          <div style={{ padding: 12, borderRadius: 12, background: "var(--teal-50)", color: "var(--teal)" }}>
            <Icon name="key-round" style={{ width: 22, height: 22 }} />
          </div>
          <div>
            <div style={{ fontSize: 20, fontWeight: 800 }}>{totalKeys}</div>
            <div style={{ fontSize: 12, color: "var(--muted)" }}>Licences & Accès actifs</div>
          </div>
        </div>

        <div className="card box" style={{ padding: 18, display: "flex", alignItems: "center", gap: 14 }}>
          <div style={{ padding: 12, borderRadius: 12, background: "var(--orange-50, #fff7ed)", color: "var(--orange)" }}>
            <Icon name="headset" style={{ width: 22, height: 22 }} />
          </div>
          <div>
            <div style={{ fontSize: 14, fontWeight: 700 }}>Support VIP</div>
            <div style={{ fontSize: 12, color: "var(--muted)" }}>WhatsApp 7j/7 actif</div>
          </div>
        </div>
      </div>

      {/* Liste des commandes & licences */}
      <div className="card box" style={{ padding: 24, borderRadius: 18 }}>
        <h2 style={{ fontSize: 18, fontWeight: 700, marginBottom: 18, display: "flex", alignItems: "center", gap: 8 }}>
          <Icon name="package" style={{ color: "var(--pri)" }} />
          Mes Licences, Abonnements & Commandes
        </h2>

        {myOrders.length === 0 ? (
          <div style={{ textAlign: "center", padding: "40px 16px" }}>
            <div style={{ display: "inline-flex", padding: 16, borderRadius: "50%", background: "var(--pri-50)", marginBottom: 14 }}>
              <Icon name="shopping-cart" style={{ width: 32, height: 32, color: "var(--pri)" }} />
            </div>
            <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 6 }}>Aucune commande pour le moment</h3>
            <p style={{ color: "var(--muted)", fontSize: 13, marginBottom: 18 }}>
              Toutes les licences et accès que vous achèterez apparaîtront ici avec vos clés en libre accès.
            </p>
            <Link className="btn b-pri" href={`/${lang}#catalogue`}>
              Explorer le catalogue
            </Link>
          </div>
        ) : (
          <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            {myOrders.map((order) => (
              <div
                key={order.id}
                style={{
                  border: "1px solid var(--line)",
                  borderRadius: 14,
                  padding: 16,
                  background: "#fff",
                }}
              >
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", flexWrap: "wrap", gap: 10, marginBottom: 12, borderBottom: "1px solid var(--line)", paddingBottom: 10 }}>
                  <div>
                    <span style={{ fontWeight: 700, fontSize: 15 }}>Commande #{order.id}</span>
                    <small style={{ display: "block", color: "var(--muted)", fontSize: 12 }}>
                      {new Date(order.createdAt).toLocaleDateString("fr-FR", { dateStyle: "long" })}
                    </small>
                  </div>
                  <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                    <span className={`stt s-${order.status}`}>{statusLabel[order.status]}</span>
                    <Link className="row-link" href={`/${lang}/commande/${order.id}`} style={{ fontSize: 13, display: "inline-flex", alignItems: "center", gap: 4 }}>
                      Suivi complet <Icon name="arrow-right" className="flip-x" style={{ width: 14, height: 14 }} />
                    </Link>
                  </div>
                </div>

                {/* Lignes de la commande */}
                <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                  {order.lines.map((line, idx) => {
                    const prod = getProduct(line.slug);
                    return (
                      <div key={idx} style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                        <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
                          <span
                            className={`pic ${prod?.gradient ?? "g-brand"}`}
                            style={{ width: 40, height: 40, borderRadius: 10, display: "grid", placeItems: "center", color: "#fff", flexShrink: 0 }}
                          >
                            <Icon name={prod?.icon ?? "key-round"} style={{ width: 20, height: 20 }} />
                          </span>
                          <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600, fontSize: 14 }}>{line.name}</div>
                            <small style={{ color: "var(--muted)", fontSize: 12 }}>
                              {line.optionLabel} · Qté: {line.qty}
                            </small>
                          </div>
                          <div style={{ fontWeight: 700, fontSize: 14, color: "var(--pri-700)" }}>
                            {formatPrice(line.unitPrice * line.qty, lang)}
                          </div>
                        </div>

                        {/* Affichage des clés livrées */}
                        {!!line.keys?.length && (
                          <div style={{ marginTop: 4, display: "flex", flexDirection: "column", gap: 6, paddingLeft: 52 }}>
                            {line.keys.map((k, kIdx) => (
                              <CopyKey key={kIdx} value={k} copy="Copier la clé" copied="Copié !" />
                            ))}
                          </div>
                        )}
                      </div>
                    );
                  })}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
