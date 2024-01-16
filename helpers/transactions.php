<?php

/**
 * Returns the proper transaction instance type
 *
 * @param mixed $order
 *
 * @return WC_PensoPay_API_Payment|WC_PensoPay_API_Subscription
 */
function woocommerce_pensopay_get_transaction_instance_by_order( $order ) {

	$order = woocommerce_pensopay_get_order( $order );

	// Instantiate a new transaction
	$api_transaction = new WC_PensoPay_API_Payment();

	// If the order is a subscription or an attempt of updating the payment method
	if ( ! WC_PensoPay_Subscription::cart_contains_switches() && ( WC_PensoPay_Order_Utils::contains_subscription( $order ) || WC_PensoPay_Requests_Utils::is_request_to_change_payment() ) ) {
		// Instantiate a subscription transaction instead of a payment transaction
		$api_transaction = new WC_PensoPay_API_Subscription();
	}

	return $api_transaction;
}

/**
 * Creates a new transaction based on the order and persists the transaction ID on the object.
 *
 * @param mixed $order
 *
 * @return int
 * @throws PensoPay_API_Exception
 */
function woocommerce_pensopay_create_order_transaction( $order ) {
	$order = woocommerce_pensopay_get_order( $order );

	$transaction = woocommerce_pensopay_get_transaction_instance_by_order( $order );
	$result      = $transaction->create( $order );

	WC_PensoPay_Order_Payments_Utils::set_payment_id( $order, $result->id );

	return (int) $result->id;
}

/**
 * Returns an existing payment link if available or creates a new one.
 *
 * @param $order
 * @param bool $force_update
 *
 * @return string
 * @throws PensoPay_API_Exception
 */
function woocommerce_pensopay_create_payment_link( $order, bool $force_update = true ): ?string {

	$order = woocommerce_pensopay_get_order( $order );

	if ( ! $order->needs_payment() && ! WC_PensoPay_Requests_Utils::is_request_to_change_payment() ) {
		throw new Exception( __( 'Order does not need payment', 'woo-pensopay' ) );
	}

	$transaction  = woocommerce_pensopay_get_transaction_instance_by_order( $order );

	$payment_link = WC_PensoPay_Order_Payments_Utils::get_payment_link( $order );
	$payment_id   = WC_PensoPay_Order_Payments_Utils::get_payment_id( $order );

	if ( empty( $payment_id ) && empty( $payment_link ) ) {
		$payment_id = woocommerce_pensopay_create_order_transaction( $order );
	} else {
		$transaction->patch_payment( $payment_id, $order );
	}

	if ( empty( $payment_link ) || $force_update ) {
		// Create or update the payment link. This is necessary to do EVERY TIME
		// to avoid fraud with changing amounts.
		$link = $transaction->patch_link( $payment_id, $order );

		if ( WC_PensoPay_Helper::is_url( $link->url ) ) {
			WC_PensoPay_Order_Payments_Utils::set_payment_link( $order, $link->url );
			$payment_link = $link->url;
		}
	}

	return $payment_link;
}

/**
 * Creates a payment transaction.
 *
 * @param mixed $order
 *
 * @return \WC_PensoPay_Order
 */
function woocommerce_pensopay_get_order( $order ): ?WC_Order {
	if ( ! is_object( $order ) ) {
		return wc_get_order( $order ) ?: null;
	}

	if ( $order instanceof WP_Post ) {
		return wc_get_order( $order->ID ) ?: null;
	}

	return $order;
}

/**
 * Returns a WC_Subscription object.
 *
 * @param mixed $subscription
 *
 * @return WC_Subscription|null
 */
function woocommerce_pensopay_get_subscription( $subscription ) {
	if ( ! function_exists( 'wcs_get_subscription' ) ) {
		return null;
	}

	if ( ! is_object( $subscription ) ) {
		return wcs_get_subscription( $subscription ) ?: null;
	}

	if ( $subscription instanceof WP_Post ) {
		return wcs_get_subscription( $subscription->ID ) ?: null;
	}

	return $subscription;
}

/**
 * Returns the locale used in the payment window
 * @return string
 */
function woocommerce_pensopay_get_language(): string {
	[ $language ] = explode( '_', get_locale() );

    return apply_filters( 'woocommerce_pensopay_language', $language );
}
