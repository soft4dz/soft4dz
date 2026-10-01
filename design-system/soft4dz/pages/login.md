# Login Page Overrides

> **PROJECT:** Soft4dz  
> Overrides `design-system/soft4dz/MASTER.md`

---

## Layout

- **Pattern:** Minimal Single Column (not split-screen)
- **Max form width:** 400px
- **Background:** light `#F8FAFC` with subtle blue radial wash
- **Brand:** Soft4dz logo/name as hero-level signal above the form
- **Content budget:** brand + title + short subtitle + form + 1 secondary path (register)
- **Remove:** left marketing panel, feature rows, quote, stats, social proof blocks

## Typography

- Title: Rubik 800, ~1.75rem, left-aligned
- Subtitle: Nunito Sans, muted, one line

## Components

- Single primary CTA: « Se connecter » in orange CTA color
- Social buttons secondary (outline), 2-column
- Errors under fields with icon
- Submit shows loading state on click (disabled + label change)

## Motion

- Form enter: fade + 8px translateY, 200ms, respect `prefers-reduced-motion`
- Button press: scale 0.96

## Mobile

- Full-bleed form, 16–24px horizontal padding
- Same single-column layout (no alternate desktop chrome)
