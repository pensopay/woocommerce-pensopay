<?php
/**
 * Store a message to display in WP admin.
 *
 * @param string The message to display
 * @since 4.9.4
 */
function woocommerce_pensopay_add_admin_notice( $message, $notice_type = 'success' ) {

	$notices = get_transient( '_wcqp_admin_notices' );

	if ( false === $notices ) {
		$notices = array();
	}

	$notices[ $notice_type ][] = $message;

	set_transient( '_wcqp_admin_notices', $notices, 60 * 60 );
}

/**
 * Delete any admin notices we stored for display later.
 *
 * @since 2.0
 */
function woocommere_pensopay_clear_admin_notices() {
	delete_transient( '_wcqp_admin_notices' );
}

/**
 * Display any notices added with @see woocommerce_pensopay_add_admin_notice()
 *
 * This method is also hooked to 'admin_notices' to display notices there.
 *
 * @since 2.0
 */
function woocommere_pensopay_display_admin_notices( $clear = true ) {

	$notices = get_transient( '_wcqp_admin_notices' );

	if ( false !== $notices && ! empty( $notices ) ) {

		if ( ! empty( $notices['success'] ) ) {
			array_walk( $notices['success'], 'esc_html' );
			echo '<div class="notice notice-info"><p>' . wp_kses_post( implode( "</p>\n<p>", $notices['success'] ) ) . '</p></div>';
		}

		if ( ! empty( $notices['error'] ) ) {
			array_walk( $notices['error'], 'esc_html' );
			echo '<div class="notice notice-error"><p>' . wp_kses_post( implode( "</p>\n<p>", $notices['error'] ) ) . '</p></div>';
		}
	}

	if ( false !== $clear ) {
		woocommere_pensopay_clear_admin_notices();
	}
}
add_action( 'admin_notices', 'woocommere_pensopay_display_admin_notices', 100 );