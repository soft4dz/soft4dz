import { NextResponse } from "next/server";
import { getGoogleAuthUrl, googleConfigured } from "@/lib/user-auth";

export async function GET(req: Request) {
  const origin = new URL(req.url).origin;

  if (!googleConfigured()) {
    // Si les clés Google ne sont pas encore renseignées dans .env.local
    return NextResponse.redirect(`${origin}/fr/connexion?erreur=google_non_configure`);
  }

  try {
    const authUrl = await getGoogleAuthUrl(origin);
    return NextResponse.redirect(authUrl);
  } catch (e) {
    console.error("Google Auth URL error:", e);
    return NextResponse.redirect(`${origin}/fr/connexion?erreur=echec_connexion`);
  }
}
