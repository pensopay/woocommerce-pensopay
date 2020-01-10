<?php

if ( ! function_exists( 'woocommerce_pensopay_get_template' ) ) {
	/**
	 * Convenience wrapper based on the wc_get_template method
	 *
	 * @param        $template_name
	 * @param array  $args
	 * @param string $template_path
	 * @param string $default_path
	 */
	function woocommerce_pensopay_get_template( $template_name, $args = [] ) {
		$template_path = 'woocommerce-pensopay/';
		$default_path = WCPP_PATH . 'templates/';

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}
}