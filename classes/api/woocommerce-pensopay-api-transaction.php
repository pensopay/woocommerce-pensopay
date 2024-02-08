<?php
/**
 * WC_PensoPay_API_Transaction class
 *
 * Used for common methods shared between payments and subscriptions
 *
 * @class          WC_PensoPay_API_Payment
 * @since          4.0.0
 * @package        Woocommerce_PensoPay/Classes
 * @category       Class
 * @author         PensoPay
 * @docs        http://tech.quickpay.net/api/services/?scope=merchant
 */

class WC_PensoPay_API_Transaction extends WC_PensoPay_API {

	/**
	 * @var bool
	 */
	protected bool $loaded_from_cache = false;

	/**
	 * get_current_type function.
	 *
	 * Returns the current payment type
	 *
	 * @access public
	 * @return string
	 * @throws PensoPay_API_Exception
	 */
	public function get_current_type(): string {
		$last_operation = $this->get_last_operation();

		if ( ! is_object( $last_operation ) ) {
			throw new PensoPay_API_Exception( "Malformed operation response", 0 );
		}

		return $last_operation->type;
	}

	/**
	 * get_current_type function.
	 *
	 * Returns the current payment type
	 *
	 * @access public
	 * @return string
	 * @throws PensoPay_API_Exception
	 */
	public function is_accepted(): string {
		if ( ! is_object( $this->resource_data ) ) {
			throw new PensoPay_API_Exception( 'No API payment resource data available.', 0 );
		}

		return $this->resource_data->accepted;
	}

	/**
	 * get_last_operation function.
	 *
	 * Returns the last successful transaction operation
	 *
	 * @access public
	 * @return stdClass
	 * @throws PensoPay_API_Exception
	 */
	public function get_last_operation() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new PensoPay_API_Exception( 'No API payment resource data available.', 0 );
		}

		// Loop through all the operations and return only the operations that were successful (based on the qp_status_code and pending mode).
		$successful_operations = array_filter( $this->resource_data->operations, static function ( $operation ) {
			return (int) $operation->qp_status_code === 20000 || wc_string_to_bool( $operation->pending );
		} );

		$last_operation = end( $successful_operations );

		if ( ! is_object( $last_operation ) ) {
			throw new PensoPay_API_Exception( 'Malformed operation object' );
		}

		if ( wc_string_to_bool( $last_operation->pending ) ) {
			$last_operation->type = __( 'Pending - check your PensoPay manager', 'woo-pensopay' );
		}

		return $last_operation;
	}

	/**
	 * @param $type
	 *
	 * @return mixed|null
	 * @throws PensoPay_API_Exception
	 */
	public function get_last_operation_of_type( $type ) {
		if ( ! is_object( $this->resource_data ) ) {
			throw new PensoPay_API_Exception( 'No API payment resource data available.', 0 );
		}

		$operations = array_reverse( $this->resource_data->operations );

		foreach ( $operations as $operation ) {
			if ( $operation->type === $type ) {
				return $operation;
			}
		}

		return null;
	}

	/**
	 * is_test function.
	 *
	 * Tests if a payment was made in test mode.
	 *
	 * @access public
	 * @return boolean
	 * @throws PensoPay_API_Exception
	 */
	public function is_test(): bool {
		if ( ! is_object( $this->resource_data ) ) {
			throw new PensoPay_API_Exception( 'No API payment resource data available.', 0 );
		}

		return (bool) $this->resource_data->test_mode;
	}

	/**
	 * create function.
	 *
	 * Creates a new payment via the API
	 *
	 * @access public
	 *
	 * @param WC_PensoPay_Order $order
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 */
	public function create( WC_Order $order ) {
		$base_params = [
			'currency'      => $order->get_currency(),
			'order_post_id' => $order->get_id(),
		];

		$text_on_statement = WC_PP()->s( 'pensopay_text_on_statement' );
		if ( ! empty( $text_on_statement ) ) {
			$base_params['text_on_statement'] = $text_on_statement;
		}

		$order_params = WC_PensoPay_Order_Payments_Utils::prepare_transaction_params( $order );

		$params = array_merge( $base_params, $order_params );

		return $this->post( '/', $params );
	}

	/**
	 * create_link function.
	 *
	 * Creates or updates a payment link via the API
	 *
	 * @param mixed $transaction_id
	 * @param WC_Order $order
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 * @since  4.5.0
	 * @access public
	 *
	 */
	public function patch_link( $transaction_id, WC_Order $order ) {
		$payment_method = strtolower( $order->get_payment_method() );

		$base_params = [
			'language'                     => woocommerce_pensopay_get_language(),
			'currency'                     => $order->get_currency(),
			'callbackurl'                  => WC_PensoPay_Helper::get_callback_url(),
			'auto_capture'                 => WC_PensoPay_Order_Transaction_Data_Utils::should_auto_capture_order( $order ),
			'autofee'                      => WC_PensoPay_Helper::option_is_enabled( WC_PP()->s( 'pensopay_autofee' ) ),
			'payment_methods'              => apply_filters( 'woocommerce_pensopay_cardtypelock_' . $payment_method, WC_PP()->s( 'pensopay_cardtypelock' ), $payment_method ),
			'branding_id'                  => WC_PP()->s( 'pensopay_branding_id' ),
			'google_analytics_tracking_id' => WC_PP()->s( 'pensopay_google_analytics_tracking_id' ),
			'customer_email'               => $order->get_billing_email(),
		];

		$order_params = WC_PensoPay_Order_Payments_Utils::prepare_transaction_link_params( $order );

		return $this->put(
			sprintf( '%d/link', $transaction_id ),
			apply_filters( 'woocommerce_pensopay_transaction_link_params', array_merge( $base_params, $order_params ), $order, $payment_method )
		);
	}

	/**
	 * @param $transaction_id
	 * @param WC_Order $order
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 */
	public function patch_payment( $transaction_id, WC_Order $order ) {
		$base_params = [
			'currency'      => $order->get_currency(),
			'order_post_id' => $order->get_id(),
		];

		$text_on_statement = WC_PP()->s( 'pensopay_text_on_statement' );

		if ( ! empty( $text_on_statement ) ) {
			$base_params['text_on_statement'] = $text_on_statement;
		}

		$order_params = WC_PensoPay_Order_Payments_Utils::prepare_transaction_params( $order );

		return $this->patch( sprintf( '/%s', $transaction_id ), array_merge( $base_params, $order_params ) );
	}

	/**
	 * Returns the payment type / card type used on the transaction
	 *
	 * @return mixed
	 * @throws PensoPay_API_Exception
	 * @since  4.5.0
	 */
	public function get_brand() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new PensoPay_API_Exception( 'No API payment resource data available.', 0 );
		}

		if ( ! empty( $this->resource_data->metadata->brand ) ) {
			return $this->resource_data->metadata->brand;
		}

		if ( ! empty( $this->resource_data->variables->payment_method ) ) {
			return str_replace( 'pensopay_', '', $this->resource_data->variables->payment_method );
		}

		if ( ! empty( $this->resource_data->link->payment_methods ) ) {
			return $this->resource_data->link->payment_methods;
		}
	}

	/**
	 * Returns the transaction balance
	 *
	 * @return mixed
	 * @throws PensoPay_API_Exception
	 * @since  4.5.0
	 */
	public function get_balance() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new PensoPay_API_Exception( 'No API payment resource data available.', 0 );
		}

		return ! empty( $this->resource_data->balance ) ? $this->resource_data->balance : null;
	}

	/**
	 * get_currency function
	 *
	 * Returns a transaction currency
	 *
	 * @return mixed
	 * @throws PensoPay_API_Exception
	 * @since  4.5.0
	 */
	public function get_currency() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new PensoPay_API_Exception( 'No API payment resource data available.', 0 );
		}

		return $this->resource_data->currency;
	}

	/**
	 * get_formatted_remaining_balance function
	 *
	 * Returns a formatted transaction balance
	 *
	 * @return mixed
	 * @throws PensoPay_API_Exception
	 * @since  4.5.0
	 */
	public function get_formatted_remaining_balance() {
		return WC_PensoPay_Helper::price_normalize( $this->get_remaining_balance(), $this->get_currency() );
	}

    /**
     * @return float|int|mixed|null
     * @throws PensoPay_API_Exception
     */
    public function get_remaining_balance_as_float() {
        $remaining_balance = $this->get_remaining_balance();

        if ( $remaining_balance > 0 && WC_PensoPay_Helper::is_currency_using_decimals( $this->get_currency() ) ) {
            return $remaining_balance / 100;
        }

        return $remaining_balance;
    }

	/**
	 * get_remaining_balance function
	 *
	 * Returns a remaining balance
	 *
	 * @return mixed
	 * @throws PensoPay_API_Exception
	 * @since  4.5.0
	 */
	public function get_remaining_balance() {
		$balance = $this->get_balance();

		$authorized_operations = array_filter( $this->resource_data->operations, static function ( $operation ) {
			return 'authorize' === $operation->type || 'recurring' === $operation->type;
		} );

		if ( empty( $authorized_operations ) ) {
			return null;
		}

		$operation = reset( $authorized_operations );

		$amount = $operation->amount;

		$remaining = $amount;

		if ( $balance > 0 ) {
			$remaining = $amount - $balance;
		}

		return $remaining;
	}

	/**
	 * @return string|null
	 */
	public function get_acquirer(): ?string {
		if ( is_object( $this->resource_data ) && isset( $this->resource_data->acquirer ) ) {
			return $this->resource_data->acquirer;
		}

		return null;
	}

	/**
	 * Returns the metadata of a transaction
	 *
	 * @return mixed
	 * @throws PensoPay_API_Exception
	 * @since  4.5.0
	 */
	public function get_metadata() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new PensoPay_API_Exception( 'No API payment resource data available.', 0 );
		}

		return $this->resource_data->metadata;
	}

	/**
	 * get_state function
	 *
	 * Returns the current transaction state
	 *
	 * @return mixed
	 * @throws PensoPay_API_Exception
	 * @since  4.5.0
	 */
	public function get_state() {
		if ( ! is_object( $this->resource_data ) ) {
			throw new PensoPay_API_Exception( 'No API payment resource data available.', 0 );
		}

		return $this->resource_data->state;
	}

	/**
	 * Fetches transaction data based on a transaction ID. This method checks if the transaction is cached in a transient before it asks the
	 * PensoPay API. Cached data will always be used if available.
	 *
	 * If no data is cached, we will fetch the transaction from the API and cache it.
	 *
	 * @param        $transaction_id
	 *
	 * @return object|stdClass
	 * @throws PensoPay_API_Exception
	 * @throws PensoPay_Exception
	 */
	public function maybe_load_transaction_from_cache( $transaction_id ) {

		$is_caching_enabled = self::is_transaction_caching_enabled();

		if ( empty( $transaction_id ) ) {
			throw new PensoPay_Exception( __( 'Transaction ID cannot be empty', 'woo-pensopay' ) );
		}

		if ( $is_caching_enabled && false !== ( $transient = get_transient( 'wcpp_transaction_' . $transaction_id ) ) ) {
			$this->loaded_from_cache = true;

			return $this->resource_data = (object) json_decode( $transient, false, 512, JSON_THROW_ON_ERROR );
		}

		$this->get( $transaction_id );

		if ( $is_caching_enabled ) {
			$this->cache_transaction();
		}

		return $this->resource_data;
	}

	/**
	 * @return boolean
	 */
	public static function is_transaction_caching_enabled(): bool {
		$is_enabled = ! ( strtolower( WC_PP()->s( 'pensopay_caching_enabled' ) ) === 'no' );

		return apply_filters( 'woocommerce_pensopay_transaction_cache_enabled', $is_enabled );
	}

	/**
	 * Updates cache data for a transaction
	 *
	 * @return boolean
	 * @throws PensoPay_Exception
	 */
	public function cache_transaction(): bool {
		if ( ! is_object( $this->resource_data ) ) {
			throw new PensoPay_Exception( "Cannot cache empty transaction." );
		}

		if ( ! self::is_transaction_caching_enabled() ) {
			return false;
		}

		$expiration = (int) WC_PP()->s( 'pensopay_caching_expiration' );

		if ( ! $expiration ) {
			$expiration = 7 * DAY_IN_SECONDS;
		}

		// Cache expiration in seconds
		$expiration = apply_filters( 'woocommerce_pensopay_transaction_cache_expiration', $expiration );

		return set_transient( 'wcpp_transaction_' . $this->resource_data->id, json_encode( $this->resource_data, JSON_THROW_ON_ERROR ), $expiration );
	}

	/**
	 * @return bool
	 */
	public function is_loaded_from_cached(): bool {
		return $this->loaded_from_cache;
	}

	/**
	 * return stdClass
	 */
	public function get_data() {
		return $this->resource_data;
	}
}
