<?php

/**
 * WC_PensoPay_Order class
 *
 * @class          WC_PensoPay_Order
 * @version        1.0.1
 * @package        Woocommerce_PensoPay/Classes
 * @category       Class
 * @author         PerfectSolution
 */

class WC_PensoPay_Order extends WC_Order {

	/**
	 * @param $callback_data
	 *
	 * @return int
	 */
	public static function get_order_id_from_callback( $callback_data ): int {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Callbacks::get_order_id_from_callback' );

		return WC_PensoPay_Callbacks::get_order_id_from_callback( $callback_data );
	}

	/**
	 * Returns the subscription ID based on the ID retrieved from the PensoPay callback, if present.
	 *
	 * @param mixed $callback_data - the callback data
	 *
	 * @return int
	 */
	public static function get_subscription_id_from_callback( $callback_data ): int {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Callbacks::get_subscription_id_from_callback' );

		return WC_PensoPay_Callbacks::get_subscription_id_from_callback( $callback_data );
	}


	/**
	 * get_payment_id function
	 *
	 * If the order has a payment ID, we will return it. If no ID is set we return FALSE.
	 *
	 * @access public
	 * @return string
	 */
	public function get_payment_id(): ?string {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::get_payment_id' );

		return WC_PensoPay_Order_Payments_Utils::get_payment_id( $this );
	}

	/**
	 * Set the payment ID on an order
	 *
	 * @param $payment_link
	 *
	 * @return void
	 */
	public function set_payment_id( $payment_link ): void {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::set_payment_id' );
		WC_PensoPay_Order_Payments_Utils::set_payment_id( $this, $payment_link );
	}

	/**
	 * Delete the payment ID on an order
	 *
	 * @access public
	 * @return void
	 */
	public function delete_payment_id(): void {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::delete_payment_id' );
		WC_PensoPay_Order_Payments_Utils::delete_payment_id( $this );
	}

	/**
	 * If the order has a payment link, we will return it. If no link is set we return FALSE.
	 *
	 * @return null|string
	 */
	public function get_payment_link(): ?string {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::get_payment_link' );

		return WC_PensoPay_Order_Payments_Utils::get_payment_link( $this );
	}

	/**
	 * Set the payment link on an order
	 *
	 * @param $payment_link
	 *
	 * @return void
	 */
	public function set_payment_link( $payment_link ): void {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::set_payment_link' );
		WC_PensoPay_Order_Payments_Utils::set_payment_link( $this, $payment_link );
	}

	/**
	 * Delete the payment link on an order
	 *
	 * @return void
	 */
	public function delete_payment_link(): void {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::delete_payment_link' );
		WC_PensoPay_Order_Payments_Utils::delete_payment_link( $this );
	}

	/**
	 * get_transaction_order_id function
	 *
	 * If the order has a transaction order reference, we will return it. If no transaction order reference is set we
	 * return FALSE.
	 *
	 * @return string
	 */
	public function get_transaction_order_id(): string {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::get_transaction_order_id' );

		return WC_PensoPay_Order_Payments_Utils::get_transaction_order_id( $this );
	}

	/**
	 * set_transaction_order_id function
	 *
	 * Set the transaction order ID on an order
	 *
	 * @access public
	 *
	 * @param $transaction_order_id
	 *
	 * @return void
	 */
	public function set_transaction_order_id( $transaction_order_id ): void {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::set_transaction_order_id' );

		WC_PensoPay_Order_Payments_Utils::set_transaction_order_id( $this, $transaction_order_id );
	}

	/**
	 * Adds order transaction fee to the order before sending out the order confirmation
	 *
	 * @param $fee_in_cents
	 *
	 * @return bool
	 */

	public function add_transaction_fee( $fee_in_cents ): bool {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::add_order_item_transaction_fee' );

		return WC_PensoPay_Order_Payments_Utils::add_order_item_transaction_fee( $this, (int) $fee_in_cents );
	}

	/**
	 * subscription_is_renewal_failure function.
	 *
	 * Checks if the order is currently in a failed renewal
	 *
	 * @return boolean
	 */
	public function subscription_is_renewal_failure(): bool {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Utils::is_failed_renewal' );

		return WC_PensoPay_Order_Utils::is_failed_renewal( $this );
	}

	/**
	 * Adds a custom order note
	 *
	 * @param $message
	 *
	 * @return void
	 */
	public function note( $message ): void {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Utils::add_note' );
		WC_PensoPay_Order_Utils::add_note( $this, (string) $message );
	}

	/**
	 * get_transaction_params function.
	 *
	 * Returns the necessary basic params to send to PensoPay when creating a payment
	 *
	 * @access public
	 * @return array
	 */
	public function get_transaction_params(): array {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::prepare_transaction_params' );

		return WC_PensoPay_Order_Payments_Utils::prepare_transaction_params( $this );
	}

	/**
	 * contains_subscription function
	 *
	 * Checks if an order contains a subscription product
	 *
	 * @access public
	 * @return boolean
	 */
	public function contains_subscription(): bool {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Utils::contains_subscription' );

		return WC_PensoPay_Order_Utils::contains_subscription( $this );
	}

	/**
	 * @return bool
	 */
	public function is_request_to_change_payment(): bool {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Requests_Utils::is_request_to_change_payment' );

		return WC_PensoPay_Requests_Utils::is_request_to_change_payment();
	}

	/**
	 * @param bool $recurring
	 *
	 * @return string
	 */
	public function get_order_number_for_api( bool $recurring = false ): ?string {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::get_order_number_for_api' );

		return WC_PensoPay_Order_Payments_Utils::get_order_number_for_api( $this, $recurring );
	}

	/**
	 * @return bool
	 */
	public function order_contains_switch(): bool {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Utils::contains_switch_order' );

		return WC_PensoPay_Order_Utils::contains_switch_order( $this );
	}

	/**
	 * Increase the amount of payment attempts done through PensoPay
	 *
	 * @return int
	 */
	public function get_failed_pensopay_payment_count(): int {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::get_failed_payment_count' );

		return WC_PensoPay_Order_Payments_Utils::get_failed_payment_count( $this );
	}

	/**
	 * @return string
	 */
	public function get_clean_order_number(): string {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Utils::get_clean_order_number' );

		return WC_PensoPay_Order_Utils::get_clean_order_number( $this );
	}

	/**
	 * Gets the amount of times the customer has updated his card.
	 *
	 * @return int
	 */
	public function get_payment_method_change_count(): int {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::get_payment_method_change_count' );

		return WC_PensoPay_Order_Payments_Utils::get_payment_method_change_count( $this );
	}

	/**
	 * Creates an array of order items formatted as "PensoPay transaction basket" format.
	 *
	 * @return array
	 */
	public function get_transaction_basket_params(): array {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Transaction_Data_Utils::get_basket_params' );

		return WC_PensoPay_Order_Transaction_Data_Utils::get_basket_params( $this );
	}


	/**
	 * @return array
	 */
	public function get_transaction_shipping_address_params(): array {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Transaction_Data_Utils::get_shipping_address' );

		return WC_PensoPay_Order_Transaction_Data_Utils::get_shipping_address( $this );
	}

	/**
	 * @return mixed
	 */
	public function get_shipping_street_name() {
		wc_deprecated_function( __METHOD__, '7.0.0', 'Use WC_PensoPay_Address::get_street_name( $order->get_shipping_address_1() )' );

		return WC_PensoPay_Address::get_street_name( $this->get_shipping_address_1() );
	}

	/**
	 * @return string
	 */
	public function get_shipping_house_number(): string {
		wc_deprecated_function( __METHOD__, '7.0.0', 'Use WC_PensoPay_Address::get_house_number( $order->get_shipping_address_1() )' );

		return WC_PensoPay_Address::get_house_number( $this->get_shipping_address_1() );
	}

	/**
	 * @return string
	 */
	public function get_shipping_house_extension(): string {
		wc_deprecated_function( __METHOD__, '7.0.0', 'Use WC_PensoPay_Address::get_house_extension( $order->get_shipping_address_1() )' );

		return WC_PensoPay_Address::get_house_extension( $this->get_shipping_address_1() );
	}

	public function get_transaction_invoice_address_params(): array {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Transaction_Data_Utils::get_invoice_address' );

		return WC_PensoPay_Order_Transaction_Data_Utils::get_invoice_address( $this );
	}

	/**
	 * @return mixed
	 */
	public function get_billing_street_name() {
		wc_deprecated_function( __METHOD__, '7.0.0', 'Use WC_PensoPay_Address::get_street_name( $order->get_billing_address_1() )' );

		return WC_PensoPay_Address::get_street_name( $this->get_billing_address_1() );
	}

	/**
	 * @return string
	 */
	public function get_billing_house_number() {
		wc_deprecated_function( __METHOD__, '7.0.0', 'Use WC_PensoPay_Address::get_house_number( $order->get_billing_address_1() )' );

		return WC_PensoPay_Address::get_house_number( $this->get_billing_address_1() );
	}

	/**
	 * @return string
	 */
	public function get_billing_house_extension() {
		wc_deprecated_function( __METHOD__, '7.0.0', 'Use WC_PensoPay_Address::get_house_extension( $order->get_billing_address_1() )' );

		return WC_PensoPay_Address::get_house_extension( $this->get_billing_address_1() );
	}

	/**
	 * @return array
	 */
	public function get_transaction_shopsystem_params(): array {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Transaction_Data_Utils::get_shop_system_params' );

		return WC_PensoPay_Order_Transaction_Data_Utils::get_shop_system_params( $this );
	}

	/**
	 * Returns custom variables chosen in the gateway settings. This information will
	 * be sent to PensoPay and stored with the transaction.
	 *
	 * @return array
	 */
	public function get_custom_variables(): array {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Transaction_Data_Utils::get_custom_variables' );

		return WC_PensoPay_Order_Transaction_Data_Utils::get_custom_variables( $this );
	}

	/**
	 * Increase the amount of payment attempts done through PensoPay
	 *
	 * @return int
	 */
	public function increase_failed_pensopay_payment_count(): int {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::increase_failed_payment_count' );

		return WC_PensoPay_Order_Payments_Utils::increase_failed_payment_count( $this );

	}

	/**
	 * Reset the failed payment attempts made through the PensoPay gateway
	 */
	public function reset_failed_pensopay_payment_count(): void {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::reset_failed_payment_count' );

		WC_PensoPay_Order_Payments_Utils::reset_failed_payment_count( $this );
	}

	/**
	 * Returns the necessary basic params to send to PensoPay when creating a payment link
	 *
	 * @return array
	 */
	public function get_transaction_link_params(): array {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::prepare_transaction_link_params' );

		return WC_PensoPay_Order_Payments_Utils::prepare_transaction_link_params( $this );
	}

	/**
	 * get_continue_url function
	 *
	 * Returns the order's continue callback url
	 *
	 * @access public
	 * @return string
	 */
	public function get_continue_url(): string {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Transaction_Data_Utils::get_continue_url' );

		return WC_PensoPay_Order_Transaction_Data_Utils::get_continue_url( $this );
	}

	/**
	 * Returns the order's cancellation callback url
	 *
	 * @return string
	 */
	public function get_cancellation_url(): string {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Transaction_Data_Utils::get_cancellation_url' );

		return WC_PensoPay_Order_Transaction_Data_Utils::get_cancellation_url( $this );
	}

	/**
	 * Determine if we should enable autocapture on the order. This is based on both the
	 * plugin configuration and the product types. If the order contains both virtual
	 * and non-virtual products,  we will default to the 'pensopay_autocapture'-setting.
	 */
	public function get_autocapture_setting(): bool {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Transaction_Data_Utils::should_auto_capture_order' );

		return WC_PensoPay_Order_Transaction_Data_Utils::should_auto_capture_order( $this );
	}

	/**
	 * has_pensopay_payment function
	 *
	 * Checks if the order is paid with the PensoPay module.
	 *
	 * @return bool
	 * @since  4.5.0
	 * @access public
	 */
	public function has_pensopay_payment(): bool {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::is_order_using_pensopay' );

		return WC_PensoPay_Order_Payments_Utils::is_order_using_pensopay( $this );
	}

	/**
	 * Increases the amount of times the customer has updated his card.
	 *
	 * @return int
	 */
	public function increase_payment_method_change_count(): int {
		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Payments_Utils::increase_payment_method_change_count' );

		return WC_PensoPay_Order_Payments_Utils::increase_payment_method_change_count( $this );
	}

	/**
	 * @param string $context
	 *
	 * @return mixed|string
	 */
	public function get_transaction_id( $context = 'view' ) {
//		wc_deprecated_function( __METHOD__, '7.0.0', 'WC_PensoPay_Order_Utils::get_transaction_id' );

		// Search for custom transaction meta added in 4.8 to avoid transaction ID
		// sometimes being empty on subscriptions in WC 3.0.
		$transaction_id = $this->get_meta( '_pensopay_transaction_id' );
		if ( empty( $transaction_id ) ) {

			$transaction_id = parent::get_transaction_id();

			if ( empty( $transaction_id ) ) {
				// Search for original transaction ID. The transaction might be temporarily removed by
				// subscriptions. Use this one instead (if available).
				$transaction_id = $this->get_meta( '_transaction_id_original' );
				if ( empty( $transaction_id ) ) {
					// Check if the old legacy TRANSACTION ID meta value is available.
					$transaction_id = $this->get_meta( 'TRANSACTION_ID' );
				}
			}
		}

		return $transaction_id;
	}
}

