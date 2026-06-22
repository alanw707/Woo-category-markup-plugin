<?php
/**
 * Plugin Name: 3W Storefront Polish Hotfix
 * Description: Small design and accessibility polish fixes for the 3W Distributing Porto storefront homepage.
 * Version: 1.1.1
 * Author: 3W Distributing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'threew/v1',
			'/purge-style-cache',
			array(
				'methods'             => 'POST',
				'permission_callback' => static function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => static function () {
					do_action( 'litespeed_purge_all' );
					do_action( 'litespeed_purge_url', home_url( '/' ) );
					return array(
						'purged' => true,
						'time'   => time(),
					);
				},
			)
		);
	}
);

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
			}

			/* Keep floating chat from covering final product/content lines. */
			body.home {
				padding-bottom: 96px;
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

			body.home ul.products li.product .labels .onhot,
			body.home .products .product-col .labels .onhot {
				font-size: 9px;
				line-height: 1;
				padding: 5px 8px;
			}

			/* Mobile: keep chat compact, catalog readable, and hero CTA space intact. */
			@media (max-width: 600px) {
				body.home {
					padding-bottom: 88px;
				}

				body.home .header-side .searchform-popup.advanced-search-layout .searchform.search-layout-advanced,
				body.home .side-nav .searchform.search-layout-advanced {
					width: 170px !important;
					max-width: 170px !important;
					top: 0 !important;
					margin: 6px 0 0 !important;
				}

				body.home .header-side .searchform .searchform-fields {
					background: rgba(0, 0, 0, .22) !important;
				}

				html.panel-opened body.home .header-side .searchform-popup.advanced-search-layout {
					visibility: hidden !important;
				}

				body.home .mobile-menu .threew-mobile-menu-search {
					padding: 14px 16px;
					border-bottom: 1px solid rgba(255, 255, 255, .08);
				}

				body.home .mobile-menu .threew-mobile-menu-search .searchform.search-layout-advanced {
					width: 100% !important;
					max-width: none !important;
					top: auto !important;
					margin: 0 !important;
					display: flex !important;
				}

				body.home .mobile-menu .threew-mobile-menu-search .searchform-fields {
					display: flex !important;
					align-items: center !important;
					width: 100% !important;
					height: 44px !important;
					background: #fff !important;
					border-radius: 999px !important;
					overflow: hidden !important;
				}

				body.home .mobile-menu .threew-mobile-menu-search input[type="text"],
				body.home .mobile-menu .threew-mobile-menu-search input[type="search"] {
					box-sizing: border-box !important;
					width: calc(100% - 44px) !important;
					min-width: 0 !important;
					max-width: calc(100% - 44px) !important;
					flex: 0 1 calc(100% - 44px) !important;
					border: 0 !important;
					box-shadow: none !important;
				}

				body.home .mobile-menu .threew-mobile-menu-search button[type="submit"] {
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

				body.home .mobile-menu .threew-mobile-menu-search button[type="submit"] i {
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

				/* Product sliders are too narrow on phones; show them as a stable grid. */
				body.home ul.products.products-slider.owl-carousel {
					display: grid !important;
					grid-template-columns: repeat(2, minmax(0, 1fr));
					gap: 28px 18px;
					width: 100% !important;
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
					margin: 0 !important;
					padding: 0 !important;
				}

				body.home ul.products.products-slider.owl-carousel .owl-item.cloned,
				body.home ul.products.products-slider.owl-carousel .owl-nav,
				body.home ul.products.products-slider.owl-carousel .owl-dots {
					display: none !important;
				}

				body.home ul.products li.product .product-image,
				body.home .products .product-col .product-image {
					min-height: 110px;
				}

				body.home ul.products li.product .price,
				body.home .products .product-col .price {
					font-size: 16px;
				}

				body.home ul.products li.product .affirm-as-low-as,
				body.home ul.products li.product .affirm-modal-trigger {
					font-size: 11px;
					line-height: 1.3;
				}
			}

			@media (max-width: 374px) {
				body.home .header-side .searchform-popup.advanced-search-layout .searchform.search-layout-advanced,
				body.home .side-nav .searchform.search-layout-advanced {
					width: 150px !important;
					max-width: 150px !important;
				}
			}

			@media (max-width: 430px) {
				/* Single column fixes the unreadable 360/390/430px product-card squeeze from the current live screenshots. */
				body.home ul.products:not(.products-slider) {
					grid-template-columns: 1fr !important;
				}

				body.home ul.products.products-slider.owl-carousel {
					grid-template-columns: 1fr !important;
					gap: 32px;
				}

				body.home ul.products.products-slider.owl-carousel .owl-item,
				body.home ul.products.products-slider.owl-carousel .owl-item:not(.cloned),
				body.home ul.products.products-slider.owl-carousel .product-col,
				body.home ul.products.products-slider.owl-carousel li.product {
					width: auto !important;
					max-width: 100% !important;
					min-width: 0 !important;
					flex: none !important;
				}

				body.home ul.products.products-slider.owl-carousel .product-content {
					padding-left: 0 !important;
					padding-right: 0 !important;
				}

				body.home ul.products li.product .product-image,
				body.home .products .product-col .product-image {
					aspect-ratio: 4 / 3;
					min-height: 170px;
					max-height: 220px;
				}
			}
		</style>
		<?php
	},
	99
);

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

				function mountMobileMenuSearch() {
					var menu = document.querySelector('.mobile-menu');
					var form = document.querySelector('.header-side .searchform.search-layout-advanced');

					if (!window.matchMedia('(max-width: 600px)').matches || !menu || !form || menu.querySelector('.threew-mobile-menu-search')) {
						return;
					}

					var item = document.createElement('li');
					var clone = form.cloneNode(true);

					item.className = 'threew-mobile-menu-search menu-item';
					clone.removeAttribute('style');
					clone.querySelectorAll('[id]').forEach(function (el) {
						el.removeAttribute('id');
					});
					item.appendChild(clone);
					menu.insertBefore(item, menu.firstElementChild);
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
					mountMobileMenuSearch();
					labelIconControls();
				});

				window.setTimeout(stabilizeHeroCarousel, 600);
				window.setTimeout(stabilizeHeroCarousel, 1600);
				window.setTimeout(mountMobileMenuSearch, 600);
				window.setTimeout(labelIconControls, 600);
			})();
		</script>
		<?php
	},
	99
);
