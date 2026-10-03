import "server-only";
import { readJson, updateJson } from "./store";

/** Réglages de la boutique, modifiables depuis Admin › Paramètres. */
export type Settings = {
  whatsapp: string;
  phone: string;
  email: string;
  facebook: string;
  instagram: string;
  tiktok: string;
  /** Messages de la barre d'annonces (un par ligne dans l'admin) */
  announce: { fr: string[]; ar: string[]; en: string[] };
  /** Livrer automatiquement les clés en stock dès que le paiement est confirmé */
  autoDelivery: boolean;
  /** Seuil d'alerte de stock de clés */
  lowStock: number;
};

export const defaultSettings = (): Settings => ({
  whatsapp: process.env.NEXT_PUBLIC_WHATSAPP ?? "213555000000",
  phone: process.env.NEXT_PUBLIC_PHONE ?? "0555 00 00 00",
  email: process.env.NEXT_PUBLIC_EMAIL ?? "contact@soft4dz.com",
  facebook: "",
  instagram: "",
  tiktok: "",
  announce: { fr: [], ar: [], en: [] },
  autoDelivery: true,
  lowStock: 3,
});

export const getSettings = async () => ({ ...defaultSettings(), ...(await readJson<Partial<Settings>>("settings.json", () => ({}))) });

export const saveSettings = (patch: Partial<Settings>) =>
  updateJson<Partial<Settings>>("settings.json", () => ({}), (s) => ({ ...s, ...patch }));

/** Données publiques transmises au navigateur (jamais de réglage interne). */
export type PublicSettings = Pick<Settings, "whatsapp" | "phone" | "email" | "facebook" | "instagram" | "tiktok" | "announce">;
export const publicSettings = (s: Settings): PublicSettings => ({
  whatsapp: s.whatsapp, phone: s.phone, email: s.email, facebook: s.facebook, instagram: s.instagram, tiktok: s.tiktok, announce: s.announce,
});

