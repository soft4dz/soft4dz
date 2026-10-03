import "server-only";
import { promises as fs } from "fs";
import path from "path";
import { randomBytes } from "crypto";

/*
 * Images envoyées depuis l'admin.
 * Stockées dans .data/uploads/ et servies par la route /media/… (voir app/media).
 * À la mise en ligne : remplacer par Supabase Storage (même fonction saveImage).
 */

export const UPLOAD_DIR = path.join(process.cwd(), ".data", "uploads");

/** Règles par type d'image : affichées dans l'admin et vérifiées côté serveur. */
export const imageRules = {
  product: { label: "Image produit", ratio: 1, recommended: [1000, 1000], min: [600, 600], maxMb: 2 },
  banner: { label: "Bannière du carrousel", ratio: 2.5, recommended: [1600, 640], min: [1200, 480], maxMb: 3 },
} as const;
export type ImageKind = keyof typeof imageRules;

const TYPES: Record<string, string> = { "image/jpeg": "jpg", "image/png": "png", "image/webp": "webp" };

/** Lit largeur × hauteur directement dans l'en-tête du fichier (PNG, JPEG, WebP). */
export function readSize(buf: Buffer): { width: number; height: number } | null {
  // PNG
  if (buf.length > 24 && buf.readUInt32BE(0) === 0x89504e47) return { width: buf.readUInt32BE(16), height: buf.readUInt32BE(20) };
  // JPEG : on parcourt les segments jusqu'au marqueur SOF
  if (buf[0] === 0xff && buf[1] === 0xd8) {
    let i = 2;
    while (i + 9 < buf.length) {
      if (buf[i] !== 0xff) { i++; continue; }
      const m = buf[i + 1];
      if (m >= 0xc0 && m <= 0xcf && m !== 0xc4 && m !== 0xc8 && m !== 0xcc) return { height: buf.readUInt16BE(i + 5), width: buf.readUInt16BE(i + 7) };
      i += 2 + buf.readUInt16BE(i + 2);
    }
    return null;
  }
  // WebP
  if (buf.toString("ascii", 0, 4) === "RIFF" && buf.toString("ascii", 8, 12) === "WEBP") {
    const chunk = buf.toString("ascii", 12, 16);
    if (chunk === "VP8 ") return { width: buf.readUInt16LE(26) & 0x3fff, height: buf.readUInt16LE(28) & 0x3fff };
    if (chunk === "VP8L") { const b = buf.readUInt32LE(21); return { width: (b & 0x3fff) + 1, height: ((b >> 14) & 0x3fff) + 1 }; }
    if (chunk === "VP8X") return { width: 1 + buf.readUIntLE(24, 3), height: 1 + buf.readUIntLE(27, 3) };
  }
  return null;
}

export type SavedImage = { url: string; width: number; height: number; warning?: string };

export async function saveImage(file: File, kind: ImageKind): Promise<SavedImage | { error: string }> {
  const rule = imageRules[kind];
  const ext = TYPES[file.type];
  if (!ext) return { error: "Format refusé : utilisez JPG, PNG ou WebP." };
  if (file.size > rule.maxMb * 1024 * 1024) return { error: `Fichier trop lourd : ${rule.maxMb} Mo maximum.` };

  const buf = Buffer.from(await file.arrayBuffer());
  const size = readSize(buf);
  if (!size) return { error: "Image illisible ou corrompue." };
  if (size.width < rule.min[0] || size.height < rule.min[1]) {
    return { error: `Image trop petite (${size.width}×${size.height} px). Minimum ${rule.min[0]}×${rule.min[1]} px, idéal ${rule.recommended[0]}×${rule.recommended[1]} px.` };
  }

  const name = `${randomBytes(8).toString("hex")}.${ext}`;
  await fs.mkdir(path.join(UPLOAD_DIR, kind), { recursive: true });
  await fs.writeFile(path.join(UPLOAD_DIR, kind, name), buf);

  const ratio = size.width / size.height;
  const warning = Math.abs(ratio - rule.ratio) / rule.ratio > 0.08
    ? `Proportions ${size.width}×${size.height} : l'image sera recadrée au centre (format conseillé ${rule.recommended[0]}×${rule.recommended[1]}).`
    : undefined;
  return { url: `/media/${kind}/${name}`, ...size, warning };
}

/** Supprime un fichier envoyé (ignore les URL qui ne sont pas des uploads). */
export async function deleteImage(url: string) {
  const m = /^\/media\/(product|banner)\/([a-f0-9]{16}\.(?:jpg|png|webp))$/.exec(url);
  if (!m) return;
  await fs.rm(path.join(UPLOAD_DIR, m[1], m[2]), { force: true });
}
