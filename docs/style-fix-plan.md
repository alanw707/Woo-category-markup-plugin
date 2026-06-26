# 3W Storefront Style Fix Plan

## Goal

Make the homepage look intentional on desktop and mobile without rebuilding the Porto theme or WooCommerce templates.

Shortest safe path: one small CSS hotfix plugin or Customizer CSS block. No new libraries. No template rewrite unless CSS cannot reach the broken element.

## Evidence reviewed

- Desktop homepage screenshot: `gshot-2026-06-22-051516-tBfc.png`
- Mobile hero screenshot: `gshot-2026-06-22-051553-oPnb.png`
- Mobile product grid screenshot: `gshot-2026-06-22-051600-demX.png`
- Repo has only plugin-level control right now:
  - `mobile-menu-hotfix/threew-mobile-menu-hotfix.php`
  - `category-markup/category-markup.php`

## Severity map

### P0 — Must fix first

1. **Mobile editor artifact visible**
   - Symptom: dotted `Build with Header Builder` box appears above homepage content.
   - Impact: site looks broken/unpublished.
   - Likely cause: Porto header builder placeholder leaking into frontend/admin preview or logged-in view.
   - Fix: hide placeholder-like builder boxes on frontend/mobile. If visible only while logged in, accept as admin-only. If visible to customers, hide with CSS immediately.

2. **Floating WhatsApp overlaps content**
   - Symptom: chat bar covers product cards and `JUST ARRIVED` heading on mobile.
   - Impact: blocks reading and taps.
   - Fix: reserve bottom space and constrain widget width/position on small screens.

3. **Mobile content ordering feels broken**
   - Symptom: copyright appears near top before hero.
   - Impact: user sees footer/legal text as primary content.
   - Fix: move copyright back to footer if configurable. CSS-hide that top copyright if it is duplicated elsewhere.

### P1 — High visual impact

4. **Desktop hero crop is weak**
   - Symptom: desktop hero only shows lower bumper/body crop; no headline in visible area.
   - Impact: first impression lacks brand/offer clarity.
   - Fix: set desktop hero focal position and minimum height. Keep mobile crop if current mobile hero remains readable.

5. **Product cards lack clear actions**
   - Symptom: product cards show image/title/price/financing but no strong CTA.
   - Impact: browsing path stalls; clickable affordance weak.
   - Fix: add/restore `View Product` or `Add to Cart` buttons if Porto/Woo has option. If not, style existing links/buttons already emitted by WooCommerce.

6. **Product grid rhythm inconsistent**
   - Symptom: product images vary in size/crop; labels and finance text dominate; cards float in loose whitespace.
   - Impact: premium shop feels like a feed, not a curated catalog.
   - Fix: normalize image boxes, card spacing, title heights, price weight, finance copy size.

### P2 — Polish

7. **Sidebar search clipped on desktop**
   - Symptom: left edge of search placeholder is cut off.
   - Fix: add left padding/width constraint to sidebar search form.

8. **Badges are noisy**
   - Symptom: many green `HOT` labels compete with product images.
   - Fix: smaller badge, consistent placement, less saturated green if possible.

9. **Header icon spacing is tight on mobile**
   - Symptom: cart badge and hamburger are close; header feels cramped.
   - Fix: set consistent icon gap and tap targets.

## Fix phases

### Phase 1 — CSS containment patch

Target: stop visible breakage without touching WooCommerce logic.

- Hide frontend builder placeholders.
- Add safe bottom padding for chat widget.
- Dock chat widget on mobile.
- Fix sidebar search clipping.
- Normalize mobile product card spacing enough to prevent overlap.

Suggested delivery: extend existing hotfix plugin or create `storefront-polish-hotfix/threew-storefront-polish-hotfix.php`.

Why separate plugin is cleaner: `mobile-menu-hotfix` name currently promises only menu behavior. Storefront CSS in that file is confusing. But shortest path is extending it if deployment friction matters.

### Phase 2 — Product card polish

Target: make catalog feel premium.

- Product image frame: fixed aspect ratio, `object-fit: contain`.
- Title: clamp to 2 lines; keep consistent card height.
- Category/brand line: smaller, uppercase, muted.
- Price: bigger and darker.
- Affirm block: smaller; reduce line-height; avoid dominating card.
- CTA: visible button under price/financing.
- Hover/focus: subtle border/shadow; keyboard focus visible.

### Phase 3 — Hero/header correction

Target: first viewport should communicate offer fast.

- Desktop hero: set focal point and minimum height.
- Mobile hero: keep current legible overlay, but make CTA tap target 44px+.
- Remove/move top copyright.
- Ensure header icon targets are 44x44px.

### Phase 4 — Optional theme/template cleanup

Only do this if CSS cannot fix markup order or CTA presence.

- Porto/WPBakery homepage edit: move copyright to footer.
- Porto theme options: enable product buttons / quick view / consistent image ratio.
- WooCommerce template override: last resort only.

## CSS starter patch

```css
/* 3W storefront polish hotfix */

/* P0: hide frontend builder artifacts if Porto leaks them */
body:not(.wp-admin) .porto-hb-placeholder,
body:not(.wp-admin) .header-builder-placeholder,
body:not(.wp-admin) [class*="header-builder"]:has(> .porto-hb-placeholder) {
  display: none !important;
}

/* P0: floating chat must not cover bottom content */
body {
  padding-bottom: 92px;
}

@media (max-width: 600px) {
  .joinchat,
  .whatsapp-chat,
  [class*="whatsapp"] {
    max-width: calc(100vw - 32px) !important;
    right: 16px !important;
    bottom: 16px !important;
  }
}

/* P1: product grid rhythm */
.products .product,
ul.products li.product {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.products .product img,
ul.products li.product img {
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: contain;
}

.products .product .woocommerce-loop-product__title,
ul.products li.product .woocommerce-loop-product__title {
  min-height: 2.7em;
  line-height: 1.35;
}

.products .price,
ul.products li.product .price {
  color: #3f3f46;
  font-size: 20px;
  font-weight: 800;
  line-height: 1.2;
}

/* P1: finance copy should support price, not overpower it */
.affirm-as-low-as,
.affirm-modal-trigger,
[class*="affirm"] {
  font-size: 13px;
  line-height: 1.4;
}

/* P2: mobile card density */
@media (max-width: 480px) {
  .products .product,
  ul.products li.product {
    gap: 5px;
  }

  .products .price,
  ul.products li.product .price {
    font-size: 17px;
  }

  .affirm-as-low-as,
  .affirm-modal-trigger,
  [class*="affirm"] {
    font-size: 12px;
  }
}
```

## Acceptance checklist

Test logged-out and logged-in views.

- Mobile width 360px:
  - No `Build with Header Builder` box visible to customers.
  - Hero headline and CTA visible without horizontal scroll.
  - Product grid not covered by WhatsApp bubble.
  - Product cards remain readable in 2 columns.
  - Tap targets feel usable.

- Tablet width 768px:
  - Hero crop still intentional.
  - Product cards keep even image heights.
  - Chat widget does not cover card content.

- Desktop width 1440px+:
  - Sidebar search text not clipped.
  - Hero crop has useful focal point.
  - Featured Products section has clean alignment.
  - Product cards have visible path to product/action.

## Risks

- `:has()` selector may not be needed and can be removed if target browsers are older. Prefer direct class selectors once actual DOM is confirmed.
- Broad `[class*="whatsapp"]` and `[class*="affirm"]` selectors are temporary. Replace with exact plugin classes after inspecting live DOM.
- CSS cannot move real markup. If copyright is truly placed in the page body, fix in WPBakery/Porto page editor.

## Recommended next action

Inspect live DOM selectors for builder box, WhatsApp widget, product card, and hero. Then ship Phase 1 CSS only. Do not touch WooCommerce pricing plugin.
