# Mobile Product Page Polish Research

## Current State Assessment
- Target page is WooCommerce single product mobile page; requested issues are visual/accessibility polish from screenshot `gshot-2026-06-26-055830-RCpa.png`.
- Current hotfix is syntactically valid: `php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php` returned no errors.
- Product pages are treated as payment context, so product/checkout scripts must not be stripped: `threew_storefront_is_payment_context()` includes `is_product()` at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:28-30`.
- Existing CSS injection owns design tokens/focus rings in the same plugin: `--threew-focus-ring` at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:1030`; global `:focus-visible` rules at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:1066-1072`.
- Existing mobile search CSS is broad/non-home scoped, not product-specific: `.threew-mobile-header-search` rules at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:1750-1879`.
- Search markup already has accessible names: wrapper `aria-label`, input `aria-label`, and submit `aria-label` at `storefront-polish-hotfix/threew-storefront-polish-hotfix.php:2786-2792`.

## Workflow Trace
- WordPress frontend request loads hotfix plugin.
- `wp_head` emits the plugin CSS block containing tokens, focus rules, header/search styles, and mobile layout rules (`storefront-polish-hotfix/threew-storefront-polish-hotfix.php:1028+`).
- `wp_footer` emits `.threew-mobile-header-search` markup via `threew_storefront_render_mobile_header_search()` (`storefront-polish-hotfix/threew-storefront-polish-hotfix.php:2782-2797`).
- Product page payment context check keeps product pages out of aggressive stripping (`storefront-polish-hotfix/threew-storefront-polish-hotfix.php:28-30`).

## Project Slice Code Map
- `threew_storefront_is_payment_context()` decides product pages are protected/payment context (`storefront-polish-hotfix/threew-storefront-polish-hotfix.php:28-30`).
- CSS variable/focus section defines reusable accessibility baseline (`storefront-polish-hotfix/threew-storefront-polish-hotfix.php:1030`, `1066-1072`).
- Existing mobile header/search block controls `.threew-mobile-header-search`, form fields, input, and submit button (`storefront-polish-hotfix/threew-storefront-polish-hotfix.php:1750-1879`).
- `threew_storefront_render_mobile_header_search()` renders the mobile search DOM and labels (`storefront-polish-hotfix/threew-storefront-polish-hotfix.php:2782-2797`).

## File Map
| File | Evidence |
| --- | --- |
| `storefront-polish-hotfix/threew-storefront-polish-hotfix.php` | Payment context includes products at lines 28-30; focus ring at 1030 and 1066-1072; mobile search CSS at 1750-1879; mobile search labels at 2786-2792. |
| `docs/scope-research/mobile-product-page-polish-plan.md` | Ready plan, constraints, task IDs, and validation commands at lines 3-110. |
| `docs/scope-research/mobile-product-page-polish-planned-structure.md` | Planned single-file scope and file list at lines 1-24. |
| `docs/scope-research/mobile-product-page-polish-design-discussion.md` | Design decisions: CSS-only, no sticky CTA initially, no template/header rebuild at lines 1-33. |

## Structure Outline
- Current affected slice is one plugin file.
- CSS lives inside one `wp_head` style block.
- Mobile header search DOM lives in one `wp_footer` function.
- No separate stylesheet, build pipeline, or browser test harness exists for this slice.

## Verified Facts
- Build/test command for this slice is exact: `php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php`.
- Product page protection from payment/script stripping is already true via `is_product()` in payment context.
- Search accessible names are already present; duplicate ARIA is not needed unless new controls are added.
- Focus-visible baseline already exists and should be reused, not duplicated.
- Existing selectors do not include a dedicated `body.single-product` polish block for breadcrumb/title/gallery/CTA findings.

## Design Question Evidence
- Full product template redesign: no evidence needed; current plugin already provides hotfix CSS/markup seam, and plan constrains work to that file.
- Sticky CTA: current evidence only proves CTA is low in screenshot; no live validation proves sticky behavior is required. Spacing-only remains unblocked.
- Carousel JS replacement: existing file already has Owl-related CSS/JS elsewhere, but no product-gallery JS premise is proven; CSS tap-target/placement evidence is sufficient for planning.
- Accessibility: labels/focus baseline exist; implementation should only add CSS focus/tap-target support or attributes if inspection proves a missing name.

## Open Unknowns
- Exact live Porto product-gallery/breadcrumb selectors may differ from screenshot/dev markup.
- Fresh 375px screenshot still needed after implementation for visual acceptance.
- Sticky add-to-cart may become necessary only if spacing-only fails manual validation.

## Remaining Blocker
- Acceptable for planning/implementation: manual screenshot validation requires a live/mobile product page after code change.

## Plan Readiness
Ready. Facts, file seam, constraints, validation command, and remaining blocker are explicit enough for `rpi-plan`/`rpi-implement`.
