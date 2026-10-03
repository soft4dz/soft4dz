import type { Locale } from "./i18n";

/** 12500 -> "12 500" (espace fine, lisible en FR comme en AR). */
export const formatNumber = (n: number) =>
  Math.round(n).toLocaleString("fr-FR").replace(/[  ]/g, " ");

export const currency = (locale: Locale) => (locale === "ar" ? "دج" : "DA");

export const formatPrice = (n: number, locale: Locale) =>
  `${formatNumber(n)} ${currency(locale)}`;
