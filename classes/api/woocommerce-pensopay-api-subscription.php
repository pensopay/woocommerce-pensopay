<?php
/**
 * WC_PensoPay_API_Subscription class
 *
 * @class 		WC_PensoPay_API_Subscription
 * @since		4.0.0
 * @package		Woocommerce_PensoPay/Classes
 * @category	Class
 * @author 		PensoPay
 * @docs        http://tech.quickpay.net/api/services/?scope=merchant
 */

class WC_PensoPay_API_Subscription extends WC_PensoPay_API_Transaction {
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $resource_data = null ) {
		// Run the parent construct
		parent::__construct();

		// Set the resource data to an object passed in on object instantiation.
		// Usually done when we want to perform actions on an object returned from
		// the API sent to the plugin callback handler.
		if ( is_object( $resource_data ) ) {
			$this->resource_data = $resource_data;
		}

		// Append the main API url
		$this->api_url .= 'subscriptions/';
	}

	/**
	 * Sends a 'recurring' request to the PensoPay API
	 *
	 * @param $subscription_id
	 * @param WC_Order $order
	 * @param null $amount
	 *
	 * @return array|object $request
	 * @throws PensoPay_API_Exception
	 */
	public function recurring( $subscription_id, WC_Order $order, $amount = null ) {
		// Check if a custom amount ha been set
		if ( $amount === null ) {
			// No custom amount set. Default to the order total
			$amount = WC_Subscriptions_Order::get_recurring_total( $order );
		}

		$order_number = WC_PensoPay_Order_Payments_Utils::get_order_number_for_api( $order, true );

		$request_url = sprintf( '%d/%s', $subscription_id, "recurring" );

		$request_data = apply_filters( 'woocommerce_pensopay_create_recurring_payment_data', [
			'amount'            => WC_PensoPay_Helper::price_multiply( $amount, $order->get_currency() ),
			'order_id'          => $order_number,
			'auto_capture'      => WC_PensoPay_Order_Transaction_Data_Utils::should_auto_capture_order( $order ),
			'autofee'           => WC_PensoPay_Helper::option_is_enabled( WC_PP()->s( 'pensopay_autofee' ) ),
			'text_on_statement' => WC_PP()->s( 'pensopay_text_on_statement' ),
			'order_post_id'     => $order->get_id(),
		], $order, $subscription_id );

		$request_data = apply_filters( 'woocommerce_pensopay_create_recurring_payment_data_' . strtolower( $order->get_payment_method() ), $request_data, $order, $subscription_id );

		return $this->post( $request_url, $request_data, true );
	}


	/**
	 * cancel function.
	 *
	 * Sends a 'cancel' request to the PensoPay API
	 *
	 * @access public
	 *
	 * @param int $subscription_id
	 *
	 * @return void
	 * @throws PensoPay_API_Exception
	 */
	public function cancel( int $subscription_id ): void {
		$this->post( sprintf( '%d/%s', $subscription_id, 'cancel' ) );
	}


	/**
	 * is_action_allowed function.
	 *
	 * Check if the action we are about to perform is allowed according to the current transaction state.
	 *
	 * @access public
	 *
	 * @param $action
	 *
	 * @return boolean
	 * @throws PensoPay_API_Exception
	 */
	public function is_action_allowed( $action ): bool {
		try {
			$state = $this->get_current_type();

			$allowed_states = [
				'cancel'           => [ 'authorize' ],
				'standard_actions' => [ 'authorize' ]
			];

			return array_key_exists( $action, $allowed_states ) and in_array( $state, $allowed_states[ $action ] );
		} catch ( Exception $e ) {
			return false;
		}

	}
}
