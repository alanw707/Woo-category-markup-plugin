<?php
/**
 * Plugin Name: Category Markup
 * Description: Dynamically applies per-category markups (default 15% for Brabus categories) without altering stored WooCommerce prices.
 * Version: 1.2.1
 * Author: Alan Wang
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const AW_CATEGORY_MARKUPS = [
    'brabus' => 15,
];
const AW_MARKUP_STRATEGY     = 'max';
const AW_APPLY_TO_SALE_PRICE = true;
const AW_ROUND_PRICE         = true;
const AW_FRONTEND_ONLY       = false;

add_filter( 'woocommerce_product_get_price',               'aw_cm_apply_markup', 25, 2 );
add_filter( 'woocommerce_product_get_regular_price',       'aw_cm_apply_markup', 25, 2 );
add_filter( 'woocommerce_product_get_sale_price',          'aw_cm_apply_markup', 25, 2 );
add_filter( 'woocommerce_product_variation_get_price',     'aw_cm_apply_markup', 25, 2 );
add_filter( 'woocommerce_product_variation_get_regular_price', 'aw_cm_apply_markup', 25, 2 );
add_filter( 'woocommerce_product_variation_get_sale_price','aw_cm_apply_markup', 25, 2 );
add_filter( 'woocommerce_variation_prices',                'aw_cm_apply_variation_prices', 25, 3 );

function aw_cm_product_category_terms( $product ) {
    $product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
    if ( ! $product_id ) {
        return [];
    }

    $terms = get_the_terms( $product_id, 'product_cat' );
    return ( empty( $terms ) || is_wp_error( $terms ) ) ? [] : $terms;
}

function aw_cm_resolve_markup_percent( $product, $terms = null ) {
    $terms = null === $terms ? aw_cm_product_category_terms( $product ) : $terms;
    if ( empty( $terms ) ) {
        return 0.0;
    }

    $markups = apply_filters( 'aw_category_markups', AW_CATEGORY_MARKUPS, $product );
    $markups = is_array( $markups ) ? $markups : [];

    foreach ( $terms as $term ) {
        $value = get_term_meta( $term->term_id, '_aw_markup_percent', true );
        if ( $value !== '' && is_numeric( $value ) ) {
            $markups[ $term->slug ] = (float) $value;
        }
    }

    $slugs = wp_list_pluck( $terms, 'slug' );
    if ( isset( $markups['brabus'] ) ) {
        foreach ( $slugs as $slug ) {
            if ( ( $slug === 'brabus' || strpos( $slug, 'brabus-' ) === 0 ) && ! array_key_exists( $slug, $markups ) ) {
                $markups[ $slug ] = $markups['brabus'];
            }
        }
    }

    $matched = array_values( array_intersect( array_keys( $markups ), $slugs ) );
    if ( empty( $matched ) ) {
        return 0.0;
    }

    $strategy = apply_filters( 'aw_markup_strategy', AW_MARKUP_STRATEGY, $product, $matched, $markups );
    switch ( $strategy ) {
        case 'first':
            foreach ( $markups as $slug => $percent ) {
                if ( in_array( $slug, $matched, true ) ) {
                    return (float) $percent;
                }
            }
            return 0.0;
        case 'sum':
            return array_sum( array_map( 'floatval', array_intersect_key( $markups, array_flip( $matched ) ) ) );
        case 'max':
        default:
            return max( array_map( 'floatval', array_intersect_key( $markups, array_flip( $matched ) ) ) );
    }
}

function aw_cm_is_empty_price( $price ) {
    return $price === '' || $price === null;
}

function aw_cm_apply_markup( $price, $product ) {
    if ( AW_FRONTEND_ONLY && is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return $price;
    }

    $filter = current_filter();
    switch ( $filter ) {
        case 'woocommerce_product_get_regular_price':
        case 'woocommerce_product_variation_get_regular_price':
            $context = 'regular';
            break;
        case 'woocommerce_product_get_sale_price':
        case 'woocommerce_product_variation_get_sale_price':
            $context = 'sale';
            break;
        default:
            $context = 'price';
            break;
    }

    $markup_percent = aw_cm_resolve_markup_percent( $product );
    if ( $markup_percent <= 0 ) {
        return $price;
    }

    if ( 'sale' === $context && ! AW_APPLY_TO_SALE_PRICE ) {
        return $price;
    }

    if ( 'regular' === $context || 'sale' === $context ) {
        if ( aw_cm_is_empty_price( $price ) ) {
            return $price;
        }
        $base_price = (float) $price;
    } else {
        $regular_raw = $product->get_regular_price( 'edit' );
        $sale_raw    = $product->get_sale_price( 'edit' );
        $base_source = ( AW_APPLY_TO_SALE_PRICE && ! aw_cm_is_empty_price( $sale_raw ) ) ? $sale_raw : $regular_raw;

        if ( aw_cm_is_empty_price( $base_source ) ) {
            if ( aw_cm_is_empty_price( $price ) ) {
                return $price;
            }
            $base_source = $price;
        }

        $base_price = (float) $base_source;
    }

    if ( $base_price <= 0 ) {
        return $price;
    }

    $new_price = $base_price * ( 100 + $markup_percent ) / 100;
    if ( AW_ROUND_PRICE ) {
        $new_price = round( $new_price, wc_get_price_decimals() );
    }

    return (string) apply_filters( 'aw_new_price_after_markup', $new_price, $product, $markup_percent, $base_price );
}

function aw_cm_apply_variation_prices( $prices_array, $product, $for_display ) {
    if ( AW_FRONTEND_ONLY && is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return $prices_array;
    }

    $markup_percent = aw_cm_resolve_markup_percent( $product );
    if ( $markup_percent <= 0 ) {
        return $prices_array;
    }

    $multiplier = ( 100 + $markup_percent ) / 100;

    foreach ( $prices_array as $price_key => $variation_prices ) {
        if ( ! is_array( $variation_prices ) ) {
            continue;
        }

        foreach ( $variation_prices as $variation_id => $price ) {
            if ( aw_cm_is_empty_price( $price ) || ! is_numeric( $price ) ) {
                continue;
            }

            $new_price = (float) $price * $multiplier;
            if ( AW_ROUND_PRICE ) {
                $new_price = round( $new_price, wc_get_price_decimals() );
            }
            $prices_array[ $price_key ][ $variation_id ] = $new_price;
        }
    }

    return $prices_array;
}

/* Term meta field */
add_action( 'product_cat_add_form_fields', function() { ?>
    <div class="form-field">
        <label for="aw_markup_percent">Markup Percent (%)</label>
        <input type="number" step="0.01" min="0" name="aw_markup_percent" />
        <p>Positive percent to increase prices. Leave blank for none.</p>
    </div>
<?php });

add_action( 'product_cat_edit_form_fields', function( $term ) { $val = get_term_meta( $term->term_id, '_aw_markup_percent', true ); ?>
    <tr class="form-field">
        <th scope="row"><label for="aw_markup_percent">Markup Percent (%)</label></th>
        <td>
            <input type="number" step="0.01" min="0" name="aw_markup_percent" value="<?php echo esc_attr( $val ); ?>" />
            <p class="description">Overrides static default. Blank = none.</p>
        </td>
    </tr>
<?php });

add_action( 'created_product_cat', 'aw_cm_save_term_meta' );
add_action( 'edited_product_cat',  'aw_cm_save_term_meta' );
function aw_cm_save_term_meta( $term_id ) {
    if ( isset( $_POST['aw_markup_percent'] ) ) {
        $raw = trim( wp_unslash( $_POST['aw_markup_percent'] ) );
        if ( $raw === '' ) { delete_term_meta( $term_id, '_aw_markup_percent' ); }
        elseif ( is_numeric( $raw ) ) { update_term_meta( $term_id, '_aw_markup_percent', sanitize_text_field( $raw ) ); }
    }
}

add_filter( 'woocommerce_get_price_hash', function( $hash, $product ) {
    $terms = aw_cm_product_category_terms( $product );

    $dynamic_markups = [];
    foreach ( $terms as $term ) {
        $meta_value = get_term_meta( $term->term_id, '_aw_markup_percent', true );
        if ( $meta_value !== '' ) {
            $dynamic_markups[ $term->slug ] = $meta_value;
        }
    }

    $hash['aw_cm'] = [
        'static'           => apply_filters( 'aw_category_markups', AW_CATEGORY_MARKUPS, $product ),
        'strategy'         => AW_MARKUP_STRATEGY,
        'saleBase'         => AW_APPLY_TO_SALE_PRICE,
        'categories'       => wp_list_pluck( $terms, 'slug' ),
        'dynamic_markups'  => $dynamic_markups,
        'resolved_percent' => aw_cm_resolve_markup_percent( $product, $terms ),
    ];
    return $hash;
}, 10, 2 );
