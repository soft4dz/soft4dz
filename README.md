# SOFT4DZ — boutique en ligne

Next.js 16 (App Router, TypeScript). Français et arabe (RTL). Paiement CIB / Edahabia via Chargily Pay, ou versement CCP.

## Démarrer

```bash
npm install
cp .env.example .env.local   # puis remplir les valeurs
npm run dev                  # http://localhost:3000
```

Sans `CHARGILY_SECRET_KEY`, le site tourne en **mode démo** : les commandes CIB/Edahabia sont marquées payées sans transaction réelle.

## Organisation

| Dossier | Rôle |
|---|---|
| `src/app/[lang]/` | Pages : accueil, `produit/[slug]`, `panier`, `paiement`, `commande/[id]` |
| `src/app/api/checkout` | Crée la commande (prix recalculés côté serveur) et le paiement Chargily |
| `src/app/api/webhooks/chargily` | Confirmation de paiement signée par Chargily |
| `src/lib/catalog.ts` | Catalogue (à migrer vers Supabase) |
| `src/lib/orders.ts` | Stockage des commandes (fichier `.data/` en dev, à migrer vers Supabase) |
| `src/lib/dictionaries.ts` | Textes FR / AR |
| `src/proxy.ts` | Redirige `/` vers `/fr` ou `/ar` selon le navigateur |

## Avant la mise en production

- Brancher Supabase (catalogue, commandes, comptes) : le disque de Vercel est en lecture seule, `.data/orders.json` ne fonctionne qu'en local.
- Renseigner la clé Chargily **live** et `CHARGILY_MODE=live`.
- Envoi automatique des clés par e-mail après paiement.
- Panneau d'administration (produits, prix, commandes, livraison des clés).
