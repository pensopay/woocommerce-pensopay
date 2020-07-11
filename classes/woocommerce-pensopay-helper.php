<?php

/**
 * WC_PensoPay_Helper class
 *
 * @class          WC_PensoPay_Helper
 * @version        1.0.0
 * @package        Woocommerce_PensoPay/Classes
 * @category       Class
 * @author         PensoPay
 */
class WC_PensoPay_Helper {

	const PENSOPAY_VAR_IFRAMEPAY = 'pensoPay';
	const PENSOPAY_VAR_IFRAMEPOLL = 'pensoPayPoll';
	const PENSOPAY_VAR_IFRAMECANCEL = 'pensoPayCancel';
	const PENSOPAY_VAR_IFRAMECONTINUE = 'pensoPayContinue';
	const PENSOPAY_VAR_ORDERID = 'order_id';
	const PENSOPAY_VAR_IFRAMESUCCESS = 'pensoPaySuccess';

    protected static function get_recurring_total($order)
    {
        $recurring_total = 0;

        foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'parent' ) ) as $subscription ) {

            // Find the total for all recurring items
            if ( empty( $product_id ) ) {
                $recurring_total += $subscription->get_total() + $subscription->get_total_discount(); //This behavior changed
            } else {
                // We want the discount for a specific item (so we need to find if this subscription contains that item)
                foreach ( $subscription->get_items() as $line_item ) {
                    if ( wcs_get_canonical_product_id( $line_item ) == $product_id ) {
                        $recurring_total += $subscription->get_total() + $subscription->get_total_discount();
                        break;
                    }
                }
            }
        }

        return $recurring_total;
    }

    public static function order_needs_payment( $needs_payment, $order, $valid_order_statuses ) {
        /**
         * We need to add an extra step here because of a WC_Subscriptions bug
         * Basically, we emulate WC_Subscriptions' check with a fix for actual recurring total
         */
        if (
            false === $needs_payment
            && 0 == $order->get_total()
            && in_array($order->get_status(), $valid_order_statuses)
            && wcs_order_contains_subscription($order)
            && self::get_recurring_total($order) > 0
            && 'yes' !== get_option(WC_Subscriptions_Admin::$option_prefix . '_turn_off_automatic_payments', 'no')
        ) {
            $needs_payment = true;
        }

        return $needs_payment;
    }

	public static function viabill_header()
	{
		$gateways = WC()->payment_gateways()->get_available_payment_gateways();
		if (isset($gateways['viabill']) && $gateways['viabill']->enabled) {
			$gateways['viabill']->viabill_header();
		}
	}

	/**
	 * price_normalize function.
	 *
	 * Returns the price with decimals. 1010 returns as 10.10.
	 *
	 * @access public static
	 *
	 * @param $price
	 *
	 * @return float
	 */
	public static function price_normalize( $price ) {
		return number_format( $price / 100, 2, wc_get_price_decimal_separator(), '' );
	}

	/**
	 * @param $price
	 *
	 * @return string
	 */
	public static function price_multiplied_to_float( $price ) {
		return number_format( $price / 100, 2, '.', '' );
	}

	/**
	 * Multiplies a custom formatted price based on the WooCommerce decimal- and thousand separators
	 *
	 * @param $price
	 *
	 * @return int
	 */
	public static function price_custom_to_multiplied( $price ) {
		$decimal_separator  = get_option( 'woocommerce_price_decimal_sep' );
		$thousand_separator = get_option( 'woocommerce_price_thousand_sep' );

		$price = str_replace( [ $thousand_separator, $decimal_separator ], [ '', '.' ], $price );

		return self::price_multiply( $price );
	}

	/**
	 * price_multiply function.
	 *
	 * Returns the price with no decimals. 10.10 returns as 1010.
	 *
	 * @access public static
	 *
	 * @param $price
	 *
	 * @return integer
	 */
	public static function price_multiply( $price ) {
		return number_format( $price * 100, 0, '', '' );
	}

	/**
	 * enqueue_javascript_backend function.
	 *
	 * @access public static
	 * @return void
	 */
	public static function enqueue_javascript_backend() {
//		if ( self::maybe_enqueue_admin_statics() ) { -- buggy conditional
			wp_enqueue_script( 'pensopay-backend', plugins_url( '/assets/javascript/backend.js', __DIR__ ), [ 'jquery' ], self::static_version() );
//			wp_localize_script( 'pensopay-backend', 'ajax_object', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
//		}

		wp_enqueue_script( 'pensopay-backend-notices', plugins_url( '/assets/javascript/backend-notices.js', __DIR__ ), [ 'jquery' ], self::static_version() );
		wp_localize_script( 'pensopay-backend-notices', 'wcppBackendNotices', [ 'flush' => admin_url( 'admin-ajax.php?action=woocommerce_pensopay_flush_runtime_errors' ) ] );
	}

	/**
	 * @return bool
	 */
	protected static function maybe_enqueue_admin_statics() {
		global $post;
		/**
		 * Enqueue on the settings page for the gateways
		 */
		if ( isset( $_GET['page'], $_GET['tab'], $_GET['section'] ) ) {
			if ( $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'checkout' && array_key_exists( $_GET['section'], array_merge( [ 'pensopay' => null ], WC_PensoPay::get_gateway_instances() ) ) ) {
				return true;
			}
		} /**
		 * Enqueue on the shop order page
		 */
		else if ( ! empty( $post ) && in_array( $post->post_type, [ 'shop_order', 'shop_subscription' ] ) ) {
			return true;
		}

		return false;
	}

	public static function static_version() {
		return 'wcpp-' . WCPP_VERSION;
	}


	/**
	 * enqueue_stylesheet function.
	 *
	 * @access public static
	 * @return void
	 */
	public static function enqueue_stylesheet() {
		wp_enqueue_style( 'woocommere-pensopay-style', plugins_url( '/assets/stylesheets/woocommerce-pensopay.css', __DIR__ ), [], self::static_version() );
	}


	/**
	 * load_i18n function.
	 *
	 * @access public static
	 * @return void
	 */
	public static function load_i18n() {
		load_plugin_textdomain( 'woo-pensopay', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
	}

	public static function determine_locale($locale)
    {
        if (isset($_POST['wc_order_action']) && $_POST['wc_order_action'] === 'pensopay_create_payment_link') {
            $post = $_POST['post_ID'];
            if ($post) {
                $lang = self::get_language_from_request_meta();
                if ($lang) {
                    switch ($lang) {
                        case 'da':
                            $locale = 'da_DK';
                            break;
                        default:
                            $locale = 'en_US';
                            break;
                    }
                }
            }
        }
        return $locale;
    }

    public static function get_language_from_request_meta()
    {
        if (isset($_POST['meta']) && is_array($_POST['meta'])) {
            foreach ($_POST['meta'] as $meta) {
                if (isset($meta['key']) && $meta['key'] === 'wpml_language' && isset($meta['value']) && !empty($meta['value'])) {
                    return $meta['value'];
                }
            }
        }
        return false;
    }


	/**
	 * option_is_enabled function.
	 *
	 * Checks if a setting options is enabled by checking on yes/no data.
	 *
	 * @access public static
	 *
	 * @param mixed $value
	 *
	 * @return int
	 */
	public static function option_is_enabled( $value ) {
		return ( $value === 'yes' ) ? 1 : 0;
	}


	/**
	 * get_callback_url function
	 *
	 * Returns the order's main callback url
	 *
	 * @access public
	 *
	 * @param null $post_id
	 *
	 * @return string
	 */
	public static function get_callback_url( $post_id = null ) {
		$args = [ 'wc-api' => 'WC_PensoPay' ];

		if ( $post_id !== null ) {
			$args['order_post_id'] = $post_id;
		}

		$args = apply_filters( 'woocommerce_pensopay_callback_args', $args, $post_id );

		return apply_filters( 'woocommerce_pensopay_callback_url', add_query_arg( $args, home_url( '/' ) ), $args, $post_id );
	}


	/**
	 * is_url function
	 *
	 * Checks if a string is a URL
	 *
	 * @access public
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public static function is_url( $url ) {
		return ! filter_var( $url, FILTER_VALIDATE_URL ) === false;
	}

	/**
	 * @param $payment_type
	 *
	 * @return null
	 * @since 4.5.0
	 *
	 */
	public static function get_payment_type_logo( $payment_type ) {
		$logos = [
			'american-express' => 'americanexpress.svg',
			'dankort'          => 'dankort.svg',
			'diners'           => 'diners.svg',
			'edankort'         => 'edankort.png',
			'fbg1886'          => 'forbrugsforeningen.svg',
			'jcb'              => 'jcb.svg',
			'maestro'          => 'maestro.svg',
			'mastercard'       => 'mastercard.svg',
			'mastercard-debet' => 'mastercard.svg',
			'mobilepay'        => 'mobilepay.svg',
			'visa'             => 'visa.svg',
			'visa-electron'    => 'visaelectron.png',
			'paypal'           => 'paypal.svg',
			'sofort'           => 'sofort.svg',
			'viabill'          => 'viabill.svg',
			'klarna'           => 'klarna.svg',
			'bank-axess'       => 'bankaxess.svg',
			'unionpay'         => 'unionpay.svg',
			'cirrus'           => 'cirrus.svg',
			'ideal'            => 'ideal.svg',
			'vipps'            => 'vipps.png',
		];

		if ( array_key_exists( trim( $payment_type ), $logos ) ) {
			return WC_PP()->plugin_url( 'assets/images/cards/' . $logos[ $payment_type ] );
		}

		return null;
	}

	/**
	 * Checks if WooCommerce Pre-Orders is active
	 */
	public static function has_preorder_plugin() {
		return class_exists( 'WC_Pre_Orders' );
	}

	/**
	 * @param      $value
	 * @param null $default
	 *
	 * @return null
	 */
	public static function value( $value, $default = null ) {
		if ( empty( $value ) ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Prevents qTranslate to make browser redirects resulting in missing callback data.
	 *
	 * @param $url_lang
	 * @param $url_orig
	 * @param $url_info
	 *
	 * @return bool
	 */
	public static function qtranslate_prevent_redirect( $url_lang, $url_orig, $url_info ) {
		// Prevent only on wc-api for this specific gateway
		if ( isset( $url_info['query'] ) && stripos( $url_info['query'], 'wc-api=wc_pensopay' ) !== false ) {
			return false;
		}

		return $url_lang;
	}

	/**
	 * @param $bypass
	 *
	 * @return bool
	 */
	public static function spamshield_bypass_security_check( $bypass ) {
		return isset( $_GET['wc-api'] ) && strtolower( $_GET['wc-api'] ) === 'wc_pensopay';
	}

	/**
	 * Inserts a new key/value after the key in the array.
	 *
	 * @param string $needle The array key to insert the element after
	 * @param array $haystack An array to insert the element into
	 * @param string $new_key The key to insert
	 * @param mixed $new_value An value to insert
	 *
	 * @return array The new array if the $needle key exists, otherwise an unmodified $haystack
	 */
	public static function array_insert_after( $needle, $haystack, $new_key, $new_value ) {

		if ( array_key_exists( $needle, $haystack ) ) {

			$new_array = [];

			foreach ( $haystack as $key => $value ) {

				$new_array[ $key ] = $value;

				if ( $key === $needle ) {
					$new_array[ $new_key ] = $new_value;
				}
			}

			return $new_array;
		}

		return $haystack;
	}
}
