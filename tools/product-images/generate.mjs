/**
 * Génère les cartes produits du site (WebP 1200×900, format 4:3 des cartes) à partir du même
 * composant que le canevas Claude Design « Soft4dz — Cartes produits ».
 *
 * Usage : node tools/product-images/generate.mjs [--only=netflix,spotify] [--out=assets/images/products]
 * Prérequis : Playwright (npm i -D playwright) et un Chromium installé.
 *
 * Pour un nouveau produit : l'ajouter dans catalog.json (marque, libellé, couleurs, noms en base),
 * déposer son logo dans logos/<file>.svg s'il en a un (Simple Icons), relancer ce script
 * puis `php database/assign_product_images.php`.
 */
import { createRequire } from 'node:module';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const here = dirname(fileURLToPath(import.meta.url));
const root = resolve(here, '../..');
const args = Object.fromEntries(process.argv.slice(2).map((a) => a.replace(/^--/, '').split('=')));
const outDir = resolve(root, args.out || 'assets/images/products');
const only = args.only ? new Set(args.only.split(',')) : null;

const catalog = JSON.parse(await readFile(join(here, 'catalog.json'), 'utf8'));

/** Logo de la marque (couleur accent) ou, à défaut, icône de catégorie (couleur du texte). */
async function glyph(product) {
  const [dir, name, color] = product.logo
    ? ['logos', product.logo, product.accent]
    : ['icons', product.icon, product.ink];
  const svg = await readFile(join(here, dir, `${name}.svg`), 'utf8');
  const viewBox = svg.match(/viewBox="([^"]+)"/)[1];
  const paths = [...svg.matchAll(/ d="([^"]+)"/g)].map((m) => `<path d="${m[1]}"/>`).join('');
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}" fill="${color}">${paths}</svg>`;
}

await mkdir(outDir, { recursive: true });
const browser = await chromium.launch(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {});
const page = await browser.newPage({ viewport: { width: 400, height: 300 }, deviceScaleFactor: 3 });
await page.goto(pathToFileURL(join(here, 'template.html')).href);
const encoder = await browser.newPage();

let count = 0;
for (const product of catalog.products) {
  if (only && !only.has(product.file)) continue;

  await page.evaluate((data) => window.render(data), {
    brand: product.brand,
    label: product.label,
    bg: product.bg,
    ink: product.ink,
    logo: Boolean(product.logo),
    badge: product.badge || '',
    glyphSvg: await glyph(product),
  });
  const png = await page.screenshot({ type: 'png' });

  // PNG → WebP via le canvas de Chromium (aucune dépendance supplémentaire)
  const webp = await encoder.evaluate(async (b64) => {
    const img = new Image();
    img.src = 'data:image/png;base64,' + b64;
    await img.decode();
    const canvas = document.createElement('canvas');
    canvas.width = img.naturalWidth;
    canvas.height = img.naturalHeight;
    canvas.getContext('2d').drawImage(img, 0, 0);
    return canvas.toDataURL('image/webp', 0.88).split(',')[1];
  }, png.toString('base64'));

  await writeFile(join(outDir, `${product.file}.webp`), Buffer.from(webp, 'base64'));
  count++;
}

await browser.close();
console.log(`${count} carte(s) générée(s) dans ${outDir}`);
