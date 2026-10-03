import type { OrderStatus, PaymentMethod } from "./orders";

export const statusLabel: Record<OrderStatus, string> = {
  pending: "Paiement en cours",
  awaiting_transfer: "En attente CCP",
  paid: "Payée · à livrer",
  delivered: "Livrée",
  failed: "Échouée",
  cancelled: "Annulée",
};

export const methodLabel: Record<PaymentMethod, string> = {
  cib: "CIB",
  edahabia: "Edahabia",
  ccp: "Versement CCP",
};
