# Larger Mobile Product Card Audit

Date: 2026-06-22
Scope: homepage product cards at 430px / 390px / 360px mobile widths.

## Objective mapping

- Increase product card size: cards now use one full-width column with minimal side padding.
- Resolve right-side cut-off from supplied screenshot: audited page and product cards for horizontal overflow; none found.
- Two columns considered: rejected because it made cards smaller and failed the explicit size requirement.
- Keep side padding/margins minimal: product strips use 8px side padding.

## Evidence

Artifacts:

- `docs/screenshots/larger-single-product-cards/mobile-430.png`
- `docs/screenshots/larger-single-product-cards/mobile-390.png`
- `docs/screenshots/larger-single-product-cards/mobile-360.png`
- `docs/screenshots/larger-single-product-cards/report.json`

Automated layout check:

```text
mobile-430: findings=0
mobile-390: findings=0
mobile-360: findings=0
```

Measured card widths:

- 430px viewport: 414px cards.
- 390px viewport: 374px cards.
- 360px viewport: 344px cards.

These are larger than the prior 320px-ish/card reference and avoid right-edge clipping.

## Validation

- Product sections: Featured Products, Just Arrived, Popular Products.
- No page horizontal overflow.
- No card right-edge overflow.
- Product images fit inside frames.
- Titles/prices/financing badges remain visible.
