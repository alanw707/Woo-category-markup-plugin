# Current Mobile Storefront UX/A11y Plan

## Current state evidence

Live URL checked: `https://shop.3wdistributing.com/`

Captured evidence:

- `docs/screenshots/current-mobile-reanalysis/360.png`
- `docs/screenshots/current-mobile-reanalysis/390.png`
- `docs/screenshots/current-mobile-reanalysis/430.png`
- `docs/screenshots/current-mobile-reanalysis/768.png`
- `docs/screenshots/current-mobile-reanalysis/1440.png`
- `docs/screenshots/current-mobile-reanalysis/report.txt`

Important correction: live body classes still show Porto active:

```text
wp-theme-porto theme-porto wpb-js-composer vc_responsive
```

So the site was not observed as a default WordPress theme in the live DOM. Treat the active delivery path as current live Porto + site-specific hotfix plugin unless admin/theme files prove otherwise.

Current active hotfix marker found:

```text
#threew-storefront-polish-hotfix-css = true
```

## Mobile-first findings

### P0 — Product cards remain too narrow on phones

At current live state:

- 360px: product cards approx `126px` wide.
- 390px: product cards approx `141px` wide.
- 430px: product cards approx `161px` wide.

This keeps product names and finance copy cramped. Highest-impact mobile fix: use a single-column product list under `430px`, or make each card substantially wider.

### P0 — Finance copy still noisy

Affirm text repeats full promotional copy inside each small card:

```text
0% APR or as low as $122/mo with Affirm. See if you qualify
```

On mobile this competes with product name/price. Keep price primary; finance copy secondary.

### P1 — Header controls inconsistent

Mobile evidence:

- Logo: `50x47`
- Search button: `36x40`
- Mobile menu: `44x44`
- Cart/mini-cart: `22x32`

Search and cart should be at least `44x44` touch targets.

### P1 — Product title hierarchy is weak

Titles are full in DOM but visually truncated. Use 2-line clamp with enough width and line-height. Avoid single-word fragments like `Eventuri Merc...` where possible.

### P1 — Floating WhatsApp remains close to product content

Mobile chat button is `56x56` at bottom-right. It is useful, but page needs enough bottom padding and safe positioning so it does not cover product copy.

### P2 — Tablet carousel regression risk

At 768px, product carousel items report negative x positions from Owl carousel. This may be normal carousel state, but mobile fixes must not worsen tablet/desktop carousel behavior.

## Delivery plan

### Delivery location

Use the site-specific hotfix plugin as the current safe delivery path:

```text
storefront-polish-hotfix/threew-storefront-polish-hotfix.php
```

Reason:

- Current repo does not include active theme files.
- Parent theme edits are forbidden.
- A Porto child theme is not confirmed available.
- The live site already loads this hotfix plugin.

Document this as intentionally retained until the active theme/child-theme file path is available.

### Implementation changes

1. Mobile product grid:
   - Single column at `max-width: 430px`.
   - Make card image area larger and consistent.
   - Keep title to 2 useful lines.
2. Finance copy:
   - Reduce font size/line-height.
   - Prevent hard line breaks where possible.
   - If markup allows, hide excess copy after 2 lines.
3. Header controls:
   - Force search/cart/menu controls to `44x44` minimum.
   - Preserve dark-header contrast.
4. Chat button:
   - Keep `56x56` mobile size.
   - Add bottom page padding and safe-area-aware positioning.
   - Add/verify accessible label.
5. Focus/labels:
   - Keep visible `:focus-visible` ring.
   - Ensure add-to-cart and quick-view icon controls have product-specific labels.

## Rollback

- Revert `storefront-polish-hotfix/threew-storefront-polish-hotfix.php` to previous version.
- Purge LiteSpeed/Porto cache.
- Re-capture 360/390/430 screenshots.

## Verification checklist

Primary mobile checks:

- 360px screenshot/DOM
- 390px screenshot/DOM
- 430px screenshot/DOM

Pass criteria:

- Product cards no longer squeezed to 126/141/161px in a two-column layout under 430px.
- Product image boxes are consistent.
- Product title, price, and finance text have clear hierarchy.
- Header search/cart/menu targets are at least 44px.
- WhatsApp has accessible label and does not cover final content.
- Focus ring exists on interactive controls.

Regression checks:

- 768px screenshot/DOM: no obvious carousel/layout break.
- 1440px screenshot/DOM: desktop remains usable.

Cache/deployment:

- Deploy plugin update.
- Activate/retain hotfix plugin intentionally.
- Purge style cache via route or WP/cache UI.
