import Link from "next/link";
import { redirect } from "next/navigation";
import { hasLocale } from "@/lib/i18n";
import { getCurrentCustomer, googleConfigured } from "@/lib/user-auth";
import { Icon } from "@/components/Icon";

export const metadata = {
  title: "Connexion Client — SOFT4DZ",
  description: "Connectez-vous pour retrouver toutes vos licences et abonnements.",
};

export default async function ConnexionPage({
  params,
  searchParams,
}: {
  params: Promise<{ lang: string }>;
  searchParams: Promise<{ erreur?: string; demo?: string }>;
}) {
  const { lang } = await params;
  if (!hasLocale(lang)) redirect("/fr/connexion");
  const sp = await searchParams;

  const current = await getCurrentCustomer();
  if (current) {
    redirect(`/${lang}/mon-compte`);
  }

  const isConfigured = googleConfigured();

  return (
    <div className="w" style={{ maxWidth: 520, margin: "48px auto", padding: "0 16px" }}>
      <div className="card box" style={{ padding: "36px 28px", textAlign: "center", borderRadius: 20 }}>
        <div style={{ display: "inline-flex", padding: 14, background: "var(--pri-50)", borderRadius: 16, marginBottom: 16 }}>
          <Icon name="user" style={{ width: 32, height: 32, color: "var(--pri)" }} />
        </div>

        <h1 style={{ fontSize: 24, fontWeight: 800, marginBottom: 8 }}>Espace Client</h1>
        <p style={{ color: "var(--muted)", fontSize: 14, marginBottom: 28, lineHeight: 1.5 }}>
          Retrouvez instantanément toutes vos licences, vos abonnements actifs et votre historique de facturation.
        </p>

        {sp.erreur === "google_non_configure" && (
          <div className="msg err" style={{ marginBottom: 20, textAlign: "start" }}>
            <Icon name="info" />
            <div>
              <b>Clés Google OAuth manquantes :</b>
              <p style={{ fontSize: 12, marginTop: 4 }}>
                Ajoutez <code>GOOGLE_CLIENT_ID</code> et <code>GOOGLE_CLIENT_SECRET</code> dans votre fichier <code>.env.local</code>.
              </p>
            </div>
          </div>
        )}

        {sp.erreur === "acces_refuse" && (
          <div className="msg err" style={{ marginBottom: 20 }}>
            <Icon name="info" />
            <span>Connexion annulée ou refusée par Google.</span>
          </div>
        )}

        {/* Bouton officiel Google Sign-In */}
        <a
          href="/api/auth/google"
          className="btn"
          style={{
            width: "100%",
            height: 52,
            background: "#fff",
            color: "#1f2937",
            border: "1.5px solid #e5e7eb",
            borderRadius: 14,
            fontWeight: 600,
            fontSize: 15,
            boxShadow: "0 2px 6px rgba(0,0,0,0.06)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            gap: 12,
            transition: "all .2s ease",
          }}
        >
          {/* Logo officiel Google multi-couleurs SVG */}
          <svg width="22" height="22" viewBox="0 0 24 24">
            <path
              fill="#4285F4"
              d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.65v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.14z"
            />
            <path
              fill="#34A853"
              d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24z"
            />
            <path
              fill="#FBBC05"
              d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15z"
            />
            <path
              fill="#EA4335"
              d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98z"
            />
          </svg>
          Continuer avec Google
        </a>

        {!isConfigured && (
          <div style={{ marginTop: 20, padding: 14, background: "#f8fafc", borderRadius: 12, border: "1px dashed #cbd5e1" }}>
            <p style={{ fontSize: 12, color: "var(--muted)", marginBottom: 10 }}>
              💡 <b>Mode Démonstration :</b> En attendant d&apos;ajouter vos identifiants Google dans <code>.env.local</code>, vous pouvez tester l&apos;Espace Client immédiatement :
            </p>
            <form action="/api/auth/demo-login" method="POST">
              <button
                type="submit"
                className="btn b-out"
                style={{ width: "100%", height: 38, fontSize: 13, gap: 6 }}
              >
                <Icon name="sparkles" />
                Tester la connexion client (Compte Démo)
              </button>
            </form>
          </div>
        )}

        <div style={{ marginTop: 28, paddingTop: 24, borderTop: "1px solid var(--line)", textAlign: "start" }}>
          <h4 style={{ fontSize: 13, fontWeight: 700, color: "var(--ink)", marginBottom: 10, display: "flex", alignItems: "center", gap: 6 }}>
            <Icon name="shield-check" style={{ color: "var(--teal)", width: 16, height: 16 }} />
            Avantages du compte Soft4dz
          </h4>
          <ul style={{ listStyle: "none", fontSize: 13, color: "var(--muted)", display: "flex", flexDirection: "column", gap: 8 }}>
            <li style={{ display: "flex", alignItems: "center", gap: 8 }}>
              <Icon name="check" style={{ width: 14, height: 14, color: "var(--teal)" }} />
              Toutes vos clés stockées et réactivables en cas de formatage
            </li>
            <li style={{ display: "flex", alignItems: "center", gap: 8 }}>
              <Icon name="check" style={{ width: 14, height: 14, color: "var(--teal)" }} />
              Formulaire de commande pré-rempli en 1 clic
            </li>
            <li style={{ display: "flex", alignItems: "center", gap: 8 }}>
              <Icon name="check" style={{ width: 14, height: 14, color: "var(--teal)" }} />
              Assistance technique et support prioritaire 7j/7
            </li>
          </ul>
        </div>

        <div style={{ marginTop: 22 }}>
          <Link href={`/${lang}`} className="row-link" style={{ fontSize: 13 }}>
            ← Retour à l&apos;accueil de la boutique
          </Link>
        </div>
      </div>
    </div>
  );
}
