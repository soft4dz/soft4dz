import "server-only";
import { promises as fs } from "fs";
import path from "path";

/*
 * Mini base de données en fichiers JSON (.data/) pour le développement.
 * À remplacer par Supabase avant la mise en ligne : seules les fonctions
 * de src/lib/products.ts et src/lib/orders.ts l'utilisent.
 */

const dir = path.join(process.cwd(), ".data");

// Les écritures sont mises en file pour éviter que deux requêtes ne s'écrasent
let queue: Promise<unknown> = Promise.resolve();

export async function readJson<T>(name: string, fallback: () => T): Promise<T> {
  try {
    return JSON.parse(await fs.readFile(path.join(dir, name), "utf8")) as T;
  } catch {
    return fallback();
  }
}

export function writeJson(name: string, data: unknown) {
  const run = queue.then(async () => {
    await fs.mkdir(dir, { recursive: true });
    const file = path.join(dir, name), tmp = `${file}.tmp`;
    await fs.writeFile(tmp, JSON.stringify(data, null, 2));
    await fs.rename(tmp, file);
  });
  queue = run.catch(() => {});
  return run;
}

/** Lecture → modification → écriture, sans qu'une autre écriture s'intercale. */
export function updateJson<T>(name: string, fallback: () => T, fn: (data: T) => T | Promise<T>) {
  const run = queue.then(async () => {
    const data = await readJson(name, fallback);
    const next = await fn(data);
    await fs.mkdir(dir, { recursive: true });
    const file = path.join(dir, name), tmp = `${file}.tmp`;
    await fs.writeFile(tmp, JSON.stringify(next, null, 2));
    await fs.rename(tmp, file);
    return next;
  });
  queue = run.catch(() => {});
  return run;
}
