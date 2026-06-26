# Mobile Product Page Polish Plan

## Plan Status
Ready. Source finding: mobile product screenshot `gshot-2026-06-26-055830-RCpa.png` plus current hotfix selectors.

## Preconditions
- Work stays in existing hotfix plugin: `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`.
- No new plugin, theme rewrite, JS library, or checkout behavior change.
- Validate on a mobile single product page around 375px width.

## Clarifications Resolved
- Primary target: WooCommerce single product mobile page, not homepage/catalog.
- Accessibility review included with visual polish.
- Fix the 5 validated findings below; skip broader redesign.

## Design Summary
Use the existing CSS/JS hotfix layer. Add a small single-product mobile CSS block and minimal ARIA/tap-target fixes. Keep Porto markup intact.

## Structure Summary
Current: one large hotfix plugin injects global CSS at `wp_head` and mobile search markup/JS at `wp_footer`.
Planned: same file, new single-product rules near existing mobile header search rules and existing focus styles.
Dependencies: WooCommerce/Porto class names only; no new dependencies.

## Validated Findings
1. Header search is cramped/weak: screenshot shows narrow low-contrast search; existing mobile search CSS exists at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:1750-1879`.
2. Header icons/search spacing inconsistent: screenshot shows logo/search/cart/menu squeezed; existing touch rules start at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:1083`.
3. Product title and carousel arrows compete: screenshot shows arrows beside title; no single-product carousel polish found in current selectors.
4. Breadcrumb trail is too dense/noisy: screenshot shows long uppercase breadcrumb consuming vertical space.
5. Add-to-cart action is below fold/weak: screenshot shows CTA cut off; product pages are currently treated as payment context at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:29`, so avoid payment-script stripping and use CSS-only CTA visibility.

## Solution Path
1. Tighten mobile header/search layout on product pages only.
2. Add single-product typography/breadcrumb/carousel rules.
3. Improve CTA visibility without sticky checkout complexity unless needed.
4. Confirm accessible names/focus/tap targets.
5. Visual regression check against screenshot.

## Task Breakdown

### T1. Product mobile header/search polish
- Files: `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`
- Action: On `body.single-product` mobile, make `.threew-mobile-header-search` wider/taller enough, improve input contrast, and set consistent gaps/tap targets for cart/menu/search controls.
- Depends on: none
- Rollback: remove the `body.single-product` header/search CSS block.
- Parallel: no
- Risk: medium
- Review required: yes
- Verify: `php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php`

### T2. Breadcrumb density reduction
- Files: `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`
- Action: On mobile single-product pages, clamp/hide overflow in `.breadcrumb`/WooCommerce breadcrumb trail and reduce visual weight.
- Depends on: none
- Rollback: remove breadcrumb-specific CSS.
- Parallel: yes
- Risk: low
- Review required: no
- Verify: `php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php`

### T3. Product title, price, and meta spacing
- Files: `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`
- Action: Normalize mobile title line-height/spacing, keep price/APR grouped, and de-emphasize SKU/category/tag metadata.
- Depends on: none
- Rollback: remove product summary CSS block.
- Parallel: yes
- Risk: low
- Review required: no
- Verify: `php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php`

### T4. Carousel arrow placement and tap targets
- Files: `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`
- Action: Move single-product gallery/owl nav controls away from the title flow and ensure previous/next controls have visible 44px tap targets and focus rings.
- Depends on: T3
- Rollback: remove gallery/owl nav CSS.
- Parallel: no
- Risk: medium
- Review required: yes
- Verify: `php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php`

### T5. Add-to-cart visibility and accessibility pass
- Files: `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`
- Action: Tighten quantity/button block spacing so CTA appears sooner; verify search/cart/menu/carousel/add-to-cart accessible names and focus-visible styles.
- Depends on: T1, T3, T4
- Rollback: remove CTA/accessibility CSS/attribute adjustments.
- Parallel: no
- Risk: medium
- Review required: yes
- Verify: `php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php`

## Requirements Traceability
- Header/search issue → T1
- Breadcrumb noise → T2
- Title/price/meta hierarchy → T3
- Carousel arrows overlap title → T4
- CTA visibility + accessibility → T5

## Constraints
- Keep product pages excluded from payment stripping: `threew_storefront_is_payment_context()` includes `is_product()` at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:29`.
- Existing focus-visible ring is global at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:1065-1072`; reuse it.
- Existing mobile search markup has ARIA labels at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:2786-2792`; do not duplicate labels unnecessarily.

## Validation
| Task | Check | Command |
| --- | --- | --- |
| T1-T5 | PHP syntax | `php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php` |
| T1 | Search visible/tappable at 375px | manual screenshot compare |
| T2 | Breadcrumb no longer dominates first fold | manual screenshot compare |
| T3 | Title/price readable, metadata subdued | manual screenshot compare |
| T4 | Carousel arrows no longer collide with title | manual screenshot compare |
| T5 | Add-to-cart visible sooner; focus rings visible | keyboard/mobile manual check |

## Replan Triggers
- Porto markup uses different classes on live product pages than screenshot/dev page.
- CTA requires sticky bottom behavior instead of spacing-only improvement.
- Theme/plugin update removes `.threew-mobile-header-search` or gallery owl selectors.
