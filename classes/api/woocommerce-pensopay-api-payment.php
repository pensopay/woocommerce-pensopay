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
	 * @param int $transaction_id
	 * @param \WC_Order $order
	 * @param int $amount
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 * @throws PensoPay_Exception
     * @throws PensoPay_Capture_Exception
	 */
	public function capture( $transaction_id, $order, $amount = null ) {
		// Check if a custom amount ha been set
		if ( $amount === null ) {
			// No custom amount set. Default to the order total
			$amount = $order->get_total();
		}

		$this->post( sprintf( '%d/%s', $transaction_id, "capture" ), [ 'amount' => WC_PensoPay_Helper::price_multiply( $amount ) ] );

		if ( ! $capture = $this->get_last_operation_of_type( 'capture' ) ) {
			throw new PensoPay_Exception( 'No capture operation found: ' . (string) json_encode( $this->resource_data ) );
		}

		if ( $capture->qp_status_code > 20200 ) {
			throw new PensoPay_Capture_Exception( sprintf( 'Capturing payment on order #%s failed. Message: %s', $order->get_id(), $capture->qp_status_msg ) );
		}

		return $this;
	}


    /**
     * cancel function.
     *
     * Sends a 'cancel' request to the PensoPay API
     *
     * @access public
     *
     * @param int $transaction_id
     *
     * @return void
     * @throws PensoPay_API_Exception
     * @throws PensoPay_Exception
     */
	public function cancel( $transaction_id ) {
		$this->post( sprintf( '%d/%s?synchronized', $transaction_id, 'cancel') );

        if ( ! $cancellation = $this->get_last_operation_of_type( 'cancel' ) ) {
            throw new PensoPay_Exception( 'No cancellation operation found: ' . (string) json_encode( $this->resource_data ) );
        }

        if ( $cancellation->qp_status_code > 20200 ) {
            $msg = sprintf( 'Cancellation of payment for transaction #%s failed. Message: %s', $transaction_id, $cancellation->qp_status_msg );
            throw new PensoPay_API_Exception( $msg );
        }
	}


	/**
	 * refund function.
	 *
	 * Sends a 'refund' request to the PensoPay API
	 *
	 * @access public
	 *
	 * @param int $transaction_id
	 * @param \WC_Order $order
	 * @param int $amount
	 *
	 * @return void
	 * @throws PensoPay_API_Exception
	 * @throws PensoPay_Exception
	 */
	public function refund( $transaction_id, $order, $amount = null ) {
		// Check if a custom amount ha been set
		if ( $amount === null ) {
			// No custom amount set. Default to the order total
			$amount = $order->get_total();
		}

		if ( ! $order instanceof WC_PensoPay_Order) {
			$order = new WC_PensoPay_Order($order->get_id());
		}

		// Get all basket items
		$basket_items = $order->get_transaction_basket_params();

		// Select the first item as this should be an actual product and not shipping or similar.
		$product = reset( $basket_items );

		$this->post( sprintf( '%d/%s?synchronized', $transaction_id, "refund" ), [
			'amount'   => WC_PensoPay_Helper::price_multiply( $amount ),
			'vat_rate' => $product['vat_rate'],
		] );

		if ( ! $refund = $this->get_last_operation_of_type( 'refund' ) ) {
			throw new PensoPay_Exception( 'No refund operation found: ' . (string) json_encode( $this->resource_data ) );
		}

		if ( $refund->qp_status_code > 20200 ) {
		    $msg = sprintf( 'Refunding payment on order #%s failed. Message: %s', $order->get_id(), $refund->qp_status_msg );
		    $order->add_order_note($msg);
			throw new PensoPay_API_Exception( $msg );
		}
	}


	/**
	 * is_action_allowed function.
	 *
	 * Check if the action we are about to perform is allowed according to the current transaction state.
	 *
	 * @access public
	 * @return boolean
	 * @throws PensoPay_API_Exception
	 */
	public function is_action_allowed( $action ) {
		$state             = $this->get_current_type();
		$remaining_balance = $this->get_remaining_balance();


		$allowed_states = [
			'capture'          => [ 'authorize', 'recurring' ],
			'cancel'           => [ 'authorize', 'recurring' ],
			'refund'           => [ 'capture', 'refund' ],
			'renew'            => [ 'authorize' ],
			'splitcapture'     => [ 'authorize', 'capture' ],
			'recurring'        => [ 'subscribe' ],
			'standard_actions' => [ 'authorize', 'recurring' ],
		];

		// We wants to still allow captures if there is a remaining balance.
		if ( 'capture' === $state && $remaining_balance > 0 && $action !== 'cancel' ) {
			return true;
		}

		return in_array( $state, $allowed_states[ $action ] );
	}
}