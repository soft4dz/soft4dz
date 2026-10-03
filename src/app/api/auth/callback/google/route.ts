import { NextResponse } from "next/server";
import {
  createCustomerSession,
  exchangeGoogleCode,
  findOrCreateGoogleCustomer,
  verifyOAuthState,
} from "@/lib/user-auth";

export async function GET(req: Request) {
  const url = new URL(req.url);
  const origin = url.origin;
  const code = url.searchParams.get("code");
  const state = url.searchParams.get("state");
  const error = url.searchParams.get("error");

  if (error || !code) {
    return NextResponse.redirect(`${origin}/fr/connexion?erreur=acces_refuse`);
  }

  // Vérifier la sécurité de l'état CSRF
  const validState = await verifyOAuthState(state);
  if (!validState) {
    return NextResponse.redirect(`${origin}/fr/connexion?erreur=etat_invalide`);
  }

  try {
    const profile = await exchangeGoogleCode(code, origin);
    const user = await findOrCreateGoogleCustomer(profile);
    await createCustomerSession(user.id);

    return NextResponse.redirect(`${origin}/fr/mon-compte`);
  } catch (e) {
    console.error("Google Callback Error:", e);
    return NextResponse.redirect(`${origin}/fr/connexion?erreur=erreur_authentification`);
  }
}
