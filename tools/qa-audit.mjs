import { readFile } from "node:fs/promises";

const BASE_URL = process.env.BASE_URL || "http://localhost/soft4dz";

function hexToRgb(hex) {
  const clean = hex.replace("#", "").trim();
  if (clean.length !== 6) return null;
  const r = Number.parseInt(clean.slice(0, 2), 16);
  const g = Number.parseInt(clean.slice(2, 4), 16);
  const b = Number.parseInt(clean.slice(4, 6), 16);
  return [r, g, b];
}

function luminance([r, g, b]) {
  const srgb = [r, g, b].map((v) => {
    const c = v / 255;
    return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
  });
  return 0.2126 * srgb[0] + 0.7152 * srgb[1] + 0.0722 * srgb[2];
}

function contrastRatio(hexA, hexB) {
  const rgbA = hexToRgb(hexA);
  const rgbB = hexToRgb(hexB);
  if (!rgbA || !rgbB) return 0;
  const l1 = luminance(rgbA);
  const l2 = luminance(rgbB);
  const lighter = Math.max(l1, l2);
  const darker = Math.min(l1, l2);
  return (lighter + 0.05) / (darker + 0.05);
}

function parseThemeVars(css, themeSelector) {
  const start = css.indexOf(themeSelector);
  if (start === -1) return {};
  const braceStart = css.indexOf("{", start);
  const braceEnd = css.indexOf("}", braceStart);
  const block = css.slice(braceStart + 1, braceEnd);
  const vars = {};
  for (const line of block.split("\n")) {
    const m = line.match(/--([a-z0-9-]+)\s*:\s*(#[0-9a-fA-F]{6})/);
    if (m) vars[`--${m[1]}`] = m[2];
  }
  return vars;
}

async function checkUrls() {
  const pages = [
    "/",
    "/products",
    "/products/windows-11-pro",
    "/cart",
    "/checkout",
    "/login",
    "/register",
    "/admin/login",
  ];
  const results = [];
  for (const path of pages) {
    const url = `${BASE_URL}${path}`;
    try {
      const res = await fetch(url, { redirect: "follow" });
      const html = await res.text();
      results.push({
        path,
        ok: res.status < 500,
        status: res.status,
        hasBottomNav: html.includes("bottom-nav"),
        hasSearchModal: html.includes("search-modal"),
        hasCartDrawer: html.includes("cart-drawer"),
      });
    } catch (error) {
      results.push({
        path,
        ok: false,
        status: "ERR",
        error: String(error),
        hasBottomNav: false,
        hasSearchModal: false,
        hasCartDrawer: false,
      });
    }
  }
  return results;
}

async function run() {
  const novaCss = await readFile(new URL("../assets/css/nova.css", import.meta.url), "utf8");

  const darkVars = parseThemeVars(novaCss, ":root,");
  const lightVars = parseThemeVars(novaCss, '[data-theme="light"]');

  const checks = [
    ["text-primary/bg-body", "--text-primary", "--bg-body", 4.5],
    ["text-secondary/bg-body", "--text-secondary", "--bg-body", 4.5],
    ["primary-light/bg-body", "--primary-light", "--bg-body", 3.0],
    ["cta/bg-card", "--cta", "--bg-card", 3.0],
  ];

  const wcag = {
    dark: checks.map(([name, fg, bg, min]) => {
      const ratio = contrastRatio(darkVars[fg] || "#ffffff", darkVars[bg] || "#000000");
      return { name, ratio: Number(ratio.toFixed(2)), min, pass: ratio >= min };
    }),
    light: checks.map(([name, fg, bg, min]) => {
      const ratio = contrastRatio(lightVars[fg] || "#000000", lightVars[bg] || "#ffffff");
      return { name, ratio: Number(ratio.toFixed(2)), min, pass: ratio >= min };
    }),
  };

  const rtlSelectors = [
    "[dir=\"rtl\"] .cart-drawer",
    "[dir=\"rtl\"] .mega-menu",
    "[dir=\"rtl\"] .chatbot-window",
    "[dir=\"rtl\"] .navbar-search-input",
  ];
  const rtl = rtlSelectors.map((s) => ({ selector: s, present: novaCss.includes(s) }));

  const urls = await checkUrls();

  const report = { baseUrl: BASE_URL, wcag, rtl, urls };
  console.log(JSON.stringify(report, null, 2));
}

run().catch((e) => {
  console.error(e);
  process.exitCode = 1;
});
