# Porto Child Theme Storefront Design + Accessibility Fix Plan

## Goal

Fix mobile product-card density, weak hierarchy, inconsistent imagery, cramped finance text, header controls, WhatsApp overlap, and keyboard/accessibility gaps using the Porto child theme first.

Do **not** edit Porto parent theme. Do **not** rebuild WooCommerce templates unless CSS/settings cannot fix the markup.

## Delivery rule

1. Porto child theme CSS first.
2. Porto/WooCommerce theme settings second.
3. Small child-theme `functions.php` filters only for accessibility labels or markup gaps.
4. WooCommerce template overrides last resort only.
5. Remove/retire temporary hotfix plugin CSS after child theme version is live and verified.

## Current evidence

Screenshots reviewed:

- `gshot-2026-06-22-060324-zoRb.png`
- `gshot-2026-06-22-060343-NdvE.png`
- `gshot-2026-06-22-060354-HYSy.png`

Existing repo evidence:

- `storefront-polish-hotfix/threew-storefront-polish-hotfix.php` already injects temporary CSS.
- `docs/style-fix-plan.md` and `docs/style-fix-implementation.md` document prior hotfix work.
- No local `wp-content/themes` copy is present in this repo snapshot, so final selectors must be confirmed against the live/staging DOM.

## Target files

Preferred production location:

```text
wp-content/themes/<porto-child>/style.css
wp-content/themes/<porto-child>/functions.php
```

Optional organization if child theme already has assets:

```text
wp-content/themes/<porto-child>/assets/css/storefront-polish.css
```

Then enqueue from child theme `functions.php` after Porto styles.

Temporary repo location while developing:

```text
docs/porto-child-theme-storefront-a11y-polish-plan.md
storefront-polish-hotfix/threew-storefront-polish-hotfix.php
```

## Phase 0 — Safety and setup

1. Confirm active theme names in WP Admin:
   - Parent: Porto
   - Active theme: Porto child theme
2. Export or copy current child theme files:
   - `style.css`
   - `functions.php`
   - any custom CSS file
3. Confirm cache layers:
   - LiteSpeed cache
   - Porto speed optimization / merged CSS
   - Cloudflare or host cache, if enabled
4. Create rollback path:
   - backup child theme files before edit
   - keep previous hotfix plugin zip/copy
5. Work on staging if available. If not, ship in one small CSS block and purge cache immediately.

Acceptance:

- Backup exists.
- Active child theme confirmed.
- Cache purge path known.

## Phase 1 — DOM discovery

Inspect live/staging homepage at mobile widths: `360`, `390`, `430`, `768`, desktop `1440`.

Record exact selectors for:

- homepage body classes
- product grid wrapper
- product card wrapper
- product image wrapper
- product title link
- price wrapper
- Affirm wrapper and APR badge
- product add-to-cart / quick-view icon
- header cart icon
- hamburger/menu button
- WhatsApp floating link/widget

Expected Porto/WooCommerce selector candidates:

```css
body.home
ul.products
ul.products li.product
.product-col
.product-inner
.product-image
.woocommerce-loop-product__title
.price
.affirm-as-low-as
.affirm-modal-trigger
.add_to_cart_button
.quickview
.mobile-toggle
#mini-cart
```

Acceptance:

- Exact selector list captured.
- No broad `[class*="affirm"]` / `[class*="whatsapp"]` selectors used in final unless no stable class exists.

## Phase 2 — Product card visual fix

### Problems

- Product cards too narrow in 2-column mobile layout.
- Product titles over-truncated.
- Images inconsistent size/crop.
- Price and finance copy crowd each other.
- Vertical rhythm inconsistent.

### CSS plan

Add child-theme CSS scoped to homepage/product loops:

```css
body.home ul.products li.product,
body.home .products-slider li.product,
body.home .product-col {
  min-width: 0;
}

body.home ul.products li.product .product-inner,
body.home .products-slider .product-inner {
  display: flex;
  flex-direction: column;
  gap: 8px;
  height: 100%;
}

body.home ul.products li.product .product-image,
body.home .products-slider .product-image {
  aspect-ratio: 1 / 1;
  display: grid;
  place-items: center;
  margin-bottom: 8px;
}

body.home ul.products li.product .product-image img,
body.home .products-slider .product-image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

body.home .woocommerce-loop-product__title,
body.home ul.products li.product h3,
body.home ul.products li.product .product-name {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  min-height: 2.8em;
  overflow: hidden;
  line-height: 1.4;
}

body.home ul.products li.product .price {
  display: block;
  margin-top: 4px;
  color: #2f3340;
  font-size: 18px;
  font-weight: 800;
  line-height: 1.2;
}
```

### Mobile layout decision

Default safest mobile fix:

```css
@media (max-width: 420px) {
  body.home ul.products:not(.products-slider) {
    grid-template-columns: 1fr !important;
  }
}
```

If client insists on 2 columns, keep 2 columns but reduce noise:

```css
@media (max-width: 420px) {
  body.home ul.products li.product .price {
    font-size: 16px;
  }

  body.home .affirm-as-low-as {
    font-size: 11px;
    line-height: 1.25;
  }
}
```

Recommended: **1 column under 420px for normal grids**, keep carousels alone.

Acceptance:

- Product titles show up to 2 lines.
- Images align consistently.
- Price does not collide with finance text.
- No product card appears narrower than usable tap/read width.

## Phase 3 — Affirm/payment copy cleanup

### Preferred setting fix

Check Affirm/WooCommerce plugin settings first. If there is a setting for promotional message length/location, choose compact message for product loops.

Preferred loop copy:

```text
From $XX/mo with Affirm
```

Avoid full repeated copy:

```text
0% APR or as low as $XX/mo with affirm. See if you qualify
```

### CSS containment if plugin markup cannot change

```css
body.home ul.products li.product .affirm-as-low-as,
body.home ul.products li.product .affirm-modal-trigger {
  color: #3f4654;
  font-size: 12px;
  line-height: 1.35;
}

body.home ul.products li.product .affirm-as-low-as br {
  display: none;
}

body.home ul.products li.product .affirm-as-low-as .affirm-logo {
  max-height: 12px;
}
```

If message remains too long on small screens:

```css
@media (max-width: 420px) {
  body.home ul.products li.product .affirm-as-low-as {
    max-height: 2.8em;
    overflow: hidden;
  }
}
```

Acceptance:

- Finance copy no longer dominates product card.
- APR badge does not wrap into unreadable fragments.
- Price remains primary.

## Phase 4 — Header and navigation controls

### Problems

- Cart/menu icons small.
- Hamburger low contrast.
- Tap target likely below 44px.
- Focus state unknown.

### CSS plan

```css
body.home header a,
body.home header button,
body.home .mobile-toggle,
body.home .cart-toggle,
body.home #mini-cart,
body.home .header-icon {
  min-width: 44px;
  min-height: 44px;
}

body.home .mobile-toggle,
body.home .cart-toggle,
body.home #mini-cart {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

body.home .mobile-toggle,
body.home .mobile-toggle:before,
body.home .mobile-toggle span {
  color: #ffffff;
  opacity: 1;
}
```

Acceptance:

- Cart/menu controls visually obvious.
- Each control has at least 44x44 CSS pixel target.
- Header spacing still fits at 360px width.

## Phase 5 — WhatsApp floating button

### Problems

- Floating button overlaps product content.
- Accessible name unknown.

### CSS plan

```css
body.home {
  padding-bottom: 96px;
}

body.home .joinchat,
body.home .qlwapp,
body.home .qlwapp-toggle,
body.home .whatsapp-button {
  right: max(16px, env(safe-area-inset-right)) !important;
  bottom: max(16px, env(safe-area-inset-bottom)) !important;
  z-index: 50;
}
```

### Accessibility plan

If widget link/button lacks accessible text, add via plugin setting first. If no setting exists, add a tiny child-theme script/filter:

```php
add_action( 'wp_footer', function () {
	?>
	<script>
	document.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp"], .qlwapp-toggle, .joinchat__button').forEach(function (el) {
		if (!el.getAttribute('aria-label') && !el.textContent.trim()) {
			el.setAttribute('aria-label', 'Chat with us on WhatsApp');
		}
	});
	</script>
	<?php
}, 99 );
```

Use this only if plugin settings cannot set the label.

Acceptance:

- Button does not cover final product copy.
- Screen reader name exists: “Chat with us on WhatsApp”.
- Keyboard focus is visible.

## Phase 6 — Focus visibility and keyboard basics

Add global child-theme focus styles scoped to visible controls:

```css
a:focus-visible,
button:focus-visible,
input:focus-visible,
select:focus-visible,
textarea:focus-visible,
[tabindex]:focus-visible {
  outline: 3px solid #74c7ff;
  outline-offset: 3px;
}

body.home ul.products li.product a:focus-visible,
body.home .add_to_cart_button:focus-visible,
body.home .quickview:focus-visible {
  border-radius: 4px;
}
```

Do not remove Porto focus styles unless replacing with stronger ones.

Acceptance:

- Keyboard user can see focus on header, product links, add-to-cart, WhatsApp, and menu.
- Focus is not hidden under sticky header or floating widgets.

## Phase 7 — Accessible product actions

### Inspect first

Check whether add-to-cart icon links already include accessible names.

Good examples:

```html
<a aria-label="Add Mansory Wide Body Kit to cart">...</a>
<a aria-label="View details for Vorsteiner Volta...">...</a>
```

Bad examples:

```html
<a><i class="icon-cart"></i></a>
<a aria-label="Add to cart"></a>
```

### Child-theme PHP fallback

If WooCommerce buttons lack product-specific labels, use filters in `functions.php`:

```php
add_filter( 'woocommerce_loop_add_to_cart_args', function ( $args, $product ) {
	if ( $product ) {
		$args['aria-label'] = sprintf( 'Add %s to cart', wp_strip_all_tags( $product->get_name() ) );
	}
	return $args;
}, 10, 2 );
```

If Porto replaces WooCommerce buttons with custom quick-view icons, inspect Porto hook/filter before overriding templates.

Acceptance:

- Icon-only product actions have product-specific accessible names.
- Button/link semantics remain correct.

## Phase 8 — Contrast cleanup

Use browser contrast checker on actual computed colors.

Likely changes:

```css
body.home ul.products li.product .category-list,
body.home ul.products li.product .product-cats,
body.home ul.products li.product .woocommerce-loop-category__title {
  color: #6b7280;
}

body.home .affirm-as-low-as {
  color: #374151;
}
```

Acceptance:

- Body/small text contrast meets 4.5:1 where practical.
- Large price text remains clearly readable.
- Icon contrast on dark header is clear.

## Phase 9 — Porto settings pass

Check these before writing template overrides:

1. Porto > Theme Options > WooCommerce > Product Archives:
   - product image aspect ratio / crop
   - product title display length
   - quick view/add-to-cart display
   - mobile columns
2. Porto > Theme Options > Header:
   - mobile menu icon color/size
   - cart icon style
3. Porto/WPBakery homepage modules:
   - product carousel columns on mobile
   - product grid spacing
   - hero crop/height
4. Affirm plugin:
   - promotional message placement and compact display
5. WhatsApp plugin:
   - accessible label/title
   - mobile bottom spacing if available

Acceptance:

- Any available setting fix used instead of CSS/PHP.
- Custom code only covers gaps settings cannot cover.

## Phase 10 — Replace hotfix with child-theme implementation

Current temporary plugin:

```text
storefront-polish-hotfix/threew-storefront-polish-hotfix.php
```

Plan:

1. Copy stable CSS from plugin into child theme.
2. Remove broad temporary selectors.
3. Keep cache purge route only if still useful, or delete plugin entirely.
4. Disable hotfix plugin on staging.
5. Verify no regression.
6. Disable hotfix plugin on production after child theme CSS is live.

Acceptance:

- One source of truth: child theme.
- No duplicate CSS fighting between plugin and child theme.

## Phase 11 — Verification checklist

### Visual breakpoints

Capture screenshots at:

- 360 mobile
- 390 mobile
- 430 mobile
- 768 tablet
- 1440 desktop

Check:

- hero not split mid-transition
- header icons visible
- product cards readable
- titles show useful text
- prices prominent
- finance copy secondary
- WhatsApp not covering content
- no random massive vertical gaps

### Accessibility manual test

Keyboard:

1. Tab from browser top through page.
2. Confirm focus visible on every interactive element.
3. Press Enter/Space on menu, cart, product actions, WhatsApp.
4. Confirm focus order matches visual order.

Screen reader quick pass:

1. Product title links announce full useful names.
2. Add-to-cart/quick-view icons announce action + product name.
3. WhatsApp announces “Chat with us on WhatsApp”.
4. Menu/cart controls announce purpose.

Contrast:

1. Header icons against dark header.
2. Product metadata text.
3. Affirm/APR text.
4. Price text.

### Technical checks

- No PHP warnings in WP debug log.
- No JS console errors from added script.
- LiteSpeed/Porto cache purged.
- CSS file loads after Porto parent styles.
- Homepage speed not worsened by new libraries: none added.

## Done criteria

- Mobile homepage product cards are readable and intentional at 360px.
- Product cards have consistent image boxes, 2-line titles, readable price hierarchy, and controlled finance copy.
- Header and floating WhatsApp controls have 44px tap targets and visible focus.
- Icon-only controls have accessible names.
- WhatsApp no longer overlays product content.
- Fixes live in Porto child theme, not Porto parent theme.
- Temporary hotfix plugin disabled or documented as intentionally retained.

## Rollback

If layout breaks:

1. Restore previous child theme `style.css` / CSS asset.
2. Restore previous child theme `functions.php` if changed.
3. Re-enable `storefront-polish-hotfix` only if needed.
4. Purge LiteSpeed/Porto/host cache.
5. Re-capture mobile screenshot.

## Implementation order summary

1. Backup child theme.
2. Inspect live DOM selectors.
3. Move stable hotfix CSS into child theme.
4. Product card layout/image/title/price CSS.
5. Affirm compacting via setting, then CSS fallback.
6. Header tap targets/focus/contrast.
7. WhatsApp spacing and label.
8. Product action aria labels if missing.
9. Purge cache.
10. Screenshot + keyboard + contrast verification.
11. Disable old hotfix plugin after child theme is confirmed.
