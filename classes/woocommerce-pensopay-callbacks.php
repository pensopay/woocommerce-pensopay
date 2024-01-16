<?php

/**
 * Class WC_PensoPay_Callbacks
 */
class WC_PensoPay_Callbacks {

	/**
	 * Regular payment logic for authorized transactions
	 *
	 * @param WC_Order $order
	 * @param stdClass $transaction
	 */
	public static function payment_authorized( $order, $transaction ): void {
		// Add order transaction fee if available
		if ( ! empty( $transaction->fee ) ) {
			WC_PensoPay_Order_Payments_Utils::add_order_item_transaction_fee( $order, (int) $transaction->fee );
		}

		// Check for pre-order
		if ( WC_PensoPay_Helper::has_preorder_plugin() && WC_Pre_Orders_Order::order_contains_pre_order( $order ) && WC_Pre_Orders_Order::order_requires_payment_tokenization( $order->get_id() ) ) {
			try {
				// Set transaction ID without marking the payment as complete
				$order->set_transaction_id( $transaction->id );
			} catch ( WC_Data_Exception $e ) {
				WC_PP()->log->add( __( 'An error occured while setting transaction id: %d on order %s. %s', $transaction->id, $order->get_id(), $e->getMessage() ) );
			}
			WC_Pre_Orders_Order::mark_order_as_pre_ordered( $order );
		}  /**
         * Regular product
         * -> Mark the payment as complete if the payment is not a scheduled payment from MobilePay Subscriptions. Scheduled payments can still fail even when authorized,
         * so we should wait marking the payment as complete until the capture
         */
        else if ( apply_filters( 'woocommerce_pensopay_callback_payment_authorized_complete_payment', $order->get_payment_method() !== WC_PensoPay_MobilePay_Subscriptions::instance_id, $order, $transaction ) ) {
            // Register the payment on the order
            $order->payment_complete( $transaction->id );
        }

		// Write a note to the order history
		WC_PensoPay_Order_Utils::add_note( $order, sprintf( __( 'Payment authorized. Transaction ID: %s', 'woo-pensopay' ), $transaction->id ) );

		// Fallback to save transaction IDs since this has seemed to sometimes fail when using WC_Order::payment_complete
		self::save_transaction_id_fallback( $order, $transaction );

		do_action( 'woocommerce_pensopay_callback_payment_authorized', $order, $transaction );
	}

	/**
	 * Triggered when a capture callback is received
	 *
	 * @param WC_Order $order
	 * @param stdClass $transaction
	 */
	public static function payment_captured( WC_Order $order, $transaction ) {
		$capture_note = __( 'Payment captured.', 'woo-pensopay' );

        $complete = WC_PensoPay_Helper::option_is_enabled( WC_PP()->s( 'pensopay_complete_on_capture' ) ) && ! $order->has_status( 'completed' );

        if ( apply_filters( 'woocommerce_pensopay_complete_order_on_capture', $complete, $order, $transaction ) ) {
            $order->update_status( 'completed', $capture_note );
        } else {
            $order->add_order_note( $capture_note );
        }

        do_action( 'woocommerce_pensopay_callback_payment_captured', $order, $transaction );
    }

	/**
	 * @param WC_Subscription $subscription
	 * @param WC_Order $related_order can be parent or renewal order
	 * @param stdClass $transaction
	 */
	public static function subscription_authorized( $subscription, WC_Order $related_order, $transaction ): void {
		WC_PensoPay_Order_Utils::add_note( $subscription, sprintf( __( 'Subscription authorized. Transaction ID: %s', 'woo-pensopay' ), $transaction->id ) );
		// Activate the subscription

		// Mark the payment as complete
		// Temporarily save the transaction ID on a custom meta row to avoid empty values in 3.0.
		self::save_transaction_id_fallback( $subscription, $transaction );

		WC_PensoPay_Order_Payments_Utils::set_transaction_order_id( $subscription, $transaction->order_id );

		// Only make an instant payment if the order total is more than 0
		if ( $related_order->get_total() > 0 ) {
			// Check if this is an order containing a subscription or if it is a renewal order
			if ( ! WC_PensoPay_Subscription::is_subscription( $related_order ) && ( WC_PensoPay_Order_Utils::contains_subscription( $related_order ) || WC_PensoPay_Subscription::is_renewal( $related_order ) ) ) {
				// Process a recurring payment, but only if the subscription needs a payment.
				// This check was introduced to avoid possible double payments in case PensoPay sends callbacks more than once.
				if ( ( $wcs_subscription = wcs_get_subscription( $subscription->get_id() ) ) && $wcs_subscription->needs_payment() ) {
					WC_PP()->process_recurring_payment( new WC_PensoPay_API_Subscription(), $transaction->id, $related_order->get_total(), $related_order );
				}
			}
		}
		// If there is no initial payment, we will mark the order as complete.
		// This is usually happening if a subscription has a free trial.
		else {
			// Only complete the order payment if we are not changing payment method.
			// This is to avoid the subscription going into a 'processing' limbo.
			if ( empty( $transaction->variables->change_payment ) ) {
				$related_order->payment_complete();
			}
		}

		do_action( 'woocommerce_pensopay_callback_subscription_authorized', $subscription, $related_order, $transaction );
	}

	/**
	 * Common logic for authorized payments/subscriptions
	 *
	 * @param WC_Order $order
	 * @param stdClass $transaction
	 */
	public static function authorized( WC_Order $order, $transaction ): void {
		// Set the transaction order ID
		WC_PensoPay_Order_Payments_Utils::set_transaction_order_id( $order, $transaction->order_id );

		// Remove payment link
		WC_PensoPay_Order_Payments_Utils::delete_payment_link( $order );

		// Remove payment ID, now we have the transaction ID
		WC_PensoPay_Order_Payments_Utils::delete_payment_id( $order );
	}

	/**
	 * @param WC_Order $order
	 * @param stdClass $transaction
	 */
	public static function save_transaction_id_fallback( WC_Order $order, $transaction ): void {
		try {
			if ( ! empty( $transaction->id ) ) {
				$order->set_transaction_id( $transaction->id );
                $order->update_meta_data( '_pensopay_transaction_id', $transaction->id );
                $order->save_meta_data();
				$order->save();
			}
		} catch ( WC_Data_Exception $e ) {
			wc_get_logger()->error( $e->getMessage() );
		}
	}

	/**
	 * Returns the order ID based on the ID retrieved from the QuickPay callback.
	 *
	 * @param object $callback_data - the callback data
	 *
	 * @return int
	 */
	public static function get_order_id_from_callback( $callback_data ): int {
		// Check for the post ID reference on the response object.
		// This should be available on all new orders.
		if ( ! empty( $callback_data->variables ) && ! empty( $callback_data->variables->order_post_id ) ) {
			return (int) $callback_data->variables->order_post_id;
		}

		if ( isset( $_GET['order_post_id'] ) ) {
			return (int) trim( $_GET['order_post_id'] );
		}

		// Fallback
		preg_match( '/\d{4,}/', $callback_data->order_id, $order_number );

		return (int) end( $order_number );
	}

	/**
	 * Returns the subscription ID based on the ID retrieved from the QuickPay callback, if present.
	 *
	 * @param object $callback_data - the callback data
	 *
	 * @return int
	 */
	public static function get_subscription_id_from_callback( $callback_data ): ?int {
		// Check for the post ID reference on the response object.
		// This should be available on all new orders.
		if ( ! empty( $callback_data->variables ) && ! empty( $callback_data->variables->subscription_post_id ) ) {
			return (int) $callback_data->variables->subscription_post_id;
		}

		if ( isset( $_GET['subscription_post_id'] ) ) {
			return (int) trim( $_GET['subscription_post_id'] );
		}

		return null;
	}
}
