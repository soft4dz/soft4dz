/** Règles partagées entre le formulaire (navigateur) et l'API (serveur). */
export const rules = {
  name: (v: string) => v.trim().length >= 3 && v.trim().length <= 80,
  // Mobile algérien : 05, 06 ou 07 + 8 chiffres (espaces autorisés)
  phone: (v: string) => /^0[5-7](\s?\d){8}$/.test(v.trim()),
  email: (v: string) => /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v.trim()) && v.length <= 120,
};

export type CheckoutField = keyof typeof rules;
export const paymentMethods = ["cib", "edahabia", "ccp"] as const;
