<?php

if ( ! function_exists( 'filter_woocommerce_pensopay_polylang_language' ) ) {
	/**
	 * Automatically sets the payment window language to the Polylang user language, if available.
	 *
	 * @param $language
	 *
	 * @return mixed
	 */
	function filter_woocommerce_pensopay_polylang_language( $language ) {
		if ( function_exists( 'pll_current_language' ) ) {
			$language = pll_current_language();
		}

		return $language;
	}

	add_filter( 'woocommerce_pensopay_language', 'filter_woocommerce_pensopay_polylang_language' );
}
