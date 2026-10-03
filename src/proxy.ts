import { NextResponse, type NextRequest } from "next/server";
import { defaultLocale, locales } from "@/lib/i18n";

/** Choisit l'arabe si le navigateur le préfère au français, sinon le français. */
function pickLocale(req: NextRequest) {
  const header = req.headers.get("accept-language") ?? "";
  const langs = header.split(",").map((part) => part.split(";")[0].trim().slice(0, 2).toLowerCase());
  return langs.find((l) => (locales as readonly string[]).includes(l)) ?? defaultLocale;
}

export function proxy(req: NextRequest) {
  const { pathname } = req.nextUrl;
  const hasLocale = locales.some((l) => pathname === `/${l}` || pathname.startsWith(`/${l}/`));
  if (hasLocale) return;
  req.nextUrl.pathname = `/${pickLocale(req)}${pathname}`;
  return NextResponse.redirect(req.nextUrl);
}

export const config = {
  // Ni les fichiers internes, ni l'API, ni l'admin, ni les fichiers statiques (logo.png…)
  matcher: ["/((?!_next|api|admin|.*\\..*).*)"],
};
