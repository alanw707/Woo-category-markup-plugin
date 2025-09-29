<?php
/**
 * Plugin Name: Category Brabus Markup
 * Description: Dynamically applies per-category markups (default 15% for 'brabus') without altering stored WooCommerce prices. Safe to remove.
 * Version: 1.1.0
 * Author: Your Name
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CONFIG: Base category->markup map.
 * Key = product_cat slug, Value = positive percent (e.g. 15 = +15%).
 * For your request: 15% on category slug 'tarrifs'.
 *
 * To change later: edit this array OR use filter 'aw_category_markups'.
 */
const AW_CATEGORY_MARKUPS = [
    'brabus' => 15, // Default 15% markup for brabus category
];

/**
 * Strategy if multiple configured categories match a product:
 * 'max'   => use highest markup
 * 'first' => first array order match
 * 'sum'   => sum all matching percentages
 */
const AW_MARKUP_STRATEGY = 'max';

/**
 * Apply markup to sale price (true) or always from regular price (false).
 */
const AW_APPLY_TO_SALE_PRICE = true;

/**
 * Round final adjusted price to WooCommerce price decimals.
 */
const AW_ROUND_PRICE = true;

/**
 * Frontend-only? If you want admin product edit screen to show original prices,
 * set to true. With false, markup also appears in admin lists.
 */
const AW_FRONTEND_ONLY = false;

/**
 * Core filters to adjust runtime price (simple + variations).
 */
add_filter( 'woocommerce_product_get_price', 'aw_ctm_apply_category_markup', 25, 2 );
add_filter( 'woocommerce_product_variation_get_price', 'aw_ctm_apply_category_markup', 25, 2 );

/**
 * Main adjustment function.
 *
 * @param string $price
 * @param WC_Product $product
 * @return string
 */
function aw_ctm_apply_category_markup( $price, $product ) {

    if ( $price === '' || $price === null ) {
        return $price;
    }

    if ( AW_FRONTEND_ONLY && is_admin() && ! defined('DOING_AJAX') ) {
        return $price;
    }

    if ( isset( $product->_aw_ctm_applied ) && $product->_aw_ctm_applied ) {
        return $price;
    }

    $product_id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();
    if ( ! $product_id ) {
        return $price;
    }

    $terms = get_the_terms( $product_id, 'product_cat' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return $price;
    }

    // 1. Static config (can be empty) + filter override
    $static_markups = apply_filters( 'aw_category_markups', AW_CATEGORY_MARKUPS, $product );
    if ( ! is_array( $static_markups ) ) {
        $static_markups = [];
    }

    // 2. Dynamic per-category term meta (_aw_markup_percent) gathered only for terms assigned to this product
    $dynamic_markups = [];
    foreach ( $terms as $term ) {
        $val = get_term_meta( $term->term_id, '_aw_markup_percent', true );
        if ( $val !== '' && is_numeric( $val ) ) {
            $dynamic_markups[ $term->slug ] = (float) $val; // existing category custom markup
        }
    }

    // Merge: dynamic overrides static if same slug appears
    $markups = array_merge( $static_markups, $dynamic_markups );
    if ( empty( $markups ) ) {
        return $price; // nothing to do for this product
    }

    $slugs = array_map( static function( $t ){ return $t->slug; }, $terms );
    $configured_slugs = array_keys( $markups );
    $matched = array_values( array_intersect( $configured_slugs, $slugs ) );
    if ( empty( $matched ) ) {
        return $price;
    }

    $strategy = apply_filters( 'aw_markup_strategy', AW_MARKUP_STRATEGY, $product, $matched, $markups );

    $markup_percent = 0.0;
    switch ( $strategy ) {
        case 'first':
            foreach ( $markups as $slug => $pct ) {
                if ( in_array( $slug, $matched, true ) ) {
                    $markup_percent = (float) $pct;
                    break;
                }
            }
            break;
        case 'sum':
            foreach ( $matched as $slug ) {
                $markup_percent += (float) $markups[ $slug ];
            }
            break;
        case 'max':
        default:
            $candidates = [];
            foreach ( $matched as $slug ) {
                $candidates[] = (float) $markups[ $slug ];
            }
            $markup_percent = max( $candidates );
            break;
    }

    if ( $markup_percent <= 0 ) {
        return $price;
    }

    $regular = $product->get_regular_price();
    $sale    = $product->get_sale_price();
    if ( $regular === '' ) {
        return $price;
    }

    $base_for_markup = ( AW_APPLY_TO_SALE_PRICE && $sale !== '' ) ? (float) $sale : (float) $regular;

    $new_price = $base_for_markup * ( 100 + $markup_percent ) / 100;

    if ( AW_ROUND_PRICE ) {
        $new_price = round( $new_price, wc_get_price_decimals() );
    }

    $product->_aw_ctm_applied = true;

    return (string) apply_filters( 'aw_new_price_after_markup', $new_price, $product, $markup_percent, $base_for_markup );
}

/**
 * (Optional) Show original + marked-up price in catalog.
 * Disabled by default. Uncomment if desired.
 */
/*
add_filter( 'woocommerce_get_price_html', function( $html, $product ){
    if ( isset( $product->_aw_ctm_applied ) && $product->_aw_ctm_applied ) {
        $regular = $product->get_regular_price();
        if ( $regular !== '' ) {
            // Basic formatting; customize as needed
            $original = wc_price( $regular );
            $html = '<del style="opacity:.7;margin-right:4px;">' . $original . '</del> ' . $html;
        }
    }
    return $html;
}, 100, 2 );
*/
/**
 * Admin term fields to allow per-category markup without adding new categories.
 * Adds a numeric field (percentage) stored in term meta _aw_markup_percent.
 */
add_action('product_cat_add_form_fields', function(){
    ?>
    <div class="form-field">
        <label for="aw_markup_percent">Markup Percent (%)</label>
        <input type="number" step="0.01" min="0" name="aw_markup_percent" />
        <p>Add a positive number to increase price for this category. Leave blank for none.</p>
    </div>
    <?php
});
add_action('product_cat_edit_form_fields', function($term){
    $val = get_term_meta($term->term_id, '_aw_markup_percent', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="aw_markup_percent">Markup Percent (%)</label></th>
        <td>
            <input type="number" step="0.01" min="0" name="aw_markup_percent" value="<?php echo esc_attr($val); ?>" />
            <p class="description">Leave blank for no markup. This value overrides any static plugin config for this category.</p>
        </td>
    </tr>
    <?php
});
add_action('created_product_cat', 'aw_ctm_save_term_markup');
add_action('edited_product_cat', 'aw_ctm_save_term_markup');
function aw_ctm_save_term_markup($term_id){
    if ( isset($_POST['aw_markup_percent']) ) {
        $val = trim(wp_unslash($_POST['aw_markup_percent']));
        if ($val === '') {
            delete_term_meta($term_id, '_aw_markup_percent');
        } elseif ( is_numeric( $val ) ) {
            update_term_meta($term_id, '_aw_markup_percent', sanitize_text_field($val));
        }
    }
}


/**
 * Price hash adjustment (so WooCommerce caches distinct prices per config).
 */
add_filter( 'woocommerce_get_price_hash', function( $hash, $product ){
    $hash['aw_ctm'] = [
        'markups'  => AW_CATEGORY_MARKUPS,
        'strategy' => AW_MARKUP_STRATEGY,
        'saleBase' => AW_APPLY_TO_SALE_PRICE,
    ];
    return $hash;
}, 10, 2 );

/**
 * Example customization via filter (REMOVE or edit as needed):
 *
 * add_filter('aw_category_markups', function($markups){
 *     $markups['tarrifs'] = 18; // change to 18%
 *     return $markups;
 * });
 */
