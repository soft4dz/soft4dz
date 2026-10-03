export const locales = ["fr", "ar", "en"] as const;
export type Locale = (typeof locales)[number];
export const defaultLocale: Locale = "fr";

export const localeNames: Record<Locale, string> = { fr: "Français", ar: "العربية", en: "English" };

export const hasLocale = (value: string): value is Locale =>
  (locales as readonly string[]).includes(value);

export const dirOf = (locale: Locale) => (locale === "ar" ? "rtl" : "ltr");

/**
 * Texte multilingue du catalogue. Le français et l'arabe sont obligatoires ;
 * l'anglais est facultatif : s'il manque, on affiche le français.
 */
export type Localized = { fr: string; ar: string; en?: string };
export const t = (text: Localized, locale: Locale) => text[locale] || text.fr;
