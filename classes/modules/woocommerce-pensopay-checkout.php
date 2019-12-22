<?php

/**
 * Class WC_PensoPay_Checkout_Frontend
 * @since 5.2.0
 */
class WC_PensoPay_Checkout extends WC_PensoPay_Module {

	/**
	 * Adds hooks and filters
	 *
	 * @return mixed
	 */
	public function hooks() {
	}

	/**
	 * @param $order
	 *
	 * @return string
	 * @throws PensoPay_API_Exception
	 */
	public static function create_overlay_payment_link_for_order( $order ) {
		add_filter( 'woocommerce_pensopay_transaction_link_params', 'WC_PensoPay_Checkout::set_acquirer_clearhaus', 10, 3 );

		$payment_link = woocommerce_pensopay_create_payment_link( $order,true );

		remove_filter( 'woocommerce_pensopay_transaction_link_params', 'WC_PensoPay_Checkout::set_acquirer_clearhaus', 10, 3 );

		return $payment_link;
	}

	/**
	 * Sets Clearhaus as acquirer on certain payment links.
	 *
	 * @param $merged_params
	 * @param $order
	 * @param $payment_method
	 *
	 * @return mixed
	 */
	public static function set_acquirer_clearhaus( $merged_params, $order, $payment_method ) {
		$merged_params['acquirer'] = 'clearhaus';

		return $merged_params;
	}
}