<?php
/**
 * WC_PensoPay_API_Payment class
 *
 * @class          WC_PensoPay_API_Payment
 * @since          4.0.0
 * @package        Woocommerce_PensoPay/Classes
 * @category       Class
 * @author         PensoPay
 * @docs        http://tech.quickpay.net/api/services/?scope=merchant
 */

class WC_PensoPay_API_Payment extends WC_PensoPay_API_Transaction {

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
		$this->api_url = $this->api_url . 'payments/';
	}


	/**
	 * create function.
	 *
	 * Creates a new payment via the API
	 *
	 * @access public
	 *
	 * @param  WC_PensoPay_Order $order
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 */
	public function create( WC_PensoPay_Order $order ) {
		return parent::create( $order );
	}


	/**
	 * capture function.
	 *
	 * Sends a 'capture' request to the PensoPay API
	 *
	 * @access public
	 *
	 * @param  int $transaction_id
	 * @param  int $amount
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 */
	public function capture( $transaction_id, $order, $amount = null ) {
		// Check if a custom amount ha been set
		if ( $amount === null ) {
			// No custom amount set. Default to the order total
			$amount = $order->get_total();
		}

		return $this->post( sprintf( '%d/%s', $transaction_id, "capture" ), array( 'amount' => WC_PensoPay_Helper::price_multiply( $amount ) ) );
	}


	/**
	 * cancel function.
	 *
	 * Sends a 'cancel' request to the PensoPay API
	 *
	 * @access public
	 *
	 * @param  int $transaction_id
	 *
	 * @return void
	 * @throws PensoPay_API_Exception
	 */
	public function cancel( $transaction_id ) {
		$this->post( sprintf( '%d/%s', $transaction_id, "cancel" ) );
	}


	/**
	 * refund function.
	 *
	 * Sends a 'refund' request to the PensoPay API
	 *
	 * @access public
	 *
	 * @param  int       $transaction_id
	 * @param  \WC_Order $order
	 * @param  int       $amount
	 *
	 * @return void
	 * @throws PensoPay_API_Exception
	 */
	public function refund( $transaction_id, $order, $amount = null ) {
		// Check if a custom amount ha been set
		if ( $amount === null ) {
			// No custom amount set. Default to the order total
			$amount = $order->get_total();
		}

		if (! $order instanceof WC_PensoPay_Order) {
			$order = new WC_PensoPay_Order($order->get_id());
		}

		// Get all basket items
		$basket_items = $order->get_transaction_basket_params();

		// Select the first item as this should be an actual product and not shipping or similar.
		$product = reset( $basket_items );

		$this->post( sprintf( '%d/%s', $transaction_id, "refund" ), array(
			'amount'   => WC_PensoPay_Helper::price_multiply( $amount ),
			'vat_rate' => $product['vat_rate'],
		) );
	}


	/**
	 * is_action_allowed function.
	 *
	 * Check if the action we are about to perform is allowed according to the current transaction state.
	 *
	 * @access public
	 * @return boolean
	 */
	public function is_action_allowed( $action ) {
		$state             = $this->get_current_type();
		$remaining_balance = $this->get_remaining_balance();

		$allowed_states = array(
			'capture'          => array( 'authorize', 'recurring' ),
			'cancel'           => array( 'authorize' ),
			'refund'           => array( 'capture', 'refund' ),
			'renew'            => array( 'authorize' ),
			'splitcapture'     => array( 'authorize', 'capture' ),
			'recurring'        => array( 'subscribe' ),
			'standard_actions' => array( 'authorize', 'recurring' ),
		);

		// We wants to still allow captures if there is a remaining balance.
		if ( 'capture' == $state && $remaining_balance > 0 ) {
			return true;
		}

		return in_array( $state, $allowed_states[ $action ] );
	}
}