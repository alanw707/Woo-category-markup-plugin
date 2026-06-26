<?php

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

function threew_storefront_force_product_search_request( $vars ) {
	$search = $vars['s'] ?? '';

	if ( ! is_admin() && is_scalar( $search ) && '' !== trim( (string) $search ) && empty( $vars['post_type'] ) ) {
		$vars['post_type'] = 'product';
	}

	return $vars;
}

function threew_storefront_buffer_catalog_optimization() {
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

function threew_storefront_dequeue_assets( $registry, $pattern, $dequeue_callback, $deregister_callback ) {
	if ( empty( $registry->queue ) ) {
		return;
	}

	foreach ( $registry->queue as $handle ) {
		$asset    = $registry->registered[ $handle ] ?? null;
		$src      = $asset ? $asset->src : '';
		$haystack = strtolower( $handle . ' ' . $src );

		if ( ! preg_match( $pattern, $haystack ) ) {
			continue;
		}

		$dequeue_callback( $handle );
		$deregister_callback( $handle );
	}
}

function threew_storefront_dequeue_payment_styles() {
	if ( threew_storefront_is_payment_context() ) {
		return;
	}

	global $wp_styles;
	threew_storefront_dequeue_assets( $wp_styles, '/(affirm|paypal|ppcp|googlepay|applepay)/', 'wp_dequeue_style', 'wp_deregister_style' );
}

function threew_storefront_dequeue_mobile_catalog_styles() {
	if ( ! threew_storefront_is_catalog_context() || ! threew_storefront_is_mobile_request() ) {
		return;
	}

	global $wp_styles;
	threew_storefront_dequeue_assets( $wp_styles, '/(porto-google-fonts|fonts\.googleapis|threew-newsletter-popup|wc-blocks|animate|widget-contact-info|widget-text|widget-follow-us|blog-legacy|account-login)/', 'wp_dequeue_style', 'wp_deregister_style' );
}

function threew_storefront_dequeue_payment_scripts() {
	if ( threew_storefront_is_payment_context() ) {
		return;
	}

	global $wp_scripts;
	threew_storefront_dequeue_assets( $wp_scripts, '/(affirm|paypal|ppcp|googlepay|applepay)/', 'wp_dequeue_script', 'wp_deregister_script' );
}

function threew_storefront_dequeue_mobile_catalog_scripts() {
	if ( ! threew_storefront_is_catalog_context() || ! threew_storefront_is_mobile_request() ) {
		return;
	}

	global $wp_scripts;
	threew_storefront_dequeue_assets( $wp_scripts, '/(threew-newsletter-popup|wc-cart-fragments|sourcebuster|wc-order-attribution|price-slider|gla-gtag-events|wp-emoji-release|porto-appear-animate|woocommerce-google-analytics|woocommerce-google-adwords|pmw-public)/', 'wp_dequeue_script', 'wp_deregister_script' );
}

function threew_storefront_register_catalog_optimization() {
	add_filter( 'request', 'threew_storefront_force_product_search_request', 1 );
	add_action( 'template_redirect', 'threew_storefront_buffer_catalog_optimization', 0 );
	add_action( 'wp_enqueue_scripts', 'threew_storefront_dequeue_payment_scripts', 1000 );
	add_action( 'wp_enqueue_scripts', 'threew_storefront_dequeue_payment_styles', 1000 );
	add_action( 'wp_print_styles', 'threew_storefront_dequeue_payment_styles', 1000 );
	add_action( 'wp_enqueue_scripts', 'threew_storefront_dequeue_mobile_catalog_styles', 1100 );
	add_action( 'wp_print_styles', 'threew_storefront_dequeue_mobile_catalog_styles', 1100 );
	add_action( 'wp_print_scripts', 'threew_storefront_dequeue_payment_scripts', 1000 );
	add_action( 'wp_print_footer_scripts', 'threew_storefront_dequeue_payment_scripts', 0 );
	add_action( 'wp_enqueue_scripts', 'threew_storefront_dequeue_mobile_catalog_scripts', 1100 );
	add_action( 'wp_print_scripts', 'threew_storefront_dequeue_mobile_catalog_scripts', 1100 );
	add_action( 'wp_print_footer_scripts', 'threew_storefront_dequeue_mobile_catalog_scripts', 1 );
}
threew_storefront_register_catalog_optimization();

