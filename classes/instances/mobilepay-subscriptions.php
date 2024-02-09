<?php

class WC_PensoPay_MobilePay_Subscriptions extends WC_PensoPay_Instance {

	public $main_settings = null;

	const instance_id = 'mobilepay-subscriptions';

	public function __construct() {
		parent::__construct();

		$this->supports = [
			'products',
			'subscriptions',
			'subscription_cancellation',
			'subscription_reactivation',
			'subscription_suspension',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change_admin',
			'subscription_payment_method_change_customer',
			'refunds',
			'multiple_subscriptions',
		];

		// Get gateway variables
		$this->id = self::instance_id;

		$this->method_title = 'Pensopay - MobilePay Subscriptions';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

		add_filter( 'woocommerce_pensopay_cardtypelock_mobilepay-subscriptions', [ $this, 'filter_cardtypelock' ] );
        add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, [ WC_PP(), 'scheduled_subscription_payment' ], 10, 2 );
        add_filter( 'woocommerce_pensopay_transaction_params_invoice', [ $this, 'maybe_remove_phone_number' ], 10, 2 );
        add_filter( 'woocommerce_available_payment_gateways', [ $this, 'adjust_available_gateways' ] );
        add_filter( "woocommerce_pensopay_create_recurring_payment_data_{$this->id}", [ $this, 'recurring_payment_data' ], 10, 3 );
        add_action( 'woocommerce_pensopay_callback_subscription_authorized', [ $this, 'on_subscription_authorized' ], 10, 3 );
        add_action( 'woocommerce_pensopay_scheduled_subscription_payment_after', [ $this, 'on_after_scheduled_payment_created' ], 10, 2 );
        add_filter( 'woocommerce_pensopay_callback_payment_captured', [ $this, 'maybe_process_order_on_capture' ], 10, 2 );
        add_filter( 'woocommerce_subscription_payment_meta', [ $this, 'woocommerce_subscription_payment_meta' ], 10, 2 );
        add_action( 'woocommerce_pensopay_callback_subscription_cancelled', [ $this, 'on_subscription_cancelled' ], 10, 4 );
	}

	/**
	 * Handle subscription cancellation
	 *
	 * @param WC_PensoPay_Order $subscription
	 * @param WC_PensoPay_Order $parent_order
	 * @param stdClass $operation
	 * @param stdClass $json
	 */
	public function on_subscription_cancelled( $subscription, WC_Order $order, $operation, $json ): void {
		if ( $subscription->get_payment_method() === $this->id && ( $transition_to = $this->s( 'mps_transaction_cancellation_status' ) ) ) {
			$allowed_transition_from = apply_filters( 'woocommerce_pensopay_mps_cancelled_from_status', [ 'active' ], $subscription, $order, $json );
			if ( $subscription->has_status( $allowed_transition_from ) && ! $subscription->has_status( $transition_to ) && WC_PensoPay_Helper::is_subscription_status( $transition_to ) ) {
				$subscription->update_status( $transition_to, ! empty( $operation->aq_status_msg ) ? $operation->aq_status_msg : __( 'Payment transaction has been cancelled by merchant or customer', 'woo-pensopay' ) );
			}
		}
	}

	/**
	 * init_form_fields function.
	 *
	 * Initiates the plugin settings form fields
	 *
	 * @access public
	 * @return array
	 */
	public function init_form_fields(): void {
		$this->form_fields = [
			'enabled'     => [
				'title'   => __( 'Enable', 'woo-pensopay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable MobilePay Subscription payment', 'woo-pensopay' ),
				'default' => 'no'
			],
			'_Shop_setup' => [
				'type'  => 'title',
				'title' => __( 'Shop setup', 'woo-pensopay' ),
			],
			'title'       => [
				'title'       => __( 'Title', 'woo-pensopay' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woo-pensopay' ),
				'default'     => __( 'MobilePay Subscriptions', 'woo-pensopay' )
			],
			'description' => [
				'title'       => __( 'Customer Message', 'woo-pensopay' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woo-pensopay' ),
				'default'     => __( 'Subscribe with your mobile phone', 'woo-pensopay' )
			],
            'checkout_instant_activation'         => [
                'title'       => __( 'Activate subscriptions immediately.', 'woo-pensopay' ),
                'type'        => 'checkbox',
                'label'       => __( 'Enable', 'woo-pensopay' ),
                'default'     => 'no',
                'description' => __( 'Activates the subscription after the customer authorizes an agreement. <strong>Not suitable for membership pages selling virtual products</strong> as the first payment might take up to 48 hours to either succeed or fail. Read more <a href="https://learn.quickpay.net/helpdesk/da/articles/payment-methods/mobilepay-subscriptions/#oprettelse-af-abonnement" target="_blank">here</a>', 'woo-pensopay' ),
            ],
            'checkout_prefill_phone_number'       => [
                'title'       => __( 'Pre-fill phone number', 'woo-pensopay' ),
                'type'        => 'checkbox',
                'label'       => __( 'Enable', 'woo-pensopay' ),
                'default'     => 'yes',
                'description' => __( 'When enabled the customer\'s phone number will be used on the MobilePay payment page.', 'woo-pensopay' ),
            ],
            [
                'type'  => 'title',
                'title' => 'Renewals'
            ],
            'renewal_keep_active'                 => [
                'title'       => __( 'Keep subscription active', 'woo-pensopay' ),
                'type'        => 'checkbox',
                'label'       => __( 'Enable', 'woo-pensopay' ),
                'default'     => 'no',
                'description' => __( 'When enabled the subscription will automatically be activated after scheduling the renewal payment. If the payment fails the subscription will be put on-hold.', 'woo-pensopay' ),
            ],
            [
                'type'  => 'title',
                'title' => __( 'Agreements', 'woo-pensopay' )
            ],
            'mps_transaction_cancellation_status' => [
                'title'             => __( 'Cancelled agreements status', 'woo-pensopay' ),
                'type'              => 'select',
                'class'             => 'wc-enhanced-select',
                'css'               => 'width: 450px;',
                'default'           => 'none',
                'description'       => __( 'Changes subscription status in case of cancelled payment agreement from either the QuickPay manager or the customer\'s MobilePay app', 'woo-pensopay' ),
                'options'           => $this->get_mps_cancel_agreement_status_options(),
                'custom_attributes' => [
                    'data-placeholder' => __( 'Select status', 'woo-pensopay' )
                ]
            ],
		];
	}

    private function get_mps_cancel_agreement_status_options() {
        return apply_filters( 'woocommerce_pensopay_mps_cancel_agreement_status_options', [
            'none'      => __( 'Do nothing', 'woo-pensopay' ),
            'on-hold'   => wc_get_order_status_name( 'on-hold' ),
            'cancelled' => wc_get_order_status_name( 'cancelled' ),
        ], $this );
    }

	/**
	 * filter_cardtypelock function.
	 *
	 * Sets the cardtypelock
	 *
	 * @access public
	 * @return string
	 */
	public function filter_cardtypelock() {
		return 'mobilepay-subscriptions';
	}

    /**
     * If disabled, the phone number won't be sent to MobilePay which means that customers will have to type in their
     * phone numbers manually.
     *
     * @param array $data
     * @param WC_Order $order
     *
     * @return array
     */
	public function maybe_remove_phone_number( array $data, WC_Order $order ): array {
		if ( $order->get_payment_method() === $this->id ) {
			if ( ! WC_PensoPay_Helper::option_is_enabled( $this->s( 'checkout_prefill_phone_number' ) ) ) {
				if ( isset( $data['phone_number'] ) ) {
					$data['phone_number'] = null;
				}
			}
		}

		return $data;
	}

	/**
	 * Only show the gateway if the cart contains a subscription product
	 *
	 * @param $available_gateways
	 *
	 * @return mixed
	 */
	public function adjust_available_gateways( $available_gateways ) {
		if ( isset( $available_gateways[ $this->id ] )
		     && WC_PensoPay_Subscription::plugin_is_active()
		     && ( is_cart() || is_checkout() ) && ! WC_Subscriptions_Cart::cart_contains_subscription()
		     && ! WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment ) {
			unset( $available_gateways[ $this->id ] );
		}

		return $available_gateways;
	}

	/**
	 * @param array $data
	 * @param WC_Order $order
	 * @param int $subscription_id
	 *
	 * @return array
	 */
	public function recurring_payment_data( $data, WC_Order $order, $subscription_id ): array {
        if ( empty( $data['due_date'] ) ) {
            $data['auto_capture_at'] = wp_date( 'Y-m-d', strtotime( 'now + 2 days' ), apply_filters( 'woocommerce_pensopay_mps_timezone', null, $data, $order, $subscription_id ) );
            $data['description']     = sprintf( __( 'Payment of #%s', 'woo-pensopay' ), $order->get_order_number() );
        }

		return $data;
	}

	/**
	 * If enabled, the module will activate the subscription after an agreement has been authorized, but
	 *
	 * @param WC_Subscription $subscription
	 * @param WC_Order $parent_order
	 * @param mixed $transaction
	 */
	public function on_subscription_authorized( WC_Subscription $subscription, WC_Order $parent_order, $transaction ): void {
        try {
            if ( $subscription->get_payment_method() === self::instance_id ) {
                $instant_activation    = WC_PensoPay_Helper::option_is_enabled( $this->s( 'checkout_instant_activation' ) );

                if ( $instant_activation && ! $subscription->has_status( 'active' ) ) {
                    $subscription->update_status( 'active', __( "'Activate subscriptions immediately.' enabled. Activating subscription due to authorized MobilePay agreement", 'woo-pensopay' ) );
                    $subscription->save();
                }
            }
        } catch ( \Exception $e ) {
            ( new WC_PensoPay_Log() )->add( 'Unable to activate subscription immediately after payment authorization: ' . $e->getMessage() );
        }
    }

	/**
	 * @param WC_Subscription $subscription
	 * @param WC_Order $renewal_order
	 */
	public function on_after_scheduled_payment_created( $subscription, WC_Order $renewal_order ): void {
        if ( WC_PensoPay_Helper::option_is_enabled( $this->s( 'renewal_keep_active' ) ) ) {
            try {
                $subscription->update_status( 'active' );
            } catch ( \Exception $e ) {
                $subscription->add_order_note( $e->getMessage() );
            }
        }
    }

	/**
	 * @param WC_Order $order
	 * @param mixed $transaction
	 *
	 * @return void
	 */
	public function maybe_process_order_on_capture( WC_Order $order, $transaction ): void {
		if ( $order->get_payment_method() === $this->id && $order->needs_payment() ) {
			$order->payment_complete( $transaction->id );
		}
	}

	/**
	 * Declare gateway's meta data requirements in case of manual payment gateway changes performed by admins.
	 *
	 * @param array $payment_meta
	 *
	 * @param WC_Subscription $subscription
	 *
	 * @return array
	 */
	public function woocommerce_subscription_payment_meta( $payment_meta, $subscription ): array {
        $payment_meta[ $this->id ] = [
            'post_meta' => [
                '_pensopay_transaction_id' => [
                    'value' => WC_PensoPay_Order_Utils::get_transaction_id( $subscription ),
                    'label' => __( 'Pensopay Transaction ID', 'woo-pensopay' ),
                ],
            ],
        ];

		return $payment_meta;
	}
}
