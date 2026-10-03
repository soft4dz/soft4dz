import "server-only";

/*
 * Slick-Pay API v2 — Passerelle de paiement CIB & Edahabia (SATIM) en Algérie.
 * Variables d'environnement :
 *   SLICKPAY_PUBLIC_KEY   Clé publique (ex. 51701|...)
 *   SLICKPAY_MODE         "production" ou "sandbox"
 *   SLICKPAY_ACCOUNT_UUID UUID du compte récepteur (optionnel, prend le compte par défaut)
 */

const key = () => process.env.SLICKPAY_PUBLIC_KEY ?? "";
export const slickpayEnabled = () => key().length > 0;

const base = () =>
  process.env.SLICKPAY_MODE === "sandbox"
    ? "https://devapi.slick-pay.com/api/v2"
    : "https://prodapi.slick-pay.com/api/v2";

export type SlickPayItem = {
  name: string;
  price: number;
  quantity: number;
};

export type SlickPayInvoice = {
  id: number;
  completed: number;
  status: string;
  serial?: string;
  amount: string | number;
  url?: string;
};

export type SlickPayCreateResponse = {
  success: number;
  message?: string;
  id: number;
  url: string; // URL directe vers le portail bancaire SATIM
  invoice: SlickPayInvoice;
};

export type SlickPayGetResponse = {
  success: number;
  completed: number;
  data: SlickPayInvoice;
};

async function call<T>(path: string, init?: RequestInit): Promise<T> {
  const res = await fetch(base() + path, {
    ...init,
    headers: {
      Authorization: `Bearer ${key()}`,
      "Content-Type": "application/json",
      Accept: "application/json",
      ...(init?.headers ?? {}),
    },
    cache: "no-store",
  });

  if (!res.ok) {
    const errorText = await res.text();
    throw new Error(`SlickPay ${res.status}: ${errorText}`);
  }

  return res.json() as Promise<T>;
}

export function createSlickPayInvoice(input: {
  amount: number;
  firstname: string;
  lastname: string;
  email: string;
  phone: string;
  address?: string;
  orderId: string;
  returnUrl: string;
  items?: SlickPayItem[];
}) {
  const payload: Record<string, unknown> = {
    amount: input.amount,
    firstname: input.firstname.trim() || "Client",
    lastname: input.lastname.trim() || "Soft4dz",
    email: input.email.trim(),
    phone: input.phone.trim(),
    address: input.address?.trim() || "Algérie",
    url: input.returnUrl,
    note: `Commande SOFT4DZ #${input.orderId}`,
    items: input.items ?? [],
  };

  if (process.env.SLICKPAY_ACCOUNT_UUID) {
    payload.account = process.env.SLICKPAY_ACCOUNT_UUID;
  }

  return call<SlickPayCreateResponse>("/users/invoices", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function getSlickPayInvoice(invoiceId: number | string): Promise<SlickPayGetResponse> {
  return call<SlickPayGetResponse>(`/users/invoices/${invoiceId}`);
}
