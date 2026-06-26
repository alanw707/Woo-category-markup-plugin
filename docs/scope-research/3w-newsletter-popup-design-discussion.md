# 3W Newsletter Popup Design Discussion

## Context Summary
The store previously used a paid MonsterInsights-style popup/capture workflow. The replacement should be cheaper and owned locally: show a popup, capture email consent, store subscribers in WordPress, and show a discount coupon code.

## Design Goals
- Replace paid popup dependency with small custom plugin.
- Keep pricing plugin separate from marketing capture.
- Avoid build tooling and third-party services.
- Make captured data usable through admin list and CSV export.
- Keep customer flow small: popup, submit, coupon shown.

## Proposed Solution Shape
A standalone `3w-newsletter-popup` WordPress plugin provides:
- Activation-created custom subscriber table.
- Admin settings for coupon code, discount percent, and delay.
- Admin subscriber list and CSV export.
- Frontend delayed modal for eligible anonymous storefront visitors.
- Nonce-protected `admin-ajax.php` submit endpoint.
- Upsert by email so repeat submissions do not duplicate rows.

## Intended Placement
Use a new plugin folder:
```text
3w-newsletter-popup/
  3w-newsletter-popup.php
  assets/newsletter-popup.js
  assets/newsletter-popup.css
```
Do not modify `category-markup`; pricing and marketing capture are unrelated responsibilities.

## Architecture Patterns
- WordPress activation hook for schema setup.
- WordPress options API for settings.
- WordPress admin menu for settings/list/export.
- `admin-ajax.php` for anonymous form submission.
- Browser `localStorage` for 30-day popup suppression.
- Server-side validation for nonce, email, and consent.
- Manual syntax check plus manual WP/WooCommerce verification.

## Design Questions and Answers
- Endpoint? `admin-ajax.php`, because one nonce-protected form does not need REST route boilerplate.
- Uninstall data? Keep subscriber table, because subscribers are business data and accidental loss is worse than orphan cleanup.
- Suppression storage? `localStorage`, because server does not need browser display state.
- Coupon management? Admin creates WooCommerce coupon manually; plugin only displays configured code/percent.
- Duplicate emails? One row per email; update timestamp and show coupon again.
- First slice? Customer flow first: delayed popup, submit, DB save, coupon success.

## Tradeoffs and Rejected Options
- Rejected external email platform integration: adds cost and failure modes; CSV export is enough now.
- Rejected double opt-in: requires outbound mail flow; single opt-in with consent is accepted.
- Rejected auto coupon creation/validation: couples plugin to WooCommerce coupon internals; admin text-only setup is simpler.
- Rejected putting code in `category-markup`: mixes pricing with marketing and makes disable/remove harder.
- Rejected cookie suppression: server-readable state not needed; `localStorage` is less plumbing.
- Rejected Docker/dev setup: current repo workflow is manual WP plugin activation.

## Follow-Up Decisions
- Confirm legal/marketing copy before production launch.
- Decide later whether uninstall should offer manual data deletion tooling.
- If abuse appears, consider unique per-email coupons or rate limiting; not in first build.
- If deploy needs change, extend upload script to support `REMOTE_PLUGIN_SLUG=3w-newsletter-popup` instead of hardcoding.
