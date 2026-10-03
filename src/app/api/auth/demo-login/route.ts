import { NextResponse } from "next/server";
import { createCustomerSession, findOrCreateGoogleCustomer } from "@/lib/user-auth";

export async function POST(req: Request) {
  const origin = new URL(req.url).origin;

  // Créer ou récupérer un compte démo
  const user = await findOrCreateGoogleCustomer({
    googleId: "google_demo_123456789",
    name: "Abdelkader Messaoudene",
    email: "kader@soft4dz.com",
    avatar: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80",
  });

  await createCustomerSession(user.id);
  return NextResponse.redirect(`${origin}/fr/mon-compte`);
}
