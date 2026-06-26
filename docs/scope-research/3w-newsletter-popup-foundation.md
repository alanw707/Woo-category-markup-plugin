# 3W Newsletter Popup Foundation

## Vision Summary
Build a lightweight WordPress/WooCommerce newsletter email capture popup to replace the previous MonsterInsights-style popup/capture workflow and reduce recurring cost.
The popup offers customers a configurable discount message, stores emails locally, and lets admins export subscribers.

## Actor Model
- Visitor: anonymous storefront user who sees popup, enters email, consents, receives coupon code.
- Repeat visitor: same browser/email may resubmit and see coupon again.
- Logged-in user: should not see popup.
- Store admin: configures coupon display, reviews subscribers, exports CSV, manually maintains matching WooCommerce coupon.

## Goals and Non-Goals Alignment
- Goals:
  - Capture newsletter emails from public storefront visitors.
  - Offer a configured discount percentage and coupon code after successful form submission.
  - Store subscribers in WordPress only.
  - Provide admin subscriber list and CSV export.
  - Avoid paid popup/newsletter dependency.
- Non-goals:
  - No email platform integration.
  - No double opt-in email flow.
  - No automatic WooCommerce coupon creation or validation.
  - No admin notification email.
  - No Docker/local dev environment.

## Terminology Decisions
- "Newsletter system" means local email capture and export, not campaign sending.
- "5% offer" means popup display text plus configured coupon code; WooCommerce coupon remains manually managed.
- "Submit" means valid email plus consent checkbox accepted and stored.
- "Subscriber" means one unique email row in the plugin table.

## Constraints and Assumptions
- Existing project is a WordPress/WooCommerce plugin repo with manual WP install/deploy workflow.
- Feature should be a separate plugin, not part of `category-markup`.
- Keep implementation low-cost and build-tool-free.
- Coupon code and discount percent are configurable display settings only.
- Admin is responsible for creating and maintaining the actual WooCommerce coupon.
- Single opt-in is acceptable: consent checkbox, timestamp, and IP stored.
- Suppress popup for 30 days after shown/submitted.
- Suppress popup for logged-in users and cart/checkout/account pages.

## Decision Surface
- Storage: custom DB table with unique email.
- Duplicate behavior: repeat email updates timestamp and can show coupon again.
- Popup timing: delayed display, once per visitor for 30 days.
- Targeting: all public storefront pages except suppressed contexts.
- Admin surface: settings, list, CSV export.
- Coupon: manual WooCommerce coupon; plugin displays configured code/percent.

## Recommended Stack
- WordPress plugin in `3w-newsletter-popup/`.
- PHP for activation, DB table, AJAX/REST handler, admin page, CSV export.
- Plain JavaScript for popup behavior and form submission.
- Plain CSS for modal styling.
- WordPress nonce for submission protection.

Rationale: matches existing hosting/runtime, avoids dependencies, keeps plugin portable and cheap.

## Architecture Shape
- Single plugin folder with one main PHP file plus frontend JS/CSS assets.
- Activation hook creates custom subscriber table.
- Frontend enqueue injects settings: delay, endpoint, nonce, coupon code, discount percent.
- JS handles localStorage/cookie suppression, modal display, submit, and success message.
- Server validates nonce, email, consent; upserts subscriber record.
- Admin page reads table, renders subscribers, settings form, and CSV export action.

## Bootstrap Shape
- Folder: `3w-newsletter-popup/`
- Files:
  - `3w-newsletter-popup.php` main plugin file.
  - `assets/newsletter-popup.js` frontend behavior.
  - `assets/newsletter-popup.css` popup styling.
- No package manager, build step, framework, or new dependency.

## Bootstrap Commands
```bash
mkdir -p 3w-newsletter-popup/assets
# Copy 3w-newsletter-popup/ to wp-content/plugins/
# Activate "3W Newsletter Popup" in WP Admin → Plugins
# Create matching WooCommerce coupon manually, e.g. SAVE5 at 5%
# Configure coupon code/discount percent in WP Admin plugin settings
# Visit storefront in logged-out/incognito browser and submit popup form
```

## First Vertical Slice
Build the customer-facing path first:
1. Plugin activates and creates subscriber table.
2. Logged-out storefront visitor sees delayed popup.
3. Visitor enters email, checks consent, submits.
4. Server stores/updates subscriber row.
5. Popup shows configured coupon code and discount percent.
6. Popup remains suppressed for 30 days.

This slice proves the replacement customer flow before admin polish.

## Risks and Spikes
- Legal copy/consent wording may need owner review before launch.
- Theme CSS may conflict with modal styling; verify on live storefront.
- AJAX endpoint must avoid duplicate rows and reject missing consent.
- CSV export should be admin-only and nonce-protected.
- Text-only coupon configuration can drift from actual WooCommerce coupon.

## Open Unknowns
- Exact consent checkbox wording.
- Exact popup headline/body copy.
- Exact delay duration; working assumption: 7 seconds.
- Whether uninstall should drop subscriber table; defer unless requested.

## Plan Readiness
Ready.

Foundation decisions are sufficient for planning: terminology, stack, architecture shape, bootstrap commands, and first vertical slice are explicit. Remaining unknowns are copy/policy details or small implementation defaults, not blockers.
