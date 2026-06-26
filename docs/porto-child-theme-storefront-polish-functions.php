<?php
/**
 * 3W Porto child theme storefront polish helpers.
 * Copy these snippets into wp-content/themes/<porto-child>/functions.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style(
			'threew-storefront-polish',
			get_stylesheet_directory_uri() . '/assets/css/storefront-polish.css',
			array(),
			'1.1.0'
		);
	},
	30
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
	'wp_footer',
	static function () {
		?>
		<script id="threew-storefront-polish-js">
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
					labelIconControls();
				});

				window.setTimeout(stabilizeHeroCarousel, 600);
				window.setTimeout(stabilizeHeroCarousel, 1600);
				window.setTimeout(labelIconControls, 600);
			})();
		</script>
		<?php
	},
	99
);
