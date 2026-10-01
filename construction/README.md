# SOFT4DZ — Page en construction

## Structure du projet

```
construction/
├── index.html              ← Page principale
├── .htaccess               ← Priorité index.html
└── assets/
    ├── css/
    │   └── style.css       ← Styles (charte, responsive)
    └── img/
        └── logo-soft4dz.png ← Logo officiel SOFT4DZ
```

## Modifier le logo

Remplacez le fichier :

`assets/img/logo-soft4dz.png`

Format recommandé : PNG transparent, largeur ~560 px minimum.

## Modifier les liens (WhatsApp, Facebook, réseaux)

Ouvrez `index.html` et éditez la section **CONFIG** en bas du fichier :

```javascript
var CONFIG = {
  links: {
    whatsapp:  'https://wa.me/213XXXXXXXXX',  // ← votre numéro
    facebook:  'https://facebook.com/soft4dz',
    instagram: 'https://instagram.com/soft4dz',
    tiktok:    'https://tiktok.com/@soft4dz'
  }
};
```

Exemple WhatsApp Algérie : `https://wa.me/213555123456` (sans + ni espaces).

## Modifier les textes

Les textes principaux sont dans le HTML (section `.hero` et `.services`).
Pour le titre, sous-titre et phrase rassurante, cherchez :

- `.hero-title`
- `.hero-subtitle`
- `.hero-note`

## Modifier les couleurs

Éditez les variables CSS en tête de `assets/css/style.css` :

```css
:root {
  --blue: #0056ff;
  --turquoise: #00c8e8;
  --orange: #f97316;
  --magenta: #e11d48;
  /* … */
}
```

## Déploiement sur cPanel

Uploadez le contenu de `construction/` dans `public_html/` :

- `index.html`
- `assets/css/style.css`
- `assets/img/logo-soft4dz.png`
- `.htaccess`

## Prévisualisation locale

Ouvrez `construction/index.html` dans le navigateur, ou servez le dossier via XAMPP.
