# 3W Newsletter Popup Plan

## Plan Status
Ready for implementation. Source: `docs/scope-research/3w-newsletter-popup-foundation.md`.

## Preconditions
- WordPress/WooCommerce site available for manual verification.
- Admin creates matching WooCommerce coupon manually.
- Plugin remains separate from `category-markup`.

## Clarifications Resolved
- Endpoint: `admin-ajax.php`, nonce-protected.
- Uninstall: keep subscriber table/data.
- Suppression: browser `localStorage` timestamp for 30 days.
- Delay default: 7 seconds.

## Design Summary
Create build-tool-free plugin `3w-newsletter-popup/` with one PHP main file and two frontend assets. Activation creates a custom subscriber table keyed by email. Frontend shows delayed popup to logged-out eligible visitors, posts email + consent to AJAX, then displays configured coupon code/discount percent. Admin page manages settings, subscriber list, and CSV export.

## Structure Summary
Current repo contains one standalone WooCommerce plugin and FTPS deploy script. Planned work adds a second standalone plugin folder, no shared runtime dependency with `category-markup`.

## Solution Path
1. Create plugin shell and data table.
2. Add admin settings/list/export so coupon text and captured data are usable.
3. Add frontend popup assets and targeting.
4. Add AJAX validation/upsert path.
5. Verify manually in WP/WooCommerce.

## Task Breakdown

### T1. Plugin skeleton and subscriber table
- Files: `3w-newsletter-popup/3w-newsletter-popup.php`
- Action: Add plugin header, constants, activation hook, table name helper, `dbDelta` schema with unique email and columns for email, consent, IP, user agent, created/updated timestamps.
- Depends on: none
- Rollback: delete `3w-newsletter-popup/` before activation; after activation leave table/data intact unless manually removed.
- Parallel: no
- Risk: medium
- Review required: yes
- Verify: `php -l 3w-newsletter-popup/3w-newsletter-popup.php`

### T2. Admin settings, subscriber list, CSV export
- Files: `3w-newsletter-popup/3w-newsletter-popup.php`
- Action: Add admin menu page with coupon code, discount percent, delay seconds settings; render subscriber table; add admin-only nonce-protected CSV export.
- Depends on: T1
- Rollback: remove admin hooks/settings code; data table remains safe.
- Parallel: no
- Risk: medium
- Review required: yes
- Verify: `php -l 3w-newsletter-popup/3w-newsletter-popup.php`

### T3. Frontend assets and targeting
- Files: `3w-newsletter-popup/3w-newsletter-popup.php`, `3w-newsletter-popup/assets/newsletter-popup.js`, `3w-newsletter-popup/assets/newsletter-popup.css`
- Action: Enqueue JS/CSS only for logged-out public visitors outside cart/checkout/account; inject AJAX URL, nonce, delay, suppression days, coupon code, discount percent, and copy.
- Depends on: T2
- Rollback: remove enqueue hook and assets.
- Parallel: no
- Risk: medium
- Review required: yes
- Verify: `php -l 3w-newsletter-popup/3w-newsletter-popup.php`

### T4. AJAX submit validation and upsert
- Files: `3w-newsletter-popup/3w-newsletter-popup.php`, `3w-newsletter-popup/assets/newsletter-popup.js`
- Action: Register `wp_ajax_nopriv` submit action; validate nonce, email, and consent; sanitize inputs; insert/update one row per email; return coupon payload; JS handles success/error and stores suppression timestamp.
- Depends on: T3
- Rollback: remove AJAX action and JS submit handler; table remains safe.
- Parallel: no
- Risk: high
- Review required: yes
- Verify: `php -l 3w-newsletter-popup/3w-newsletter-popup.php`

### T5. Manual verification notes
- Files: `docs/scope-research/3w-newsletter-popup-plan.md`
- Action: Run syntax checks and verify in WP admin/storefront: activate plugin, configure settings, submit popup logged out, confirm row/list/export, confirm suppression and page exclusions.
- Depends on: T4
- Rollback: deactivate plugin; keep data.
- Parallel: no
- Risk: low
- Review required: no
- Verify: `php -l 3w-newsletter-popup/3w-newsletter-popup.php`

## Requirements Traceability
- Local capture/export: T1, T2, T4.
- Configurable coupon display: T2, T3, T4.
- 30-day delayed popup suppression: T3, T4.
- Logged-in/cart/checkout/account suppression: T3.
- Separate build-tool-free plugin: T1, T3.
- First vertical slice customer flow: T1, T3, T4.

## Constraints
- Separate plugin required: `docs/scope-research/3w-newsletter-popup-foundation.md:35`.
- Low-cost/build-tool-free required: `docs/scope-research/3w-newsletter-popup-foundation.md:36`, `:74`.
- Coupon code/percent are display settings only: `docs/scope-research/3w-newsletter-popup-foundation.md:37`.
- Suppress for 30 days and logged-in/cart/checkout/account: `docs/scope-research/3w-newsletter-popup-foundation.md:40-41`.
- Custom DB table with unique email: `docs/scope-research/3w-newsletter-popup-foundation.md:44`.
- Nonce protection required: `docs/scope-research/3w-newsletter-popup-foundation.md:56`.
- Manual WP activation workflow exists: `README.md:21-22`.

## Validation
| Task | Check | Expected |
| --- | --- | --- |
| T1-T4 | `php -l 3w-newsletter-popup/3w-newsletter-popup.php` | No syntax errors |
| T1 | Activate plugin in WP Admin | Table exists; no activation fatal |
| T2 | Save settings and export CSV as admin | Settings persist; CSV downloads; non-admin blocked |
| T3 | Visit storefront logged out/incognito | Popup appears after configured delay |
| T3 | Visit logged in/cart/checkout/account | Popup does not appear |
| T4 | Submit valid email + consent | Subscriber row stored/updated; coupon shown |
| T4 | Submit invalid email or missing consent | Error returned; no valid subscriber row added |
| T4 | Reload after success | Popup suppressed for 30 days via `localStorage` |

## Replan Triggers
- Need to send emails, integrate ESP, or double opt-in.
- Admin wants automatic WooCommerce coupon creation/validation.
- Legal requires different consent/data retention behavior.
- Theme blocks modal display or conflicts require broader frontend design work.
- Deployment script must support multiple plugin slugs before shipping.

## Implementation Verification Notes
- `php -l 3w-newsletter-popup/3w-newsletter-popup.php`: passed after T1, T2, T3, T4, and final CSV status-code fix.
- Manual WP/WooCommerce checks not run in this workspace; requires active WordPress admin/storefront.
