/**
 * Génère les visuels produits (WebP 1200×900, format 4:3 des cartes du site).
 *
 * Usage : node tools/product-images/generate.mjs [--only=netflix,spotify] [--out=assets/images/products]
 * Prérequis : Playwright (npm i -D playwright) et un Chromium installé.
 *
 * Pour un nouveau produit : l'ajouter dans catalog.json (titre, famille, noms en base),
 * relancer ce script puis `php database/assign_product_images.php`.
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
const iconCache = new Map();
async function icon(name) {
  if (!iconCache.has(name)) {
    const svg = await readFile(join(here, 'icons', `${name}.svg`), 'utf8');
    iconCache.set(name, svg.replace(/ width="16" height="16"/, ''));
  }
  return iconCache.get(name);
}

await mkdir(outDir, { recursive: true });
const browser = await chromium.launch(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {});
const page = await browser.newPage({ viewport: { width: 1200, height: 900 } });
await page.goto(pathToFileURL(join(here, 'template.html')).href);
const encoder = await browser.newPage();

let count = 0;
for (const product of catalog.products) {
  if (only && !only.has(product.file)) continue;
  const palette = catalog.families[product.family];
  if (!palette) throw new Error(`Famille inconnue « ${product.family} » pour ${product.file}`);

  await page.evaluate((data) => window.render(data), {
    title: product.title,
    badge: product.badge || '',
    palette,
    iconSvg: await icon(product.icon || palette.icon),
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
    return canvas.toDataURL('image/webp', 0.86).split(',')[1];
  }, png.toString('base64'));

  await writeFile(join(outDir, `${product.file}.webp`), Buffer.from(webp, 'base64'));
  count++;
}

await browser.close();
console.log(`${count} visuel(s) généré(s) dans ${outDir}`);
