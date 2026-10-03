/** Lien WhatsApp vers le numéro de la boutique (réglé dans Admin › Paramètres). */
export const waLink = (number: string, text?: string) =>
  `https://wa.me/${number.replace(/\D/g, "")}${text ? `?text=${encodeURIComponent(text)}` : ""}`;
