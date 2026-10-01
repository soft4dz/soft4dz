# Design System Master File

> **LOGIC:** When building a specific page, first check `design-system/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules below.

---

**Project:** Soft4dz  
**Updated:** 2026-07-10  
**Category:** Digital Products Marketplace (adapted from UI/UX Pro Max)

> Adaptation notes: Pro Max suggested indigo + Bodoni. Overridden to **SaaS trust blue** + **E-commerce Clean** typography to avoid AI-purple / luxury-serif clichés while keeping marketplace conversion patterns.

---

## Global Rules

### Color Palette

| Role | Hex | CSS Variable |
|------|-----|--------------|
| Primary | `#2563EB` | `--color-primary` |
| Secondary | `#3B82F6` | `--color-secondary` |
| CTA/Accent | `#F97316` | `--color-cta` |
| Success | `#16A34A` | `--color-success` |
| Background | `#F8FAFC` | `--color-background` |
| Surface | `#FFFFFF` | `--color-surface` |
| Text | `#1E293B` | `--color-text` |
| Muted | `#64748B` | `--color-muted` |
| Line | `#E2E8F0` | `--color-line` |

**Dark mode**

| Role | Hex |
|------|-----|
| Background | `#0B1220` |
| Surface | `#111827` |
| Text | `#E2E8F0` |
| Primary | `#60A5FA` |
| CTA | `#FB923C` |

**Color Notes:** Trust blue + orange CTA contrast (SaaS General). Buy/success actions use green.

### Typography

- **Heading Font:** Rubik
- **Body Font:** Nunito Sans
- **Mood:** ecommerce, clean, shopping, conversion
- **Google Fonts:** [Rubik + Nunito Sans](https://fonts.google.com/share?selection.family=Nunito+Sans:wght@300;400;500;600;700|Rubik:wght@400;500;600;700;800)

```css
@import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700&family=Rubik:wght@400;500;600;700;800&display=swap');
```

### Spacing Variables

| Token | Value |
|-------|-------|
| `--space-xs` | `4px` |
| `--space-sm` | `8px` |
| `--space-md` | `16px` |
| `--space-lg` | `24px` |
| `--space-xl` | `32px` |
| `--space-2xl` | `48px` |
| `--space-3xl` | `64px` |

### Shadow Depths

| Level | Value |
|-------|-------|
| `--shadow-sm` | `0 1px 2px rgba(15,23,42,0.05)` |
| `--shadow-md` | `0 4px 12px rgba(15,23,42,0.08)` |
| `--shadow-lg` | `0 12px 28px rgba(15,23,42,0.12)` |

---

## Component Specs

### Buttons

- Primary CTA: orange `#F97316`, white text, radius 10px, min-height 48px
- Brand/secondary: blue `#2563EB`
- Success/buy confirm: green `#16A34A`
- Transitions: `background-color, transform, box-shadow` 200ms — never `transition: all`
- Active: `scale(0.96)`

### Cards

- White surface, soft shadow (not heavy borders)
- Hover: lift via shadow only (no layout-shifting scale on cards)

### Inputs

- Height ≥ 48px, radius 10px, border `#E2E8F0`
- Focus: blue ring `0 0 0 3px rgba(37,99,235,0.2)`
- Always visible labels (never placeholder-only)

---

## Style Guidelines

**Style:** Flat Design + Marketplace blocks  
**Pattern:** Marketplace / Directory — search-first hero, category blocks, featured listings, trust band  
**Effects:** 150–300ms hovers, bold type hierarchy, large section gaps (32–48px)

---

## Anti-Patterns (Do NOT Use)

- ❌ Indigo / purple / violet gradients
- ❌ Dark mode as default for auth
- ❌ Split-screen auth with feature lists + testimonials + stats
- ❌ Serif luxury display fonts
- ❌ Emojis as icons
- ❌ Invisible focus states
- ❌ `transition: all`

---

## Pre-Delivery Checklist

- [ ] Contrast ≥ 4.5:1
- [ ] Touch targets ≥ 44×44
- [ ] `cursor: pointer` on clickables
- [ ] `prefers-reduced-motion` respected
- [ ] Responsive 375 / 768 / 1024 / 1440
