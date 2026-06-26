# Mobile storefront 9-point validation

Goal mapping for `address all issues in 9 recommended order validate 1 by 1`.

Implementation artifact:
- `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`
- Current deployed version: `1.2.98`

Primary live validator:
- `node tools/validate-mobile-storefront-nine.js`
- Result: `pass: true`

Supporting probe:
- `node tools/probe-mobile-category-header.js`
- Result: `pass: true`

## 1. Home header row alignment
- Fix: aligned logo, search, cart, menu on one baseline.
- Validation: item `1` in `tools/validate-mobile-storefront-nine.js`.
- Evidence: logo/search/cart/menu all at `y=14` on 375px mobile viewport.

## 2. Home search containment and cart hitbox
- Fix: stopped the search form from overflowing into the cart tap area.
- Validation: item `2` in `tools/validate-mobile-storefront-nine.js`.
- Evidence: search `right=259`, cart `x=269`, cart center topmost element resolves inside the cart.

## 3. Home mini-cart panel presentation
- Fix: replaced the raw white popover feel with a rounded dropdown panel under the header.
- Validation: item `3` in `tools/validate-mobile-storefront-nine.js`.
- Evidence: popup `x=12 y=80 w=351 h=124 borderRadius=18px`.
- Screenshot: `docs/cart-open-home-mobile-after-v1298.png`

## 4. Home mini-cart dismiss behavior
- Fix: added outside-click / Escape dismissal.
- Validation: item `4` in `tools/validate-mobile-storefront-nine.js`.
- Evidence: `cartDismissed: true` after clicking outside the popup.

## 5. Category compact breadcrumb header
- Fix: replaced stacked taxonomy crumbs with a compact mobile archive header.
- Validation: item `5` in `tools/validate-mobile-storefront-nine.js`.
- Evidence: compact header present; original `.breadcrumb` hidden.
- Screenshot: `docs/category-page-after-archive-header-v1298.png`

## 6. Category visible mobile title
- Fix: surfaced the current archive title as a visible mobile heading.
- Validation: item `6` in `tools/validate-mobile-storefront-nine.js`.
- Evidence: title text `W465`, height `31px` on live page.
- Screenshot: `docs/category-page-after-archive-header-v1298.png`

## 7. Category toolbar compact controls
- Fix: converted filter / sort / count into a compact 3-control grid and removed top page-number clutter.
- Validation: item `7` in `tools/validate-mobile-storefront-nine.js`.
- Evidence: toolbar `display=grid`; filter `68x44`; sort `140x44`; count `56x44`.
- Screenshot: `docs/category-page-after-toolbar-v1298b.png`

## 8. Category product card shell
- Fix: added card boundary, radius, shadow, and padded content for archive products.
- Validation: item `8` in `tools/validate-mobile-storefront-nine.js`.
- Evidence: first live card has `borderRadius=20px`, solid border, non-none shadow.
- Screenshot: `docs/category-page-after-cards-v1298.png`

## 9. State consistency and safe-area chat sizing
- Fix: added shared focus/active state rules for toolbar controls and cart CTA; reduced catalog WhatsApp button to `44x44` in the lower-right safe area.
- Validation: item `9` in `tools/validate-mobile-storefront-nine.js`.
- Evidence:
  - source contains `:focus-visible` rules for filter, sort, count, and cart CTA
  - live WhatsApp button measures `44x44`
- Screenshot: `docs/category-page-after-chat-v1298b.png`

## Extra checks
- `php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php` -> no syntax errors
- `node tools/check-home-header-four-issues.js` -> pass
- `bash upload_plugin_lftp.sh storefront-polish-hotfix` -> upload success
