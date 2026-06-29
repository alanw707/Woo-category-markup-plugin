<?php
/**
 * Plugin Name: 3W Storefront Polish Hotfix
 * Description: Small design and accessibility polish fixes for the 3W Distributing Porto storefront homepage.
 * Version: 1.2.117
 * Author: 3W Distributing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'THREEW_STOREFRONT_POLISH_VERSION', '1.2.117' );

add_action(
	'init',
	static function () {
		if ( get_option( 'threew_storefront_moderncart_position_configured' ) !== 'yes' ) {
			$moderncart_floating_settings = get_option( 'moderncart_floating', [] );

			if ( ! is_array( $moderncart_floating_settings ) ) {
				$moderncart_floating_settings = [];
			}

			$moderncart_floating_settings['floating_cart_position'] = 'bottom-left';
			update_option( 'moderncart_floating', $moderncart_floating_settings, false );
			update_option( 'threew_storefront_moderncart_position_configured', 'yes', false );
		}

		if ( get_option( 'threew_storefront_polish_version' ) === THREEW_STOREFRONT_POLISH_VERSION ) {
			return;
		}

		update_option( 'threew_storefront_polish_version', THREEW_STOREFRONT_POLISH_VERSION );
		do_action( 'litespeed_purge_all' );
	},
	1
);

require_once __DIR__ . '/inc/catalog-optimization.php';
require_once __DIR__ . '/inc/product-enrichment.php';
require_once __DIR__ . '/inc/mobile-storefront.php';
require_once __DIR__ . '/inc/trust-badge.php';
