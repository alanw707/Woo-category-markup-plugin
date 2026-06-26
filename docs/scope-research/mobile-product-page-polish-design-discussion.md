# Mobile Product Page Polish Design Discussion

## Context Summary
A mobile product page screenshot shows the header search squeezed, breadcrumb trail noisy, product title/controls crowded, and add-to-cart action pushed below the first fold. User requested frontend design review with accessibility review, then asked to validate all findings and create fix tasks.

## Design Goals
- Make the first mobile fold easier to scan.
- Keep search usable without rebuilding the header.
- Preserve WooCommerce product/checkout behavior.
- Improve accessibility basics: labels, tap targets, focus visibility.
- Keep the fix small and reversible.

## Proposed Solution Shape
Use one existing hotfix plugin file. Add a product-page mobile CSS slice scoped to `body.single-product`. Avoid new components unless CSS cannot solve the CTA visibility issue.

## Intended Placement
Place new rules near existing mobile header search rules in `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`, reusing:
- `--threew-focus-ring` and focus-visible rules.
- `.threew-mobile-header-search` markup and labels.
- Existing `wp_head` injected CSS block.

## Architecture Patterns
- Scoped CSS over DOM rewrites.
- Existing plugin over new plugin.
- Visual tweaks near related selectors.
- Manual screenshot validation because repo has no WordPress browser test harness.

## Design Questions and Answers
- Q: Should this be a full product template redesign?
  - A: No. Screenshot findings are fixable as CSS polish.
- Q: Should CTA be sticky?
  - A: Not initially. Spacing-only first; sticky bottom is a replan trigger if CTA still fails.
- Q: Should product page scripts be optimized too?
  - A: No. Product pages are payment context; avoid stripping payment/product scripts.

## Tradeoffs and Rejected Options
- Rejected new header component: too much risk for one cramped search issue.
- Rejected new carousel JS: CSS placement/tap target should cover the finding.
- Rejected sticky CTA by default: higher overlap/accessibility risk; add only if spacing fails.
- Rejected broad breadcrumb rewrite: CSS truncation/collapse is cheaper and safer.

## Follow-Up Decisions
After implementation, compare a fresh 375px screenshot to the original and decide whether sticky add-to-cart is necessary.
