<?php
/**
 * Plugin Name: 3W Storefront Polish Hotfix
 * Description: Small design and accessibility polish fixes for the 3W Distributing Porto storefront homepage.
 * Version: 1.2.112
 * Author: 3W Distributing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'THREEW_STOREFRONT_POLISH_VERSION', '1.2.112' );

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

require_once __DIR__ . '/inc/catalog-optimization.php';
require_once __DIR__ . '/inc/product-enrichment.php';
require_once __DIR__ . '/inc/mobile-storefront.php';
