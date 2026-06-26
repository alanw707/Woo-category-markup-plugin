# Mobile Product Page Polish Planned Structure

## Scope and Intent
Fix five validated mobile product page visual/accessibility findings with the smallest safe hotfix change.

## Current Shape
- `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`
  - Defines frontend context helpers.
  - Injects global/mobile CSS in one `wp_head` style block.
  - Renders `.threew-mobile-header-search` in `wp_footer`.
  - Adds mobile search/menu JS in `wp_footer`.

## Planned Shape
- `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`
  - Keep existing plugin boundaries.
  - Add/adjust CSS only for `body.single-product` under mobile media rules.
  - Reuse existing focus ring token and mobile search markup.
  - Only touch JS/PHP attributes if an accessible name is missing after inspection.

## File List
- Change: `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`
- Reference only: `/mnt/c/Users/alanw/Pictures/Screenshots/gshot-2026-06-26-055830-RCpa.png`

## Responsibility Changes
- Header/search CSS: product-page layout polish.
- Product summary CSS: hierarchy and CTA visibility.
- Gallery CSS: carousel control placement.
- Accessibility: maintain visible focus and labels.

## Dependency Notes
- Depends on Porto/WooCommerce selectors already present in rendered page.
- No new package, build step, shortcode, or plugin.

## Review Diff Basis
Review against this plan plus before/after 375px mobile screenshot. Syntax gate: `php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php`.
