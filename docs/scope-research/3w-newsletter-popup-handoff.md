# 3W Newsletter Popup Handoff

## Status
- Current phase: `implement` complete for planned code tasks T1-T5; manual WP verification still pending.
- Recommended next phase: `rpi-review` post-implementation validation.
- Readiness: Ready.

## Objective
Ship a separate build-tool-free WordPress plugin that shows a delayed newsletter popup to eligible logged-out visitors, stores local subscribers, and lets admins configure coupon display plus export CSV.

## Source Artifact Chain
- `docs/scope-research/3w-newsletter-popup-foundation.md` — authoritative source artifact; no spec or research artifact exists.
- `docs/scope-research/3w-newsletter-popup-plan.md` — implementation plan and validation notes.
- `docs/scope-research/3w-newsletter-popup-planned-structure.md` — planned file list and review scope.
- `docs/scope-research/3w-newsletter-popup-design-discussion.md` — decisions/tradeoffs.
- `docs/scope-research/3w-newsletter-popup-handoff.md` — this handoff.

## Decisions Locked
- Plugin stays separate from `category-markup/`.
- No build tools, npm, composer, framework, ESP integration, or outbound email flow.
- Coupon code and discount percent are display settings only; WooCommerce coupon remains manually managed.
- Subscriber storage is a custom DB table keyed by unique email.
- Submission is single opt-in with consent checkbox, timestamp, IP, and user agent stored.
- Popup suppression uses browser `localStorage` for 30 days after shown/submitted.
- Popup is suppressed for logged-in users and cart/checkout/account pages.
- AJAX path uses `admin-ajax.php` with nonce protection.

## Constraints
- Review scope is limited to `3w-newsletter-popup/` plus `docs/scope-research/3w-newsletter-popup-plan.md` verification notes.
- No edits to `category-markup/` expected.
- Deployment script changes are out of scope unless explicitly requested.
- Manual WordPress/WooCommerce verification requires a live/admin site; not runnable in this workspace.

## Next Step
Run `rpi-review` in post-implementation mode, starting by reading this handoff and then inspecting the planned files under `3w-newsletter-popup/` against the plan, foundation, planned structure, and design discussion.

## Review Scope
- `3w-newsletter-popup/3w-newsletter-popup.php`
- `3w-newsletter-popup/assets/newsletter-popup.js`
- `3w-newsletter-popup/assets/newsletter-popup.css`
- `docs/scope-research/3w-newsletter-popup-plan.md`
- Confirm source-contract coverage for T1-T5 and preserve task ids in findings.
- Confirm no unplanned coupling to `category-markup/` or external services.

## Validation Commands
- `php -l 3w-newsletter-popup/3w-newsletter-popup.php`
- Manual WP checks still pending:
  - Activate plugin in WP Admin.
  - Configure coupon code, discount percent, and delay.
  - Visit storefront logged out/incognito; confirm delayed popup.
  - Submit valid email plus consent; confirm coupon display and subscriber row.
  - Submit invalid email or missing consent; confirm error.
  - Export CSV as admin; confirm non-admin blocked.
  - Confirm popup does not appear logged-in, cart, checkout, or account pages.
  - Reload after success/show; confirm 30-day `localStorage` suppression.

## Replan Triggers
- Need to send emails, integrate ESP, or add double opt-in.
- Admin wants automatic WooCommerce coupon creation/validation.
- Legal requires different consent or data-retention behavior.
- Theme conflicts require broader frontend design work outside the planned plugin assets.
- Deployment must support multiple plugin slugs before shipping.
- Review discovers required changes outside the planned file list.

## Resume Prompt
Use `rpi-review` for `docs/scope-research/3w-newsletter-popup-handoff.md`; perform post-implementation review of the planned files, run `php -l 3w-newsletter-popup/3w-newsletter-popup.php`, and write `docs/scope-research/3w-newsletter-popup-review.md`.
