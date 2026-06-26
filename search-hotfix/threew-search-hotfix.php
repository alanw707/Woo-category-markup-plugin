<?php
/**
 * Plugin Name: 3W Search Hotfix
 * Description: Maps "new arrivals" product searches to newest visible products.
 * Version: 1.0.0
 * Author: 3W Distributing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function threew_is_new_arrivals_search( $term ) {
	$normalized = strtolower( trim( preg_replace( '/\s+/', ' ', (string) $term ) ) );

	return in_array(
		$normalized,
		array( 'new arrival', 'new arrivals', 'new-arrival', 'new-arrivals' ),
		true
	);
}

add_action(
	'pre_get_posts',
	static function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return;
		}

		$post_type = $query->get( 'post_type' );
		if ( 'product' !== $post_type && array( 'product' ) !== $post_type ) {
			return;
		}

		if ( ! threew_is_new_arrivals_search( $query->get( 's' ) ) ) {
			return;
		}

		$query->set( 's', '' );
		$query->set( 'post_type', 'product' );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
		$query->set( 'posts_per_page', 24 );

		if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
			$visibility_terms = wc_get_product_visibility_term_ids();
			$excluded_terms   = array_filter(
				array(
					$visibility_terms['exclude-from-search'] ?? 0,
				)
			);

			if ( $excluded_terms ) {
				$tax_query   = (array) $query->get( 'tax_query' );
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => $excluded_terms,
					'operator' => 'NOT IN',
				);

				$query->set( 'tax_query', $tax_query );
			}
		}
	},
	20
);
