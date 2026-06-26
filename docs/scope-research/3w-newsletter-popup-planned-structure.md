# 3W Newsletter Popup Planned Structure

## Scope and Intent
Add a second standalone WordPress/WooCommerce plugin for local newsletter email capture. Keep it independent from existing category markup pricing plugin.

## Current Shape
- `category-markup/` contains existing pricing plugin.
- `upload_plugin_lftp.sh` deploys plugin content to `wp-content/plugins`.
- No build system or test harness exists for plugin work.

## Planned Shape
```text
3w-newsletter-popup/
  3w-newsletter-popup.php
  assets/
    newsletter-popup.js
    newsletter-popup.css
```

## File List
- `3w-newsletter-popup/3w-newsletter-popup.php`
  - Plugin header and constants.
  - Activation hook and subscriber table creation.
  - Settings registration/admin page.
  - Subscriber list rendering and CSV export.
  - Frontend enqueue/targeting.
  - `admin-ajax.php` submit handler.
- `3w-newsletter-popup/assets/newsletter-popup.js`
  - 7-second delayed modal display.
  - `localStorage` 30-day suppression.
  - Form validation light guard.
  - AJAX submit and success/error UI.
- `3w-newsletter-popup/assets/newsletter-popup.css`
  - Modal layout, overlay, form, responsive states.

## Responsibility Changes
- Existing `category-markup/` remains pricing-only.
- New plugin owns marketing popup capture only.
- WordPress admin owns coupon text configuration.
- WooCommerce coupon system remains manually managed by store admin.

## Dependency Notes
- Runtime dependencies: WordPress, WooCommerce page helpers when available.
- No npm/composer dependency.
- No direct coupling to `category-markup`.
- No external email/campaign provider.

## Review Diff Basis
Implementation review should inspect:
- All files under `3w-newsletter-popup/`.
- No required edits to `category-markup/`.
- Optional deploy script changes only if user requests multi-plugin upload support.
