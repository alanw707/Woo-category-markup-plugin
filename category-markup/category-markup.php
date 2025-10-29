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

    $product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
    if ( ! $product_id ) {
        return $price;
    }

    $terms = get_the_terms( $product_id, 'product_cat' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return $price;
    }

    $static = apply_filters( 'aw_category_markups', AW_CATEGORY_MARKUPS, $product );
    if ( ! is_array( $static ) ) {
        $static = [];
    }

    $dynamic = [];
    foreach ( $terms as $term ) {
        $v = get_term_meta( $term->term_id, '_aw_markup_percent', true );
        if ( $v !== '' && is_numeric( $v ) ) {
            $dynamic[ $term->slug ] = (float) $v;
        }
    }

    $merged = array_merge( $static, $dynamic );
    if ( empty( $merged ) ) {
        return $price;
    }

    $slugs = wp_list_pluck( $terms, 'slug' );

    // Allow base "brabus" markup to cascade to slugs like "brabus-mercedes" automatically.
    if ( isset( $merged['brabus'] ) ) {
        foreach ( $slugs as $term_slug ) {
            if ( $term_slug === 'brabus' || strpos( $term_slug, 'brabus-' ) === 0 ) {
                if ( ! array_key_exists( $term_slug, $merged ) ) {
                    $merged[ $term_slug ] = $merged['brabus'];
                }
            }
        }
    }

    $matched = array_values( array_intersect( array_keys( $merged ), $slugs ) );
    if ( empty( $matched ) ) {
        return $price;
    }

    $strategy = apply_filters( 'aw_markup_strategy', AW_MARKUP_STRATEGY, $product, $matched, $merged );
    $markup_percent = 0.0;
    switch ( $strategy ) {
        case 'first':
            foreach ( $merged as $slug => $pct ) {
                if ( in_array( $slug, $matched, true ) ) {
                    $markup_percent = (float) $pct;
                    break;
                }
            }
            break;
        case 'sum':
            foreach ( $matched as $slug ) {
                $markup_percent += (float) $merged[ $slug ];
            }
            break;
        case 'max':
        default:
            $candidates = [];
            foreach ( $matched as $slug ) {
                $candidates[] = (float) $merged[ $slug ];
            }
            $markup_percent = max( $candidates );
            break;
    }

    if ( $markup_percent <= 0 ) {
        return $price;
    }

    if ( $context === 'regular' ) {
        if ( $price === '' || $price === null ) {
            return $price;
        }
        $base_price = (float) $price;
    } elseif ( $context === 'sale' ) {
        if ( ! AW_APPLY_TO_SALE_PRICE || $price === '' || $price === null ) {
            return $price;
        }
        $base_price = (float) $price;
    } else {
        $regular_raw = $product->get_regular_price( 'edit' );
        $sale_raw    = $product->get_sale_price( 'edit' );
        $base_source = ( AW_APPLY_TO_SALE_PRICE && $sale_raw !== '' ) ? $sale_raw : $regular_raw;

        if ( $base_source === '' || $base_source === null ) {
            if ( $price === '' || $price === null ) {
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

    $product_id = $product->get_id();
    if ( ! $product_id ) {
        return $prices_array;
    }

    $terms = get_the_terms( $product_id, 'product_cat' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return $prices_array;
    }

    $static = apply_filters( 'aw_category_markups', AW_CATEGORY_MARKUPS, $product );
    if ( ! is_array( $static ) ) {
        $static = [];
    }

    $dynamic = [];
    foreach ( $terms as $term ) {
        $v = get_term_meta( $term->term_id, '_aw_markup_percent', true );
        if ( $v !== '' && is_numeric( $v ) ) {
            $dynamic[ $term->slug ] = (float) $v;
        }
    }

    $merged = array_merge( $static, $dynamic );
    if ( empty( $merged ) ) {
        return $prices_array;
    }

    $slugs = wp_list_pluck( $terms, 'slug' );

    if ( isset( $merged['brabus'] ) ) {
        foreach ( $slugs as $term_slug ) {
            if ( $term_slug === 'brabus' || strpos( $term_slug, 'brabus-' ) === 0 ) {
                if ( ! array_key_exists( $term_slug, $merged ) ) {
                    $merged[ $term_slug ] = $merged['brabus'];
                }
            }
        }
    }

    $matched = array_values( array_intersect( array_keys( $merged ), $slugs ) );
    if ( empty( $matched ) ) {
        return $prices_array;
    }

    $strategy = apply_filters( 'aw_markup_strategy', AW_MARKUP_STRATEGY, $product, $matched, $merged );
    $markup_percent = 0.0;
    switch ( $strategy ) {
        case 'first':
            foreach ( $merged as $slug => $pct ) {
                if ( in_array( $slug, $matched, true ) ) {
                    $markup_percent = (float) $pct;
                    break;
                }
            }
            break;
        case 'sum':
            foreach ( $matched as $slug ) {
                $markup_percent += (float) $merged[ $slug ];
            }
            break;
        case 'max':
        default:
            $candidates = [];
            foreach ( $matched as $slug ) {
                $candidates[] = (float) $merged[ $slug ];
            }
            $markup_percent = max( $candidates );
            break;
    }

    if ( $markup_percent <= 0 ) {
        return $prices_array;
    }

    $multiplier = ( 100 + $markup_percent ) / 100;

    foreach ( $prices_array as $price_key => $variation_prices ) {
        if ( is_array( $variation_prices ) ) {
            foreach ( $variation_prices as $variation_id => $price ) {
                if ( $price !== '' && is_numeric( $price ) ) {
                    $new_price = (float) $price * $multiplier;
                    if ( AW_ROUND_PRICE ) {
                        $new_price = round( $new_price, wc_get_price_decimals() );
                    }
                    $prices_array[ $price_key ][ $variation_id ] = $new_price;
                }
            }
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
    $product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

    // Include category slugs so different categories get different cached prices
    $terms = get_the_terms( $product_id, 'product_cat' );
    $category_slugs = ( ! empty( $terms ) && ! is_wp_error( $terms ) )
        ? wp_list_pluck( $terms, 'slug' )
        : [];

    // Include dynamic term meta so cache invalidates when markup percentages change
    $dynamic_markups = [];
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $meta_value = get_term_meta( $term->term_id, '_aw_markup_percent', true );
            if ( $meta_value !== '' ) {
                $dynamic_markups[ $term->slug ] = $meta_value;
            }
        }
    }

    $hash['aw_cm'] = [
        'static'          => AW_CATEGORY_MARKUPS,
        'strategy'        => AW_MARKUP_STRATEGY,
        'saleBase'        => AW_APPLY_TO_SALE_PRICE,
        'categories'      => $category_slugs,
        'dynamic_markups' => $dynamic_markups,
    ];
    return $hash;
}, 10, 2 );
