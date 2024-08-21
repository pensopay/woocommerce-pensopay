<?php

class WC_PensoPay_Admin_Orders extends WC_PensoPay_Module {

	/**
	 * Perform actions and filters
	 *
	 * @return mixed
	 */
	public function hooks() {
		// Custom order actions
		add_filter( 'woocommerce_order_actions', [ $this, 'admin_order_actions' ], 10, 1 );
		add_action( 'woocommerce_order_action_pensopay_create_payment_link', [ $this, 'order_action_pensopay_create_payment_link' ], 50, 2 );
	}

	/**
	 * @param \WC_Order $order
	 *
	 * @return bool|void
	 */
	public function order_action_pensopay_create_payment_link( $order ) {
		if ( ! $order ) {
			return;
		}

		// The order used to create transaction data with PensoPay.
		$is_subscription                         = WC_PensoPay_Subscription::is_subscription( $order );
		$resource_order                          = $order;
		$subscription                            = null;
		$is_renewal_order                        = false;
		$is_card_update_enabled                  = wc_string_to_bool( WC_PP()->s( 'subscription_update_card_on_manual_renewal_payment' ) );
		$is_change_payment_request_flag_modified = false;

		// Determine if payment link creation should be skipped.
		// By default, we will skip payment link creation if the order is paid already.
		if ( ! apply_filters( 'woocommerce_pensopay_order_action_create_payment_link_for_order', ! $order->is_paid(), $order ) ) {
			woocommerce_pensopay_add_admin_notice( sprintf( __( 'Payment link creation skipped for order #%s', 'woo-pensopay' ), $order->get_id() ), 'error' );

			return;
		}

		try {

			$order->set_payment_method( WC_PP()->id );
			$order->set_payment_method_title( WC_PP()->get_method_title() );

			$transaction_id = WC_PensoPay_Order_Utils::get_transaction_id( $order );

			if ( $is_subscription ) {
				$resource = new WC_PensoPay_API_Subscription();

				if ( ! $order_parent_id = $resource_order->get_parent_id() ) {
					throw new PensoPay_Exception( __( 'A parent order must be mapped to the subscription.', 'woo-pensopay' ) );
				}
				$resource_order = wc_get_order( $order_parent_id );

				// Set the appropriate payment method id and title on the parent order as well
				$resource_order->set_payment_method( WC_PP()->id );
				$resource_order->set_payment_method_title( WC_PP()->get_method_title() );
				$resource_order->save();

				if ( $transaction_id ) {
					$check_subscription_transaction = new WC_PensoPay_API_Subscription();
					$check_subscription_transaction->get( $transaction_id );

					if ( $check_subscription_transaction->get_state() !== 'initial' ) {
						$transaction_id = null;
					}
				}
			} else {
				// In case of a renewal order, and if card update option is enabled, we will consider this as a payment method change request.
				// This will create a subscription transaction and authorize a new recurring payment for the renewal order on the newly created transaction.
				// On callback: The subscription transaction ID will be stored on the subscription in WC and the recurring payment will be created and the transaction id
				// will be stored on the renewal order.
				/** @noinspection NotOptimalIfConditionsInspection */
				if ( $is_card_update_enabled && ( $is_renewal_order = WC_PensoPay_Subscription::is_renewal( $order ) ) ) {
					WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment = true;
					$is_change_payment_request_flag_modified                               = true;
				}

				$resource = woocommerce_pensopay_get_transaction_instance_by_order( $resource_order );
			}

			if ( ! $transaction_id ) {
				// Append string to the order number to ensure that errors about "duplicate order numbers" are returned from the API.
				add_filter( 'woocommerce_pensopay_order_number_for_api', [ $this, 'make_api_order_number_unique' ], 5 );
				$transaction = $resource->create( $resource_order );

				// Remove filter from above.
				remove_filter( 'woocommerce_pensopay_order_number_for_api', [ $this, 'make_api_order_number_unique' ], 5 );

				$transaction_id = $transaction->id;
				$order->set_transaction_id( $transaction_id );
			}

			$link = $resource->patch_link( $transaction_id, $resource_order );

			// Reset change payment request flag
			if ( $is_renewal_order && $is_card_update_enabled && $is_change_payment_request_flag_modified ) {
				WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment = false;
			}

			// Check URL
			if ( ! WC_PensoPay_Helper::is_url( $link->url ) ) {
				throw new Exception( sprintf( __( 'Invalid payment link received from API for order #%s', 'woo-pensopay' ), $order->get_id() ) );
			}


			WC_PensoPay_Order_Payments_Utils::set_payment_link( $order, $link->url );

			// Late save for subscriptions. This is only to make sure that manual renewal is not set to true if an error occurs during the link creation.
			if ( $is_subscription ) {
				$subscription = wcs_get_subscription( $order->get_id() );
				$subscription->set_requires_manual_renewal( false );
				$subscription->save();
			}

			// Make sure to save the changes to the order/subscription object
			$order->save();
			$order->add_order_note( sprintf( __( 'Payment link manually created from backend: %s', 'woo-pensopay' ), $link->url ), false, true );

			do_action( 'woocommerce_pensopay_order_action_payment_link_created', $link->url, $order );

			return true;
		} catch ( \Exception $e ) {
			woocommerce_pensopay_add_admin_notice( sprintf( __( 'Payment link could not be created for order #%s. Error: %s', 'woo-pensopay' ), $order->get_id(), $e->getMessage() ), 'error' );

			return false;
		}
	}

	/**
	 * Filter to append a random string to the order number sent to the API.
	 *
	 * @param $api_order_number
	 *
	 * @return string
	 */
	public function make_api_order_number_unique( $api_order_number ): string {
		if ( ! preg_match( '/-.{3,}$/', $api_order_number ) ) {
			$api_order_number .= '-' . WC_PensoPay_Helper::create_random_string( 3 );
		}

		return $api_order_number;
	}

	/**
	 * Adds custom actions
	 *
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function admin_order_actions( $actions ) {
		$actions['pensopay_create_payment_link'] = __( 'Create payment link', 'woo-pensopay' );

		return $actions;
	}
}
