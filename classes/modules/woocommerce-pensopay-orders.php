<?php

/**
 * Class WC_PensoPay_Orders
 */
class WC_PensoPay_Orders extends WC_PensoPay_Module {

	public function hooks() {
		// Reset failed payment count
		add_action( 'woocommerce_order_status_completed', [ $this, 'reset_failed_payment_count' ], 10 );
		add_action( 'woocommerce_order_status_processing', [ $this, 'reset_failed_payment_count' ], 10 );
		add_action( 'woocommerce_order_status_cancelled', [ $this, 'maybe_cancel_transaction' ], 10, 2 );

		add_action( 'woocommerce_pensopay_callback_payment_authorized', [ $this, 'on_payment_authorized' ], 10 );
	}

	/**
	 * @param $order_id
	 * @param $order
	 */
	public function maybe_cancel_transaction( $order_id, $order ): void {
		if ( $order && WC_PensoPay_Helper::option_is_enabled( WC_PP()->s( 'pensopay_cancel_transaction_on_cancel' ) ) ) {
			$order = new WC_PensoPay_Order( $order_id );
			if ( $transaction_id = $order->get_transaction_id() ) {
				$transaction = woocommerce_pensopay_get_transaction_instance_by_order( $order );
				try {
					$transaction->get( $transaction_id );
					if ( $transaction->is_action_allowed( 'cancel' ) ) {
						$transaction->cancel( $transaction_id );
						$order->add_order_note( __( 'PensoPay: Payment cancelled due to order cancellation', 'woo-pensopay' ) );
					}
				} catch ( Exception $e ) {
					WC_PP()->log->add( 'Event: Order cancelled -> Error occured when cancelling transaction: ' . $e->getMessage() );
				}
			}
		}
	}

	/**
	 * When the order status changes to either processing or completed, we will reset the failed payment count (if any).
	 *
	 * @param $order_id
	 */
	public function reset_failed_payment_count( $order_id ): void {
		if ( $order = woocommerce_pensopay_get_order( $order_id ) ) {
			WC_PensoPay_Order_Payments_Utils::reset_failed_payment_count( $order );
		}
	}

	/**
	 * @param WC_PensoPay_Order $order
	 */
	public function on_payment_authorized( $order ): void {
		$is_mp_subscription          = $order->get_payment_method() === WC_PensoPay_MobilePay_Subscriptions::instance_id;
		$autocomplete_renewal_orders = WC_PensoPay_Helper::option_is_enabled( WC_PP()->s( 'subscription_autocomplete_renewal_orders' ) );

		if ( ! $is_mp_subscription && $autocomplete_renewal_orders && WC_PensoPay_Subscription::is_renewal( $order ) ) {
			$order->update_status( 'completed', __( 'Automatically completing order status due to successful recurring payment', 'woo-pensopay' ), false );
		}
	}
}
