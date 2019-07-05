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
	 */
	public static function price_custom_to_multiplied( $price ) {
		$decimal_separator  = get_option( 'woocommerce_price_decimal_sep' );
		$thousand_separator = get_option( 'woocommerce_price_thousand_sep' );

		$price = str_replace( array( $thousand_separator, $decimal_separator ), array( '', '.' ), $price );

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
		wp_enqueue_script( 'pensopay-backend', plugins_url( '/assets/javascript/backend.js', __DIR__ ), array( 'jquery' ), self::static_version() );
		wp_localize_script( 'pensopay-backend', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
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
		wp_enqueue_style( 'style', plugins_url( '/assets/stylesheets/woocommerce-pensopay.css', __DIR__ ), array(), self::static_version() );
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
		$args = array( 'wc-api' => 'WC_PensoPay' );

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
	 * @since 4.5.0
	 *
	 * @param $payment_type
	 *
	 * @return null
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
	 * @param string $needle    The array key to insert the element after
	 * @param array  $haystack  An array to insert the element into
	 * @param string $new_key   The key to insert
	 * @param mixed  $new_value An value to insert
	 *
	 * @return array The new array if the $needle key exists, otherwise an unmodified $haystack
	 */
	public static function array_insert_after( $needle, $haystack, $new_key, $new_value ) {

		if ( array_key_exists( $needle, $haystack ) ) {

			$new_array = array();

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
