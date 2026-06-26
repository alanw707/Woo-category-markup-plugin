# Two-Column Mobile Product Card Audit

Date: 2026-06-22
Scope: homepage product cards at 430px / 390px / 360px mobile widths.

## Objective mapping

- Increase product card usable size: product rows now use minimal 8px side padding and cards fill two equal columns.
- Resolve right-side cut-off: audited page and product cards for horizontal overflow; none found.
- Consider/update to two columns: all homepage product sections now render in two columns under 430px, including slider and non-slider product grids.
- Keep side padding/margins minimal: product strips use 8px side padding and 8px column gap.

## Evidence

Artifacts:

- `docs/screenshots/two-column-product-cards-v2/mobile-430.png`
- `docs/screenshots/two-column-product-cards-v2/mobile-390.png`
- `docs/screenshots/two-column-product-cards-v2/mobile-360.png`
- `docs/screenshots/two-column-product-cards-v2/report.json`

Automated layout check:

```text
mobile-430: findings=0
mobile-390: findings=0
mobile-360: findings=0
```

Measured card widths:

- 430px viewport: two 203px cards.
- 390px viewport: two 183px cards.
- 360px viewport: two 168px cards.

## Validation

- Product sections: Featured Products, Just Arrived, Popular Products.
- No page horizontal overflow.
- No card right-edge overflow.
- Product images fit inside frames.
- Titles/prices/financing badges remain visible.
