import "server-only";
import { readJson, updateJson } from "./store";

/** Journal d'activité de l'admin (500 dernières actions). */
export type AuditEntry = { at: string; action: string; target?: string };

const FILE = "audit.json";
const empty = (): AuditEntry[] => [];

export const logAction = (action: string, target?: string) =>
  updateJson(FILE, empty, (list) => [{ at: new Date().toISOString(), action, target }, ...list].slice(0, 500)).catch(() => {});

export const listAudit = () => readJson(FILE, empty);
