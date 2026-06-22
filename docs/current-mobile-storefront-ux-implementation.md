# Current Mobile Storefront UX Implementation Log

## Delivery

Implemented in existing site-specific plugin:

```text
storefront-polish-hotfix/threew-storefront-polish-hotfix.php
```

Version: `1.1.1`

Reason plugin remains intentionally retained:

- Live DOM still reports `wp-theme-porto theme-porto`; the current active theme files are not present in this repo.
- Editing a parent/default theme directly is unsafe.
- The live site already loads `#threew-storefront-polish-hotfix-css`.
- Therefore the hotfix plugin is the confirmed safe delivery path until a child theme or active theme file path is provided.

## Changes shipped

Mobile-first fixes:

- Product sliders become single-column at `max-width: 430px`.
- Mobile cards are constrained to viewport/container width with no horizontal overflow.
- Product image area uses `4 / 3` ratio under 430px with bounded height.
- Product titles keep 2-line clamp behavior.
- Affirm/payment copy stays smaller/secondary.
- Header/menu/cart targets get 44px minimum hit areas.
- Desktop side-nav search restored to a usable width.
- Mobile header search is visible, and the opened mobile menu gets its own search row.
- Visible `:focus-visible` outline added.
- WooCommerce add-to-cart buttons receive product-specific `aria-label` values when missing.
- WhatsApp and quick-view icon controls get JS fallback labels when missing.
- WhatsApp remains compact on mobile with safe-area-aware positioning.

## Deployment

Uploaded by FTPS:

```text
REMOTE_PLUGIN_SLUG=storefront-polish-hotfix
LOCAL_PLUGIN_FILE=storefront-polish-hotfix/threew-storefront-polish-hotfix.php
REMOTE_PLUGIN_FILE=threew-storefront-polish-hotfix.php
```

Upload result: success.

Cache purge route could not be called because `WP_USER` / `WP_APP_PASS` were not present in `.env`. Verification used cache-busting query strings.

## Verification

Syntax/static:

```text
php -l storefront-polish-hotfix/threew-storefront-polish-hotfix.php
No syntax errors detected
```

Implementation markers verified in plugin:

- `Version: 1.1.0`
- `max-width: 430px`
- `focus-visible`
- `woocommerce_loop_add_to_cart_args`
- `Chat with us on WhatsApp`
- `#mini-cart > a`
- `aspect-ratio: 4 / 3`

Final screenshots:

- `docs/screenshots/current-mobile-after-fix/360.png`
- `docs/screenshots/current-mobile-after-fix/390.png`
- `docs/screenshots/current-mobile-after-fix/430.png`
- `docs/screenshots/current-mobile-after-fix/768.png`
- `docs/screenshots/current-mobile-after-fix/1440.png`
- `docs/screenshots/current-mobile-after-fix/report.txt`

Final DOM measurements:

```text
360px cards: 270x354, scrollW=360, clientW=360
390px cards: 300x374, scrollW=390, clientW=390
430px cards: 340x374, scrollW=430, clientW=430
```

Before fix:

```text
360px cards: 126px wide
390px cards: 141px wide
430px cards: 161px wide
```

Header target checks after final fix:

```text
Search: 44x44
Mobile menu: 44x44
Mini-cart visible instance: 44x44
WhatsApp: 56x56 with aria-label="Chat with us on WhatsApp"
```

The Porto search button required a JS inline-style fallback because later theme CSS kept computed width at 36px despite the stylesheet rule.

Tablet/desktop regression check:

- 768 and 1440 screenshots captured.
- No horizontal document overflow reported: `scrollW == clientW` at checked breakpoints.

## Remaining gaps

- WordPress admin credentials were unavailable, so LiteSpeed/Porto cache purge route was not called.
- Active theme source files are not in this repo, so the hotfix plugin remains the durable delivery path for now.
- Screen-reader testing was approximated through DOM accessible-label checks; no manual assistive-technology pass was performed.
