<?php

add_filter(
	'woocommerce_loop_add_to_cart_args',
	static function ( $args, $product ) {
		if ( $product && empty( $args['aria-label'] ) ) {
			$args['aria-label'] = sprintf(
				/* translators: %s: product name. */
				__( 'Add %s to cart', 'threew-storefront-polish' ),
				wp_strip_all_tags( $product->get_name() )
			);
		}

		return $args;
	},
	10,
	2
);

add_action(
	'wp_head',
	static function () {
		?>
		<style id="threew-storefront-polish-hotfix-css">
			:root {
				--threew-focus-ring: #74c7ff;
				--threew-text-strong: #2f3340;
				--threew-text-muted: #5f6673;
				--threew-ink: #101418;
				--threew-ink-soft: #1a2028;
				--threew-accent: #19c37d;
			}

			/* Keep floating chat from covering final product/content lines. */
			body.home {
				padding-bottom: 96px;
			}

			.threew-parts-only-notice {
				margin: 14px 0;
				padding: 12px 14px;
				border: 1px solid #d7dde5;
				border-left: 4px solid var(--threew-accent);
				border-radius: 6px;
				background: #f8fafc;
				color: var(--threew-text-strong);
				font-size: 14px;
				line-height: 1.5;
			}

			/* Porto/WPBakery can leak page metadata/builder placeholders in logged-in mobile previews. */
			body.home .page-content > .post-meta,
			body.home .page-content > .entry-meta,
			body.home .page-content > .vcard,
			body.home .page-content > time,
			body.home .porto-hb-placeholder,
			body.home .header-builder-placeholder {
				display: none !important;
			}

			/* Visible keyboard focus across header, products, forms, and chat. */
			a:focus-visible,
			button:focus-visible,
			input:focus-visible,
			select:focus-visible,
			textarea:focus-visible,
			[tabindex]:focus-visible {
				outline: 3px solid var(--threew-focus-ring) !important;
				outline-offset: 3px !important;
			}

			body.home ul.products li.product a:focus-visible,
			body.home .add_to_cart_button:focus-visible,
			body.home .quickview:focus-visible,
			body.home .qlwapp__button:focus-visible {
				border-radius: 6px;
			}

			/* Header controls: keep mobile cart/menu visible and touch-friendly. */
			body.home header a,
			body.home header button,
			body.home .mobile-toggle,
			body.home .cart-toggle,
			body.home #mini-cart,
			body.home .header-icon {
				min-width: 44px !important;
				min-height: 44px !important;
			}

			body.home header .search-toggle,
			body.home header .searchform button,
			body.home header button.btn-special,
			body.home button.btn.btn-special[aria-label="Search"] {
				width: 44px !important;
				min-width: 44px !important;
				height: 44px !important;
				min-height: 44px !important;
			}

			body.home .mobile-toggle,
			body.home .cart-toggle,
			body.home #mini-cart,
			body.home #mini-cart > a,
			body.home .mini-cart > a {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				min-width: 44px;
				min-height: 44px;
			}

			body.home .mobile-toggle,
			body.home .mobile-toggle:before,
			body.home .mobile-toggle span,
			body.home .mobile-toggle i {
				color: #fff !important;
				opacity: 1 !important;
			}

			/* Header search: keep Porto's desktop side-nav and mobile header search usable. */
			body.home .header-side .searchform-popup.advanced-search-layout {
				overflow: visible !important;
			}

			body.home .header-side .searchform-popup.advanced-search-layout .searchform.search-layout-advanced,
			body.home .side-nav .searchform.search-layout-advanced,
			body.home .side-nav-panel .searchform.search-layout-advanced {
				box-sizing: border-box;
				width: 220px !important;
				max-width: 220px !important;
			}

			body.home .header-side .searchform .searchform-fields {
				width: 100% !important;
				overflow: hidden !important;
				border-radius: 999px !important;
			}

			body.home .header-side .searchform input[type="text"],
			body.home .header-side .searchform input[type="search"] {
				box-sizing: border-box;
				width: auto !important;
				min-width: 0 !important;
				flex: 1 1 auto !important;
				padding-left: 14px !important;
				border: 0 !important;
				box-shadow: none !important;
			}

			body.home .header-side .searchform button[type="submit"] {
				flex: 0 0 44px !important;
				border: none !important;
				border-radius: 0 999px 999px 0 !important;
			}

			/* Mobile header: fix icon alignment, search width, and cart badge. */
			@media (max-width: 767px) {
				body.home header .searchform.search-layout-advanced,
				body.home .header-side .searchform.search-layout-advanced,
				body.home .side-nav .searchform.search-layout-advanced {
					width: 200px !important;
					max-width: 200px !important;
					min-width: 0 !important;
				}

				body.home .header-side .searchform .searchform-fields {
					width: 100% !important;
				}

				body.home .header-side .searchform input[type="text"],
				body.home .header-side .searchform input[type="search"] {
					padding-left: 10px !important;
					font-size: 14px !important;
				}

				/* Header icon row: consistent spacing and alignment. */
				body.home .header-right,
				body.home .header-icons,
				body.home .header-actions {
					display: inline-flex !important;
					align-items: center !important;
					gap: 12px !important;
				}

				/* Even spacing for all header elements. */
				body.home header .logo,
				body.home header .searchform,
				body.home header .mini-cart,
				body.home header .mobile-toggle,
				body.home header .cart-toggle,
				body.home header .header-icon {
					margin-left: 0 !important;
					margin-right: 0 !important;
				}

				/* Fix diamond / secondary icon alignment so it sits inline, not below. */
				body.home .header-right .icon-diamond,
				body.home .header-icons .icon-diamond,
				body.home .header-right .diamond-icon,
				body.home .header-icons .diamond-icon,
				body.home .header-right [class*="diamond"],
				body.home .header-icons [class*="diamond"] {
					display: inline-flex !important;
					align-items: center !important;
					justify-content: center !important;
					vertical-align: middle !important;
					margin: 0 !important;
					padding: 0 !important;
					position: static !important;
					transform: none !important;
					top: auto !important;
					bottom: auto !important;
				}

				/* Hide empty cart badge (0) or make it neutral. */
				body.home .cart-badge:empty,
				body.home .cart-count:empty,
				body.home .cart-items-count:empty,
				body.home .cart-items:empty,
				body.home .cart-items-text:empty,
				body.home .mini-cart .cart-badge[data-count="0"],
				body.home .mini-cart .cart-count[data-count="0"],
				body.home .mini-cart .cart-items[data-count="0"],
				body.home .mini-cart .cart-items-text[data-count="0"],
				body.home #mini-cart .cart-badge[data-count="0"],
				body.home #mini-cart .cart-count[data-count="0"],
				body.home #mini-cart .cart-items[data-count="0"],
				body.home #mini-cart .cart-items-text[data-count="0"],
				body.home .cart-toggle .cart-badge[data-count="0"],
				body.home .cart-toggle .cart-count[data-count="0"],
				body.home .cart-toggle .cart-items[data-count="0"],
				body.home .cart-toggle .cart-items-text[data-count="0"],
				body.home .header-icon .cart-badge[data-count="0"],
				body.home .header-icon .cart-count[data-count="0"],
				body.home .header-icon .cart-items[data-count="0"],
				body.home .header-icon .cart-items-text[data-count="0"],
				body.home .cart-icon .cart-items:empty,
				body.home .cart-icon .cart-items-text:empty {
					display: none !important;
				}

				/* If badge is present but count is 0, hide the numeric badge. */
				body.home .cart-count,
				body.home .cart-badge,
				body.home .cart-items,
				body.home .cart-items-text {
					background: #ff5b5b !important;
					color: #fff !important;
					font-size: 10px !important;
					min-width: 18px !important;
					height: 18px !important;
					line-height: 18px !important;
					padding: 0 4px !important;
					border-radius: 9px !important;
				}
			}

			/* Cart popup: ensure it floats above hero and content sections. */
			body.home #header,
			body.home header {
				position: relative !important;
				z-index: 10000 !important;
			}

			body.home .mini-cart .cart-popup,
			body.home #mini-cart .cart-popup,
			body.home .cart-toggle .cart-popup,
			body.home .header-main .mini-cart .cart-popup,
			body.home .widget_shopping_cart,
			body.home .mini-cart .widget_shopping_cart,
			body.home #mini-cart .widget_shopping_cart {
				z-index: 10030 !important;
			}

			/* Hero: freeze carousel to one full-width slide so users never catch split transitions. */
			body.home .home-banner .owl-item.threew-hero-static {
				display: block !important;
				width: 100% !important;
				max-width: 100% !important;
			}

			body.home .home-banner .owl-item.threew-hero-hidden {
				display: none !important;
			}

			body.home .home-banner .owl-stage,
			body.home .home-banner .owl-stage-outer {
				width: 100% !important;
				transform: none !important;
			}

			body.home .home-banner .owl-nav,
			body.home .home-banner .owl-dots {
				display: none !important;
			}

			body.home .home-banner .porto-ibanner,
			body.home .home-banner .porto-ibanner-img {
				width: 100% !important;
			}

			/* Hide broken gray promo placeholder before dealer strip. */
			body.home .home-mid-banner,
			body.home .threew-promo-panel {
				display: none !important;
			}

			/* Tablet product carousels: disable clipped owl transforms and render as stable grid. */
			@media (min-width: 601px) and (max-width: 991px) {
				body.home ul.products.products-slider.owl-carousel {
					display: grid !important;
					grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
					gap: 26px 18px !important;
					width: 100% !important;
					max-width: 100% !important;
					margin: 0 !important;
					padding: 0 !important;
					overflow: visible !important;
					list-style: none !important;
				}

				body.home ul.products.products-slider.owl-carousel .owl-stage-outer,
				body.home ul.products.products-slider.owl-carousel .owl-stage {
					display: contents !important;
					width: auto !important;
					height: auto !important;
					transform: none !important;
				}

				body.home ul.products.products-slider.owl-carousel .owl-item {
					display: block !important;
					width: auto !important;
					max-width: none !important;
					margin: 0 !important;
					padding: 0 !important;
					transform: none !important;
					opacity: 1 !important;
					filter: none !important;
				}

				body.home ul.products.products-slider.owl-carousel .owl-item.cloned,
				body.home ul.products.products-slider.owl-carousel .owl-nav,
				body.home ul.products.products-slider.owl-carousel .owl-dots {
					display: none !important;
				}

				body.home ul.products.products-slider.owl-carousel .product-col,
				body.home ul.products.products-slider.owl-carousel li.product {
					box-sizing: border-box !important;
					width: 100% !important;
					max-width: 100% !important;
					min-width: 0 !important;
					margin: 0 !important;
					padding: 0 !important;
					float: none !important;
					justify-self: stretch !important;
				}
			}

			/* Product card rhythm and hierarchy. */
			body.home ul.products.products-slider .owl-item > .product-col,
			body.home ul.products.products-slider .owl-item > li.product,
			body.home ul.products.products-slider .product-inner {
				width: 100% !important;
				max-width: 100% !important;
			}

			body.home ul.products li.product,
			body.home .products .product-col {
				box-sizing: border-box;
				min-width: 0;
			}

			body.home ul.products li.product .product-inner,
			body.home .products .product-col .product-inner {
				display: flex;
				flex-direction: column;
				gap: 8px;
				height: 100%;
			}

			body.home ul.products li.product .product-image,
			body.home .products .product-col .product-image {
				aspect-ratio: 1 / 1;
				min-height: 118px;
				display: grid;
				place-items: center;
				margin-bottom: 8px;
			}

			body.home ul.products li.product .product-image img,
			body.home .products .product-col .product-image img {
				width: 100% !important;
				height: 100% !important;
				max-width: 100% !important;
				object-fit: contain;
			}

			body.home ul.products li.product .product-content,
			body.home .products .product-col .product-content {
				padding-top: 0;
			}

			body.home ul.products li.product .category-list,
			body.home .products .product-col .category-list {
				min-height: 1.2em;
				color: var(--threew-text-muted);
				font-size: 10px;
				line-height: 1.25;
				letter-spacing: .02em;
			}

			body.home ul.products li.product .product-loop-title,
			body.home .products .product-col .product-loop-title,
			body.home .woocommerce-loop-product__title,
			body.home ul.products li.product h3,
			body.home ul.products li.product .product-name {
				display: -webkit-box;
				-webkit-line-clamp: 2;
				-webkit-box-orient: vertical;
				overflow: hidden;
				min-height: 2.8em;
				line-height: 1.4;
			}

			body.home ul.products li.product .price,
			body.home .products .product-col .price {
				display: block;
				color: var(--threew-text-strong);
				font-size: 18px;
				font-weight: 800;
				line-height: 1.2;
				margin-top: 4px;
				margin-bottom: 6px;
			}

			body.home ul.products li.product .affirm-as-low-as,
			body.home ul.products li.product .affirm-modal-trigger {
				color: #374151;
				font-size: 12px;
				line-height: 1.35;
			}

			body.home ul.products li.product .affirm-as-low-as br {
				display: none;
			}

			body.home ul.products li.product .affirm-as-low-as .affirm-logo {
				max-height: 12px;
			}

			body.archive.woocommerce p.affirm-as-low-as,
			body.search.woocommerce p.affirm-as-low-as,
			body.post-type-archive-product p.affirm-as-low-as,
			body.tax-product_cat p.affirm-as-low-as,
			body.tax-product_tag p.affirm-as-low-as {
				display: none !important;
			}

			body.home ul.products li.product .labels .onhot,
			body.home .products .product-col .labels .onhot {
				font-size: 9px;
				line-height: 1;
				padding: 5px 8px;
			}

			.threew-mobile-header-search,
			.threew-mobile-shop-shortcuts {
				display: none;
			}

			/* Mobile: keep chat compact, catalog readable, and hero CTA space intact. */
			@media (max-width: 600px) {
				body:not(.home) #header,
				body:not(.home) header {
					height: 72px !important;
					min-height: 72px !important;
					max-height: 72px !important;
					background: var(--threew-ink-soft) !important;
					box-shadow: 0 1px 0 rgba(255, 255, 255, .08), 0 8px 22px rgba(0, 0, 0, .16) !important;
					overflow: visible !important;
				}

				body:not(.home) .header-main,
				body:not(.home) .header-center,
				body:not(.home) .header-left,
				body:not(.home) .header-right {
					height: 72px !important;
					min-height: 72px !important;
					max-height: 72px !important;
					align-items: center !important;
					overflow: visible !important;
				}

				body:not(.home) .header-main {
					padding-top: 0 !important;
					padding-bottom: 0 !important;
				}

				body:not(.home) .logo,
				body:not(.home) .header-logo,
				body:not(.home) #header .logo {
					display: flex !important;
					align-items: center !important;
					height: 44px !important;
					min-height: 44px !important;
					max-width: 48px !important;
					margin-left: 0 !important;
					position: relative !important;
					left: 3vw !important;
					line-height: 0 !important;
				}

				body:not(.home) .logo img,
				body:not(.home) .header-logo img,
				body:not(.home) #header .logo img {
					width: 44px !important;
					max-width: 44px !important;
					height: auto !important;
				}

				body:not(.home) .mobile-toggle,
				body:not(.home) .cart-toggle,
				body:not(.home) #mini-cart,
				body:not(.home) #mini-cart > a,
				body:not(.home) .mini-cart > a,
				body:not(.home) .header-icon {
					display: inline-flex !important;
					align-items: center !important;
					justify-content: center !important;
					width: 44px !important;
					min-width: 44px !important;
					height: 44px !important;
					min-height: 44px !important;
					color: #fff !important;
					opacity: 1 !important;
					z-index: 1005 !important;
				}

				body:not(.home) .header-main .mini-cart,
				body:not(.home) #mini-cart,
				body:not(.home) .cart-toggle {
					position: fixed !important;
					top: -6px !important;
					right: -26px !important;
					left: auto !important;
					bottom: auto !important;
					width: 44px !important;
					height: 44px !important;
					min-width: 44px !important;
					min-height: 44px !important;
					transform: translateX(-8px) !important;
				}

				body:not(.home) .header-main .mini-cart .cart-head {
					width: 44px !important;
					height: 44px !important;
					min-width: 44px !important;
					min-height: 44px !important;
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
				}

				body.home #mini-cart .cart-items,
				body.home .mini-cart .cart-items,
				body.home #mini-cart .cart-count,
				body.home .mini-cart .cart-count,
				body.home #mini-cart .cart-badge,
				body.home .mini-cart .cart-badge,
				body:not(.home) #mini-cart .cart-items,
				body:not(.home) .mini-cart .cart-items,
				body:not(.home) #mini-cart .cart-count,
				body:not(.home) .mini-cart .cart-count,
				body:not(.home) #mini-cart .cart-badge,
				body:not(.home) .mini-cart .cart-badge {
					display: inline-flex !important;
					align-items: center !important;
					justify-content: center !important;
					min-width: 16px !important;
					height: 16px !important;
					padding: 0 4px !important;
					border-radius: 999px !important;
					font-size: 10px !important;
					font-weight: 700 !important;
					line-height: 1 !important;
					text-align: center !important;
				}

				body.home #mini-cart .cart-items,
				body.home .mini-cart .cart-items,
				body:not(.home) #mini-cart .cart-items,
				body:not(.home) .mini-cart .cart-items {
					top: -4px !important;
					right: -6px !important;
					left: auto !important;
					bottom: auto !important;
				}

				body:not(.home) .mobile-toggle {
					position: fixed !important;
					top: 6px !important;
					left: calc(100vw - 52px) !important;
					right: auto !important;
					margin-left: 0 !important;
					margin-right: 0 !important;
					transform: none !important;
				}

				body:not(.home) .mobile-toggle,
				body:not(.home) .mobile-toggle:before,
				body:not(.home) .mobile-toggle span,
				body:not(.home) .mobile-toggle i,
				body:not(.home) #mini-cart i,
				body:not(.home) .cart-toggle i,
				body:not(.home) .header-icon i {
					color: #fff !important;
					opacity: 1 !important;
				}

				body:not(.home) .mobile-toggle {
					background-image: linear-gradient(#fff, #fff), linear-gradient(#fff, #fff), linear-gradient(#fff, #fff) !important;
					background-repeat: no-repeat !important;
					background-size: 22px 2px, 22px 2px, 22px 2px !important;
					background-position: center 14px, center 21px, center 28px !important;
				}

				body:not(.home) .mobile-toggle:before {
					content: "" !important;
				}

				body.home {
					padding-bottom: 88px;
					background: #f4f6f8;
					overflow-x: hidden;
				}

				body.home .mobile-toggle,
				body.home #mini-cart,
				body.home .mini-cart,
				body.home #mini-cart > a,
				body.home .cart-toggle {
					z-index: 1005 !important;
					pointer-events: auto !important;
					background: rgba(255, 255, 255, .001) !important;
				}

				body.home #header,
				body.home header {
					height: 72px !important;
					min-height: 72px !important;
					max-height: 72px !important;
					background: var(--threew-ink-soft) !important;
					overflow: visible !important;
				}

				body.home .header-main,
				body.home .header-center,
				body.home .header-left,
				body.home .header-right {
					height: 72px !important;
					min-height: 72px !important;
					max-height: 72px !important;
					align-items: center !important;
					overflow: visible !important;
				}

				body.home .header-main {
					padding-top: 0 !important;
					padding-bottom: 0 !important;
				}

				body.home .logo,
				body.home .header-logo,
				body.home #header .logo {
					display: flex !important;
					align-items: center !important;
					max-width: 48px !important;
					margin-left: 0 !important;
					position: relative !important;
					left: 3vw !important;
				}

				body.home .logo img,
				body.home .header-logo img,
				body.home #header .logo img {
					width: 44px !important;
					max-width: 44px !important;
					height: auto !important;
				}

				body.home .header-main .mini-cart,
				body.home #mini-cart,
				body.home .cart-toggle {
					position: fixed !important;
					top: -6px !important;
					right: -26px !important;
					left: auto !important;
					bottom: auto !important;
					width: 44px !important;
					height: 44px !important;
					min-width: 44px !important;
					min-height: 44px !important;
					transform: translateX(-8px) !important;
				}

				body.home .mobile-toggle {
					position: fixed !important;
					top: 6px !important;
					left: calc(100vw - 52px) !important;
					right: auto !important;
					transform: none !important;
					width: 44px !important;
					height: 44px !important;
					background-image: linear-gradient(#fff, #fff), linear-gradient(#fff, #fff), linear-gradient(#fff, #fff) !important;
					background-repeat: no-repeat !important;
					background-size: 22px 2px, 22px 2px, 22px 2px !important;
					background-position: center 14px, center 21px, center 28px !important;
				}

				body.home .mobile-toggle:before {
					content: "" !important;
				}

				body .mobile-toggle i,
				body .mobile-toggle span {
					display: none !important;
				}

				/* Porto adds sticky-header on scroll and shrinks the logo; keep mobile header geometry fixed. */
				body.sticky-header .logo img,
				body.sticky-header .header-logo img,
				header.sticky-header .logo img,
				header.sticky-header .header-logo img {
					width: 44px !important;
					max-width: 44px !important;
					height: auto !important;
					transform: none !important;
				}

				body.sticky-header .mobile-toggle,
				header.sticky-header .mobile-toggle {
					top: 14px !important;
				}

				/* Mobile: show Porto's real header search in the logo row. */
				body .header-center .searchform-popup.advanced-search-layout,
				body .header-side .header-center .searchform-popup.advanced-search-layout {
					display: block !important;
					position: fixed !important;
					top: 14px !important;
					left: 90px !important;
					right: 116px !important;
					width: auto !important;
					max-width: none !important;
					height: 44px !important;
					min-height: 44px !important;
					max-height: 44px !important;
					border: 0 !important;
					overflow: visible !important;
					z-index: 1004 !important;
				}

				body.home .header-center .searchform-popup.advanced-search-layout,
				body.home .header-side .header-center .searchform-popup.advanced-search-layout {
					top: 14px !important;
					left: 90px !important;
					right: 116px !important;
				}

				body .header-center .search-toggle {
					display: none !important;
				}

				body .header-center .searchform-popup.advanced-search-layout .searchform.search-layout-advanced,
				body.home .header-side .searchform-popup.advanced-search-layout .searchform.search-layout-advanced {
					display: flex !important;
					position: static !important;
					inset: auto !important;
					transform: none !important;
					width: 100% !important;
					max-width: 100% !important;
					height: 44px !important;
					min-height: 44px !important;
					max-height: 44px !important;
					margin: 0 !important;
					overflow: hidden !important;
				}

				body .header-center .searchform-popup.advanced-search-layout .searchform-fields,
				body.home .header-side .searchform-popup.advanced-search-layout .searchform-fields {
					display: flex !important;
					align-items: center !important;
					box-sizing: border-box !important;
					width: 100% !important;
					max-width: 100% !important;
					min-width: 0 !important;
					height: 44px !important;
					min-height: 44px !important;
					max-height: 44px !important;
					border-radius: 999px !important;
					overflow: hidden !important;
				}

				body .header-center .searchform-popup.advanced-search-layout .text,
				body.home .header-side .searchform-popup.advanced-search-layout .text {
					display: block !important;
					flex: 1 1 auto !important;
					width: auto !important;
					min-width: 0 !important;
				}

				body .header-center .searchform-popup.advanced-search-layout input[type="text"],
				body .header-center .searchform-popup.advanced-search-layout input[type="search"],
				body.home .header-side .searchform-popup.advanced-search-layout input[type="text"],
				body.home .header-side .searchform-popup.advanced-search-layout input[type="search"] {
					width: 100% !important;
					height: 100% !important;
					min-width: 0 !important;
					padding: 0 14px !important;
					border: 0 !important;
					box-shadow: none !important;
					outline: none !important;
					appearance: none !important;
					-webkit-appearance: none !important;
					background: transparent !important;
					font-size: .9375rem !important;
					line-height: normal !important;
				}

				body .header-center .searchform-popup.advanced-search-layout input[type="text"]:focus,
				body .header-center .searchform-popup.advanced-search-layout input[type="text"]:focus-visible,
				body .header-center .searchform-popup.advanced-search-layout input[type="search"]:focus,
				body .header-center .searchform-popup.advanced-search-layout input[type="search"]:focus-visible,
				body.home .header-side .searchform-popup.advanced-search-layout input[type="text"]:focus,
				body.home .header-side .searchform-popup.advanced-search-layout input[type="text"]:focus-visible,
				body.home .header-side .searchform-popup.advanced-search-layout input[type="search"]:focus,
				body.home .header-side .searchform-popup.advanced-search-layout input[type="search"]:focus-visible {
					outline: none !important;
					box-shadow: none !important;
				}

				body .header-center .searchform-popup.advanced-search-layout button[type="submit"],
				body.home .header-side .searchform-popup.advanced-search-layout button[type="submit"] {
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					flex: 0 0 44px !important;
					width: 44px !important;
					min-width: 44px !important;
					height: 100% !important;
					margin: 0 !important;
					padding: 0 !important;
					border-radius: 0 999px 999px 0 !important;
				}

				body.home .side-nav .searchform.search-layout-advanced {
					display: none !important;
				}

				body .threew-mobile-header-search {
					display: none !important;
				}

				body:not(.home) .threew-mobile-header-search {
					display: none !important;
				}

				html.panel-opened body .threew-mobile-header-search {
					display: none !important;
				}

				body .threew-mobile-header-search .searchform.search-layout-advanced {
					display: flex !important;
					width: 100% !important;
					max-width: none !important;
					margin: 0 !important;
					top: auto !important;
				}

				body .threew-mobile-header-search .searchform-fields {
					display: flex !important;
					align-items: center !important;
					width: 100% !important;
					height: 44px !important;
					min-height: 44px !important;
					max-height: 44px !important;
					background: #fff !important;
					border: 0 !important;
					border-radius: 999px !important;
					overflow: hidden !important;
				}

				body .threew-mobile-header-search .text {
					flex: 1 1 auto !important;
					width: auto !important;
					min-width: 0 !important;
				}

				body .threew-mobile-header-search input[type="text"],
				body .threew-mobile-header-search input[type="search"] {
					box-sizing: border-box !important;
					width: 100% !important;
					height: 44px !important;
					min-width: 0 !important;
					max-width: 100% !important;
					flex: 1 1 auto !important;
					padding: 0 16px !important;
					border: 0 !important;
					box-shadow: none !important;
					font-size: 15px !important;
					line-height: 44px !important;
				}

				body .threew-mobile-header-search button[type="submit"] {
					display: flex !important;
					position: static !important;
					inset: auto !important;
					align-items: center !important;
					justify-content: center !important;
					flex: 0 0 44px !important;
					width: 44px !important;
					height: 44px !important;
					margin: 0 !important;
					transform: none !important;
					color: #fff !important;
					background: var(--threew-accent) !important;
					border: none !important;
					border-radius: 0 999px 999px 0 !important;
				}

				body .threew-mobile-header-search button[type="submit"] i {
					color: #fff !important;
				}

				body.home .threew-mobile-cart-popup,
				body.woocommerce-shop .threew-mobile-cart-popup,
				body.post-type-archive-product .threew-mobile-cart-popup,
				body.tax-product_cat .threew-mobile-cart-popup,
				body.tax-product_tag .threew-mobile-cart-popup,
				body.search .threew-mobile-cart-popup {
					display: block !important;
					position: fixed !important;
					top: 80px !important;
					left: 12px !important;
					right: 12px !important;
					width: auto !important;
					max-width: none !important;
					min-height: 0 !important;
					overflow: hidden !important;
					z-index: 2147483000 !important;
					background: #fff !important;
					border: 1px solid rgba(16, 20, 24, .08) !important;
					border-radius: 18px !important;
					box-shadow: 0 18px 44px rgba(15, 23, 42, .24) !important;
				}

				body.woocommerce-shop .threew-mobile-cart-popup,
				body.post-type-archive-product .threew-mobile-cart-popup,
				body.tax-product_cat .threew-mobile-cart-popup,
				body.tax-product_tag .threew-mobile-cart-popup,
				body.search .threew-mobile-cart-popup {
					top: 96px !important;
				}

				body.home .threew-mobile-cart-popup .widget_shopping_cart_content,
				body.woocommerce-shop .threew-mobile-cart-popup .widget_shopping_cart_content,
				body.post-type-archive-product .threew-mobile-cart-popup .widget_shopping_cart_content,
				body.tax-product_cat .threew-mobile-cart-popup .widget_shopping_cart_content,
				body.tax-product_tag .threew-mobile-cart-popup .widget_shopping_cart_content,
				body.search .threew-mobile-cart-popup .widget_shopping_cart_content {
					padding: 16px !important;
				}

				body.home .threew-mobile-cart-popup .total-count,
				body.woocommerce-shop .threew-mobile-cart-popup .total-count,
				body.post-type-archive-product .threew-mobile-cart-popup .total-count,
				body.tax-product_cat .threew-mobile-cart-popup .total-count,
				body.tax-product_tag .threew-mobile-cart-popup .total-count,
				body.search .threew-mobile-cart-popup .total-count {
					display: flex !important;
					align-items: center !important;
					justify-content: space-between !important;
					gap: 12px !important;
					margin: 0 !important;
					padding: 0 0 12px !important;
					border-bottom: 1px solid rgba(16, 20, 24, .08) !important;
				}

				body.home .threew-mobile-cart-popup .total-count span,
				body.woocommerce-shop .threew-mobile-cart-popup .total-count span,
				body.post-type-archive-product .threew-mobile-cart-popup .total-count span,
				body.tax-product_cat .threew-mobile-cart-popup .total-count span,
				body.tax-product_tag .threew-mobile-cart-popup .total-count span,
				body.search .threew-mobile-cart-popup .total-count span {
					color: var(--threew-text-muted) !important;
					font-size: 11px !important;
					font-weight: 800 !important;
					letter-spacing: .08em !important;
				}

				body.home .threew-mobile-cart-popup .total-count a,
				body.woocommerce-shop .threew-mobile-cart-popup .total-count a,
				body.post-type-archive-product .threew-mobile-cart-popup .total-count a,
				body.tax-product_cat .threew-mobile-cart-popup .total-count a,
				body.tax-product_tag .threew-mobile-cart-popup .total-count a,
				body.search .threew-mobile-cart-popup .total-count a {
					display: inline-flex !important;
					align-items: center !important;
					justify-content: center !important;
					min-height: 40px !important;
					padding: 0 14px !important;
					border-radius: 999px !important;
					background: #101418 !important;
					color: #fff !important;
					font-size: 12px !important;
					font-weight: 700 !important;
					letter-spacing: .04em !important;
					text-decoration: none !important;
					text-transform: uppercase !important;
				}

				body.home .threew-mobile-cart-popup .cart_list,
				body.woocommerce-shop .threew-mobile-cart-popup .cart_list,
				body.post-type-archive-product .threew-mobile-cart-popup .cart_list,
				body.tax-product_cat .threew-mobile-cart-popup .cart_list,
				body.tax-product_tag .threew-mobile-cart-popup .cart_list,
				body.search .threew-mobile-cart-popup .cart_list {
					margin: 12px 0 0 !important;
					padding: 0 !important;
					max-height: min(52vh, 420px) !important;
					overflow: auto !important;
					list-style: none !important;
				}

				body.home .threew-mobile-cart-popup .woocommerce-mini-cart__empty-message,
				body.home .threew-mobile-cart-popup .cart_list .empty,
				body.woocommerce-shop .threew-mobile-cart-popup .woocommerce-mini-cart__empty-message,
				body.woocommerce-shop .threew-mobile-cart-popup .cart_list .empty,
				body.post-type-archive-product .threew-mobile-cart-popup .woocommerce-mini-cart__empty-message,
				body.post-type-archive-product .threew-mobile-cart-popup .cart_list .empty,
				body.tax-product_cat .threew-mobile-cart-popup .woocommerce-mini-cart__empty-message,
				body.tax-product_cat .threew-mobile-cart-popup .cart_list .empty,
				body.tax-product_tag .threew-mobile-cart-popup .woocommerce-mini-cart__empty-message,
				body.tax-product_tag .threew-mobile-cart-popup .cart_list .empty,
				body.search .threew-mobile-cart-popup .woocommerce-mini-cart__empty-message,
				body.search .threew-mobile-cart-popup .cart_list .empty {
					margin: 0 !important;
					padding: 4px 0 0 !important;
					color: var(--threew-text-muted) !important;
					font-size: 14px !important;
					line-height: 1.5 !important;
				}

				body.single-product .threew-mobile-cart-popup {
					display: block !important;
					position: fixed !important;
					top: 96px !important;
					left: 12px !important;
					right: 12px !important;
					width: auto !important;
					max-width: none !important;
					min-height: 0 !important;
					overflow: hidden !important;
					z-index: 2147483000 !important;
					background: #fff !important;
					border: 1px solid rgba(16, 20, 24, .08) !important;
					border-radius: 18px !important;
					box-shadow: 0 18px 44px rgba(15, 23, 42, .24) !important;
				}

				body.single-product .threew-mobile-cart-popup .widget_shopping_cart_content {
					padding: 16px !important;
				}

				body.single-product .threew-mobile-cart-popup .total-count {
					display: flex !important;
					align-items: center !important;
					justify-content: space-between !important;
					gap: 12px !important;
					margin: 0 !important;
					padding: 0 0 12px !important;
					border-bottom: 1px solid rgba(16, 20, 24, .08) !important;
				}

				body.single-product .threew-mobile-cart-popup .total-count span {
					color: var(--threew-text-muted) !important;
					font-size: 11px !important;
					font-weight: 800 !important;
					letter-spacing: .08em !important;
				}

				body.single-product .threew-mobile-cart-popup .total-count a {
					display: inline-flex !important;
					align-items: center !important;
					justify-content: center !important;
					min-height: 40px !important;
					padding: 0 14px !important;
					border-radius: 999px !important;
					background: #101418 !important;
					color: #fff !important;
					font-size: 12px !important;
					font-weight: 700 !important;
					letter-spacing: .04em !important;
					text-decoration: none !important;
					text-transform: uppercase !important;
				}

				body.single-product .threew-mobile-cart-popup .cart_list {
					margin: 12px 0 0 !important;
					padding: 0 !important;
					max-height: min(52vh, 420px) !important;
					overflow: auto !important;
					list-style: none !important;
				}

				body.single-product .threew-mobile-cart-popup .woocommerce-mini-cart__empty-message,
				body.single-product .threew-mobile-cart-popup .cart_list .empty {
					margin: 0 !important;
					padding: 4px 0 0 !important;
					color: var(--threew-text-muted) !important;
					font-size: 14px !important;
					line-height: 1.5 !important;
				}

				/* Hide cart popup arrow/diamond without hiding the popup content. */
				body.home .cart-loading,
				body.home .cart-popup::before,
				body.home .cart-popup::after,
				body.home .widget_shopping_cart::before,
				body.home .widget_shopping_cart::after,
				body.home .cart-icon::after,
				body.home .mini-cart::after,
				body.home #mini-cart::after,
				body.home .cart-toggle::after,
				body.home .cart-head::after,
				body.home .cart-icon .diamond,
				body.home .cart-head .diamond,
				body.home .mini-cart .diamond,
				body.home [class*="cart"] .diamond,
				body.woocommerce-shop .cart-loading,
				body.woocommerce-shop .cart-popup::before,
				body.woocommerce-shop .cart-popup::after,
				body.woocommerce-shop .widget_shopping_cart::before,
				body.woocommerce-shop .widget_shopping_cart::after,
				body.woocommerce-shop .cart-icon::after,
				body.woocommerce-shop .mini-cart::after,
				body.woocommerce-shop #mini-cart::after,
				body.woocommerce-shop .cart-toggle::after,
				body.woocommerce-shop .cart-head::after,
				body.woocommerce-shop .cart-icon .diamond,
				body.woocommerce-shop .cart-head .diamond,
				body.woocommerce-shop .mini-cart .diamond,
				body.woocommerce-shop [class*="cart"] .diamond,
				body.post-type-archive-product .cart-loading,
				body.post-type-archive-product .cart-popup::before,
				body.post-type-archive-product .cart-popup::after,
				body.post-type-archive-product .widget_shopping_cart::before,
				body.post-type-archive-product .widget_shopping_cart::after,
				body.post-type-archive-product .cart-icon::after,
				body.post-type-archive-product .mini-cart::after,
				body.post-type-archive-product #mini-cart::after,
				body.post-type-archive-product .cart-toggle::after,
				body.post-type-archive-product .cart-head::after,
				body.post-type-archive-product .cart-icon .diamond,
				body.post-type-archive-product .cart-head .diamond,
				body.post-type-archive-product .mini-cart .diamond,
				body.post-type-archive-product [class*="cart"] .diamond,
				body.tax-product_cat .cart-loading,
				body.tax-product_cat .cart-popup::before,
				body.tax-product_cat .cart-popup::after,
				body.tax-product_cat .widget_shopping_cart::before,
				body.tax-product_cat .widget_shopping_cart::after,
				body.tax-product_cat .cart-icon::after,
				body.tax-product_cat .mini-cart::after,
				body.tax-product_cat #mini-cart::after,
				body.tax-product_cat .cart-toggle::after,
				body.tax-product_cat .cart-head::after,
				body.tax-product_cat .cart-icon .diamond,
				body.tax-product_cat .cart-head .diamond,
				body.tax-product_cat .mini-cart .diamond,
				body.tax-product_cat [class*="cart"] .diamond,
				body.tax-product_tag .cart-loading,
				body.tax-product_tag .cart-popup::before,
				body.tax-product_tag .cart-popup::after,
				body.tax-product_tag .widget_shopping_cart::before,
				body.tax-product_tag .widget_shopping_cart::after,
				body.tax-product_tag .cart-icon::after,
				body.tax-product_tag .mini-cart::after,
				body.tax-product_tag #mini-cart::after,
				body.tax-product_tag .cart-toggle::after,
				body.tax-product_tag .cart-head::after,
				body.tax-product_tag .cart-icon .diamond,
				body.tax-product_tag .cart-head .diamond,
				body.tax-product_tag .mini-cart .diamond,
				body.tax-product_tag [class*="cart"] .diamond,
				body.search .cart-loading,
				body.search .cart-popup::before,
				body.search .cart-popup::after,
				body.search .widget_shopping_cart::before,
				body.search .widget_shopping_cart::after,
				body.search .cart-icon::after,
				body.search .mini-cart::after,
				body.search #mini-cart::after,
				body.search .cart-toggle::after,
				body.search .cart-head::after,
				body.search .cart-icon .diamond,
				body.search .cart-head .diamond,
				body.search .mini-cart .diamond,
				body.search [class*="cart"] .diamond {
					display: none !important;
				}

				body.home #mini-cart.threew-cart-open .cart-popup,
				body.home .mini-cart.threew-cart-open .cart-popup,
				body.woocommerce-shop #mini-cart.threew-cart-open .cart-popup,
				body.woocommerce-shop .mini-cart.threew-cart-open .cart-popup,
				body.post-type-archive-product #mini-cart.threew-cart-open .cart-popup,
				body.post-type-archive-product .mini-cart.threew-cart-open .cart-popup,
				body.tax-product_cat #mini-cart.threew-cart-open .cart-popup,
				body.tax-product_cat .mini-cart.threew-cart-open .cart-popup,
				body.tax-product_tag #mini-cart.threew-cart-open .cart-popup,
				body.tax-product_tag .mini-cart.threew-cart-open .cart-popup,
				body.search #mini-cart.threew-cart-open .cart-popup,
				body.search .mini-cart.threew-cart-open .cart-popup {
					display: none !important;
				}

				body.single-product .cart-loading,
				body.single-product .cart-popup::before,
				body.single-product .cart-popup::after,
				body.single-product .widget_shopping_cart::before,
				body.single-product .widget_shopping_cart::after,
				body.single-product .cart-icon::after,
				body.single-product .mini-cart::after,
				body.single-product #mini-cart::after,
				body.single-product .cart-toggle::after,
				body.single-product .cart-head::after,
				body.single-product .cart-icon .diamond,
				body.single-product .cart-head .diamond,
				body.single-product .mini-cart .diamond,
				body.single-product [class*="cart"] .diamond,
				body.single-product #mini-cart.threew-cart-open .cart-popup,
				body.single-product .mini-cart.threew-cart-open .cart-popup {
					display: none !important;
				}

				body:not(.home) .threew-mobile-header-search {
					top: 23px;
					left: 58px;
					right: 120px;
				}

				body:not(.home) .threew-mobile-header-search .searchform-fields,
				body:not(.home) .threew-mobile-header-search input[type="text"],
				body:not(.home) .threew-mobile-header-search input[type="search"] {
					height: 44px !important;
					line-height: 44px !important;
				}

				body:not(.home) .threew-mobile-header-search .searchform-fields {
					box-shadow: 0 2px 8px rgba(0, 0, 0, .18) !important;
				}

				body:not(.home) .threew-mobile-header-search button[type="submit"] {
					flex-basis: 44px !important;
					width: 44px !important;
					height: 44px !important;
				}

				/* Mobile product page: keep first fold scannable without changing Porto markup. */
				body.single-product .threew-mobile-header-search {
					top: 18px !important;
					left: 54px !important;
					right: 104px !important;
				}

				body.single-product .threew-mobile-header-search .searchform-fields,
				body.single-product .threew-mobile-header-search input[type="text"],
				body.single-product .threew-mobile-header-search input[type="search"],
				body.single-product .threew-mobile-header-search button[type="submit"] {
					height: 46px !important;
					min-height: 46px !important;
					line-height: 46px !important;
				}

				body.single-product .threew-mobile-header-search .searchform-fields {
					border: 0 !important;
					box-shadow: 0 3px 12px rgba(0, 0, 0, .22) !important;
				}

				body.single-product .threew-mobile-header-search .text,
				body.single-product .threew-mobile-header-search input[type="text"],
				body.single-product .threew-mobile-header-search input[type="search"] {
					border: 0 !important;
					box-shadow: none !important;
					background: transparent !important;
				}

				body.single-product .threew-mobile-header-search input[type="text"],
				body.single-product .threew-mobile-header-search input[type="search"] {
					color: var(--threew-ink) !important;
					font-size: 15px !important;
				}

				body.single-product header a,
				body.single-product header button,
				body.single-product .mobile-toggle,
				body.single-product .cart-toggle {
					min-width: 44px !important;
					min-height: 44px !important;
				}

				body.single-product .breadcrumb,
				body.single-product .woocommerce-breadcrumb {
					display: block !important;
					max-width: 100% !important;
					margin: 6px 0 8px !important;
					overflow: hidden !important;
					color: var(--threew-text-muted) !important;
					font-size: 11px !important;
					font-weight: 500 !important;
					line-height: 1.35 !important;
					letter-spacing: .01em !important;
					text-overflow: ellipsis !important;
					text-transform: none !important;
					white-space: nowrap !important;
				}

				body.single-product .breadcrumb a,
				body.single-product .woocommerce-breadcrumb a {
					color: inherit !important;
				}

				body.single-product .product_title,
				body.single-product h1.product_title,
				body.single-product .summary .product_title {
					margin: 8px 0 8px !important;
					color: var(--threew-ink) !important;
					font-size: clamp(22px, 6vw, 28px) !important;
					font-weight: 800 !important;
					line-height: 1.12 !important;
					letter-spacing: -.03em !important;
				}

				body.single-product .summary,
				body.single-product .entry-summary {
					padding-top: 8px !important;
				}

				body.single-product .summary .price,
				body.single-product .entry-summary .price,
				body.single-product .porto-price-box {
					margin: 8px 0 10px !important;
					line-height: 1.25 !important;
				}

				body.single-product .product_meta,
				body.single-product .summary .posted_in,
				body.single-product .summary .tagged_as,
				body.single-product .summary .sku_wrapper {
					margin-top: 6px !important;
					color: var(--threew-text-muted) !important;
					font-size: 12px !important;
					line-height: 1.4 !important;
				}

				body.single-product .woocommerce-product-gallery,
				body.single-product .product-layout-full_width .product-images,
				body.single-product .product-images {
					position: relative !important;
					margin-bottom: 10px !important;
				}

				body.single-product .woocommerce-product-gallery .owl-nav,
				body.single-product .product-images .owl-nav {
					position: static !important;
					margin-top: 8px !important;
					text-align: center !important;
				}

				body.single-product .woocommerce-product-gallery .owl-nav button,
				body.single-product .product-images .owl-nav button,
				body.single-product .woocommerce-product-gallery .owl-prev,
				body.single-product .woocommerce-product-gallery .owl-next,
				body.single-product .product-images .owl-prev,
				body.single-product .product-images .owl-next {
					display: inline-flex !important;
					align-items: center !important;
					justify-content: center !important;
					min-width: 44px !important;
					min-height: 44px !important;
					margin: 0 4px !important;
					border-radius: 999px !important;
				}

				body.single-product form.cart,
				body.single-product .summary form.cart {
					display: flex !important;
					flex-wrap: wrap !important;
					gap: 8px !important;
					align-items: stretch !important;
					margin: 10px 0 12px !important;
				}

				body.single-product form.cart .quantity {
					margin: 0 !important;
				}

				body.single-product form.cart .quantity input,
				body.single-product form.cart .single_add_to_cart_button {
					min-height: 48px !important;
				}

				body.single-product form.cart .single_add_to_cart_button {
					flex: 1 1 190px !important;
					margin: 0 !important;
					border-radius: 8px !important;
					font-size: 16px !important;
					font-weight: 700 !important;
				}

				body.single-product form.cart .single_add_to_cart_button:focus-visible,
				body.single-product .woocommerce-product-gallery .owl-nav button:focus-visible,
				body.single-product .product-images .owl-nav button:focus-visible,
				body.single-product .woocommerce-product-gallery .owl-prev:focus-visible,
				body.single-product .woocommerce-product-gallery .owl-next:focus-visible,
				body.single-product .product-images .owl-prev:focus-visible,
				body.single-product .product-images .owl-next:focus-visible {
					outline: 3px solid var(--threew-focus-ring) !important;
					outline-offset: 3px !important;
				}

				/* Mobile category archive: screenshot target was product-category, not product page. */
				body.tax-product_cat .threew-mobile-header-search,
				body.post-type-archive-product .threew-mobile-header-search,
				body.woocommerce-shop .threew-mobile-header-search {
					top: 18px !important;
					left: 54px !important;
					right: 104px !important;
				}

				body.tax-product_cat .threew-mobile-header-search .searchform-fields,
				body.post-type-archive-product .threew-mobile-header-search .searchform-fields,
				body.woocommerce-shop .threew-mobile-header-search .searchform-fields {
					height: 46px !important;
					min-height: 46px !important;
					border: 0 !important;
					box-shadow: 0 3px 12px rgba(0, 0, 0, .22) !important;
				}

				body.tax-product_cat .threew-mobile-header-search .text,
				body.tax-product_cat .threew-mobile-header-search input[type="text"],
				body.tax-product_cat .threew-mobile-header-search input[type="search"],
				body.post-type-archive-product .threew-mobile-header-search .text,
				body.post-type-archive-product .threew-mobile-header-search input[type="text"],
				body.post-type-archive-product .threew-mobile-header-search input[type="search"],
				body.woocommerce-shop .threew-mobile-header-search .text,
				body.woocommerce-shop .threew-mobile-header-search input[type="text"],
				body.woocommerce-shop .threew-mobile-header-search input[type="search"] {
					border: 0 !important;
					box-shadow: none !important;
					background: transparent !important;
				}

				body.tax-product_cat .page-top.page-header-6 {
					display: none !important;
				}

				body.tax-product_cat .breadcrumb,
				body.tax-product_cat .woocommerce-breadcrumb,
				body.post-type-archive-product .breadcrumb,
				body.post-type-archive-product .woocommerce-breadcrumb,
				body.woocommerce-shop .breadcrumb,
				body.woocommerce-shop .woocommerce-breadcrumb {
					display: block !important;
					max-width: 100% !important;
					margin: 8px 0 12px !important;
					overflow: hidden !important;
					color: var(--threew-text-muted) !important;
					font-size: 11px !important;
					font-weight: 500 !important;
					line-height: 1.35 !important;
					text-overflow: ellipsis !important;
					text-transform: none !important;
					white-space: nowrap !important;
				}

				body.threew-mobile-archive-header-ready.tax-product_cat .breadcrumb,
				body.threew-mobile-archive-header-ready.tax-product_cat .woocommerce-breadcrumb,
				body.threew-mobile-archive-header-ready.post-type-archive-product .breadcrumb,
				body.threew-mobile-archive-header-ready.post-type-archive-product .woocommerce-breadcrumb,
				body.threew-mobile-archive-header-ready.woocommerce-shop .breadcrumb,
				body.threew-mobile-archive-header-ready.woocommerce-shop .woocommerce-breadcrumb,
				body.threew-mobile-archive-header-ready.search .breadcrumb,
				body.threew-mobile-archive-header-ready.search .woocommerce-breadcrumb {
					display: none !important;
				}

				body .threew-mobile-archive-header {
					margin: 4px 0 14px !important;
				}

				body .threew-mobile-breadcrumb-compact {
					display: flex !important;
					flex-wrap: wrap !important;
					align-items: center !important;
					gap: 6px !important;
					margin: 0 0 8px !important;
					color: var(--threew-text-muted) !important;
					font-size: 12px !important;
					font-weight: 600 !important;
					line-height: 1.4 !important;
				}

				body .threew-mobile-breadcrumb-compact a,
				body .threew-mobile-breadcrumb-compact span {
					color: inherit !important;
					text-decoration: none !important;
				}

				body .threew-mobile-archive-title {
					margin: 0 !important;
					color: var(--threew-ink) !important;
					font-size: clamp(28px, 8vw, 34px) !important;
					font-weight: 800 !important;
					line-height: 1.03 !important;
					letter-spacing: -.03em !important;
				}

				body.tax-product_cat .term-description,
				body.tax-product_cat .archive-description,
				body.post-type-archive-product .term-description,
				body.post-type-archive-product .archive-description,
				body.woocommerce-shop .term-description,
				body.woocommerce-shop .archive-description {
					margin: 0 0 18px !important;
					color: var(--threew-text-strong) !important;
					font-size: 14px !important;
					line-height: 1.65 !important;
				}

				body.tax-product_cat .shop-loop-before,
				body.post-type-archive-product .shop-loop-before,
				body.woocommerce-shop .shop-loop-before,
				body.tax-product_cat .woocommerce-ordering,
				body.post-type-archive-product .woocommerce-ordering,
				body.woocommerce-shop .woocommerce-ordering {
					margin: 0 0 14px !important;
				}

				body.tax-product_cat .shop-loop-before,
				body.post-type-archive-product .shop-loop-before,
				body.woocommerce-shop .shop-loop-before {
					display: grid !important;
					grid-template-columns: 78px minmax(0, 1fr) 56px !important;
					gap: 10px !important;
					align-items: stretch !important;
					padding: 12px !important;
					border-radius: 18px !important;
					background: #f7f9fc !important;
					box-shadow: inset 0 0 0 1px rgba(16, 20, 24, .05) !important;
				}

				body.tax-product_cat .shop-loop-before .porto-product-filters-toggle,
				body.post-type-archive-product .shop-loop-before .porto-product-filters-toggle,
				body.woocommerce-shop .shop-loop-before .porto-product-filters-toggle {
					display: inline-flex !important;
					align-items: center !important;
					justify-content: center !important;
					gap: 6px !important;
					min-height: 44px !important;
					padding: 0 10px !important;
					background: #fff !important;
					border: 1px solid rgba(16, 20, 24, .08) !important;
					border-radius: 12px !important;
					box-shadow: 0 1px 2px rgba(16, 20, 24, .04) !important;
				}

				body.tax-product_cat .shop-loop-before .porto-product-filters-toggle svg,
				body.post-type-archive-product .shop-loop-before .porto-product-filters-toggle svg,
				body.woocommerce-shop .shop-loop-before .porto-product-filters-toggle svg {
					width: 16px !important;
					height: 16px !important;
				}

				body.tax-product_cat .shop-loop-before .woocommerce-ordering,
				body.post-type-archive-product .shop-loop-before .woocommerce-ordering,
				body.woocommerce-shop .shop-loop-before .woocommerce-ordering {
					display: block !important;
					min-width: 0 !important;
					margin: 0 !important;
				}

				body.tax-product_cat .shop-loop-before .woocommerce-ordering label,
				body.post-type-archive-product .shop-loop-before .woocommerce-ordering label,
				body.woocommerce-shop .shop-loop-before .woocommerce-ordering label,
				body.tax-product_cat .shop-loop-before .woocommerce-pagination label,
				body.post-type-archive-product .shop-loop-before .woocommerce-pagination label,
				body.woocommerce-shop .shop-loop-before .woocommerce-pagination label,
				body.tax-product_cat .shop-loop-before .woocommerce-pagination .page-numbers,
				body.post-type-archive-product .shop-loop-before .woocommerce-pagination .page-numbers,
				body.woocommerce-shop .shop-loop-before .woocommerce-pagination .page-numbers,
				body.tax-product_cat .shop-loop-before .gridlist-toggle,
				body.post-type-archive-product .shop-loop-before .gridlist-toggle,
				body.woocommerce-shop .shop-loop-before .gridlist-toggle {
					display: none !important;
				}

				body.tax-product_cat .shop-loop-before .woocommerce-pagination,
				body.post-type-archive-product .shop-loop-before .woocommerce-pagination,
				body.woocommerce-shop .shop-loop-before .woocommerce-pagination {
					display: flex !important;
					align-items: center !important;
					justify-content: flex-end !important;
					margin: 0 !important;
				}

				body.tax-product_cat .shop-loop-before .woocommerce-viewing,
				body.post-type-archive-product .shop-loop-before .woocommerce-viewing,
				body.woocommerce-shop .shop-loop-before .woocommerce-viewing {
					margin: 0 !important;
				}

				body.tax-product_cat .shop-loop-before button,
				body.tax-product_cat .shop-loop-before .button,
				body.tax-product_cat .shop-loop-before select,
				body.post-type-archive-product .shop-loop-before button,
				body.post-type-archive-product .shop-loop-before .button,
				body.post-type-archive-product .shop-loop-before select,
				body.woocommerce-shop .shop-loop-before button,
				body.woocommerce-shop .shop-loop-before .button,
				body.woocommerce-shop .shop-loop-before select {
					min-height: 44px !important;
					border-radius: 12px !important;
					font-size: 12px !important;
					font-weight: 700 !important;
				}

				body.tax-product_cat .shop-loop-before .orderby,
				body.post-type-archive-product .shop-loop-before .orderby,
				body.woocommerce-shop .shop-loop-before .orderby {
					width: 100% !important;
					padding: 0 36px 0 14px !important;
					background: #fff !important;
					border: 1px solid rgba(16, 20, 24, .08) !important;
					font-size: 11px !important;
				}

				body.tax-product_cat .shop-loop-before .count,
				body.post-type-archive-product .shop-loop-before .count,
				body.woocommerce-shop .shop-loop-before .count {
					width: 56px !important;
					min-width: 56px !important;
					padding: 0 24px 0 12px !important;
					background: #fff !important;
					border: 1px solid rgba(16, 20, 24, .08) !important;
				}

				body.tax-product_cat .shop-loop-before .porto-product-filters-toggle,
				body.post-type-archive-product .shop-loop-before .porto-product-filters-toggle,
				body.woocommerce-shop .shop-loop-before .porto-product-filters-toggle,
				body.tax-product_cat .shop-loop-before .orderby,
				body.post-type-archive-product .shop-loop-before .orderby,
				body.woocommerce-shop .shop-loop-before .orderby,
				body.tax-product_cat .shop-loop-before .count,
				body.post-type-archive-product .shop-loop-before .count,
				body.woocommerce-shop .shop-loop-before .count,
				body.home .threew-mobile-cart-popup .total-count a {
					transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease, background-color .16s ease !important;
				}

				body.tax-product_cat .shop-loop-before .porto-product-filters-toggle:active,
				body.post-type-archive-product .shop-loop-before .porto-product-filters-toggle:active,
				body.woocommerce-shop .shop-loop-before .porto-product-filters-toggle:active,
				body.home .threew-mobile-cart-popup .total-count a:active {
					transform: translateY(1px) !important;
				}

				body.tax-product_cat .shop-loop-before .porto-product-filters-toggle:focus-visible,
				body.post-type-archive-product .shop-loop-before .porto-product-filters-toggle:focus-visible,
				body.woocommerce-shop .shop-loop-before .porto-product-filters-toggle:focus-visible,
				body.tax-product_cat .shop-loop-before .orderby:focus-visible,
				body.post-type-archive-product .shop-loop-before .orderby:focus-visible,
				body.woocommerce-shop .shop-loop-before .orderby:focus-visible,
				body.tax-product_cat .shop-loop-before .count:focus-visible,
				body.post-type-archive-product .shop-loop-before .count:focus-visible,
				body.woocommerce-shop .shop-loop-before .count:focus-visible,
				body.home .threew-mobile-cart-popup .total-count a:focus-visible {
					outline: none !important;
					border-color: rgba(25, 195, 125, .45) !important;
					box-shadow: 0 0 0 3px rgba(25, 195, 125, .16) !important;
				}

				body.tax-product_cat ul.products,
				body.post-type-archive-product ul.products,
				body.woocommerce-shop ul.products {
					display: grid !important;
					grid-template-columns: 1fr !important;
					gap: 22px !important;
					margin-top: 0 !important;
				}

				body.tax-product_cat ul.products li.product,
				body.post-type-archive-product ul.products li.product,
				body.woocommerce-shop ul.products li.product {
					width: 100% !important;
					margin: 0 !important;
					padding: 0 !important;
					border: 1px solid rgba(16, 20, 24, .08) !important;
					border-radius: 20px !important;
					background: #fff !important;
					overflow: hidden !important;
					box-shadow: 0 10px 24px rgba(15, 23, 42, .06) !important;
				}

				body.tax-product_cat ul.products li.product .product-inner,
				body.post-type-archive-product ul.products li.product .product-inner,
				body.woocommerce-shop ul.products li.product .product-inner {
					display: flex !important;
					flex-direction: column !important;
					height: 100% !important;
					background: #fff !important;
				}

				body.tax-product_cat ul.products li.product .product-image,
				body.post-type-archive-product ul.products li.product .product-image,
				body.woocommerce-shop ul.products li.product .product-image {
					margin: 0 !important;
					background: #fff !important;
				}

				body.tax-product_cat ul.products li.product .product-image img,
				body.tax-product_cat ul.products li.product img,
				body.post-type-archive-product ul.products li.product .product-image img,
				body.post-type-archive-product ul.products li.product img,
				body.woocommerce-shop ul.products li.product .product-image img,
				body.woocommerce-shop ul.products li.product img {
					width: 100% !important;
					max-width: 100% !important;
				}

				body.tax-product_cat ul.products li.product .product-content,
				body.post-type-archive-product ul.products li.product .product-content,
				body.woocommerce-shop ul.products li.product .product-content {
					padding: 14px 16px 18px !important;
				}

				body.tax-product_cat ul.products li.product .category-list,
				body.post-type-archive-product ul.products li.product .category-list,
				body.woocommerce-shop ul.products li.product .category-list {
					display: block !important;
					margin: 0 0 6px !important;
					color: var(--threew-text-muted) !important;
					font-size: 11px !important;
					letter-spacing: .04em !important;
					text-transform: uppercase !important;
				}

				body.tax-product_cat ul.products li.product .woocommerce-loop-product__title,
				body.tax-product_cat ul.products li.product .product-title,
				body.tax-product_cat ul.products li.product h3,
				body.post-type-archive-product ul.products li.product .woocommerce-loop-product__title,
				body.post-type-archive-product ul.products li.product .product-title,
				body.post-type-archive-product ul.products li.product h3,
				body.woocommerce-shop ul.products li.product .woocommerce-loop-product__title,
				body.woocommerce-shop ul.products li.product .product-title,
				body.woocommerce-shop ul.products li.product h3 {
					margin: 8px 12px 4px !important;
					color: var(--threew-ink) !important;
					font-size: 14px !important;
					font-weight: 800 !important;
					line-height: 1.25 !important;
				}

				body.tax-product_cat ul.products li.product .price,
				body.post-type-archive-product ul.products li.product .price,
				body.woocommerce-shop ul.products li.product .price {
					display: block !important;
					margin: 0 12px !important;
					color: var(--threew-ink) !important;
					font-size: 16px !important;
					font-weight: 800 !important;
					line-height: 1.25 !important;
				}

				body.tax-product_cat ul.products li.product .add_to_cart_button,
				body.tax-product_cat ul.products li.product .quickview,
				body.post-type-archive-product ul.products li.product .add_to_cart_button,
				body.post-type-archive-product ul.products li.product .quickview,
				body.woocommerce-shop ul.products li.product .add_to_cart_button,
				body.woocommerce-shop ul.products li.product .quickview {
					min-width: 44px !important;
					min-height: 44px !important;
				}

				/* Search results pages: extend the deliberate single-column large-card
			   catalog treatment (see docs/larger-product-card-mobile-audit.md) to
			   body.search, which the selectors above do not cover. Keeps one card
			   per row but caps image height so ~1.5-2 cards show per screen. */
				body.search .breadcrumb,
				body.search .woocommerce-breadcrumb {
					display: block !important;
					max-width: 100% !important;
					margin: 8px 0 12px !important;
					overflow: hidden !important;
					color: var(--threew-ink) !important;
					font-size: 14px !important;
					font-weight: 700 !important;
					line-height: 1.35 !important;
					text-overflow: ellipsis !important;
					white-space: nowrap !important;
				}

				body.search .shop-loop-before,
				body.search .woocommerce-ordering {
					margin: 0 0 14px !important;
				}

				body.search .shop-loop-before {
					display: flex !important;
					flex-wrap: wrap !important;
					gap: 8px !important;
					align-items: center !important;
					padding: 10px !important;
					background: #f6f7f9 !important;
				}

				body.search .shop-loop-before button,
				body.search .shop-loop-before .button,
				body.search .shop-loop-before select {
					min-height: 44px !important;
					border-radius: 4px !important;
					font-size: 12px !important;
					font-weight: 700 !important;
				}

				body.search ul.products {
					display: grid !important;
					grid-template-columns: 1fr !important;
					gap: 22px !important;
					margin-top: 0 !important;
				}

				body.search ul.products li.product {
					width: 100% !important;
					margin: 0 !important;
					padding: 0 0 14px !important;
					border-bottom: 1px solid rgba(16, 20, 24, .08) !important;
					border-radius: 8px !important;
				}

				/* Specificicity must beat the existing catalog rule
				   `body:not(.home).woocommerce ul.products li.product .product-image img { max-height: 100% !important }`
				   (0,5,4). Use body:not(.home).woocommerce.search (0,6,4) so the image-height
				   cap actually computes; cap the wrappers too (they carry max-height:none). */
				body:not(.home).woocommerce.search ul.products li.product .product-image {
					width: 100% !important;
					max-width: 100% !important;
					max-height: 45vh !important;
					overflow: hidden !important;
				}

				body:not(.home).woocommerce.search ul.products li.product .product-image > a,
				body:not(.home).woocommerce.search ul.products li.product .product-image .inner {
					max-height: 45vh !important;
					overflow: hidden !important;
				}

				body:not(.home).woocommerce.search ul.products li.product .product-image img,
				body:not(.home).woocommerce.search ul.products li.product img {
					width: 100% !important;
					max-width: 100% !important;
					max-height: 45vh !important;
					aspect-ratio: 1 !important;
					object-fit: contain !important;
					object-position: center !important;
				}

				body.search ul.products li.product .woocommerce-loop-product__title,
				body.search ul.products li.product .product-title,
				body.search ul.products li.product h3 {
					margin: 8px 12px 4px !important;
					color: var(--threew-ink) !important;
					font-size: 14px !important;
					font-weight: 800 !important;
					line-height: 1.25 !important;
				}

				body.search ul.products li.product .category-list {
					margin: 0 12px !important;
					overflow: hidden !important;
					color: var(--threew-text-muted) !important;
					font-size: 10px !important;
					line-height: 1.3 !important;
					text-overflow: ellipsis !important;
					white-space: nowrap !important;
				}

				body.search ul.products li.product .price {
					display: block !important;
					margin: 0 12px !important;
					color: var(--threew-ink) !important;
					font-size: 16px !important;
					font-weight: 800 !important;
					line-height: 1.25 !important;
				}

				body.search ul.products li.product .add_to_cart_button,
				body.search ul.products li.product .quickview {
					min-width: 44px !important;
					min-height: 44px !important;
				}

				/* Mobile home top band: remove Porto gutter so hero/shortcuts can bleed edge-to-edge. */
				body.home #main > .container-fluid {
					padding-left: 0 !important;
					padding-right: 0 !important;
				}

				/* Mobile hero: compact, centered, and CTA-first. */
				body.home .home-banner {
					width: 100vw !important;
					max-width: 100vw !important;
					margin-top: 0 !important;
					margin-right: calc(50% - 50vw) !important;
					margin-bottom: 0 !important;
					margin-left: calc(50% - 50vw) !important;
					background: var(--threew-ink);
				}

				body.home .home-banner .porto-ibanner {
					position: relative !important;
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					min-height: 318px !important;
					max-height: 335px !important;
					overflow: hidden !important;
					background: var(--threew-ink) !important;
				}

				body.home .home-banner .porto-ibanner:after {
					content: "";
					position: absolute;
					inset: 0;
					z-index: 1;
					pointer-events: none;
					background:
						radial-gradient(circle at 50% 43%, rgba(0, 0, 0, .2) 0%, rgba(0, 0, 0, .58) 58%, rgba(0, 0, 0, .82) 100%),
						linear-gradient(180deg, rgba(16, 20, 24, .12) 0%, rgba(16, 20, 24, .72) 100%);
				}

				body.home .home-banner .porto-ibanner-img {
					height: 318px !important;
					object-fit: cover !important;
					object-position: center center !important;
					filter: brightness(.7) contrast(1.12);
				}

				body.home .home-banner .porto-ibanner-desc,
				body.home .home-banner .porto-ibanner-layer {
					z-index: 2 !important;
				}

				body.home .home-banner .porto-ibanner-desc {
					position: absolute !important;
					top: 50% !important;
					right: 24px !important;
					bottom: auto !important;
					left: 24px !important;
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					width: auto !important;
					max-width: none !important;
					transform: translateY(-50%) !important;
					text-align: center !important;
				}

				body.home .home-banner .porto-ibanner-desc .porto-ibanner-layer {
					position: static !important;
					inset: auto !important;
					width: 100% !important;
					max-width: 330px !important;
					text-align: center !important;
					transform: none !important;
				}

				body.home .home-banner h1,
				body.home .home-banner h2,
				body.home .home-banner h3,
				body.home .home-banner h4 {
					max-width: 12ch;
					margin-right: auto !important;
					margin-left: auto !important;
					color: #fff !important;
					font-size: clamp(30px, 8.6vw, 40px) !important;
					line-height: .9 !important;
					letter-spacing: .025em !important;
					text-align: center !important;
					text-shadow: 0 3px 22px rgba(0, 0, 0, .75);
				}

				body.home .home-banner h4 {
					max-width: 24ch;
					font-size: 13px !important;
					line-height: 1.15 !important;
					letter-spacing: .14em !important;
				}

				body.home .home-banner p,
				body.home .home-banner .porto-ibanner-desc span {
					color: rgba(255, 255, 255, .96) !important;
					font-size: 14px !important;
					font-weight: 800 !important;
					letter-spacing: .14em !important;
					text-shadow: 0 2px 14px rgba(0, 0, 0, .7);
				}

				body.home .home-banner .vc_btn3-container {
					display: block !important;
					text-align: center !important;
				}

				body.home .home-banner .btn,
				body.home .home-banner a.btn,
				body.home .home-banner .porto-btn {
					min-height: 48px !important;
					margin: 14px auto 0 !important;
					padding: 14px 20px !important;
					border-radius: 0 !important;
					font-size: 13px !important;
					font-weight: 800 !important;
					letter-spacing: .03em !important;
					box-shadow: 0 12px 28px rgba(0, 0, 0, .28);
				}

				body.home .threew-mobile-shop-shortcuts {
					display: block;
					width: 100vw;
					max-width: 100vw;
					margin-right: calc(50% - 50vw);
					margin-left: calc(50% - 50vw);
					padding: 18px 16px 22px;
					background: var(--threew-ink);
					color: #fff;
				}

				body.home .threew-mobile-shop-shortcuts__eyebrow {
					margin: 0 0 10px;
					color: rgba(255, 255, 255, .66);
					font-size: 11px;
					font-weight: 800;
					letter-spacing: .16em;
					text-transform: uppercase;
				}

				body.home .threew-mobile-shop-shortcuts__grid {
					display: grid;
					grid-template-columns: repeat(2, minmax(0, 1fr));
					gap: 10px;
				}

				body.home .threew-mobile-shop-shortcuts a {
					display: flex;
					align-items: center;
					justify-content: space-between;
					min-height: 48px;
					padding: 0 14px;
					border: 1px solid rgba(255, 255, 255, .12);
					border-radius: 14px;
					background: rgba(255, 255, 255, .06);
					color: #fff !important;
					font-size: 13px;
					font-weight: 800;
					text-decoration: none !important;
				}

				body.home .threew-mobile-shop-shortcuts a:first-child,
				body.home .threew-mobile-shop-shortcuts a:nth-child(2) {
					border-color: rgba(25, 195, 125, .45);
					background: rgba(25, 195, 125, .14);
				}

				body.home .threew-mobile-shop-shortcuts a:after {
					content: "›";
					color: var(--threew-accent);
					font-size: 22px;
					line-height: 1;
				}

				html.panel-opened body.home .header-side .searchform-popup.advanced-search-layout {
					visibility: hidden !important;
				}

				body .mobile-menu .threew-mobile-menu-search {
					padding: 14px 16px;
					border-bottom: 1px solid rgba(255, 255, 255, .08);
				}

				body .mobile-menu .threew-mobile-menu-search .searchform.search-layout-advanced {
					width: 100% !important;
					max-width: none !important;
					top: auto !important;
					margin: 0 !important;
					display: flex !important;
				}

				body .mobile-menu .threew-mobile-menu-search .searchform-fields {
					display: flex !important;
					align-items: center !important;
					width: 100% !important;
					height: 44px !important;
					background: #fff !important;
					border-radius: 999px !important;
					overflow: hidden !important;
				}

				body .mobile-menu .threew-mobile-menu-search .text {
					flex: 1 1 auto !important;
					min-width: 0 !important;
				}

				body .mobile-menu .threew-mobile-menu-search input[type="text"],
				body .mobile-menu .threew-mobile-menu-search input[type="search"] {
					box-sizing: border-box !important;
					width: 100% !important;
					min-width: 0 !important;
					max-width: none !important;
					border: 0 !important;
					box-shadow: none !important;
				}

				body .mobile-menu .threew-mobile-menu-search button[type="submit"] {
					display: flex !important;
					position: static !important;
					inset: auto !important;
					align-items: center !important;
					justify-content: center !important;
					flex: 0 0 44px !important;
					margin: 0 !important;
					transform: none !important;
					color: #1f2933 !important;
					background: transparent !important;
				}

				body .mobile-menu .threew-mobile-menu-search button[type="submit"] i {
					color: #1f2933 !important;
				}

				/* Footer copyright is being injected above the hero on mobile in the Porto side-nav layout. */
				body.home #main .main-content-wrap > .footer-bottom {
					display: none !important;
				}

				body.home .qlwapp__container {
					width: auto !important;
					height: auto !important;
					right: max(16px, env(safe-area-inset-right)) !important;
					bottom: max(16px, env(safe-area-inset-bottom)) !important;
					left: auto !important;
					align-items: flex-end !important;
					z-index: 50;
				}

				body.home .qlwapp__button {
					width: 56px !important;
					height: 56px !important;
					min-width: 56px !important;
					min-height: 56px !important;
					border-radius: 999px !important;
					padding: 0 !important;
					justify-content: center !important;
				}

				body.home .qlwapp__button .qlwapp__icon {
					margin: 0 !important;
					font-size: 27px !important;
				}

				body.home .qlwapp__text,
				body.home .qlwapp__time {
					display: none !important;
				}

				/* Mobile drawer: make it feel intentional instead of a narrow leftover side panel. */
				html.panel-opened body #side-nav-panel,
				html.panel-opened body .side-nav-panel {
					width: min(88vw, 360px) !important;
					max-width: min(88vw, 360px) !important;
					background: var(--threew-ink-soft) !important;
				}

				body.home .mobile-menu li > a {
					display: flex !important;
					align-items: center !important;
					min-height: 50px !important;
					padding: 0 18px !important;
					border-bottom: 1px solid rgba(255, 255, 255, .06) !important;
					color: #fff !important;
					font-size: 13px !important;
					font-weight: 750 !important;
				}

				body.home .mobile-menu li > a:hover,
				body.home .mobile-menu li > a:focus-visible {
					background: rgba(255, 255, 255, .06) !important;
				}

				body.home .mobile-menu .tip,
				body.home .mobile-menu .menu-label,
				body.home .mobile-menu .narrow .tip {
					margin-left: 6px !important;
					padding: 3px 5px !important;
					border-radius: 4px !important;
					background: var(--threew-accent) !important;
					color: #fff !important;
					font-size: 8px !important;
					font-weight: 900 !important;
					line-height: 1 !important;
				}

				/* Mobile product sweep: one stable card system for every Porto product section/layer. */
				body.home .threew-product-strip {
					box-sizing: border-box !important;
					width: 100vw !important;
					max-width: 100vw !important;
					margin-right: calc(50% - 50vw) !important;
					margin-left: calc(50% - 50vw) !important;
					padding: 28px 16px 0 !important;
				}

				body.home .threew-product-strip > .vc_column_container,
				body.home .threew-product-strip .vc_column-inner,
				body.home .threew-product-strip .wpb_wrapper,
				body.home .threew-product-strip .porto-products,
				body.home .threew-product-strip .slider-wrapper,
				body.home .threew-product-strip .woocommerce {
					box-sizing: border-box !important;
					width: 100% !important;
					max-width: 100% !important;
					margin-right: 0 !important;
					margin-left: 0 !important;
					padding-right: 0 !important;
					padding-left: 0 !important;
				}

				body.home .products,
				body.home ul.products,
				body.home ul.products.products-slider.owl-carousel {
					display: grid !important;
					grid-template-columns: repeat(2, minmax(0, 1fr));
					justify-items: stretch !important;
					gap: 24px 14px !important;
					box-sizing: border-box !important;
					width: 100% !important;
					max-width: 100% !important;
					margin-right: 0 !important;
					margin-left: 0 !important;
					padding-right: 0 !important;
					padding-left: 0 !important;
					list-style: none !important;
				}

				body.home ul.products.products-slider.owl-carousel .owl-stage-outer,
				body.home ul.products.products-slider.owl-carousel .owl-stage {
					display: contents !important;
					width: auto !important;
					height: auto !important;
					transform: none !important;
				}

				body.home ul.products.products-slider.owl-carousel .owl-item {
					display: block !important;
					width: auto !important;
					max-width: none !important;
					margin: 0 !important;
					padding: 0 !important;
					opacity: 1 !important;
					filter: none !important;
				}

				body.home ul.products.products-slider.owl-carousel .owl-item.cloned,
				body.home ul.products.products-slider.owl-carousel .owl-nav,
				body.home ul.products.products-slider.owl-carousel .owl-dots {
					display: none !important;
				}

				body.home ul.products li.product,
				body.home .products .product-col,
				body.home ul.products.products-slider.owl-carousel .owl-item:not(.cloned),
				body.home ul.products.products-slider.owl-carousel .product-col,
				body.home ul.products.products-slider.owl-carousel li.product {
					box-sizing: border-box !important;
					width: 100% !important;
					max-width: 100% !important;
					min-width: 0 !important;
					margin: 0 !important;
					padding: 0 !important;
					float: none !important;
					flex: none !important;
					justify-self: stretch !important;
				}

				body.home ul.products li.product .product-inner,
				body.home .products .product-col .product-inner {
					display: flex !important;
					flex-direction: column !important;
					gap: 9px !important;
					box-sizing: border-box !important;
					width: 100% !important;
					height: 100% !important;
					padding: 14px !important;
					border: 1px solid #e5e8ee;
					border-radius: 18px;
					background: #fff;
					box-shadow: 0 10px 26px rgba(16, 24, 40, .08);
					overflow: hidden;
				}

				body.home ul.products li.product .product-image,
				body.home .products .product-col .product-image {
					display: grid !important;
					place-items: center !important;
					box-sizing: border-box !important;
					width: 100% !important;
					aspect-ratio: 4 / 3 !important;
					min-height: 0 !important;
					max-height: none !important;
					margin: 0 !important;
					padding: 8px !important;
					border-radius: 14px;
					background: #f7f9fc;
					overflow: hidden !important;
				}

				body.home ul.products li.product .product-image > a,
				body.home .products .product-col .product-image > a {
					display: grid !important;
					position: absolute !important;
					inset: 8px !important;
					place-items: center !important;
					width: auto !important;
					height: auto !important;
				}

				body.home ul.products li.product .product-image .inner,
				body.home .products .product-col .product-image .inner {
					display: grid !important;
					place-items: center !important;
					width: 100% !important;
					height: 100% !important;
					max-height: none !important;
					overflow: visible !important;
				}

				body.home ul.products li.product .product-image img.hover-image,
				body.home .products .product-col .product-image img.hover-image {
					display: none !important;
				}

				body.home ul.products li.product .product-image img,
				body.home .products .product-col .product-image img {
					display: block !important;
					width: 100% !important;
					height: 100% !important;
					max-width: 100% !important;
					max-height: 100% !important;
					object-fit: contain !important;
					object-position: center !important;
					opacity: 1 !important;
					filter: none !important;
				}

				body.home ul.products li.product .labels,
				body.home .products .product-col .labels {
					top: 8px !important;
					left: 8px !important;
				}

				body.home ul.products li.product .quickview,
				body.home .products .product-col .quickview {
					top: 10px !important;
					right: 10px !important;
					width: 40px !important;
					height: 40px !important;
					min-width: 40px !important;
					min-height: 40px !important;
					border-radius: 999px !important;
					background: #fff !important;
					box-shadow: 0 4px 14px rgba(15, 23, 42, .12) !important;
				}

				body.home ul.products li.product .product-content,
				body.home .products .product-col .product-content {
					box-sizing: border-box !important;
					width: 100% !important;
					padding: 0 !important;
				}

				body.home ul.products li.product .product-loop-title,
				body.home .products .product-col .product-loop-title,
				body.home .woocommerce-loop-product__title,
				body.home ul.products li.product h3,
				body.home ul.products li.product .product-name {
					-webkit-line-clamp: 2;
					min-height: 2.6em;
					margin: 0 !important;
					color: #111827;
					font-size: 14px;
					font-weight: 800;
					line-height: 1.3;
				}

				body.home ul.products li.product .category-list,
				body.home .products .product-col .category-list {
					min-height: 0;
					margin: 0 !important;
					color: #6b7280;
					font-size: 10px;
					font-weight: 800;
					line-height: 1.2;
					text-transform: uppercase;
				}

				body.home ul.products li.product .price,
				body.home .products .product-col .price {
					margin: 1px 0 0 !important;
					color: #0f172a;
					font-size: 16px;
					font-weight: 900;
					line-height: 1.2;
				}

				body.home ul.products li.product .affirm-as-low-as,
				body.home ul.products li.product .affirm-modal-trigger {
					font-size: 11px;
					line-height: 1.3;
				}

				body.home ul.products li.product .affirm-as-low-as {
					min-height: 18px;
					margin: 0 !important;
					font-size: 0 !important;
					line-height: 1 !important;
				}

				body.home ul.products li.product .affirm-as-low-as > * {
					display: none !important;
				}

				body.home ul.products li.product .affirm-as-low-as:before {
					content: "Financing available";
					display: inline-flex;
					align-items: center;
					min-height: 18px;
					padding: 2px 7px;
					border-radius: 999px;
					background: #eef2ff;
					color: #4338ca;
					font-size: 10px;
					font-weight: 800;
					letter-spacing: .01em;
				}

				body.home .home-mid-banner {
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					min-height: 190px !important;
					margin: 34px 0 36px !important;
					background: linear-gradient(135deg, #eceff3, #dfe4e9) !important;
					overflow: hidden !important;
				}

				body.home .home-mid-banner .porto-ibanner-desc {
					position: relative !important;
					inset: auto !important;
					width: 100% !important;
					min-height: 190px !important;
					align-items: center !important;
					justify-content: center !important;
					padding: 22px !important;
					text-align: center !important;
				}

				body.home .home-mid-banner .porto-ibanner-layer {
					position: static !important;
					transform: none !important;
					text-align: center !important;
				}

				body.home .home-mid-banner .coupon-sale-text {
					margin: 8px 0 12px !important;
					color: #111827 !important;
					font-size: 24px !important;
					line-height: 1.05 !important;
				}

				body.home .home-mid-banner .vc_btn3 {
					min-height: 42px !important;
					padding: 11px 16px !important;
					background: #111827 !important;
					color: #fff !important;
					font-size: 12px !important;
					font-weight: 900 !important;
				}

				body.home .threew-dealer-strip {
					padding: 30px 0 34px !important;
					background: #fff;
				}

				body.home .threew-dealer-strip .wpb_heading,
				body.home .threew-dealer-strip h2 {
					margin: 0 0 22px !important;
					color: #1f2933 !important;
					font-size: 26px !important;
					line-height: 1.15 !important;
					letter-spacing: -.02em !important;
					text-align: left !important;
				}

				body.home .threew-dealer-strip .vc_images_carousel,
				body.home .threew-dealer-strip .vc_carousel-slideline,
				body.home .threew-dealer-strip .vc_carousel-slideline-inner {
					width: 100% !important;
					height: auto !important;
					transform: none !important;
				}

				body.home .threew-dealer-strip .vc_carousel-slideline-inner {
					display: grid !important;
					grid-template-columns: repeat(3, minmax(0, 1fr));
					gap: 14px 12px;
				}

				body.home .threew-dealer-strip .vc_item {
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					width: auto !important;
					height: 56px !important;
					padding: 8px !important;
					background: #fff;
				}

				body.home .threew-dealer-strip .vc_inner {
					display: flex !important;
					align-items: center !important;
					justify-content: center !important;
					width: 100% !important;
					height: 100% !important;
				}

				body.home .threew-dealer-strip img {
					display: block !important;
					width: 72px !important;
					max-width: 72px !important;
					height: 42px !important;
					max-height: 42px !important;
					object-fit: contain !important;
					opacity: 1 !important;
					filter: none !important;
				}

				body.home .threew-footer-links {
					padding-top: 22px !important;
					border-top: 1px solid #e5e7eb;
					background: #fff;
				}

				body.home .threew-footer-links h4 {
					margin: 0 0 12px !important;
					color: #1f2933 !important;
					font-size: 14px !important;
					font-weight: 900 !important;
					letter-spacing: .01em !important;
				}

				body.home .threew-footer-links ul {
					list-style: none !important;
					margin: 0 0 22px !important;
					padding: 0 !important;
				}

				body.home .threew-footer-links li {
					list-style: none !important;
					min-height: 30px;
					margin: 0 !important;
					padding: 0 !important;
				}
			}

			@media (max-width: 600px) {
				body:not(.home) .woocommerce ul.products,
				body:not(.home).woocommerce ul.products {
					display: grid !important;
					grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
					gap: 18px 12px !important;
					width: 100% !important;
					max-width: 100% !important;
					margin-right: 0 !important;
					margin-left: 0 !important;
					padding-right: 0 !important;
					padding-left: 0 !important;
				}

				body:not(.home) .woocommerce ul.products li.product,
				body:not(.home) .woocommerce .products .product-col,
				body:not(.home).woocommerce ul.products li.product {
					box-sizing: border-box !important;
					width: 100% !important;
					max-width: 100% !important;
					margin: 0 !important;
					padding: 0 !important;
					float: none !important;
				}

				body:not(.home) .woocommerce ul.products li.product .product-image,
				body:not(.home) .woocommerce .products .product-col .product-image,
				body:not(.home).woocommerce ul.products li.product .product-image {
					display: grid !important;
					place-items: center !important;
					aspect-ratio: 4 / 3 !important;
					height: auto !important;
					min-height: 0 !important;
					max-height: none !important;
					overflow: hidden !important;
				}

				body:not(.home) .woocommerce ul.products li.product .product-image > a,
				body:not(.home) .woocommerce .products .product-col .product-image > a,
				body:not(.home).woocommerce ul.products li.product .product-image > a,
				body:not(.home) .woocommerce ul.products li.product .product-image .inner,
				body:not(.home) .woocommerce .products .product-col .product-image .inner,
				body:not(.home).woocommerce ul.products li.product .product-image .inner {
					display: grid !important;
					place-items: center !important;
					width: 100% !important;
					height: 100% !important;
					max-height: none !important;
					overflow: visible !important;
				}

				body:not(.home) .woocommerce ul.products li.product .product-image img,
				body:not(.home) .woocommerce .products .product-col .product-image img,
				body:not(.home).woocommerce ul.products li.product .product-image img {
					display: block !important;
					width: 100% !important;
					height: 100% !important;
					max-width: 100% !important;
					max-height: 100% !important;
					object-fit: contain !important;
					object-position: center !important;
				}

				body:not(.home).woocommerce .qlwapp__container,
				body.post-type-archive-product .qlwapp__container,
				body.tax-product_cat .qlwapp__container,
				body.tax-product_tag .qlwapp__container {
					width: auto !important;
					height: auto !important;
					right: max(8px, env(safe-area-inset-right)) !important;
					bottom: max(6px, env(safe-area-inset-bottom)) !important;
					left: auto !important;
					align-items: flex-end !important;
					z-index: 50 !important;
				}

				body:not(.home).woocommerce .qlwapp__button,
				body.post-type-archive-product .qlwapp__button,
				body.tax-product_cat .qlwapp__button,
				body.tax-product_tag .qlwapp__button {
					width: 44px !important;
					height: 44px !important;
					min-width: 44px !important;
					min-height: 44px !important;
					padding: 0 !important;
					border-radius: 999px !important;
					justify-content: center !important;
				}

				body:not(.home).woocommerce .qlwapp__button .qlwapp__icon,
				body.post-type-archive-product .qlwapp__button .qlwapp__icon,
				body.tax-product_cat .qlwapp__button .qlwapp__icon,
				body.tax-product_tag .qlwapp__button .qlwapp__icon {
					margin: 0 !important;
					font-size: 20px !important;
				}

				body:not(.home).woocommerce .qlwapp__text,
				body:not(.home).woocommerce .qlwapp__time,
				body.post-type-archive-product .qlwapp__text,
				body.post-type-archive-product .qlwapp__time,
				body.tax-product_cat .qlwapp__text,
				body.tax-product_cat .qlwapp__time,
				body.tax-product_tag .qlwapp__text,
				body.tax-product_tag .qlwapp__time {
					display: none !important;
				}
			}

			@media (max-width: 600px) {
				/* Search results: keep the deliberate single-column large-card layout,
				   but make the filter bar a compact sticky pill row and lift the
				   WhatsApp FAB above card content. */
				body.search {
					padding-bottom: 96px;
				}

				body.search .shop-loop-before {
					position: sticky !important;
					top: 0 !important;
					z-index: 5 !important;
					margin: 0 0 14px !important;
					padding: 8px 12px !important;
					background: #fff !important;
					border-bottom: 1px solid #e5e5e5 !important;
				}

				body.search .woocommerce-result-count,
				body.search .shop-loop-before .woocommerce-viewing {
					display: none !important;
				}

				body.search .shop-loop-before button,
				body.search .shop-loop-before .button,
				body.search .woocommerce-ordering select {
					min-height: 44px !important;
				}

				body.tax-product_cat .sidebar.mobile-sidebar,
				body.tax-product_tag .sidebar.mobile-sidebar,
				body.post-type-archive-product .sidebar.mobile-sidebar,
				body.woocommerce-shop .sidebar.mobile-sidebar {
					position: fixed !important;
					top: 0 !important;
					bottom: 0 !important;
					left: -88vw !important;
					z-index: 10000 !important;
					display: block !important;
					width: min(88vw, 320px) !important;
					height: 100vh !important;
					margin: 0 !important;
					padding: 72px 18px 24px !important;
					overflow-y: auto !important;
					background: #fff !important;
					box-shadow: 8px 0 24px rgba(0, 0, 0, .22) !important;
					transition: left .2s ease !important;
				}

				body.threew-mobile-filters-open .sidebar.mobile-sidebar {
					left: 0 !important;
				}

				body.tax-product_cat .page-wrapper.side-nav,
				body.tax-product_tag .page-wrapper.side-nav,
				body.post-type-archive-product .page-wrapper.side-nav,
				body.woocommerce-shop .page-wrapper.side-nav {
					left: 0 !important;
					margin-left: 0 !important;
					transform: none !important;
				}

				body.threew-mobile-filters-open::after {
					content: "";
					position: fixed;
					inset: 0;
					z-index: 9999;
					background: rgba(0, 0, 0, .35);
				}

				/* Specificicity must beat the existing catalog FAB rule
				   `body:not(.home).woocommerce .qlwapp__container { bottom: 16px }` (0,3,1).
				   Use body:not(.home).woocommerce.search (0,4,1) so the raise to 80px computes. */
				body:not(.home).woocommerce.search .qlwapp__container,
				body:not(.home).woocommerce.search .qlwapp-toggle,
				body:not(.home).woocommerce.search a[href*="wa.me"],
				body:not(.home).woocommerce.search a[href*="whatsapp"] {
					bottom: max(80px, env(safe-area-inset-bottom)) !important;
					right: max(16px, env(safe-area-inset-right)) !important;
					z-index: 50 !important;
				}

				body:not(.home).woocommerce.search .qlwapp__button {
					width: 52px !important;
					height: 52px !important;
					min-width: 52px !important;
					min-height: 52px !important;
					padding: 0 !important;
					border-radius: 999px !important;
					justify-content: center !important;
				}

				body:not(.home).woocommerce.search .qlwapp__text,
				body:not(.home).woocommerce.search .qlwapp__time {
					display: none !important;
				}

				/* 44x44 tap targets for header cart/menu/search icons on search pages.
				   #mini-cart has no direct <a>; keep the tap target on .cart-head and leave
				   the inner .cart-icon at its natural size so the badge stays anchored. */
				body.search #mini-cart .cart-head,
				body.search .mini-cart .cart-head,
				body.search header .btn-special,
				body.search header button[aria-label="Search"] {
					min-width: 44px !important;
					min-height: 44px !important;
				}

				body.search #mini-cart .cart-icon,
				body.search .mini-cart .cart-icon {
					width: 22px !important;
					height: 22px !important;
					min-width: 0 !important;
					min-height: 0 !important;
					display: inline-flex !important;
					align-items: center !important;
					justify-content: center !important;
				}
			}

			@media (max-width: 430px) {
				body:not(.home) .woocommerce ul.products,
				body:not(.home).woocommerce ul.products {
					grid-template-columns: minmax(0, 1fr) !important;
				}
			}

			@media (max-width: 374px) {
				body.home .header-side .searchform-popup.advanced-search-layout .searchform.search-layout-advanced,
				body.home .side-nav .searchform.search-layout-advanced {
					width: 150px !important;
					max-width: 150px !important;
				}

				body .threew-mobile-header-search .searchform.search-layout-advanced {
					width: 100% !important;
					max-width: none !important;
				}
			}

			@media (max-width: 430px) {
				/* Larger single-column mobile cards: minimal side padding, no right-edge clipping. */
				body.home .threew-product-strip {
					padding-right: 16px !important;
					padding-left: 16px !important;
				}

				body.home ul.products li.product .product-inner,
				body.home .products .product-col .product-inner {
					max-width: calc(100vw - 32px) !important;
					margin-right: auto !important;
					margin-left: auto !important;
				}

				body.home .products,
				body.home ul.products,
				body.home ul.products:not(.products-slider),
				body.home ul.products.products-container.grid,
				body.home ul.products.products-slider.owl-carousel {
					display: grid !important;
					grid-template-columns: minmax(0, 1fr) !important;
					justify-content: stretch !important;
					justify-items: stretch !important;
					gap: 24px !important;
					width: 100% !important;
					max-width: 100% !important;
					margin-right: 0 !important;
					margin-left: 0 !important;
				}

				body.home ul.products li.product,
				body.home ul.products:not(.products-slider) > li.product,
				body.home ul.products:not(.products-slider) > .product-col,
				body.home ul.products.products-container.grid > li.product,
				body.home ul.products.products-container.grid > .product-col,
				body.home .products .product-col,
				body.home ul.products.products-slider.owl-carousel .owl-item,
				body.home ul.products.products-slider.owl-carousel .owl-item:not(.cloned),
				body.home ul.products.products-slider.owl-carousel .product-col,
				body.home ul.products.products-slider.owl-carousel li.product {
					width: 100% !important;
					max-width: 100% !important;
					justify-self: stretch !important;
				}

				body.home ul.products li.product .product-inner,
				body.home .products .product-col .product-inner {
					gap: 9px !important;
					padding: 12px !important;
					border-radius: 18px;
				}

				body.home ul.products li.product .product-image,
				body.home .products .product-col .product-image {
					aspect-ratio: 4 / 3 !important;
					padding: 8px !important;
					border-radius: 14px;
				}

				body.home ul.products li.product .product-image > a,
				body.home .products .product-col .product-image > a {
					inset: 8px !important;
				}

				body.home ul.products li.product .product-loop-title,
				body.home .products .product-col .product-loop-title,
				body.home .woocommerce-loop-product__title,
				body.home ul.products li.product h3,
				body.home ul.products li.product .product-name {
					-webkit-line-clamp: 2;
					font-size: 14px;
					line-height: 1.3;
					min-height: 2.6em;
				}

				body.home ul.products li.product .category-list,
				body.home .products .product-col .category-list {
					font-size: 10px;
				}

				body.home ul.products li.product .price,
				body.home .products .product-col .price {
					font-size: 16px;
				}

				body.home ul.products li.product .affirm-as-low-as:before {
					padding: 2px 7px;
					font-size: 10px;
				}
			}
		</style>
		<?php
	},
	99
);

function threew_storefront_render_mobile_header_search() {
	if ( is_admin() ) {
		return;
	}
	?>
	<div class="threew-mobile-header-search" aria-label="<?php esc_attr_e( 'Product search', 'threew-storefront-polish' ); ?>">
		<form role="search" method="get" class="searchform search-layout-advanced" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:flex!important;width:100%!important;height:44px!important;margin:0!important;">
			<div class="searchform-fields">
				<span class="text"><input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search products…', 'threew-storefront-polish' ); ?>" autocomplete="off" aria-label="<?php esc_attr_e( 'Search products', 'threew-storefront-polish' ); ?>"></span>
				<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'threew-storefront-polish' ); ?>"><i class="fas fa-search" aria-hidden="true"></i></button>
			</div>
		</form>
	</div>
	<?php
}
add_action( 'wp_footer', 'threew_storefront_render_mobile_header_search', 98 );

add_action(
	'wp_footer',
	static function () {
		?>
		<script id="threew-storefront-polish-hotfix-js">
			(function () {
				function stabilizeHeroCarousel() {
					if (!window.jQuery) {
						return;
					}

					window.jQuery('.home-banner .porto-carousel.owl-carousel').each(function () {
						var carousel = window.jQuery(this);
						var items = carousel.find('.owl-item');
						var staticItem = items.not('.cloned').first();

						carousel.trigger('stop.owl.autoplay');
						items.removeClass('threew-hero-static').addClass('threew-hero-hidden');
						staticItem.removeClass('threew-hero-hidden').addClass('threew-hero-static');
					});
				}

				function isMobile() {
					return window.matchMedia('(max-width: 600px)').matches;
				}

				function isMobileHome() {
					return document.body.classList.contains('home') && isMobile();
				}

				function isMobileCartOverlayContext() {
					return isMobile() && (
						document.body.classList.contains('home') ||
						document.body.classList.contains('woocommerce-shop') ||
						document.body.classList.contains('post-type-archive-product') ||
						document.body.classList.contains('single-product') ||
						document.body.classList.contains('tax-product_cat') ||
						document.body.classList.contains('tax-product_tag') ||
						document.body.classList.contains('search')
					);
				}

				function buildFallbackSearchForm() {
					// ponytail: Porto's source form is absent on some loads; build a minimal
					// one matching the existing CSS selectors so the bar never vanishes.
					var form = document.createElement('form');
					form.setAttribute('role', 'search');
					form.setAttribute('method', 'get');
					form.setAttribute('action', '/');
					form.className = 'searchform search-layout-advanced';
					form.innerHTML =
						'<div class="searchform-fields">' +
						'<span class="text"><input type="search" name="s" placeholder="Search products\u2026" autocomplete="off" aria-label="Search products"></span>' +
						'<button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>' +
						'</div>';
					return form;
				}

				function cloneSearchForm() {
					var form = document.querySelector('.header-side .searchform.search-layout-advanced, header .searchform.search-layout-advanced, .searchform.search-layout-advanced');

					if (!form) {
						return buildFallbackSearchForm();
					}

					var clone = form.cloneNode(true);
					clone.removeAttribute('style');
					clone.style.setProperty('display', 'flex', 'important');
					clone.style.setProperty('width', '100%', 'important');
					clone.style.setProperty('height', '44px', 'important');
					clone.querySelectorAll('[id]').forEach(function (el) {
						el.removeAttribute('id');
					});

					return clone;
				}

				function mountMobileHeaderSearch() {
					var header = document.querySelector('#header, header');
					var clone = cloneSearchForm();

					if (!isMobile() || !header || !clone || document.querySelector('.threew-mobile-header-search')) {
						return;
					}

					var wrap = document.createElement('div');
					wrap.className = 'threew-mobile-header-search';
					wrap.appendChild(clone);
					header.insertAdjacentElement('afterend', wrap);
				}

				function mountMobileMenuSearch() {
					var menu = document.querySelector('.mobile-menu');
					var clone = cloneSearchForm();

					if (!isMobile() || !menu || !clone || menu.querySelector('.threew-mobile-menu-search')) {
						return;
					}

					var item = document.createElement('li');

					item.className = 'threew-mobile-menu-search menu-item';
					item.appendChild(clone);
					menu.insertBefore(item, menu.firstElementChild);
				}

				function normalizedText(value) {
					return (value || '').replace(/\s+/g, ' ').trim().toLowerCase();
				}

				function productSearchUrl(query) {
					return '/?s=' + encodeURIComponent(query);
				}

				function submitProductSearch(form, event) {
					var input = form ? form.querySelector('input[name="s"]') : null;
					var query = input ? input.value.trim() : '';

					if (!query) {
						return;
					}

					if (event) {
						event.preventDefault();
						event.stopPropagation();
					}

					window.location.href = productSearchUrl(query);
				}

				function normalizeSearchForms() {
					document.querySelectorAll('form.searchform.search-layout-advanced, form.searchform').forEach(function (form) {
						var clean;

						if (form.dataset.threewSearchReady) {
							return;
						}

						clean = form.cloneNode(true);
						clean.dataset.threewSearchReady = '1';
						clean.querySelectorAll('.live-search-list, .autocomplete-suggestions').forEach(function (el) {
							el.remove();
						});
						clean.querySelectorAll('input[type="text"], input[type="search"]').forEach(function (input) {
							input.classList.add('porto-search-init');
							input.setAttribute('autocomplete', 'off');
						});

						clean.addEventListener('submit', function (event) {
							submitProductSearch(clean, event);
						});

						clean.addEventListener('click', function (event) {
							if (event.target.closest('button[type="submit"], input[type="submit"], .btn-special')) {
								submitProductSearch(clean, event);
							}
						});

						clean.addEventListener('keydown', function (event) {
							if (event.key === 'Enter' && event.target.matches('input[name="s"]')) {
								submitProductSearch(clean, event);
							}
						});

						form.replaceWith(clean);
					});
				}

				function menuHrefFor(label) {
					var target = normalizedText(label);
					var links = Array.prototype.slice.call(document.querySelectorAll('.mobile-menu a[href], .menu a[href]'));
					var exact = links.find(function (link) {
						return normalizedText(link.textContent) === target;
					});
					var contains = links.find(function (link) {
						return normalizedText(link.textContent).indexOf(target) !== -1;
					});
					return (exact || contains || {}).href || '';
				}

				function mountMobileShopShortcuts() {
					var hero = document.querySelector('.home-banner');
					var shortcuts = [
						{ label: 'Brabus', href: '/product-category/brabus/' },
						{ label: 'Wheels', href: '/product-category/mansory/mansory-mercedes/mansory-gwagon-w463/mansory-gwagon-w463-wheels/' },
						{ label: 'G-Wagon', href: '/product-category/brabus/brabus-mercedes/g-wagon/gwagon-w465/' },
						{ label: 'BMW', href: '/product-category/autotecknic/bmw-autotecknic/' },
						{ label: 'Exhaust', href: '/product-category/capristo-exhaust/' },
						{ label: 'Carbon Fiber', href: '/product-category/eventuri/' }
					];

					if (!isMobileHome() || !hero || document.querySelector('.threew-mobile-shop-shortcuts')) {
						return;
					}

					var section = document.createElement('section');
					var grid = document.createElement('div');
					var eyebrow = document.createElement('p');

					section.className = 'threew-mobile-shop-shortcuts';
					section.setAttribute('aria-label', 'Shop shortcuts');
					eyebrow.className = 'threew-mobile-shop-shortcuts__eyebrow';
					eyebrow.textContent = 'Shop by need';
					grid.className = 'threew-mobile-shop-shortcuts__grid';

					shortcuts.forEach(function (shortcut) {
						var link = document.createElement('a');
						link.href = shortcut.href || menuHrefFor(shortcut.label) || productSearchUrl(shortcut.query);
						link.textContent = shortcut.label;
						grid.appendChild(link);
					});

					section.appendChild(eyebrow);
					section.appendChild(grid);
					hero.insertAdjacentElement('afterend', section);
				}

				function markMobilePolishBlocks() {
					var headings = Array.prototype.slice.call(document.querySelectorAll('h1, h2, h3, h4, .wpb_heading, .vc_custom_heading'));

					if (!isMobileHome()) {
						return;
					}

					headings.forEach(function (heading) {
						var text = normalizedText(heading.textContent);
						var row = heading.closest('.vc_row.wpb_row.row.top-row, .vc_row.wpb_row, .wpb_raw_html');

						if (!row) {
							return;
						}

						if (text === 'authorized dealers for:' || text === 'authorized dealers for') {
							row.classList.add('threew-dealer-strip');
						}

						if (text === 'customer service' || text === 'about us') {
							row.classList.add('threew-footer-links');
						}
					});

					document.querySelectorAll('.porto-products').forEach(function (products) {
						var row = products.closest('.vc_row.wpb_row, .vc_row');

						if (row) {
							row.classList.add('threew-product-strip');
						}
					});

					document.querySelectorAll('.home-mid-banner').forEach(function (banner) {
						banner.classList.add('threew-promo-panel');
					});
				}


				function labelIconControls() {
					document.querySelectorAll('button.btn-special[aria-label="Search"], header button[aria-label="Search"]').forEach(function (el) {
						el.style.width = '44px';
						el.style.minWidth = '44px';
						el.style.height = '44px';
						el.style.minHeight = '44px';
					});

					var chatControls = document.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp"], .qlwapp-toggle, .qlwapp__button, .joinchat__button');
					chatControls.forEach(function (el) {
						if (!el.getAttribute('aria-label')) {
							el.setAttribute('aria-label', 'Chat with us on WhatsApp');
						}
					});

					document.querySelectorAll('a.quickview, button.quickview').forEach(function (el) {
						var card = el.closest('li.product, .product-col');
						var title = card ? card.querySelector('.product-loop-title, .woocommerce-loop-product__title, .product-name') : null;
						var name = title ? title.textContent.replace(/\s+/g, ' ').trim() : '';

						if (!el.getAttribute('aria-label')) {
							el.setAttribute('aria-label', name ? 'Quick view ' + name : 'Quick view product');
						}
					});

					document.querySelectorAll('a.add_to_cart_button, button.add_to_cart_button').forEach(function (el) {
						if (el.getAttribute('aria-label')) {
							return;
						}
						var card = el.closest('li.product, .product-col');
						var title = card ? card.querySelector('.product-loop-title, .woocommerce-loop-product__title, .product-name') : null;
						var name = title ? title.textContent.replace(/\s+/g, ' ').trim() : '';
						el.setAttribute('aria-label', name ? 'Add ' + name + ' to cart' : 'Add to cart');
					});
				}

				function cartCountFromText(text) {
					var match = String(text || '').match(/\d+/);
					return match ? parseInt(match[0], 10) : 0;
				}

				function elementVisible(el) {
					var rect;
					var style;

					if (!el) {
						return false;
					}

					rect = el.getBoundingClientRect();
					style = window.getComputedStyle(el);
					return rect.width > 0 && rect.height > 0 && style.display !== 'none' && style.visibility !== 'hidden' && Number(style.opacity) > 0;
				}

				function cartBadgeNodes() {
					var carts = Array.prototype.slice.call(document.querySelectorAll('#mini-cart'));
					var visibleCarts = carts.filter(elementVisible);
					var scope = visibleCarts.length ? visibleCarts : carts;
					var badges = [];

					scope.forEach(function (cart) {
						badges = badges.concat(Array.prototype.slice.call(cart.querySelectorAll('.cart-items, .cart-count, .cart-badge')));
					});

					return badges;
				}

				function syncCartBadge() {
					var badges = cartBadgeNodes();
					var count = badges.reduce(function (max, el) {
						return Math.max(max, cartCountFromText(el.textContent));
					}, 0);

					badges.forEach(function (el) {
						el.dataset.count = String(count);
						el.style.display = count > 0 ? 'inline-flex' : 'none';
					});
				}

				function refreshMiniCartFragments() {
					if (!window.fetch || !isMobile()) {
						return;
					}

					fetch('/?wc-ajax=get_refreshed_fragments', { credentials: 'same-origin' })
						.then(function (response) { return response.ok ? response.json() : null; })
						.then(function (data) {
							var openCart;

							if (!data || !data.fragments) {
								return;
							}

							Object.keys(data.fragments).forEach(function (selector) {
								document.querySelectorAll(selector).forEach(function (el) {
									el.outerHTML = data.fragments[selector];
								});
							});
							syncCartBadge();
							openCart = document.querySelector('#mini-cart.threew-cart-open, .mini-cart.threew-cart-open');
							if (openCart) {
								toggleMobileCartOverlay(openCart);
							}
						})
						.catch(function () {});
				}

				function closeMobileCartOverlay() {
					document.querySelectorAll('.threew-mobile-cart-popup').forEach(function (el) {
						el.remove();
					});
				}

				function closeMobileCartState() {
					document.querySelectorAll('#mini-cart, .mini-cart').forEach(function (cart) {
						cart.classList.remove('threew-cart-open');
						cart.classList.remove('open');
					});
					closeMobileCartOverlay();
				}

				function dismissAddToCartSuccessMessage() {
					document.querySelectorAll('.after-loading-success-message').forEach(function (el) {
						el.remove();
					});
				}

				function toggleMobileCartOverlay(cart) {
					var popup = cart.querySelector('.cart-popup');
					var clone;

					closeMobileCartOverlay();
					if (!isMobileCartOverlayContext() || !popup || !cart.classList.contains('threew-cart-open')) {
						return;
					}

					cart.classList.remove('open');
					dismissAddToCartSuccessMessage();
					clone = popup.cloneNode(true);
					clone.className = 'threew-mobile-cart-popup widget_shopping_cart';
					document.body.appendChild(clone);
				}

				function bindMobileCartPopup() {
					document.querySelectorAll('#mini-cart, .mini-cart').forEach(function (cart) {
						if (cart.dataset.threewCartToggleReady) {
							return;
						}

						cart.dataset.threewCartToggleReady = '1';
						cart.addEventListener('click', function (event) {
							if (!isMobile()) {
								return;
							}

							event.preventDefault();
							event.stopPropagation();
							if (event.stopImmediatePropagation) {
								event.stopImmediatePropagation();
							}

							if (cart.classList.contains('threew-cart-open') || document.querySelector('.threew-mobile-cart-popup')) {
								closeMobileCartState();
								return;
							}

							cart.classList.add('threew-cart-open');
							toggleMobileCartOverlay(cart);
						}, true);
					});

					document.addEventListener(
						'click',
						function (event) {
							if (!isMobileCartOverlayContext()) {
								return;
							}

							if (!document.querySelector('#mini-cart.threew-cart-open, .mini-cart.threew-cart-open')) {
								return;
							}

							if (event.target.closest('#mini-cart, .mini-cart, .threew-mobile-cart-popup')) {
								return;
							}

							closeMobileCartState();
						},
						true
					);

					document.addEventListener('keydown', function (event) {
						if (event.key === 'Escape') {
							closeMobileCartState();
						}
					});
				}

				function bindCartFragmentEvents() {
					if (!window.jQuery || !window.jQuery.fn || document.body.dataset.threewCartFragmentEventsReady) {
						return;
					}

					document.body.dataset.threewCartFragmentEventsReady = '1';
					window.jQuery(document.body).on('wc_fragments_refreshed added_to_cart removed_from_cart', function () {
						var openCart = document.querySelector('#mini-cart.threew-cart-open, .mini-cart.threew-cart-open');
						syncCartBadge();
						if (openCart) {
							toggleMobileCartOverlay(openCart);
						}
					});
				}

				function hideEmptyCartBadge() {
					syncCartBadge();
				}

				function optimizeCatalogImages() {
					var isCatalog = document.body.classList.contains('woocommerce-shop') ||
						document.body.classList.contains('post-type-archive-product') ||
						document.body.classList.contains('tax-product_cat') ||
						document.body.classList.contains('tax-product_tag') ||
						document.body.classList.contains('search');

					if (!isMobile() || !isCatalog) {
						return;
					}

					document.querySelectorAll('ul.products img.hover-image, .products img.hover-image').forEach(function (img) {
						img.remove();
					});

					Array.prototype.slice.call(document.querySelectorAll('ul.products img, .products img')).forEach(function (img, index) {
						img.decoding = 'async';

						if (index < 2) {
							img.loading = 'eager';
							img.setAttribute('fetchpriority', index === 0 ? 'high' : 'auto');
							return;
						}

						img.loading = 'lazy';
						img.setAttribute('fetchpriority', 'low');
					});
				}

				function simplifyCatalogCategoryLists() {
					if (!isMobile()) {
						return;
					}
					var isCatalog = document.body.classList.contains('search') ||
						document.body.classList.contains('woocommerce-shop') ||
						document.body.classList.contains('post-type-archive-product') ||
						document.body.classList.contains('tax-product_cat');
					if (!isCatalog) {
						return;
					}
					document.querySelectorAll('ul.products li.product .category-list, .products .product-col .category-list').forEach(function (list) {
						if (list.dataset.threewCategorySimplified) {
							return;
						}
						var first = list.querySelector('a, span, li');
						if (first) {
							list.dataset.threewCategorySimplified = '1';
							list.textContent = first.textContent.trim();
						}
					});
				}

				function enhanceMobileArchiveHeader() {
					var isCatalog;
					var crumb;
					var items;
					var current;
					var title;
					var compact;
					var keep;
					var fragment;
					var mount;

					if (!isMobile()) {
						return;
					}

					isCatalog = document.body.classList.contains('search') ||
						document.body.classList.contains('woocommerce-shop') ||
						document.body.classList.contains('post-type-archive-product') ||
						document.body.classList.contains('tax-product_cat');
					if (!isCatalog || document.querySelector('.threew-mobile-archive-header')) {
						return;
					}

					crumb = document.querySelector('.breadcrumb, .woocommerce-breadcrumb');
					if (!crumb) {
						return;
					}

					items = Array.prototype.slice.call(crumb.querySelectorAll('li')).filter(function (item) {
						return item.textContent.replace(/\s+/g, ' ').trim();
					});
					if (!items.length) {
						return;
					}

					current = items[items.length - 1];
					title = document.createElement('h1');
					title.className = 'threew-mobile-archive-title';
					title.textContent = current.textContent.replace(/\s+/g, ' ').trim();

					compact = document.createElement('div');
					compact.className = 'threew-mobile-breadcrumb-compact';
					keep = [];
					if (items[1]) {
						keep.push(items[1]);
					}
					if (items.length > 3 && items[items.length - 2]) {
						keep.push(items[items.length - 2]);
					}
					if (!keep.length && items[0]) {
						keep.push(items[0]);
					}

					fragment = document.createDocumentFragment();
					keep
						.filter(function (item, index, array) {
							return array.indexOf(item) === index;
						})
						.forEach(function (item, index) {
							var link = item.querySelector('a');
							var node = document.createElement(link ? 'a' : 'span');
							node.textContent = item.textContent.replace(/\s+/g, ' ').trim();
							if (link) {
								node.href = link.href;
							}
							fragment.appendChild(node);
							if (index < keep.length - 1) {
								fragment.appendChild(document.createTextNode('/'));
							}
						});
					compact.appendChild(fragment);

					mount = document.createElement('div');
					mount.className = 'threew-mobile-archive-header';
					if (compact.textContent.trim()) {
						mount.appendChild(compact);
					}
					mount.appendChild(title);
					crumb.insertAdjacentElement('beforebegin', mount);
					document.body.classList.add('threew-mobile-archive-header-ready');
				}

				function bindMobileCatalogFilters() {
					function getSidebar() {
						return document.querySelector('.sidebar.mobile-sidebar');
					}

					function clearPortoSidebarOverlay() {
						document.querySelectorAll('.sidebar-overlay.active').forEach(function (overlay) {
							overlay.classList.remove('active');
						});
					}

					function closeMobileCatalogFilters() {
						document.body.classList.remove('threew-mobile-filters-open');
						clearPortoSidebarOverlay();
					}

					if (document.body.dataset.threewFilterBound) {
						return;
					}

					document.body.dataset.threewFilterBound = '1';
					document.addEventListener('click', function (event) {
						var toggle;
						var sidebar;

						if (!isMobile()) {
							return;
						}

						toggle = event.target.closest('.porto-product-filters-toggle.sidebar-toggle, .sidebar-toggle.d-lg-none');
						sidebar = getSidebar();

						if (toggle && sidebar) {
							event.preventDefault();
							event.stopImmediatePropagation();
							clearPortoSidebarOverlay();
							document.body.classList.toggle('threew-mobile-filters-open');
							return;
						}

						if (event.target.closest('.sidebar.mobile-sidebar a')) {
							closeMobileCatalogFilters();
							return;
						}

						if (!document.body.classList.contains('threew-mobile-filters-open')) {
							return;
						}

						if (sidebar && sidebar.contains(event.target)) {
							return;
						}

						event.preventDefault();
						event.stopPropagation();
						closeMobileCatalogFilters();
					}, true);
					window.addEventListener('pagehide', closeMobileCatalogFilters);
					document.addEventListener('keydown', function (event) {
						if (event.key === 'Escape') {
							closeMobileCatalogFilters();
						}
					});
				}

				function ready(callback) {
					if (document.readyState === 'loading') {
						document.addEventListener('DOMContentLoaded', callback);
						return;
					}

					callback();
				}

				ready(function () {
					stabilizeHeroCarousel();
					mountMobileHeaderSearch();
					mountMobileMenuSearch();
					mountMobileShopShortcuts();
					markMobilePolishBlocks();
					normalizeSearchForms();
					labelIconControls();
					enhanceMobileArchiveHeader();
					simplifyCatalogCategoryLists();
					bindMobileCartPopup();
					bindCartFragmentEvents();
					hideEmptyCartBadge();
					refreshMiniCartFragments();
					optimizeCatalogImages();
					bindMobileCatalogFilters();
				});

				window.setTimeout(stabilizeHeroCarousel, 600);
				window.setTimeout(stabilizeHeroCarousel, 1600);
				window.setTimeout(mountMobileHeaderSearch, 600);
				window.setTimeout(mountMobileHeaderSearch, 1600);
				window.setTimeout(mountMobileMenuSearch, 600);
				window.setTimeout(mountMobileShopShortcuts, 600);
				window.setTimeout(markMobilePolishBlocks, 600);
				window.setTimeout(markMobilePolishBlocks, 1600);
				window.setTimeout(normalizeSearchForms, 600);
				window.setTimeout(normalizeSearchForms, 1600);
				window.setTimeout(labelIconControls, 600);
				window.setTimeout(enhanceMobileArchiveHeader, 600);
				window.setTimeout(simplifyCatalogCategoryLists, 600);
				window.setTimeout(simplifyCatalogCategoryLists, 1600);
				window.setTimeout(enhanceMobileArchiveHeader, 1600);
				window.setTimeout(bindMobileCartPopup, 600);
				window.setTimeout(bindCartFragmentEvents, 600);
				window.setTimeout(hideEmptyCartBadge, 300);
				window.setTimeout(hideEmptyCartBadge, 900);
				window.setTimeout(hideEmptyCartBadge, 1800);
				window.setTimeout(optimizeCatalogImages, 300);
				window.setTimeout(optimizeCatalogImages, 900);
				window.setTimeout(optimizeCatalogImages, 1800);
				window.setTimeout(bindMobileCatalogFilters, 600);
				window.setTimeout(bindMobileCatalogFilters, 1600);
			})();
		</script>
		<?php
	},
	99
);
