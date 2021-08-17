<?php

if ( ! function_exists( 'filter_woocommerce_pensopay_wpml_language' ) ) {
	/**
	 * Automatically sets the payment window language to the WPML user language, if available.
	 *
	 * @param $language
	 *
	 * @return mixed
	 */
	function filter_woocommerce_pensopay_wpml_language( $language ) {
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$language = ICL_LANGUAGE_CODE;
		}

		return $language;
	}

	add_filter( 'woocommerce_pensopay_language', 'filter_woocommerce_pensopay_wpml_language' );
}
