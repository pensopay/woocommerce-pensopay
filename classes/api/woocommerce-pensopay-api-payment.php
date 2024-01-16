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
		$this->api_url .= 'payments/';
	}

	/**
	 * Capture function.
	 *
	 * Sends a 'capture' request to the Pensopay API
	 *
	 * @access public
	 *
	 * @param mixed $transaction_id
	 * @param WC_Order $order
	 * @param float $amount
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 * @throws PensoPay_Exception
     * @throws PensoPay_Capture_Exception
	 */
	public function capture( $transaction_id, WC_Order $order, $amount = null ) {
		// Check if a custom amount ha been set
		if ( $amount === null ) {
			// No custom amount set. Default to the order total
			$amount = $order->get_total();
		}

		$request = $this->post( sprintf( '%d/%s', $transaction_id, "capture" ), [ 'amount' => WC_PensoPay_Helper::price_multiply( $amount, $order->get_currency() ) ], true );

        $this->check_last_operation_of_type_with_location_fallback( 'capture', $order, $request );

        return $this;
	}


    /**
     * @param string $action action|refund
     * @param array $request
     * @param WC_Order $order
     *
     * @throws PensoPay_API_Exception
     * @throws PensoPay_Capture_Exception
     * @throws PensoPay_Exception
     */
	public function check_last_operation_of_type_with_location_fallback( string $action, $order, $request ) {
        $follow_location = isset( $request[5]['location'] ) && ! empty( $request[5]['location'] );

        try {
            $_action = $this->get_last_operation_of_type( $action );
        } catch ( PensoPay_Exception $e ) {
            $_action = null;
        }

        if ( $follow_location && ! $_action ) {
            $api     = new WC_PensoPay_API( WC_PP()->s( 'pensopay_apikey' ) );
            $_action = $api->get( $request[5]['location'][0] );

            if ( empty( $_action ) ) {
                throw new PensoPay_Exception( sprintf( '%s inconclusive. Response from location header is empty.', ucfirst( $action ) ) );
            }
        }

        if ( ! $follow_location && ! $_action ) {
            throw new PensoPay_Exception( sprintf( 'No %s operation or location found: %s', $action, json_encode( $this->resource_data ) ) );
        }

        if ( $_action->qp_status_code > 20200 ) {
            throw new PensoPay_Capture_Exception( sprintf( '%s payment on order #%s failed. Message: %s', ucfirst( $action ), $order->get_id(), $_action->qp_status_msg ) );
        }
    }


    /**
     * Cancel function.
     *
     * Sends a 'cancel' request to the Pensopay API
     *
     * @access public
     *
     * @param int $transaction_id
     *
     * @return void
     * @throws PensoPay_API_Exception
     * @throws PensoPay_Exception
     */
	public function cancel( $transaction_id ): void {
		$this->post( sprintf( '%d/%s', $transaction_id, "cancel" ) );
	}


	/**
	 * Refund function.
	 *
	 * Sends a 'refund' request to the Pensopay API
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
	public function refund( int $transaction_id, WC_Order $order, ?float $amount = null ): void {
		// Check if a custom amount ha been set
		if ( $amount === null ) {
			// No custom amount set. Default to the order total
			$amount = $order->get_total();
		}

		// Get all basket items
		$basket_items = WC_PensoPay_Order_Transaction_Data_Utils::get_basket_params( $order );

		// Select the first item as this should be an actual product and not shipping or similar.
		$product = reset( $basket_items );

		$request = $this->post( sprintf( '%d/%s', $transaction_id, "refund" ), [
			'amount'   => WC_PensoPay_Helper::price_multiply( $amount, $order->get_currency() ),
			'vat_rate' => $product['vat_rate'],
		], true );

		$this->check_last_operation_of_type_with_location_fallback( 'refund', $order, $request );
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
	public function is_action_allowed( $action ): bool {
		try {
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

			// MP Subscription payments cannot be manually captured as they are automatically captured on the due date.
			if ( $action === 'capture' && 'mobilepaysubscriptions' === $this->get_acquirer() ) {
				return false;
			}

			// We want to still allow captures if there is a remaining balance.
			if ( 'capture' === $state && $remaining_balance > 0 && $action !== 'cancel' ) {
				return true;
			}

			return in_array( $state, $allowed_states[ $action ] );
		} catch ( Exception $e ) {
			return false;
		}
	}
}
