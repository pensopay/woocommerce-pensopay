<?php
/**
 * Plugin Name: WooCommerce PensoPay
 * Plugin URI: http://wordpress.org/plugins/pensopay/
 * Description: Integrates your PensoPay payment gateway into your WooCommerce installation.
 * Version: 7.1.2
 * Author: PensoPay
 * Text Domain: woo-pensopay
 * Domain Path: /languages/
 * Author URI: https://pensopay.com/
 * Wiki: https://pensopay.zendesk.com/hc/da
 * WC requires at least: 7.1.0
 * WC tested up to: 8.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WCPP_VERSION', '7.1.2' );
define( 'WCPP_URL', plugins_url( __FILE__ ) );
define( 'WCPP_PATH', plugin_dir_path( __FILE__ ) );

add_action( 'plugins_loaded', 'init_pensopay_gateway', 0 );

/**
 * Adds notice in case of WooCommerce being inactive
 */
function wc_pensopay_woocommerce_inactive_notice() {
	$class    = 'notice notice-error';
	$headline = __( 'WooCommerce Pensopay requires WooCommerce to be active.', 'woo-pensopay' );
	$message  = __( 'Go to the plugins page to activate WooCommerce', 'woo-pensopay' );
	printf( '<div class="%1$s"><h2>%2$s</h2><p>%3$s</p></div>', $class, $headline, $message );
}

function init_pensopay_gateway() {
	/**
	 * Required functions
	 */
	if ( ! function_exists( 'is_woocommerce_active' ) ) {
		require_once WCPP_PATH . 'woo-includes/woo-functions.php';
	}

	/**
	 * Check if WooCommerce is active, and if it isn't, disable Subscriptions.
	 *
	 * @since 1.0
	 */
	if ( ! is_woocommerce_active() ) {
		add_action( 'admin_notices', 'wc_pensopay_woocommerce_inactive_notice' );

		return;
	}

	// Import helper methods
	require_once WCPP_PATH . 'includes/template.php';

	// Import helper classes
	require_once WCPP_PATH . 'helpers/notices.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-install.php';
	require_once WCPP_PATH . 'classes/api/woocommerce-pensopay-api.php';
	require_once WCPP_PATH . 'classes/api/woocommerce-pensopay-api-transaction.php';
	require_once WCPP_PATH . 'classes/api/woocommerce-pensopay-api-payment.php';
	require_once WCPP_PATH . 'classes/api/woocommerce-pensopay-api-subscription.php';
	require_once WCPP_PATH . 'classes/utils/woocommerce-pensopay-order-utils.php';
	require_once WCPP_PATH . 'classes/utils/woocommerce-pensopay-order-payments-utils.php';
	require_once WCPP_PATH . 'classes/utils/woocommerce-pensopay-order-transaction-data-utils.php';
	require_once WCPP_PATH . 'classes/utils/woocommerce-pensopay-requests-utils.php';
	require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-module.php';
    require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-blocks-checkout.php';
	require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-emails.php';
	require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-admin-ajax.php';
	require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-admin-orders.php';
	require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-admin-orders-lists-table.php';
	require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-admin-orders-meta.php';
    require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-orders.php';
	require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-subscriptions.php';
	require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-subscriptions-change-payment-method.php';
	require_once WCPP_PATH . 'classes/modules/woocommerce-pensopay-subscriptions-early-renewals.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-statekeeper.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-exceptions.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-log.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-helper.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-address.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-settings.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-order.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-subscription.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-countries.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-views.php';
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-callbacks.php';
	require_once WCPP_PATH . 'helpers/permissions.php';
	require_once WCPP_PATH . 'helpers/requests.php';
	require_once WCPP_PATH . 'helpers/transactions.php';

    require_once WCPP_PATH . 'extensions/wpml.php';
    require_once WCPP_PATH . 'extensions/polylang.php';


	// Main class
	class WC_PensoPay extends WC_Payment_Gateway {

		/**
		 * $_instance
		 * @var mixed
		 * @access public
		 * @static
		 */
		public static $_instance = null;

		/**
		 * @var WC_PensoPay_Log
		 */
		public $log;

		/**
		 * get_instance
		 *
		 * Returns a new instance of self, if it does not already exist.
		 *
		 * @access public
		 * @static
		 * @return WC_PensoPay
		 */
		public static function get_instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}


		/**
		 * __construct function.
		 *
		 * The class construct
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->id           = 'pensopay';
			$this->method_title = 'PensoPay';
			$this->icon         = '';
			$this->has_fields   = true;

			$this->supports = [
				'subscriptions',
				'products',
				'subscription_cancellation',
				'subscription_reactivation',
				'subscription_suspension',
				'subscription_amount_changes',
				'subscription_date_changes',
				'subscription_payment_method_change_admin',
				'subscription_payment_method_change_customer',
				'refunds',
				'multiple_subscriptions',
				'pre-orders'
			];

			$this->log = new WC_PensoPay_Log();

			// Load the form fields and settings
			$this->init_form_fields();
			$this->init_settings();

			// Get gateway variables
			$this->title             = $this->s( 'title' );
			$this->description       = $this->s( 'description' );
			$this->instructions      = $this->s( 'instructions' );
			$this->order_button_text = $this->s( 'checkout_button_text' );

			do_action( 'woocommerce_pensopay_loaded' );
		}


		/**
		 * filter_load_instances function.
		 *
		 * Loads in extra instances of as separate gateways
		 *
		 * @access public static
		 * @return array
		 */
		public static function filter_load_instances( $methods ) {
			require_once WCPP_PATH . 'classes/instances/instance.php';

			$instances = self::get_gateway_instances();

			foreach ( $instances as $file_name => $class_name ) {
				$file_path = WCPP_PATH . 'classes/instances/' . $file_name . '.php';

				if ( file_exists( $file_path ) ) {
					require_once $file_path;
					$methods[] = $class_name;
				}
			}

			return $methods;
		}

		/**
		 * @return array
		 */
		public static function get_gateway_instances() {
			return [
				'anyday'             => 'WC_PensoPay_Anyday',
                'apple-pay'          => 'WC_PensoPay_Apple_Pay',
				'fbg1886'            => 'WC_PensoPay_FBG1886',
                'google-pay'         => 'WC_PensoPay_Google_Pay',
				'ideal'              => 'WC_PensoPay_iDEAL',
				'klarna'             => 'WC_PensoPay_Klarna',
				'klarna-payments'    => 'WC_PensoPay_Klarna_Payments',
				'mobilepay'          => 'WC_PensoPay_MobilePay',
				'mobilepay-subscriptions' => 'WC_PensoPay_MobilePay_Subscriptions',
				'paypal'             => 'WC_PensoPay_PayPal',
				'pensopay-extra'     => 'WC_PensoPay_Extra',
				'resurs'             => 'WC_PensoPay_Resurs',
				'sofort'             => 'WC_PensoPay_Sofort',
				'swish'              => 'WC_PensoPay_Swish',
				'trustly'            => 'WC_PensoPay_Trustly',
				'viabill'            => 'WC_PensoPay_ViaBill',
				'vipps'              => 'WC_PensoPay_Vipps',
			];
		}


		/**
		 * hooks_and_filters function.
		 *
		 * Applies plugin hooks and filters
		 *
		 * @access public
		 * @return string
		 */
		public function hooks_and_filters() {
			WC_PensoPay_Admin_Ajax::get_instance();
			WC_PensoPay_Admin_Orders::get_instance();
			WC_PensoPay_Admin_Orders_Lists_Table::get_instance();
			WC_PensoPay_Admin_Orders_Meta::get_instance();
			WC_PensoPay_Blocks_Checkout::get_instance();
            WC_PensoPay_Emails::get_instance();
			WC_PensoPay_Orders::get_instance();
			WC_PensoPay_Subscriptions::get_instance();
			WC_PensoPay_Subscriptions_Change_Payment_Method::get_instance();
			WC_PensoPay_Subscriptions_Early_Renewals::get_instance();

			add_action( 'woocommerce_api_wc_' . $this->id, [ $this, 'callback_handler' ] );
			add_action( 'woocommerce_order_status_completed', [ $this, 'woocommerce_order_status_completed' ] );
			add_action( 'in_plugin_update_message-woocommerce-pensopay/woocommerce-pensopay.php', [ __CLASS__, 'in_plugin_update_message' ] );

            // WooCommerce Subscriptions hooks/filters
            if ( $this->supports( 'subscriptions' ) ) {
                add_action('woocommerce_scheduled_subscription_payment_' . $this->id, [$this, 'scheduled_subscription_payment'], 10, 2);
                add_action('woocommerce_subscription_cancelled_' . $this->id, [$this, 'subscription_cancellation']);
                add_action('woocommerce_subscription_payment_method_updated_to_' . $this->id, [$this, 'on_subscription_payment_method_updated_to_pensopay',], 10, 2);
	            add_filter( 'wc_subscriptions_renewal_order_data', [ $this, 'remove_renewal_meta_data' ], 10 );
                add_filter('woocommerce_subscription_payment_meta', [$this, 'woocommerce_subscription_payment_meta'], 10, 2);
                add_action('woocommerce_subscription_validate_payment_meta_' . $this->id, [$this, 'woocommerce_subscription_validate_payment_meta',], 10, 2);
            }

			// WooCommerce Pre-Orders
			add_action( 'wc_pre_orders_process_pre_order_completion_payment_' . $this->id, [ $this, 'process_pre_order_payments' ] );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );

			// Make sure not to add these actions multiple times
			if ( ! has_action( 'init', 'WC_PensoPay_Helper::load_i18n' ) ) {
                // Custom bulk actions
                add_action( 'admin_footer-edit.php', [ $this, 'register_bulk_actions' ] );
                add_action( 'load-edit.php', [ $this, 'handle_bulk_actions' ] );

                add_action( 'admin_enqueue_scripts', 'WC_PensoPay_Helper::enqueue_stylesheet' );
                add_action( 'admin_enqueue_scripts', 'WC_PensoPay_Helper::enqueue_javascript_backend' );
                add_action( 'wp_ajax_pensopay_manual_transaction_actions', [ $this, 'ajax_pensopay_manual_transaction_actions' ] );
                add_action( 'wp_ajax_pensopay_empty_logs', [ $this, 'ajax_empty_logs' ] );
                add_action( 'wp_ajax_pensopay_flush_cache', [ $this, 'ajax_flush_cache' ] );
                add_action( 'wp_ajax_pensopay_ping_api', [ $this, 'ajax_ping_api' ] );
                add_action( 'wp_ajax_pensopay_fetch_private_key', [ $this, 'ajax_fetch_private_key' ] );
                add_action( 'wp_ajax_pensopay_run_data_upgrader', 'WC_PensoPay_Install::ajax_run_upgrader' );
                add_action( 'in_plugin_update_message-woocommerce-pensopay/woocommerce-pensopay.php', [ __CLASS__, 'in_plugin_update_message' ] );

				add_action( 'woocommerce_email_before_order_table', [ $this, 'email_instructions' ], 10, 2 );

				if ( WC_PensoPay_Helper::option_is_enabled( $this->s( 'pensopay_orders_transaction_info', 'yes' ) ) ) {
					add_filter( 'manage_edit-shop_order_columns', [ $this, 'filter_shop_order_posts_columns' ], 10, 1 );
					add_filter( 'manage_shop_order_posts_custom_column', [ $this, 'apply_custom_order_data' ] );
					add_filter( 'manage_shop_subscription_posts_custom_column', [ $this, 'apply_custom_order_data' ] );
					add_action( 'woocommerce_pensopay_accepted_callback', [ $this, 'callback_update_transaction_cache' ], 10, 2 );
				}

				add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
				add_action( 'admin_notices', [ $this, 'admin_notices' ] );
			}

			add_action( 'init', 'WC_PensoPay_Helper::load_i18n' );
			add_filter( 'woocommerce_gateway_icon', [ $this, 'apply_gateway_icons' ], 2, 3 );

			// Third party plugins
			add_filter( 'qtranslate_language_detect_redirect', 'WC_PensoPay_Helper::qtranslate_prevent_redirect', 10, 3 );
			add_filter( 'wpss_misc_form_spam_check_bypass', 'WC_PensoPay_Helper::spamshield_bypass_security_check', - 10, 1 );

			//Needs Payment Subscription Fix
			add_filter( 'woocommerce_order_needs_payment', 'WC_PensoPay_Helper::order_needs_payment', 10, 3 );
			add_filter( 'woocommerce_valid_order_statuses_for_payment', 'WC_PensoPay_Helper::valid_statuses_payment', 10, 2 );

			//Cancel transaction on order cancel (if setting enabled)
            add_action('woocommerce_order_status_changed', [$this, 'transaction_cancel_on_order_cancel'], 10, 3);

            //Translations for emails
            add_filter( 'determine_locale', 'WC_PensoPay_Helper::determine_locale', 10, 1 );

            add_action('wp_head', 'WC_PensoPay_Helper::viabill_header'); //Header JS

            add_action( 'woocommerce_order_status_refunded', [$this, 'pensopay_refund_order'], 10, 1 );
		}


        /**
         * Update used coupon amount for each coupon within an order.
         *
         * @since 3.0.0
         * @param int $order_id Order ID.
         */
        public function pensopay_refund_order( $order_id ) {
            $order = wc_get_order( $order_id );

            if ( ! $order ) {
                return;
            }

            if (WC_PensoPay_Order_Payments_Utils::is_order_using_pensopay( $order )) {
                $this->process_refund($order_id);
            }
        }


		/**
		 * s function.
		 *
		 * Returns a setting if set. Introduced to prevent undefined key when introducing new settings.
		 *
		 * @access public
		 *
		 * @param      $key
		 * @param null $default
		 *
		 * @return mixed
		 */
		public function s( $key, $default = null ) {
			if ( isset( $this->settings[ $key ] ) ) {
				return $this->settings[ $key ];
			}

			return apply_filters( 'woocommerce_pensopay_get_setting' . $key, ! is_null( $default ) ? $default : '', $this );
		}

        public function transaction_cancel_on_order_cancel($order_id, $status, $newStatus)
        {
            $order = wc_get_order( $order_id );
            if ($order) {
                if ($newStatus === 'cancelled' && $status !== 'cancelled' && !$order->get_meta('_quickpay_tried_cancel')) {
                    if ($this->s('pensopay_orders_order_cancel_transaction') === 'yes') {
                        try {
                            $originalLanguage = false;
                            $data = $order->get_data();
                            $transactionId = isset($data['transaction_id']) ? $data['transaction_id'] : false;
                            if ($transactionId) {
                                $originalLanguage = $this->maybe_change_language($order);

                                $order->add_meta_data('_quickpay_tried_cancel', '1', true);
                                $order->save_meta_data();
                                $api_transaction = new WC_PensoPay_API_Payment();
                                $api_transaction->cancel($transactionId);
                                $order->add_order_note('Cancelled transaction #' . $transactionId);
                            }
                        } catch (\Exception $e) {
                            if ($order) {
                                $order->add_order_note($e->getMessage());
                            }
                        } finally {
                            if ($originalLanguage) {
                                $this->maybe_restore_language($originalLanguage);
                            }
                        }
                    }
                }
            }
        }

		/**
		 * Hook used to display admin notices
		 */
		public function admin_notices() {
			WC_PensoPay_Settings::show_admin_setup_notices();
			WC_PensoPay_Install::show_update_warning();
		}


		/**
		 * add_action_links function.
		 *
		 * Adds action links inside the plugin overview
		 *
		 * @access public static
		 * @return array
		 */
		public static function add_action_links( $links ) {
			$links = array_merge( [
				'<a href="' . WC_PensoPay_Settings::get_settings_page_url() . '">' . __( 'Settings', 'woo-pensopay' ) . '</a>',
			], $links );

			return $links;
		}


		/**
		 * ajax_pensopay_manual_transaction_actions function.
		 *
		 * Ajax method taking manual transaction requests from wp-admin.
		 *
		 * @access public
		 * @return void
		 */
		public function ajax_pensopay_manual_transaction_actions() {
			if ( isset( $_REQUEST['pensopay_action'] ) && isset( $_REQUEST['post'] ) ) {
				$param_action = sanitize_text_field( $_REQUEST['pensopay_action'] );
				$param_post   = sanitize_text_field( $_REQUEST['post'] );

                if ( ! woocommerce_pensopay_can_user_manage_payments( $param_action ) ) {
                    printf( 'Your user is not capable of %s payments.', $param_action );
                    exit;
                }

				$order = new WC_PensoPay_Order( (int) $param_post );

				try {
					$transaction_id = $order->get_transaction_id();

					// Subscription
					if ( WC_PensoPay_Subscription::is_subscription( $order ) ) {
						$payment = new WC_PensoPay_API_Subscription();
						$payment->get( $transaction_id );
					} // Payment
					else {
						$payment = new WC_PensoPay_API_Payment();
						$payment->get( $transaction_id );
					}

					$payment->get( $transaction_id );

					// Based on the current transaction state, we check if
					// the requested action is allowed
					if ( $payment->is_action_allowed( $param_action ) ) {
						// Check if the action method is available in the payment class
						if ( method_exists( $payment, $param_action ) ) {
							// Fetch amount if sent.
							$amount = isset( $_REQUEST['pensopay_amount'] ) ? WC_PensoPay_Helper::price_custom_to_multiplied( $_REQUEST['pensopay_amount'], $payment->get_currency() ) : $payment->get_remaining_balance();

							// Call the action method and parse the transaction id and order object
                            $payment->$param_action( $transaction_id, $order, WC_PensoPay_Helper::price_multiplied_to_float( $amount, $payment->get_currency() ) );
						} else {
							throw new PensoPay_API_Exception( sprintf( "Unsupported action: %s.", $param_action ) );
						}
					} // The action was not allowed. Throw an exception
					else {
						throw new PensoPay_API_Exception( sprintf( "Action: \"%s\", is not allowed for order #%d, with type state \"%s\"", $param_action, $order->get_clean_order_number(), $payment->get_current_type() ) );
					}
				} catch ( PensoPay_Exception $e ) {
					echo $e->getMessage();
					$e->write_to_logs();
					exit;
				} catch ( PensoPay_API_Exception $e ) {
					echo $e->getMessage();
					$e->write_to_logs();
					exit;
				}
			}
		}

		/**
		 * ajax_empty_logs function.
		 *
		 * Ajax method to empty the debug logs
		 *
		 * @access public
		 * @return json
		 */
		public function ajax_empty_logs() {
			if ( woocommerce_pensopay_can_user_empty_logs() ) {
				$this->log->clear();
				echo json_encode( [ 'status' => 'success', 'message' => 'Logs successfully emptied' ] );
				exit;
			}
		}

		/**
		 * ajax_empty_logs function.
		 *
		 * Ajax method to empty the debug logs
		 *
		 * @access public
		 * @return json
		 */
		public function ajax_flush_cache() {
			global $wpdb;
			if ( woocommerce_pensopay_can_user_flush_cache() ) {
				$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wcpp_transaction_%' OR option_name LIKE '_transient_timeout_wcpp_transaction_%'" );
				echo json_encode( [ 'status' => 'success', 'message' => 'The transaction cache has been cleared.' ] );
				exit;
			}
		}

		/**
		 * Returns the private key
		 */
		public function ajax_fetch_private_key() {
			try {
				if ( empty( $_POST['api_key'] ) ) {
					throw new \Exception( __( 'Please type in the API key before requesting a private key', 'woo-pensopay' ) );
				}

				if ( ! current_user_can( 'manage_woocommerce' ) ) {
					throw new \Exception( __( 'You are not authorized to perform this action.', 'woo-pensopay' ) );
				}

				$api_key = $_POST['api_key'];

				$api = new WC_PensoPay_API( $api_key );

				$response = $api->get( 'account/private-key' );
				echo json_encode( [ 'status' => 'success', 'data' => $response ] );
			} catch ( \Exception $e ) {
				echo json_encode( [ 'status' => 'error', 'message' => $e->getMessage() ] );
			}

			exit;

		}

		/**
		 * Checks if an API key is able to connect to the API
		 */
		public function ajax_ping_api() {
			$status = 'error';
			if ( ! empty( $_POST['api_key'] ) ) {
				try {
					$api = new WC_PensoPay_API( sanitize_text_field( $_POST['api_key'] ) );
					$api->get( '/payments?page_size=1' );
					$status = 'success';
				} catch ( PensoPay_API_Exception $e ) {
					var_dump( $e->getMessage() );
				}
			}
			echo json_encode( [ 'status' => $status ] );
			exit;
		}

		/**
		 * woocommerce_order_status_completed function.
		 *
		 * Captures one or several transactions when order state changes to complete.
		 *
		 * @param $post_id
		 *
		 * @return void
		 */
		public function woocommerce_order_status_completed( $post_id ): void {
			// Instantiate new order object
			if ( ! $order = woocommerce_pensopay_get_order( $post_id ) ) {
				return;
			}

			// Only run logic on the correct instance to avoid multiple calls, or if all extra instances has not been loaded.
			if ( ( WC_PensoPay_Statekeeper::$gateways_added && $this->id !== $order->get_payment_method() ) || ! WC_PensoPay_Order_Payments_Utils::is_order_using_pensopay( $order ) ) {
				return;
			}

			// Check the gateway settings.
			if ( apply_filters( 'woocommerce_pensopay_capture_on_order_completion', WC_PensoPay_Helper::option_is_enabled( $this->s( 'pensopay_captureoncomplete' ) ), $order ) ) {
				// Capture only orders that are actual payments (regular orders / recurring payments)
				if ( ! WC_PensoPay_Subscription::is_subscription( $order ) ) {
					$transaction_id = WC_PensoPay_Order_Utils::get_transaction_id( $order );

					// Check if there is a transaction ID
					if ( $transaction_id ) {
						try {
							$payment        = new WC_PensoPay_API_Payment();

							// Retrieve resource data about the transaction
							$payment->get( $transaction_id );

							// Check if the transaction can be captured
							if ( $payment->is_action_allowed( 'capture' ) ) {

								// In case a payment has been partially captured, we check the balance and subtracts it from the order
								// total to avoid exceptions.
								$amount_multiplied = WC_PensoPay_Helper::price_multiply( $order->get_total(), $payment->get_currency() ) - $payment->get_balance();
								$amount            = WC_PensoPay_Helper::price_multiplied_to_float( $amount_multiplied, $payment->get_currency() );

								$payment->capture( $transaction_id, $order, $amount );
							}
						} catch ( PensoPay_Capture_Exception $e ) {
							woocommerce_pensopay_add_runtime_error_notice( $e->getMessage() );
							$order->add_order_note( $e->getMessage() );
							$this->log->add( $e->getMessage() );
						} catch ( \Exception $e ) {
							$error = sprintf( 'Unable to capture payment on order #%s. Problem: %s', $order->get_id(), $e->getMessage() );
							woocommerce_pensopay_add_runtime_error_notice( $error );
							$order->add_order_note( $error );
							$this->log->add( $error );
						}
					}
				}
			}
		}


		/**
		 * Prints out the description of the gateway. Also adds two checkboxes for viaBill/creditcard for customers to choose how to pay.
		 *
		 * @return void
		 */
		public function payment_fields(): void {
			if ( $description = $this->get_description() ) {
				echo wpautop( wptexturize( $description ) );
			}
		}


		/**
		 * Processing payments on checkout
		 *
		 * @param $order_id
		 *
		 * @return array
		 */
		public function process_payment( $order_id ) {
			return $this->prepare_external_window_payment( woocommerce_pensopay_get_order( $order_id ) );
		}

		/**
		 * Processes a payment
		 *
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		private function prepare_external_window_payment( WC_Order $order ) {
			try {
				// Does the order need a new PensoPay payment?
				$needs_payment = true;

				// Default redirect to
				$redirect_to = $this->get_return_url( $order );

				/** @noinspection NotOptimalIfConditionsInspection */
				if ( wc_string_to_bool( WC_PP()->s( 'subscription_update_card_on_manual_renewal_payment' ) ) && WC_PensoPay_Subscription::is_renewal( $order ) ) {
					WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment = true;
				}

				// Instantiate a new transaction
				$api_transaction = woocommerce_pensopay_get_transaction_instance_by_order( $order );

				// If the order is a subscription or an attempt of updating the payment method
				if ( $api_transaction instanceof WC_PensoPay_API_Subscription ) {
					// Clean up any legacy data regarding old payment links before creating a new payment.
					WC_PensoPay_Order_Payments_Utils::delete_payment_id( $order );
					WC_PensoPay_Order_Payments_Utils::delete_payment_link( $order );
				}
				// If the order contains a product switch and does not need a payment, we will skip the PensoPay
				// payment window since we do not need to create a new payment nor modify an existing.
				else if ( WC_PensoPay_Order_Utils::contains_switch_order( $order ) && ! $order->needs_payment() ) {
					$needs_payment = false;
				}

				if ( $needs_payment ) {
					$redirect_to = woocommerce_pensopay_create_payment_link( $order );
				}

				// Perform redirect
				return [
					'result'   => 'success',
					'redirect' => $redirect_to,
				];

			} catch ( PensoPay_Exception $e ) {
				$e->write_to_logs();
				wc_add_notice( $e->getMessage(), 'error' );
			}
		}

		/**
		 * HOOK: Handles pre-order payments
		 */
		public function process_pre_order_payments( $order ) {
			// Set order object
			$order = woocommerce_pensopay_get_order( $order );
            $originalLanguage = $this->maybe_change_language($order);

			// Get transaction ID
			$transaction_id = WC_PensoPay_Order_Utils::get_transaction_id( $order );

			// Check if there is a transaction ID
			if ( $transaction_id ) {
				try {
					// Set payment object
					$payment = new WC_PensoPay_API_Payment();

					// Retrieve resource data about the transaction
					$payment->get( $transaction_id );

					// Check if the transaction can be captured
					if ( $payment->is_action_allowed( 'capture' ) ) {
						try {
							// Capture the payment
							$payment->capture( $transaction_id, $order );
						} // Payment failed
						catch ( PensoPay_API_Exception $e ) {
							$this->log->add( sprintf( "Could not process pre-order payment for order: #%s with transaction id: %s. Payment failed. Exception: %s", $order->get_clean_order_number(), $transaction_id, $e->getMessage() ) );

							$order->update_status( 'failed' );
						}
					}
				} catch ( PensoPay_API_Exception $e ) {
					$this->log->add( sprintf( "Could not process pre-order payment for order: #%s with transaction id: %s. Transaction not found. Exception: %s", $order->get_clean_order_number(), $transaction_id, $e->getMessage() ) );
				}
			}
            $this->maybe_restore_language($originalLanguage);
        }

		/**
		 * Process refunds
		 * WooCommerce 2.2 or later
		 *
		 * @param int $order_id
		 * @param null|float $amount
		 * @param string $reason
		 *
		 * @return bool|WP_Error
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			try {
				if ( ! $order = woocommerce_pensopay_get_order( $order_id ) ) {
					throw new PensoPay_Exception( sprintf( 'Could not load the order with ID: %d', $order_id ) );
				}

				$originalLanguage = $this->maybe_change_language($order);

				$transaction_id = WC_PensoPay_Order_Utils::get_transaction_id( $order );

				// Check if there is a transaction ID
				if ( ! $transaction_id ) {
					throw new PensoPay_Exception( sprintf( __( "No transaction ID for order: %s", 'woo-pensopay' ), $order_id ) );
				}

				// Create a payment instance and retrieve transaction information
				$payment = new WC_PensoPay_API_Payment();
				$payment->get( $transaction_id );

				// Check if the transaction can be refunded
				if ( ! $payment->is_action_allowed( 'refund' ) ) {
					if ( in_array( $payment->get_current_type(), [ 'authorize', 'recurring' ], true ) ) {
						throw new PensoPay_Exception( __( 'A non-captured payment cannot be refunded.', 'woo-pensopay' ) );
					}
					throw new PensoPay_Exception( __( 'Transaction state does not allow refunds.', 'woo-pensopay' ) );
				}

				// Perform a refund API request
				$payment->refund( (int) $transaction_id, $order, $amount === null ? null : (float) $amount );
				$this->maybe_restore_language($originalLanguage);
				return true;
			} catch ( PensoPay_Exception $e ) {
				$e->write_to_logs();
                $this->maybe_restore_language($originalLanguage);
				return new WP_Error( 'pensopay_refund_error', $e->getMessage() );
			}
		}

		/**
		 * Clear cart in case its not already done.
		 *
		 * @return void
		 */
		public function thankyou_page() {
			global $woocommerce;
			$woocommerce->cart->empty_cart();
		}

		/**
		 * scheduled_subscription_payment function.
		 *
		 * Runs every time a scheduled renewal of a subscription is required
		 *
		 * @access public
		 *
		 * @param $amount_to_charge
		 * @param WC_Order $renewal_order
		 *
		 * @return mixed|void|null
		 */
		public function scheduled_subscription_payment( $amount_to_charge, WC_Order $renewal_order ) {
			if ( ( $renewal_order->get_payment_method() === $this->id ) && $renewal_order->needs_payment() ) {
                // Create subscription instance
                $transaction = new WC_PensoPay_API_Subscription();

                /** @var WC_Subscription $subscription */
                // Get the subscription based on the renewal order
                $subscription = WC_PensoPay_Subscription::get_subscriptions_for_renewal_order( $renewal_order, $single = true );

                // Get the transaction ID from the subscription
                $transaction_id = WC_PensoPay_Order_Utils::get_transaction_id( $subscription );

                // Capture a recurring payment with fixed amount
                $response = $this->process_recurring_payment( $transaction, $transaction_id, $amount_to_charge, $renewal_order );

                do_action( 'woocommerce_pensopay_scheduled_subscription_payment_after', $subscription, $renewal_order, $response, $transaction, $transaction_id, $amount_to_charge );

                return $response;
            }
		}

		/**
		 * Wrapper to process a recurring payment on an order/subscription
		 *
		 * @param WC_PensoPay_API_Subscription $transaction
		 * @param                              $subscription_transaction_id
		 * @param                              $amount_to_charge
		 * @param                              $order
		 *
		 * @return mixed
		 */
		public function process_recurring_payment( WC_PensoPay_API_Subscription $transaction, $subscription_transaction_id, $amount_to_charge, $order ) {
			$order = woocommerce_pensopay_get_order( $order );


			$originalLanguage = $this->maybe_change_language($order);
            $response = null;

            try {
                // Capture a recurring payment with fixed amount
                [ $response ] = $transaction->recurring( $subscription_transaction_id, $order, $amount_to_charge );
            } catch ( PensoPay_Exception $e ) {
	            WC_PensoPay_Order_Payments_Utils::increase_failed_payment_count( $order );
                // Set the payment as failed
                $order->update_status( 'failed', 'Automatic renewal of ' . $order->get_order_number() . ' failed. Message: ' . $e->getMessage() );

                // Write debug information to the logs
                $e->write_to_logs();
            }

            $this->maybe_restore_language($originalLanguage);

            return $response;
		}

		/**
		 * Prevents the failed attempts count to be copied to renewal orders
		 *
		 * @param array $meta
		 *
		 * @return array
		 */
		public function remove_renewal_meta_data( array $meta ): array {
			$avoid_keys = [
				'_pensopay_failed_payment_count',
				'_pensopay_transaction_id',
				'_transaction_id',
				'TRANSACTION_ID', // Prevents the legacy transaction ID from being copied to renewal orders
			];

			foreach ( $avoid_keys as $avoid_key ) {
				if ( ! empty( $meta[ $avoid_key ] ) ) {
					unset( $meta[ $avoid_key ] );
				}
			}

			return $meta;
		}

		public function remove_failed_pensopay_attempts_meta_query( $order_meta_query ) {
			$order_meta_query .= " AND `meta_key` NOT IN ('" . WC_PensoPay_Order::META_FAILED_PAYMENT_COUNT . "')";
			$order_meta_query .= " AND `meta_key` NOT IN ('_pensopay_transaction_id')";
            $order_meta_query .= " AND `meta_key` NOT IN ('_transaction_id')";

			return $order_meta_query;
		}

		/**
		 * Prevents the legacy transaction ID from being copied to renewal orders
		 *
		 * @param $order_meta_query
		 *
		 * @return string
		 */
		public function remove_legacy_transaction_id_meta_query( $order_meta_query ) {
			$order_meta_query .= " AND `meta_key` NOT IN ('TRANSACTION_ID')";

			return $order_meta_query;
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
			$payment_meta['pensopay'] = [
				'post_meta' => [
					'_pensopay_transaction_id' => [
						'value' => WC_PensoPay_Order_Utils::get_transaction_id( $subscription ),
						'label' => __( 'PensoPay Transaction ID', 'woo-pensopay' ),
					],
				],
			];

			return $payment_meta;
		}

		/**
		 * Check if the transaction ID actually exists as a subscription transaction in the manager.
		 * If not, an exception will be thrown resulting in a validation error.
		 *
		 * @param array $payment_meta
		 *
		 * @param WC_Subscription $subscription
		 *
		 * @throws PensoPay_API_Exception
		 */
		public function woocommerce_subscription_validate_payment_meta( $payment_meta, $subscription ) {
			if ( isset( $payment_meta['post_meta']['_pensopay_transaction_id']['value'] ) ) {
				$transaction_id = $payment_meta['post_meta']['_pensopay_transaction_id']['value'];
				// Validate only if the transaction ID has changed
				$sub_transaction_id = WC_PensoPay_Order_Utils::get_transaction_id( $subscription );
				if ( $transaction_id !== $sub_transaction_id ) {
					$transaction = new WC_PensoPay_API_Subscription();
					$transaction->get( $transaction_id );

					// If transaction could be found, add a note on the order for history and debugging reasons.
					$subscription->add_order_note( sprintf( __( 'PensoPay Transaction ID updated from #%d to #%d', 'woo-pensopay' ), $sub_transaction_id, $transaction_id ), 0, true );
				}
			}
		}

		/**
		 * Triggered when customers are changing payment method to PensoPay.
		 *
		 * @param $new_payment_method
		 * @param $subscription
		 * @param $old_payment_method
		 */
		public function on_subscription_payment_method_updated_to_pensopay( $subscription, $old_payment_method ): void {
			WC_PensoPay_Order_Payments_Utils::increase_payment_method_change_count( $subscription );
		}


		/**
		 * Cancels a transaction when the subscription is cancelled
		 *
		 * @param WC_Order $order - WC_Order object
		 *
		 * @return void
		 */
		public function subscription_cancellation( WC_Order $order ): void {
			if ( 'cancelled' !== $order->get_status() ) {
				return;
			}

			try {
				if ( WC_PensoPay_Subscription::is_subscription( $order ) && apply_filters( 'woocommerce_pensopay_allow_subscription_transaction_cancellation', true, $order, $this ) ) {
					$transaction_id = WC_PensoPay_Order_Utils::get_transaction_id( $order );


					$subscription = new WC_PensoPay_API_Subscription();
					$subscription->get( $transaction_id );

					if ( $subscription->is_action_allowed( 'cancel' ) ) {
						$subscription->cancel( $transaction_id );
					}
				}
			} catch ( PensoPay_Exception $e ) {
				$e->write_to_logs();
			}
		}

		/**
		 * on_order_cancellation function.
		 *
		 * Is called when a customer cancels the payment process from the PensoPay payment window.
		 *
		 * @access public
		 * @return void
		 */
		public function on_order_cancellation( $order_id ) {
			$order = new WC_Order( $order_id );

			// Redirect the customer to account page if the current order is failed
			if ( $order->get_status() === 'failed' ) {
				$payment_failure_text = sprintf( __( '<p><strong>Payment failure</strong> A problem with your payment on order <strong>#%i</strong> occured. Please try again to complete your order.</p>', 'woo-pensopay' ), $order_id );

				wc_add_notice( $payment_failure_text, 'error' );

				wp_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
			}

			$order->add_order_note( __( 'PensoPay Payment', 'woo-pensopay' ) . ': ' . __( 'Cancelled during process', 'woo-pensopay' ) );

			wc_add_notice( __( '<p><strong>%s</strong>: %s</p>', __( 'Payment cancelled', 'woo-pensopay' ), __( 'Due to cancellation of your payment, the order process was not completed. Please fulfill the payment to complete your order.', 'woo-pensopay' ) ), 'error' );
		}

		/**
		 * Is called after a payment has been submitted in the PensoPay payment window.
		 *
		 * @return void
		 */
		public function callback_handler(): void {
			// Get callback body
			$request_body = file_get_contents( "php://input" );

			// Decode the body into JSON
			$json = json_decode( $request_body, false, 512, JSON_THROW_ON_ERROR );

			// Instantiate payment object
			$payment = new WC_PensoPay_API_Payment( $json );

            // Fetch order number;
            $order_number = WC_PensoPay_Callbacks::get_order_id_from_callback( $json );

            // Fetch subscription post ID if present
            $subscription_id = WC_PensoPay_Callbacks::get_subscription_id_from_callback( $json );
			$subscription    = null;
			if ( $subscription_id !== null ) {
				$subscription = woocommerce_pensopay_get_subscription( $subscription_id );
			}

            if ( $payment->is_authorized_callback( $request_body ) ) {
				// Instantiate order object
	            $order = woocommerce_pensopay_get_order( $order_number );

				// Get last transaction in operation history
				$transaction = end( $json->operations );

				// Is the transaction accepted and approved by QP / Acquirer?
				// Did we find an order?
				if ( $json->accepted && $order && in_array($transaction->qp_status_code, [WC_PensoPay_VirtualTerminal_Payment::STATUS_APPROVED, WC_PensoPay_VirtualTerminal_Payment::STATUS_3D_SECURE_REQUIRED], false) ) {
					// Overwrite the order object to inherit specific PensoPay logic
					$order = new WC_PensoPay_Order( $order->get_id() );

					do_action( 'woocommerce_pensopay_accepted_callback_before_processing', $order, $json );
					do_action( 'woocommerce_pensopay_accepted_callback_before_processing_status_' . $transaction->type, $order, $json );

					// Perform action depending on the operation status type
					try {
						switch ( $transaction->type ) {
							//
							// Cancel callbacks are currently not supported by the PensoPay API
							//
							case 'cancel' :
								if ( $subscription_id !== null && $subscription ) {
                                    do_action( 'woocommerce_pensopay_callback_subscription_cancelled', $subscription, $order, $transaction, $json );
                                }
								// Write a note to the order history
								$order->note( __( 'Payment cancelled.', 'woo-pensopay' ) );
								break;

							case 'capture' :
                                WC_PensoPay_Callbacks::payment_captured( $order, $json );
								break;

							case 'refund' :
								$order->note( sprintf( __( 'Refunded %s %s', 'woo-pensopay' ), WC_PensoPay_Helper::price_normalize( $transaction->amount, $json->currency ), $json->currency ) );
                                break;

                            case 'recurring':
                                WC_PensoPay_Callbacks::payment_authorized( $order, $json );
                                break;

							case 'authorize' :
								WC_PensoPay_Callbacks::authorized( $order, $json );

								// Subscription authorization
								if ( $subscription_id !== null && isset( $subscription ) && strtolower( $json->type ) === 'subscription' ) {
									// Write log
									WC_PensoPay_Callbacks::subscription_authorized( $subscription, $order, $json );
								} // Regular payment authorization
								else {
									WC_PensoPay_Callbacks::payment_authorized( $order, $json );
								}
								break;
						}

						do_action( 'woocommerce_pensopay_accepted_callback', $order, $json );
						do_action( 'woocommerce_pensopay_accepted_callback_status_' . $transaction->type, $order, $json );

					} catch ( PensoPay_API_Exception $e ) {
						$e->write_to_logs();
					}
				}

				// The transaction was not accepted.
				// Print debug information to logs
				else {
					// Write debug information
					$this->log->add( [
						'order'          => $order_number,
						'qp_status_code' => $transaction->qp_status_code,
						'qp_status_msg'  => $transaction->qp_status_msg,
						'aq_status_code' => $transaction->aq_status_code,
						'aq_status_msg'  => $transaction->aq_status_msg,
						'request'        => $request_body,
					] );

                    if ( $order && ( $transaction->type === 'recurring' || 'rejected' !== $json->state ) ) {
                        $order->update_status( 'failed', sprintf( 'Payment failed <br />Pensopay Message: %s<br />Acquirer Message: %s', $transaction->qp_status_msg, $transaction->aq_status_msg ) );
                    }
				}
			} else {
				$this->log->add( sprintf( __( 'Invalid callback body for order #%s.', 'woo-pensopay' ), $order_number ) );
			}
		}

		/**
		 * @param WC_Order $order
		 * @param                   $json
		 */
		public function callback_update_transaction_cache( WC_Order $order, $json ): void {
            try {
                // Instantiating a payment transaction.
                // The type of transaction is currently not important for caching - hence no logic for handling subscriptions is added.
                $transaction = new WC_PensoPay_API_Payment( $json );
                $transaction->cache_transaction();
            } catch ( PensoPay_Exception $e ) {
                $this->log->add( sprintf( 'Could not cache transaction from callback for order: #%s -> %s', $order->get_id(), $e->getMessage() ) );
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
		public function init_form_fields() {
			$this->form_fields = WC_PensoPay_Settings::get_fields();
		}


		/**
		 * @param array $form_fields
		 * @param bool $echo
		 *
		 * @return string|void
		 */
		public function generate_settings_html( $form_fields = array(), $echo = true ) {
			$html  = "<h3>PensoPay - {$this->id}, v" . WCPP_VERSION . "</h3>";
			$html .= "<p>" . __( 'Allows you to receive payments via Pensopay.', 'woo-pensopay' ) . "</p>";
			$html .= WC_PensoPay_Settings::clear_logs_section();

            ob_start();
            do_action( 'woocommerce_pensopay_settings_table_before' );
            $html .= ob_get_clean();

            $html .= parent::generate_settings_html( $form_fields, $echo );

            ob_start();
            do_action( 'woocommerce_pensopay_settings_table_after' );
            $html .= ob_get_clean();

            if ( $echo ) {
                echo $html; // WPCS: XSS ok.
            } else {
                return $html;
            }
		}


		/**
		 * add_meta_boxes function.
		 *
		 * Adds the action meta box inside the single order view.
		 *
		 * @access public
		 * @return void
		 */
		public function add_meta_boxes() {
			global $post;

			$screen     = get_current_screen();
			$post_types = [ 'shop_order', 'shop_subscription' ];

			if ( in_array( $screen->id, $post_types, true ) && in_array( $post->post_type, $post_types, true ) ) {
				$order = new WC_PensoPay_Order( $post->ID );
				if ( WC_PensoPay_Order_Payments_Utils::is_order_using_pensopay( $order ) ) {
					add_meta_box( 'pensopay-payment-actions', __( 'PensoPay Payment', 'woo-pensopay' ), [
						&$this,
						'meta_box_payment',
					], 'shop_order', 'side', 'high' );
					add_meta_box( 'pensopay-payment-actions', __( 'PensoPay Subscription', 'woo-pensopay' ), [
						&$this,
						'meta_box_subscription',
					], 'shop_subscription', 'side', 'high' );
				}
			}
		}


		/**
		 * meta_box_payment function.
		 *
		 * Inserts the content of the API actions meta box - Payments
		 *
		 * @access public
		 * @return void
		 */
		public function meta_box_payment() {
			global $post;
			$order = new WC_PensoPay_Order( $post->ID );

			$transaction_id = $order->get_transaction_id();

			do_action( 'woocommerce_pensopay_meta_box_payment_before_content', $order );
			if ( $transaction_id && WC_PensoPay_Order_Payments_Utils::is_order_using_pensopay( $order ) ) {
				$state = null;
				try {
                    $originalLanguage = $this->maybe_change_language($order);

					$transaction = new WC_PensoPay_API_Payment();
					$transaction->get( $transaction_id );
					$transaction->cache_transaction();

                    $this->maybe_restore_language($originalLanguage);

					$state = $transaction->get_state();

					try {
						$status = $transaction->get_current_type();
					} catch ( PensoPay_API_Exception $e ) {
						if ( $state !== 'initial' ) {
							throw new PensoPay_API_Exception( $e->getMessage() );
						}

						$status = $state;
					}

					echo "<p class=\"woocommerce-pensopay-{$status}\"><strong>" . __( 'Current payment state', 'woo-pensopay' ) . ": " . $status . "</strong></p>";

					if ( $transaction->is_action_allowed( 'standard_actions' ) ) {
						echo "<h4><strong>" . __( 'Actions', 'woo-pensopay' ) . "</strong></h4>";
						echo "<ul class=\"order_action\">";

						if ( $transaction->is_action_allowed( 'capture' ) ) {
                            echo "<li class=\"pp-full-width\"><a class=\"button button-primary\" data-action=\"capture\" data-confirm=\"" . __( 'You are about to capture this payment', 'woo-pensopay' ) . "\">" . sprintf( __( 'Capture Full Amount (%s)', 'woo-pensopay' ), wc_price( $transaction->get_remaining_balance_as_float(), [ 'currency' => $transaction->get_currency() ] ) ) . "</a></li>";
						}

						printf( "<li class=\"pp-balance\"><span class=\"pp-balance__label\">%s:</span><span class=\"pp-balance__amount\"><span class='pp-balance__currency'>%s</span>%s</span></li>", __( 'Remaining balance', 'woo-pensopay' ), $transaction->get_currency(), $transaction->get_formatted_remaining_balance() );

						if ( $transaction->is_action_allowed( 'capture' ) ) {
                            printf( "<li class=\"pp-balance last\"><span class=\"pp-balance__label\">%s:</span><span class=\"pp-balance__amount\"><span class='pp-balance__currency'>%s</span><input id='pp-balance__amount-field' type='text' value='%s' /></span></li>", __( 'Capture amount', 'woo-pensopay' ), $transaction->get_currency(), $transaction->get_formatted_remaining_balance() );
							echo "<li class=\"pp-full-width\"><a class=\"button\" data-action=\"captureAmount\" data-confirm=\"" . __( 'You are about to capture this payment', 'woo-pensopay' ) . "\">" . __( 'Capture Specified Amount', 'woo-pensopay' ) . "</a></li>";
						}


						if ( $transaction->is_action_allowed( 'cancel' ) ) {
							echo "<li class=\"pp-full-width\"><a class=\"button\" data-action=\"cancel\" data-confirm=\"" . __( 'You are about to CANCEL this payment', 'woo-pensopay' ) . "\">" . __( 'Cancel', 'woo-pensopay' ) . "</a></li>";
						}

						echo "</ul>";
					}

					printf( '<p><small><strong>%s:</strong> %d <span class="pp-meta-card"><img src="%s" /></span></small>', __( 'Transaction ID', 'woo-pensopay' ), $transaction_id, WC_Pensopay_Helper::get_payment_type_logo( $transaction->get_brand() ) );

					$transaction_order_id = WC_PensoPay_Order_Payments_Utils::get_transaction_order_id( $order );
					if ( isset( $transaction_order_id ) && ! empty( $transaction_order_id ) ) {
						printf( '<p><small><strong>%s:</strong> %s</small>', __( 'Transaction Order ID', 'woo-pensopay' ), $transaction_order_id );
					}
				} catch ( PensoPay_API_Exception $e ) {
					$e->write_to_logs();
					if ( $state !== 'initial' ) {
						$e->write_standard_warning();
					}
				} catch ( PensoPay_Exception $e ) {
					$e->write_to_logs();
					if ( $state !== 'initial' ) {
						$e->write_standard_warning();
					}
				}
			}

			// Show payment ID and payment link for orders that have not yet
			// been paid. Show this information even if the transaction ID is missing.
			$payment_id = $order->get_payment_id();
			if ( isset( $payment_id ) && ! empty( $payment_id ) ) {
				printf( '<p><small><strong>%s:</strong> %d</small>', __( 'Payment ID', 'woo-pensopay' ), $payment_id );
			}

			$payment_link = $order->get_payment_link();
			if ( isset( $payment_link ) && ! empty( $payment_link ) ) {
				printf( '<p><small><strong>%s:</strong> <br /><input type="text" style="%s"value="%s" readonly /></small></p>', __( 'Payment Link', 'woo-pensopay' ), 'width:100%', $payment_link );
			}

			do_action( 'woocommerce_pensopay_meta_box_payment_after_content', $order );
		}


		/**
		 * meta_box_payment function.
		 *
		 * Inserts the content of the API actions meta box - Subscriptions
		 *
		 * @access public
		 * @return void
		 */
		public function meta_box_subscription() {
			global $post;
			$order = new WC_PensoPay_Order( $post->ID );

			$transaction_id = $order->get_transaction_id();
			$state          = null;

			do_action( 'woocommerce_pensopay_meta_box_subscription_before_content', $order );

			if ( $transaction_id && WC_PensoPay_Order_Payments_Utils::is_order_using_pensopay( $order ) ) {
				try {

					$transaction = new WC_PensoPay_API_Subscription();
					$transaction->get( $transaction_id );
					$status = null;
					$state  = $transaction->get_state();
					try {
						$status = $transaction->get_current_type() . ' (' . __( 'subscription', 'woo-pensopay' ) . ')';
					} catch ( PensoPay_API_Exception $e ) {
						if ( 'initial' !== $state ) {
							throw new PensoPay_API_Exception( $e->getMessage() );
						}
						$status = $state;
					}

					echo "<p class=\"woocommerce-pensopay-{$status}\"><strong>" . __( 'Current payment state', 'woo-pensopay' ) . ": " . $status . "</strong></p>";

					printf( '<p><small><strong>%s:</strong> %d <span class="pp-meta-card"><img src="%s" /></span></small>', __( 'Transaction ID', 'woo-pensopay' ), $transaction_id, WC_Pensopay_Helper::get_payment_type_logo( $transaction->get_brand() ) );

					$transaction_order_id = WC_PensoPay_Order_Payments_Utils::get_transaction_order_id( $order );
					if ( isset( $transaction_order_id ) && ! empty( $transaction_order_id ) ) {
						printf( '<p><small><strong>%s:</strong> %s</small>', __( 'Transaction Order ID', 'woo-pensopay' ), $transaction_order_id );
					}
				} catch ( PensoPay_API_Exception $e ) {
					$e->write_to_logs();
					if ( 'initial' !== $state ) {
						$e->write_standard_warning();
					}
				}
			}

			do_action( 'woocommerce_pensopay_meta_box_subscription_after_content', $order );
		}


		/**
		 * email_instructions function.
		 *
		 * Adds custom text to the order confirmation email.
		 *
		 * @param WC_Order $order
		 * @param boolean $sent_to_admin
		 *
		 * @return void /string/void
		 */
		public function email_instructions( $order, $sent_to_admin ) {
			$payment_method = $order->get_payment_method();

			if ( $sent_to_admin || ( $order->get_status() !== 'processing' && $order->get_status() !== 'completed' ) || $payment_method !== 'pensopay' ) {
				return;
			}

			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}

		/**
		 * Adds a separate column for payment info
		 *
		 * @param array $show_columns
		 *
		 * @return array
		 */
		public function filter_shop_order_posts_columns( $show_columns ) {
			$column_name   = 'pensopay_transaction_info';
			$column_header = __( 'Payment', 'woo-pensopay' );

			return WC_PensoPay_Helper::array_insert_after( 'shipping_address', $show_columns, $column_name, $column_header );
		}

		/**
		 * apply_custom_order_data function.
		 *
		 * Applies transaction ID and state to the order data overview
		 *
		 * @access public
		 * @return void
		 */
		public function apply_custom_order_data( $column ) {
			global $post, $woocommerce;

			$order = new WC_PensoPay_Order( $post->ID );
            $originalLanguage = false;
			// Show transaction ID on the overview
			if ( ( $post->post_type == 'shop_order' && $column == 'pensopay_transaction_info' ) || ( $post->post_type == 'shop_subscription' && $column == 'order_title' ) ) {
				// Insert transaction id and payment status if any
				$transaction_id = $order->get_transaction_id();

				try {
					if ( $transaction_id && WC_PensoPay_Order_Payments_Utils::is_order_using_pensopay( $order ) ) {

						if ( WC_PensoPay_Subscription::is_subscription( $post->ID ) ) {
							$transaction = new WC_PensoPay_API_Subscription();
						} else {
							$transaction = new WC_PensoPay_API_Payment();
						}

                        $originalLanguage = $this->maybe_change_language($order);

						// Get transaction data
						$transaction->maybe_load_transaction_from_cache( $transaction_id );

                        $this->maybe_restore_language($originalLanguage);

                        if ( WC_PensoPay_Order_Utils::is_failed_renewal( $order ) ) {
							$status = __( 'Failed renewal', 'woo-pensopay' );
						} else {
							$status = $transaction->get_current_type();
						}

                        $brand = $transaction->get_brand();

						WC_PensoPay_Views::get_view( 'html-order-table-transaction-data.php', [
							'transaction_id'             => $transaction_id,
							'transaction_order_id'       => WC_PensoPay_Order_Payments_Utils::get_transaction_order_id( $order ),
							'transaction_brand'          => $transaction->get_brand(),
							'transaction_brand_logo_url' => WC_PensoPay_Helper::get_payment_type_logo( $brand ? $brand : $transaction->get_acquirer() ),
							'transaction_status'         => $status,
							'transaction_is_test'        => $transaction->is_test(),
							'is_cached'                  => $transaction->is_loaded_from_cached(),
						] );
					}
				} catch ( PensoPay_API_Exception $e ) {
					$this->log->add( sprintf( 'Order list: #%s - %s', $order->get_id(), $e->getMessage() ) );
				} catch ( PensoPay_Exception $e ) {
					$this->log->add( sprintf( 'Order list: #%s - %s', $order->get_id(), $e->getMessage() ) );
				} finally {
                    $this->maybe_restore_language($originalLanguage);
                }
			}
		}

		/**
		 * FILTER: apply_gateway_icons function.
		 *
		 * Sets gateway icons on frontend
		 *
		 * @access public
		 * @return void
		 */
		public function apply_gateway_icons( $icon, $id ) {
			if ( $id == $this->id ) {
				$icon = '';

				$icons = $this->s( 'pensopay_icons' );

				if ( ! empty( $icons ) ) {
					$icons_maxheight = $this->gateway_icon_size();

					foreach ( $icons as $key => $item ) {
						$icon .= $this->gateway_icon_create( $item, $icons_maxheight );
					}
				}
			}

			return $icon;
		}


		/**
		 * gateway_icon_create
		 *
		 * Helper to get the a gateway icon image tag
		 *
		 * @access protected
		 * @return string
		 */
		protected function gateway_icon_create( $icon, $max_height ) {
            $icon = str_replace( 'pensopay_', '', $icon );

            $icon = apply_filters( 'woocommerce_pensopay_checkout_gateway_icon', $icon );

			if ( file_exists( __DIR__ . '/assets/images/cards/' . $icon . '.svg' ) ) {
				$icon_url = WC_HTTPS::force_https_url( plugin_dir_url( __FILE__ ) . 'assets/images/cards/' . $icon . '.svg' );
			} else {
				$icon_url = WC_HTTPS::force_https_url( plugin_dir_url( __FILE__ ) . 'assets/images/cards/' . $icon . '.png' );
			}

			$icon_url = apply_filters( 'woocommerce_pensopay_checkout_gateway_icon_url', $icon_url, $icon );

			return '<img src="' . $icon_url . '" alt="' . esc_attr( $this->get_title() ) . '" style="max-height:' . $max_height . '"/>';
		}


		/**
		 * gateway_icon_size
		 *
		 * Helper to get the a gateway icon image max height
		 *
		 * @access protected
		 * @return void
		 */
		protected function gateway_icon_size() {
			$settings_icons_maxheight = $this->s( 'pensopay_icons_maxheight' );

			return ! empty( $settings_icons_maxheight ) ? $settings_icons_maxheight . 'px' : '20px';
		}


		/**
		 *
		 * get_gateway_language
		 *
		 * Returns the gateway language
		 *
		 * @access public
		 * @return string
		 */
		public function get_gateway_language() {

			$language = apply_filters( 'woocommerce_pensopay_language', $this->s( 'pensopay_language' ) );

			return $language;
		}

		/**
		 * Registers custom bulk actions
		 */
		public function register_bulk_actions() {
			global $post_type;

			if ( $post_type === 'shop_order' && WC_PensoPay_Subscription::plugin_is_active() ) {
				WC_PensoPay_Views::get_view( 'bulk-actions.php' );
			}
		}

		/**
		 * Handles custom bulk actions
		 */
		public function handle_bulk_actions() {
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );

			$action = $wp_list_table->current_action();

			// Check for posts
			if ( ! empty( $_GET['post'] ) ) {
				$order_ids = $_GET['post'];

				// Make sure the $posts variable is an array
				if ( ! is_array( $order_ids ) ) {
					$order_ids = [ $order_ids ];
				}
			}

			if ( current_user_can( 'manage_woocommerce' ) ) {
				switch ( $action ) {
					// 3. Perform the action
					case 'pensopay_capture_recurring':
						// Security check
						$this->bulk_action_pensopay_capture_recurring( $order_ids );

						// Redirect client
						wp_redirect( $_SERVER['HTTP_REFERER'] );
						exit;
						break;

					default:
						return;
				}
			}
		}

		/**
		 * @param array $order_ids
		 */
		public function bulk_action_pensopay_capture_recurring( $order_ids = [] ) {
			if ( ! empty( $order_ids ) ) {
				foreach ( $order_ids as $order_id ) {
					$order          = new WC_PensoPay_Order( $order_id );
					$payment_method = $order->get_payment_method();
					if ( WC_PensoPay_Subscription::is_renewal( $order ) && $order->needs_payment() && $payment_method === $this->id ) {
						$this->scheduled_subscription_payment( $order->get_total(), $order );
					}
				}
			}
		}

		public function reset_failed_a( $order_id ) {
            // Instantiate new order object
            $order = new WC_PensoPay_Order( $order_id );
        }

		public function maybe_change_language($order)
        {
            global $sitepress;

            if (function_exists('icl_translate') && $sitepress) {
                $restoreLang = null;
                $order_lang = $order->get_meta('wpml_language');
                $currentLanguage = $sitepress->get_current_language();
                if ($currentLanguage !== $order_lang) {
                    $sitepress->switch_lang($order_lang);
                    return $currentLanguage;
                }
            }
            return false;
        }

        public function maybe_restore_language($language)
        {
            global $sitepress;

            if (function_exists('icl_translate') && $sitepress) {
                if ($language) {
                    $sitepress->switch_lang($language);
                }
            }
        }

		/**
		 *
		 * in_plugin_update_message
		 *
		 * Show plugin changes. Code adapted from W3 Total Cache.
		 *
		 * @param $args
		 *
		 * @return void
		 */
		public static function in_plugin_update_message( $args ) {
			$transient_name = 'wcPp_upgrade_notice_' . $args['Version'];
			if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {
				$response = wp_remote_get( 'https://plugins.svn.wordpress.org/woo-pensopay/trunk/README.txt' );

				if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
					$upgrade_notice = self::parse_update_notice( $response['body'] );
					set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
				}
			}

			echo wp_kses_post( $upgrade_notice );
		}

		/**
		 *
		 * parse_update_notice
		 *
		 * Parse update notice from readme file.
		 *
		 * @param string $content
		 *
		 * @return string
		 */
		private static function parse_update_notice( $content ) {
			// Output Upgrade Notice
			$matches        = null;
			$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( WCPP_VERSION, '/' ) . '\s*=|$)~Uis';
			$upgrade_notice = '';

			if ( preg_match( $regexp, $content, $matches ) ) {
				$version = trim( $matches[1] );
				$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

				if ( version_compare( WCPP_VERSION, $version, '<' ) ) {

					$upgrade_notice .= '<div class="wc_plugin_upgrade_notice">';

					foreach ( $notices as $index => $line ) {
						$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) );
					}

					$upgrade_notice .= '</div> ';
				}
			}

			return wp_kses_post( $upgrade_notice );
		}

		/**
		 * path
		 *
		 * Returns a plugin URL path
		 *
		 * @param $path
		 *
		 * @return mixed
		 */
		public function plugin_url( $path ) {
			return plugins_url( $path, __FILE__ );
		}

        /**
         * @param string $path
         *
         * @return string
         */
        public function plugin_path( string $path ): string {
            return __DIR__ . $path;
        }
	}

	/**
	 * Make the object available for later use
	 *
	 * @return WC_PensoPay
	 */
	function WC_PP() {
		return WC_PensoPay::get_instance();
	}

	// Instantiate
	WC_PP();
	WC_PP()->hooks_and_filters();

	// Add the gateway to WooCommerce
	function add_pensopay_gateway( $methods ) {
		$methods[] = 'WC_PensoPay';

        WC_PensoPay_Statekeeper::$gateways_added = true;

		return apply_filters( 'woocommerce_pensopay_load_instances', $methods );
	}

	add_filter( 'woocommerce_payment_gateways', 'add_pensopay_gateway' );
	add_filter( 'woocommerce_pensopay_load_instances', 'WC_PensoPay::filter_load_instances' );
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'WC_PensoPay::add_action_links' );
}

if (!class_exists( 'WC_PensoPay_VirtualTerminal_Payment' )) {
    require_once WCPP_PATH . 'classes/woocommerce-pensopay-virtualterminal-payment.php';
}

add_action('admin_menu', function($t) {
    if (is_admin() && current_user_can('manage_woocommerce')) {
        add_submenu_page('woo-virtualterminal', __('New Payment', 'woo-pensopay'),
            __('New Payment', 'woo-pensopay'), 'manage_woocommerce', 'pensopay-virtualterminal-payment', array(
                WC_PensoPay_VirtualTerminal_Payment::get_instance(),
                'render'
            ));
    }
}, 90, 1);
add_action('init', array(WC_PensoPay_VirtualTerminal_Payment::class, 'register_post_types'), 9);

//add_filter('cron_schedules', 'add_penso_cron');
//function add_penso_cron($schedules)
//{
//    $schedules['minute'] = array(
//        'interval' => 1, //*60
//        'display' => esc_html__('Every Minute'),
//    );
//    return $schedules;
//}

add_action('pensopay_virtualpayments_update', 'WC_PensoPay_VirtualTerminal_Payment::vterminal_update_payments');
if (!wp_next_scheduled('pensopay_virtualpayments_update')) {
    wp_schedule_event(time(), 'daily', 'pensopay_virtualpayments_update');
}

register_deactivation_hook(__FILE__, 'pensopay_virtualpayments_update_deactivate');
function pensopay_virtualpayments_update_deactivate()
{
    $timestamp = wp_next_scheduled('pensopay_virtualpayments_update');
    wp_unschedule_event($timestamp, 'pensopay_virtualpayments_update');
}

if ( function_exists('icl_object_id') ) {
    add_filter( 'woocommerce_pensopay_language', 'tk_woocommerce_pensopay_language', 99);
}

function tk_woocommerce_pensopay_language() {
    // Get WPML language code and use for pensopay default language
    return ICL_LANGUAGE_CODE;
}

register_activation_hook( __FILE__, static function () {
	require_once WCPP_PATH . 'classes/woocommerce-pensopay-install.php';

	// Run the installer on the first install.
	if ( WC_PensoPay_Install::is_first_install() ) {
		WC_PensoPay_Install::install();
	}
} );

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
