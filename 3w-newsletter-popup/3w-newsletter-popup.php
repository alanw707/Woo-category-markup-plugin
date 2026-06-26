<?php
/**
 * Plugin Name: Newsletter Popup
 * Description: Lightweight local newsletter popup with coupon display and CSV export.
 * Version: 1.0.0
 * Author: Alan Wang
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const THREEW_NP_VERSION          = '1.0.0';
const THREEW_NP_TABLE_VERSION    = '1';
const THREEW_NP_OPTION_GROUP     = 'threew_np_settings';
const THREEW_NP_OPTION_CODE      = 'threew_np_coupon_code';
const THREEW_NP_OPTION_DISCOUNT  = 'threew_np_discount_percent';
const THREEW_NP_OPTION_DELAY     = 'threew_np_delay_seconds';
const THREEW_NP_SUPPRESSION_DAYS = 30;

register_activation_hook( __FILE__, 'threew_np_activate' );

function threew_np_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'threew_newsletter_subscribers';
}

function threew_np_activate() {
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset_collate = $wpdb->get_charset_collate();
    $table           = threew_np_table_name();

    $sql = "CREATE TABLE {$table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        email varchar(190) NOT NULL,
        consent tinyint(1) NOT NULL DEFAULT 0,
        ip varchar(45) NOT NULL DEFAULT '',
        user_agent text NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email)
    ) {$charset_collate};";

    dbDelta( $sql );
    update_option( 'threew_np_table_version', THREEW_NP_TABLE_VERSION );

    add_option( THREEW_NP_OPTION_CODE, '3wd5' );
    add_option( THREEW_NP_OPTION_DISCOUNT, '5' );
    add_option( THREEW_NP_OPTION_DELAY, '7' );
}

add_action( 'admin_menu', 'threew_np_admin_menu' );
add_action( 'admin_init', 'threew_np_register_settings' );
add_action( 'admin_post_threew_np_export_csv', 'threew_np_export_csv' );

function threew_np_admin_menu() {
    add_options_page(
        'Newsletter Popup',
        'Newsletter Popup',
        'manage_options',
        'threew-newsletter-popup',
        'threew_np_render_admin_page'
    );
}

function threew_np_register_settings() {
    register_setting( THREEW_NP_OPTION_GROUP, THREEW_NP_OPTION_CODE, [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '3wd5',
    ] );

    register_setting( THREEW_NP_OPTION_GROUP, THREEW_NP_OPTION_DELAY, [
        'type'              => 'integer',
        'sanitize_callback' => 'threew_np_sanitize_delay',
        'default'           => 7,
    ] );
}

function threew_np_sanitize_delay( $value ) {
    return max( 0, min( 300, absint( $value ) ) );
}

function threew_np_get_coupon_options() {
    if ( ! post_type_exists( 'shop_coupon' ) ) {
        return [];
    }

    return get_posts( [
        'post_type'      => 'shop_coupon',
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ] );
}

function threew_np_get_subscribers( $limit = 100 ) {
    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            'SELECT email, consent, ip, user_agent, created_at, updated_at FROM ' . threew_np_table_name() . ' ORDER BY updated_at DESC LIMIT %d',
            $limit
        )
    );
}

function threew_np_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $export_url  = wp_nonce_url( admin_url( 'admin-post.php?action=threew_np_export_csv' ), 'threew_np_export_csv' );
    $subscribers = threew_np_get_subscribers();
    $coupons     = threew_np_get_coupon_options();
    $coupon_code = threew_np_coupon_code();
    ?>
    <div class="wrap">
        <h1>Newsletter Popup</h1>

        <form method="post" action="options.php">
            <?php settings_fields( THREEW_NP_OPTION_GROUP ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( THREEW_NP_OPTION_CODE ); ?>">WooCommerce coupon</label></th>
                    <td>
                        <?php if ( empty( $coupons ) ) : ?>
                            <input class="regular-text" id="<?php echo esc_attr( THREEW_NP_OPTION_CODE ); ?>" name="<?php echo esc_attr( THREEW_NP_OPTION_CODE ); ?>" value="<?php echo esc_attr( $coupon_code ); ?>">
                            <p class="description">No WooCommerce coupons found. Create one under Marketing > Coupons.</p>
                        <?php else : ?>
                            <select id="<?php echo esc_attr( THREEW_NP_OPTION_CODE ); ?>" name="<?php echo esc_attr( THREEW_NP_OPTION_CODE ); ?>">
                                <?php foreach ( $coupons as $coupon ) : ?>
                                    <option value="<?php echo esc_attr( $coupon->post_title ); ?>" <?php selected( $coupon_code, $coupon->post_title ); ?>><?php echo esc_html( $coupon->post_title ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Discount amount comes from the selected WooCommerce coupon.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( THREEW_NP_OPTION_DELAY ); ?>">Delay seconds</label></th>
                    <td><input type="number" min="0" max="300" id="<?php echo esc_attr( THREEW_NP_OPTION_DELAY ); ?>" name="<?php echo esc_attr( THREEW_NP_OPTION_DELAY ); ?>" value="<?php echo esc_attr( get_option( THREEW_NP_OPTION_DELAY, '7' ) ); ?>"></td>
                </tr>
            </table>
            <?php submit_button( 'Save settings' ); ?>
        </form>

        <h2>Subscribers</h2>
        <p><a class="button" href="<?php echo esc_url( $export_url ); ?>">Export CSV</a></p>
        <table class="widefat striped">
            <thead><tr><th>Email</th><th>Consent</th><th>IP</th><th>Created</th><th>Updated</th></tr></thead>
            <tbody>
                <?php if ( empty( $subscribers ) ) : ?>
                    <tr><td colspan="5">No subscribers yet.</td></tr>
                <?php else : ?>
                    <?php foreach ( $subscribers as $subscriber ) : ?>
                        <tr>
                            <td><?php echo esc_html( $subscriber->email ); ?></td>
                            <td><?php echo $subscriber->consent ? 'Yes' : 'No'; ?></td>
                            <td><?php echo esc_html( $subscriber->ip ); ?></td>
                            <td><?php echo esc_html( $subscriber->created_at ); ?></td>
                            <td><?php echo esc_html( $subscriber->updated_at ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function threew_np_export_csv() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Forbidden', '', [ 'response' => 403 ] );
    }

    check_admin_referer( 'threew_np_export_csv' );

    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=3w-newsletter-subscribers.csv' );

    $out = fopen( 'php://output', 'w' );
    fputcsv( $out, [ 'email', 'consent', 'ip', 'user_agent', 'created_at', 'updated_at' ] );

    foreach ( threew_np_get_subscribers( 5000 ) as $subscriber ) {
        fputcsv( $out, [
            $subscriber->email,
            $subscriber->consent,
            $subscriber->ip,
            $subscriber->user_agent,
            $subscriber->created_at,
            $subscriber->updated_at,
        ] );
    }

    fclose( $out );
    exit;
}

add_action( 'wp_enqueue_scripts', 'threew_np_enqueue_frontend' );
add_action( 'wp_footer', 'threew_np_render_popup' );
add_action( 'wp_ajax_nopriv_threew_np_subscribe', 'threew_np_ajax_subscribe' );

function threew_np_should_show_popup() {
    if ( is_admin() || is_user_logged_in() ) {
        return false;
    }

    if ( function_exists( 'is_cart' ) && is_cart() ) {
        return false;
    }
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        return false;
    }
    if ( function_exists( 'is_account_page' ) && is_account_page() ) {
        return false;
    }

    return true;
}

function threew_np_coupon_code() {
    $code = get_option( THREEW_NP_OPTION_CODE, '3wd5' );
    return 'SAVE5' === $code ? '3wd5' : $code;
}

function threew_np_coupon_percent() {
    if ( class_exists( 'WC_Coupon' ) ) {
        $coupon = new WC_Coupon( threew_np_coupon_code() );
        if ( 'percent' === $coupon->get_discount_type() ) {
            return (int) $coupon->get_amount();
        }
    }

    return (int) get_option( THREEW_NP_OPTION_DISCOUNT, '5' );
}

function threew_np_coupon_payload() {
    return [
        'couponCode'      => threew_np_coupon_code(),
        'discountPercent' => threew_np_coupon_percent(),
    ];
}

function threew_np_enqueue_frontend() {
    if ( ! threew_np_should_show_popup() ) {
        return;
    }

    $base_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style(
        'threew-newsletter-popup',
        $base_url . 'assets/newsletter-popup.css',
        [],
        THREEW_NP_VERSION
    );

    wp_enqueue_script(
        'threew-newsletter-popup',
        $base_url . 'assets/newsletter-popup.js',
        [],
        THREEW_NP_VERSION,
        true
    );

    wp_localize_script( 'threew-newsletter-popup', 'threewNewsletterPopup', array_merge( threew_np_coupon_payload(), [
        'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
        'nonce'           => wp_create_nonce( 'threew_np_subscribe' ),
        'delaySeconds'    => (int) get_option( THREEW_NP_OPTION_DELAY, '7' ),
        'suppressionDays' => THREEW_NP_SUPPRESSION_DAYS,
    ] ) );
}

function threew_np_render_popup() {
    if ( ! threew_np_should_show_popup() ) {
        return;
    }

    $payload = threew_np_coupon_payload();
    ?>
    <div id="threew-newsletter-popup" hidden>
        <div class="threew-np-dialog" role="dialog" aria-modal="true" aria-labelledby="threew-np-title">
            <button id="threew-newsletter-popup-close" class="threew-np-close" type="button" aria-label="Close newsletter signup">&times;</button>
            <h2 id="threew-np-title">Get <?php echo esc_html( $payload['discountPercent'] ); ?>% off</h2>
            <p>Join our newsletter and receive your discount code after signup.</p>
            <form id="threew-newsletter-popup-form" class="threew-np-form">
                <label>Email address <input type="email" name="email" required></label>
                <label class="threew-np-consent"><input type="checkbox" name="consent" value="1" required> I agree to receive newsletter emails from 3W Distributing.</label>
                <button type="submit">Get coupon</button>
                <p id="threew-newsletter-popup-status" class="threew-np-status" aria-live="polite"></p>
            </form>
        </div>
    </div>
    <?php
}

function threew_np_ajax_subscribe() {
    if ( ! check_ajax_referer( 'threew_np_subscribe', 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => 'Security check failed.' ], 403 );
    }

    $email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $consent = ! empty( $_POST['consent'] );

    if ( ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => 'Enter a valid email address.' ], 400 );
    }
    if ( ! $consent ) {
        wp_send_json_error( [ 'message' => 'Newsletter consent is required.' ], 400 );
    }

    global $wpdb;

    $now        = current_time( 'mysql' );
    $ip         = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
    $table      = threew_np_table_name();

    $saved = $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO {$table} (email, consent, ip, user_agent, created_at, updated_at)
            VALUES (%s, 1, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE consent = VALUES(consent), ip = VALUES(ip), user_agent = VALUES(user_agent), updated_at = VALUES(updated_at)",
            $email,
            $ip,
            $user_agent,
            $now,
            $now
        )
    );

    if ( false === $saved ) {
        wp_send_json_error( [ 'message' => 'Signup failed. Please try again.' ], 500 );
    }

    wp_send_json_success( threew_np_coupon_payload() );
}
