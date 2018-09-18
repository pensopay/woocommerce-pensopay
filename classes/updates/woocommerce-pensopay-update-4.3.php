<?php
/**
 * Update WC_PensoPay to 4.3
 *
 * @author 		PensoPay
 * @version     2.0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$settings = get_option( 'woocommerce_pensopay_settings' );

if ( ! isset( $settings['pensopay_autocapture_virtual'] ) && isset( $settings['pensopay_autocapture'] ) ) {
    $settings['pensopay_autocapture_virtual'] = $settings['pensopay_autocapture'];
}

update_option( 'woocommerce_pensopay_settings', $settings );