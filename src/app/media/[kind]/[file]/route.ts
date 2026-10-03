import { promises as fs } from "fs";
import path from "path";
import { UPLOAD_DIR } from "@/lib/images";

const TYPES: Record<string, string> = { jpg: "image/jpeg", png: "image/png", webp: "image/webp" };

/** Sert les images envoyées depuis l'admin. Noms stricts : aucune remontée de dossier possible. */
export async function GET(_: Request, ctx: RouteContext<"/media/[kind]/[file]">) {
  const { kind, file } = await ctx.params;
  const m = /^[a-f0-9]{16}\.(jpg|png|webp)$/.exec(file);
  if (!m || (kind !== "product" && kind !== "banner")) return new Response("Not found", { status: 404 });
  try {
    const data = await fs.readFile(path.join(UPLOAD_DIR, kind, file));
    return new Response(new Uint8Array(data), {
      headers: {
        "Content-Type": TYPES[m[1]],
        // Nom de fichier aléatoire et jamais réutilisé : cache long sans risque
        "Cache-Control": "public, max-age=31536000, immutable",
        "X-Content-Type-Options": "nosniff",
      },
    });
  } catch {
    return new Response("Not found", { status: 404 });
  }
}
