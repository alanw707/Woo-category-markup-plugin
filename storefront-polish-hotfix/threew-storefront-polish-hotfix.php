<?php
/**
 * Plugin Name: 3W Storefront Polish Hotfix
 * Description: Small design and accessibility polish fixes for the 3W Distributing Porto storefront homepage.
 * Version: 1.2.50
 * Author: 3W Distributing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'THREEW_STOREFRONT_POLISH_VERSION', '1.2.50' );

add_action(
	'init',
	static function () {
		if ( get_option( 'threew_storefront_polish_version' ) === THREEW_STOREFRONT_POLISH_VERSION ) {
			return;
		}

		update_option( 'threew_storefront_polish_version', THREEW_STOREFRONT_POLISH_VERSION );
		do_action( 'litespeed_purge_all' );
	},
	1
);

function threew_storefront_is_payment_context() {
	return is_admin() || is_cart() || is_checkout() || is_account_page() || ( function_exists( 'is_product' ) && is_product() );
}

function threew_storefront_is_catalog_context() {
	if ( is_admin() ) {
		return false;
	}

	return ( function_exists( 'is_shop' ) && is_shop() )
		|| ( function_exists( 'is_product_category' ) && is_product_category() )
		|| ( function_exists( 'is_product_tag' ) && is_product_tag() )
		|| ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() )
		|| is_post_type_archive( 'product' )
		|| is_search();
}

function threew_storefront_is_mobile_request() {
	return function_exists( 'wp_is_mobile' ) && wp_is_mobile();
}

function threew_storefront_delay_mobile_catalog_third_party_scripts( $html ) {
	if ( ! threew_storefront_is_mobile_request() ) {
		return $html;
	}

	$third_party_pattern = '#<script\b(?=[^>]*\bsrc=["\'][^"\']*(?:googletagmanager\.com/gtag/js|google-analytics\.com/analytics\.js|googleadservices\.com|googleads\.g\.doubleclick\.net|accounts\.google\.com/gsi/client)[^"\']*["\'])[^>]*></script>#i';
	$delayed_count       = 0;

	$html = preg_replace_callback(
		$third_party_pattern,
		static function ( $matches ) use ( &$delayed_count ) {
			$tag = $matches[0];

			if ( ! preg_match( '/\bsrc=(["\'])(.*?)\1/i', $tag, $src_match ) ) {
				return $tag;
			}

			$delayed_count++;
			$src = esc_url( html_entity_decode( $src_match[2], ENT_QUOTES ) );
			return '<script type="application/json" class="threew-delayed-third-party-script" data-src="' . esc_attr( $src ) . '"></script>';
		},
		$html
	);

	if ( 0 === $delayed_count || false !== strpos( $html, 'threewLoadDelayedThirdPartyScripts' ) ) {
		return $html;
	}

	$loader = <<<'HTML'
<script id="threew-delayed-third-party-loader">
(function () {
	var loaded = false;
	function loadDelayedThirdPartyScripts() {
		if (loaded) return;
		loaded = true;
		document.querySelectorAll('script.threew-delayed-third-party-script[data-src]').forEach(function (placeholder) {
			var script = document.createElement('script');
			script.async = true;
			script.src = placeholder.getAttribute('data-src');
			placeholder.parentNode.insertBefore(script, placeholder.nextSibling);
		});
	}
	window.threewLoadDelayedThirdPartyScripts = loadDelayedThirdPartyScripts;
	['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach(function (eventName) {
		window.addEventListener(eventName, loadDelayedThirdPartyScripts, { once: true, passive: true });
	});
	window.addEventListener('load', function () {
		window.setTimeout(loadDelayedThirdPartyScripts, 12000);
	}, { once: true });
})();
</script>
HTML;

	return str_replace( '</body>', $loader . "\n</body>", $html );
}

function threew_storefront_strip_catalog_payment_html( $html ) {
	$marker = "if ('undefined' === typeof _affirm_config) {";
	$marker_position = strpos( $html, $marker );

	if ( false !== $marker_position ) {
		$script_start = strrpos( substr( $html, 0, $marker_position ), '<script' );
		$script_end   = strpos( $html, '</script>', $marker_position );

		if ( false !== $script_start && false !== $script_end ) {
			$html = substr( $html, 0, $script_start ) . substr( $html, $script_end + 9 );
		}
	}

	$payment_link_markers = array(
		'ppcp-local-alternative-payment-methods-css-gateway.css',
	);

	foreach ( $payment_link_markers as $link_marker ) {
		$link_position = strpos( $html, $link_marker );

		if ( false === $link_position ) {
			continue;
		}

		$link_start = strrpos( substr( $html, 0, $link_position ), '<link' );
		$link_end   = strpos( $html, '>', $link_position );

		if ( false !== $link_start && false !== $link_end ) {
			$html = substr( $html, 0, $link_start ) . substr( $html, $link_end + 1 );
		}
	}

	return $html;
}

function threew_storefront_optimize_mobile_catalog_html( $html ) {
	if ( ! threew_storefront_is_catalog_context() ) {
		return $html;
	}

	$html = threew_storefront_strip_catalog_payment_html( $html );
	if ( threew_storefront_is_mobile_request() ) {
		$html = preg_replace( '#<link\b[^>]*\bhref=["\'][^"\']*wc-blocks\.css[^"\']*["\'][^>]*>#i', '', $html );
	}
	$html = threew_storefront_delay_mobile_catalog_third_party_scripts( $html );

	return $html;
}

add_filter(
	'request',
	static function ( $vars ) {
		$search = $vars['s'] ?? '';

		if ( ! is_admin() && is_scalar( $search ) && '' !== trim( (string) $search ) && empty( $vars['post_type'] ) ) {
			$vars['post_type'] = 'product';
		}

		return $vars;
	},
	1
);

add_action(
	'template_redirect',
	static function () {
		if ( threew_storefront_is_payment_context() ) {
			return;
		}

		ob_start(
			static function ( $html ) {
				if ( threew_storefront_is_catalog_context() ) {
					return threew_storefront_optimize_mobile_catalog_html( $html );
				}

				$html = preg_replace( '#<script\b[^>]*\bsrc=["\'][^"\']*(affirm|paypal|ppcp|googlepay|applepay)[^"\']*["\'][^>]*></script>#i', '', $html );
				$html = preg_replace( '#<script\b[^>]*>(?:(?!</script>).)*(affirm|paypal|ppcp|googlepay|applepay)(?:(?!</script>).)*</script>#is', '', $html );

				/* Remove the disabled Porto category top banner from source, including logged-in builder wrappers. */
				$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*banner-container\s+my-banner[^"\']*["\'][^>]*>\s*<div\b[^>]*id=["\']banner-wrapper["\'][^>]*>\s*<div\b[^>]*data-id=["\']881["\'][^>]*>\s*(?:<style\b[^>]*>.*?</style>\s*)?</div>\s*</div>\s*</div>#is', '', $html );
				$html = preg_replace( '#<div\b[^>]*data-id=["\']881["\'][^>]*>\s*(?:<style\b[^>]*>.*?</style>\s*)?</div>#is', '', $html );

				return $html;
			}
		);
	},
	0
);

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

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( threew_storefront_is_payment_context() ) {
			return;
		}

		global $wp_scripts;

		if ( empty( $wp_scripts->queue ) ) {
			return;
		}

		foreach ( $wp_scripts->queue as $handle ) {
			$script = $wp_scripts->registered[ $handle ] ?? null;
			$src    = $script ? $script->src : '';
			$haystack = strtolower( $handle . ' ' . $src );

			if ( preg_match( '/(affirm|paypal|ppcp|googlepay|applepay)/', $haystack ) ) {
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
	},
	1000
);

function threew_storefront_dequeue_payment_styles() {
	if ( threew_storefront_is_payment_context() ) {
		return;
	}

	global $wp_styles;

	if ( empty( $wp_styles->queue ) ) {
		return;
	}

	foreach ( $wp_styles->queue as $handle ) {
		$style    = $wp_styles->registered[ $handle ] ?? null;
		$src      = $style ? $style->src : '';
		$haystack = strtolower( $handle . ' ' . $src );

		if ( preg_match( '/(affirm|paypal|ppcp|googlepay|applepay)/', $haystack ) ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'threew_storefront_dequeue_payment_styles', 1000 );
add_action( 'wp_print_styles', 'threew_storefront_dequeue_payment_styles', 1000 );

function threew_storefront_dequeue_mobile_catalog_styles() {
	if ( ! threew_storefront_is_catalog_context() || ! threew_storefront_is_mobile_request() ) {
		return;
	}

	global $wp_styles;

	if ( empty( $wp_styles->queue ) ) {
		return;
	}

	foreach ( $wp_styles->queue as $handle ) {
		$style    = $wp_styles->registered[ $handle ] ?? null;
		$src      = $style ? $style->src : '';
		$haystack = strtolower( $handle . ' ' . $src );

		if ( preg_match( '/(porto-google-fonts|fonts\.googleapis|threew-newsletter-popup|wc-blocks|animate|widget-contact-info|widget-text|widget-follow-us|blog-legacy|account-login)/', $haystack ) ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'threew_storefront_dequeue_mobile_catalog_styles', 1100 );
add_action( 'wp_print_styles', 'threew_storefront_dequeue_mobile_catalog_styles', 1100 );

function threew_storefront_dequeue_payment_scripts() {
	if ( threew_storefront_is_payment_context() ) {
		return;
	}

	global $wp_scripts;

	if ( empty( $wp_scripts->queue ) ) {
		return;
	}

	foreach ( $wp_scripts->queue as $handle ) {
		$script   = $wp_scripts->registered[ $handle ] ?? null;
		$src      = $script ? $script->src : '';
		$haystack = strtolower( $handle . ' ' . $src );

		if ( preg_match( '/(affirm|paypal|ppcp|googlepay|applepay)/', $haystack ) ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}
}
add_action( 'wp_print_scripts', 'threew_storefront_dequeue_payment_scripts', 1000 );
add_action( 'wp_print_footer_scripts', 'threew_storefront_dequeue_payment_scripts', 0 );

function threew_storefront_dequeue_mobile_catalog_scripts() {
	if ( ! threew_storefront_is_catalog_context() || ! threew_storefront_is_mobile_request() ) {
		return;
	}

	global $wp_scripts;

	if ( empty( $wp_scripts->queue ) ) {
		return;
	}

	foreach ( $wp_scripts->queue as $handle ) {
		$script   = $wp_scripts->registered[ $handle ] ?? null;
		$src      = $script ? $script->src : '';
		$haystack = strtolower( $handle . ' ' . $src );

		if ( preg_match( '/(threew-newsletter-popup|wc-cart-fragments|sourcebuster|wc-order-attribution|price-slider|gla-gtag-events|wp-emoji-release|porto-appear-animate|woocommerce-google-analytics|woocommerce-google-adwords|pmw-public)/', $haystack ) ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'threew_storefront_dequeue_mobile_catalog_scripts', 1100 );
add_action( 'wp_print_scripts', 'threew_storefront_dequeue_mobile_catalog_scripts', 1100 );
add_action( 'wp_print_footer_scripts', 'threew_storefront_dequeue_mobile_catalog_scripts', 1 );

function threew_storefront_known_product_brands() {
	return array(
		'akrapovic'  => 'Akrapovic',
		'apr'        => 'APR',
		'awe'        => 'AWE Tuning',
		'awe-tuning' => 'AWE Tuning',
		'brabus'     => 'BRABUS',
		'capristo'   => 'Capristo',
		'dinan'      => 'Dinan',
		'eventuri'   => 'Eventuri',
		'fi-exhaust' => 'FI Exhaust',
		'letech'     => 'LETECH',
	);
}

function threew_storefront_product_schema_brand_name( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	$known_brands = threew_storefront_known_product_brands();
	$product_id   = $product->get_id();

	foreach ( array( 'product_brand', 'pa_brand', 'product_cat' ) as $taxonomy ) {
		$terms = taxonomy_exists( $taxonomy ) ? wp_get_post_terms( $product_id, $taxonomy ) : array();

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		foreach ( $terms as $term ) {
			$slug = sanitize_title( $term->slug );
			$name = sanitize_title( $term->name );

			if ( isset( $known_brands[ $slug ] ) ) {
				return $known_brands[ $slug ];
			}

			if ( isset( $known_brands[ $name ] ) ) {
				return $known_brands[ $name ];
			}
		}
	}

	$title = sanitize_title( $product->get_name() );
	foreach ( $known_brands as $slug => $brand_name ) {
		if ( 0 === strpos( $title, $slug ) ) {
			return $brand_name;
		}
	}

	return '';
}

add_filter(
	'woocommerce_structured_data_product',
	static function ( $markup, $product ) {
		if ( ! empty( $markup['brand'] ) ) {
			return $markup;
		}

		$brand_name = threew_storefront_product_schema_brand_name( $product );

		if ( '' === $brand_name ) {
			return $markup;
		}

		$markup['brand'] = array(
			'@type' => 'Brand',
			'name'  => $brand_name,
		);

		return $markup;
	},
	20,
	2
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
				--threew-ink: #101418;
				--threew-ink-soft: #1a2028;
				--threew-accent: #19c37d;
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
					height: 88px !important;
					min-height: 88px !important;
					max-height: 88px !important;
					background: var(--threew-ink-soft) !important;
					box-shadow: 0 1px 0 rgba(255, 255, 255, .08), 0 8px 22px rgba(0, 0, 0, .16) !important;
					overflow: visible !important;
				}

				body:not(.home) .header-main,
				body:not(.home) .header-center,
				body:not(.home) .header-left,
				body:not(.home) .header-right {
					height: 88px !important;
					min-height: 88px !important;
					max-height: 88px !important;
					align-items: center !important;
				}

				body:not(.home) .header-main {
					padding-top: 10px !important;
					padding-bottom: 10px !important;
				}

				body:not(.home) .logo,
				body:not(.home) .header-logo,
				body:not(.home) #header .logo {
					display: flex !important;
					align-items: center !important;
					max-width: 48px !important;
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
					right: -18px !important;
					left: auto !important;
					bottom: auto !important;
					width: 44px !important;
					height: 44px !important;
					min-width: 44px !important;
					min-height: 44px !important;
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

				body:not(.home) .mobile-toggle {
					position: fixed !important;
					top: 36px !important;
					right: -13px !important;
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
				body.home #mini-cart > a,
				body.home .cart-toggle {
					z-index: 1005 !important;
					pointer-events: auto !important;
					background: rgba(255, 255, 255, .001) !important;
				}

				body.home .mobile-toggle {
					position: fixed !important;
					top: 7px !important;
					right: 0 !important;
					left: auto !important;
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


				/* Mobile v1.2.1: keep search in the logo row instead of adding a second header band. */
				body.home .header-side .searchform-popup.advanced-search-layout,
				body.home .side-nav .searchform.search-layout-advanced {
					display: none !important;
				}

				body .threew-mobile-header-search {
					display: block;
					position: fixed;
					top: 14px;
					left: 74px;
					right: 120px;
					z-index: 1004;
					padding: 0;
					background: transparent;
					border: 0;
					box-shadow: none;
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
					height: 46px !important;
					background: #fff !important;
					border: 1px solid rgba(255, 255, 255, .2) !important;
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
					height: 46px !important;
					min-width: 0 !important;
					max-width: 100% !important;
					flex: 1 1 auto !important;
					padding: 0 16px !important;
					border: 0 !important;
					box-shadow: none !important;
					font-size: 15px !important;
					line-height: 46px !important;
				}

				body .threew-mobile-header-search button[type="submit"] {
					display: flex !important;
					position: static !important;
					inset: auto !important;
					align-items: center !important;
					justify-content: center !important;
					flex: 0 0 48px !important;
					width: 48px !important;
					height: 46px !important;
					margin: 0 !important;
					transform: none !important;
					color: #fff !important;
					background: var(--threew-accent) !important;
				}

				body .threew-mobile-header-search button[type="submit"] i {
					color: #fff !important;
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
					right: max(16px, env(safe-area-inset-right)) !important;
					bottom: max(16px, env(safe-area-inset-bottom)) !important;
					left: auto !important;
					align-items: flex-end !important;
					z-index: 50 !important;
				}

				body:not(.home).woocommerce .qlwapp__button,
				body.post-type-archive-product .qlwapp__button,
				body.tax-product_cat .qlwapp__button,
				body.tax-product_tag .qlwapp__button {
					width: 52px !important;
					height: 52px !important;
					min-width: 52px !important;
					min-height: 52px !important;
					padding: 0 !important;
					border-radius: 999px !important;
					justify-content: center !important;
				}

				body:not(.home).woocommerce .qlwapp__button .qlwapp__icon,
				body.post-type-archive-product .qlwapp__button .qlwapp__icon,
				body.tax-product_cat .qlwapp__button .qlwapp__icon,
				body.tax-product_tag .qlwapp__button .qlwapp__icon {
					margin: 0 !important;
					font-size: 25px !important;
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

				function cloneSearchForm() {
					var form = document.querySelector('.header-side .searchform.search-layout-advanced, header .searchform.search-layout-advanced, .searchform.search-layout-advanced');

					if (!form) {
						return null;
					}

					var clone = form.cloneNode(true);
					clone.removeAttribute('style');
					clone.style.setProperty('display', 'flex', 'important');
					clone.style.setProperty('width', '100%', 'important');
					clone.style.setProperty('height', '46px', 'important');
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
					optimizeCatalogImages();
				});

				window.setTimeout(stabilizeHeroCarousel, 600);
				window.setTimeout(stabilizeHeroCarousel, 1600);
				window.setTimeout(mountMobileHeaderSearch, 600);
				window.setTimeout(mountMobileMenuSearch, 600);
				window.setTimeout(mountMobileShopShortcuts, 600);
				window.setTimeout(markMobilePolishBlocks, 600);
				window.setTimeout(markMobilePolishBlocks, 1600);
				window.setTimeout(normalizeSearchForms, 600);
				window.setTimeout(normalizeSearchForms, 1600);
				window.setTimeout(labelIconControls, 600);
				window.setTimeout(optimizeCatalogImages, 300);
				window.setTimeout(optimizeCatalogImages, 900);
				window.setTimeout(optimizeCatalogImages, 1800);
			})();
		</script>
		<?php
	},
	99
);
