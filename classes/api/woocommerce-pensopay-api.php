<?php
/**
 * WC_PensoPay_API class
 *
 * @class 		WC_PensoPay_API
 * @since		4.0.0
 * @package		Woocommerce_PensoPay/Classes
 * @category	Class
 * @author 		PensoPay
 */

class WC_PensoPay_API {

	/**
	 * Contains cURL instance
	 * @access protected
	 */
    protected $ch;

	/**
	 * Contains the API url
	 * @access protected
	 */
	protected $api_url = 'https://api.quickpay.net/';

	/**
	 * Contains a resource data object
	 * @access protected
	 */
	protected $resource_data;

	/**
	 * @var null
	 */
	protected $api_key = null;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $api_key = null ) {
		if ( empty( $api_key ) ) {
			$this->api_key = WC_PP()->s( 'pensopay_apikey' );
		} else {
			$this->api_key = $api_key;
		}

		// Instantiate an empty object ready for population
		$this->resource_data = new stdClass();
	}


	/**
	 * is_authorized_callback function.
	 *
	 * Performs a check on payment callbacks to see if it is legal or spoofed
	 *
	 * @access public
	 *
	 * @param $response_body
	 *
	 * @return boolean
	 */
	public function is_authorized_callback( $response_body ): bool {
		if ( ! isset( $_SERVER["HTTP_QUICKPAY_CHECKSUM_SHA256"] ) ) {
			return false;
		}

		$hash = hash_hmac( 'sha256', $response_body, WC_PP()->s( 'pensopay_privatekey' ) );

		return hash_equals($_SERVER["HTTP_QUICKPAY_CHECKSUM_SHA256"], $hash);
	}


	/**
	 * get function.
	 *
	 * Performs an API GET request
	 *
	 * @access public
	 *
	 * @param      $path
	 * @param bool $return_array
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 */
	public function get( $path, bool $return_array = false ) {
		// Instantiate a new instance
		$this->remote_instance();

		// Set the request params
		$this->set_url( $path );

		// Start the request and return the response
		return $this->execute( 'GET', [], $return_array );
	}


	/**
	 * Performs an API POST request
	 *
	 * @param       $path
	 * @param array $form
	 * @param bool $return_array
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 */
	public function post( $path, array $form = [], bool $return_array = false ) {
		// Instantiate a new instance
		$this->remote_instance( $this->get_post_id_from_form_object( $form ) );

		// Set the request params
		$this->set_url( $path );

		// Start the request and return the response
		return $this->execute( 'POST', $form, $return_array );
	}


	/**
	 * Performs an API PUT request
	 *
	 * @param       $path
	 * @param array $form
	 * @param bool $return_array
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 */
	public function put( $path, array $form = [], bool $return_array = false ) {
		// Instantiate a new instance
		$this->remote_instance( $this->get_post_id_from_form_object( $form ) );

		// Set the request params
		$this->set_url( $path );

		// Start the request and return the response
		return $this->execute( 'PUT', $form, $return_array );
	}

	/**
	 * Performs an API PUT request
	 *
	 * @param $path
	 * @param array $form
	 * @param bool $return_array
	 *
	 * @return object
	 * @throws PensoPay_API_Exception
	 */
	public function patch( $path, array $form = [], bool $return_array = false ) {
		// Instantiate a new instance
		$this->remote_instance( $this->get_post_id_from_form_object( $form ) );

		// Set the request params
		$this->set_url( $path );

		// Start the request and return the response
		return $this->execute( 'PATCH', $form, $return_array );
	}


	/**
	 * Executes the API request
	 *
	 * @param string $request_type
	 * @param array $form
	 * @param bool $return_array - if we want to retrieve an array with additional
	 *
	 * @return object|array
	 * @throws PensoPay_API_Exception
	 */
	public function execute( string $request_type, array $form = [], bool $return_array = false ) {
		// Set the HTTP request type
		curl_setopt( $this->ch, CURLOPT_CUSTOMREQUEST, $request_type );

		// Prepare empty variable passed to any exception thrown
		$request_form_data = '';
        $response_headers = [];

        curl_setopt( $this->ch, CURLOPT_HEADERFUNCTION,
            static function ( $curl, $header ) use ( &$response_headers ) {
                $len    = strlen( $header );
                $header = explode( ':', $header, 2 );
                if ( count( $header ) < 2 ) // ignore invalid headers
                {
                    return $len;
                }

                $response_headers[ strtolower( trim( $header[0] ) ) ][] = trim( $header[1] );

                return $len;
            }
        );

		// If additional data is delivered, we will send it along with the API request
		if ( is_array( $form ) && ! empty( $form ) ) {
			// Build a string query based on the form array values
			$request_form_data = preg_replace( '/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query( $form, '', '&' ) );

			// Prepare to post the data string
			curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $request_form_data );
		}
		// Execute the request and decode the response to JSON
		$this->resource_data = json_decode( curl_exec( $this->ch ) );
		// Retrieve the HTTP response code
		$response_code    = (int) curl_getinfo( $this->ch, CURLINFO_HTTP_CODE );
		$response_data    = json_encode( $this->resource_data );
		$curl_request_url = curl_getinfo( $this->ch, CURLINFO_EFFECTIVE_URL );

		// If the HTTP response code is higher than 299, the request failed.
		// Throw an exception to handle the error
		if ( $response_code > 299 ) {
			if ( isset( $this->resource_data->errors ) ) {
				$errors         = json_decode( json_encode( $this->resource_data ), true );
				$error_messages = [];

				if ( ! empty( $errors['message'] ) ) {
					$error_messages[] = $errors['message'];
				}

				if ( ! empty( $errors['errors'] ) && $errors['error_code'] === null ) {
					foreach ( $errors['errors'] as $field => $field_errors ) {
						foreach ( $field_errors as $field_error ) {
							$error_messages[] = sprintf( '- <strong>%s</strong>: %s', $field, $field_error );
						}
					}
				}

				throw new PensoPay_API_Exception( implode( "\n", $error_messages ), $response_code, null, $curl_request_url, $request_form_data, $response_data );
			} else if ( isset( $this->resource_data->message ) ) {
				throw new PensoPay_API_Exception( $this->resource_data->message, $response_code, null, $curl_request_url, $request_form_data, $response_data );
			} else {
				throw new PensoPay_API_Exception( (string) json_encode( $this->resource_data ), $response_code, null, $curl_request_url, $request_form_data, $response_data );
			}

		}
		// Everything went well, return the resource data object.
		if ( $return_array ) {
			$return_data = [
				$this->resource_data,
				$curl_request_url,
				$request_form_data,
				$response_data,
				curl_getinfo( $this->ch ),
                $response_headers
			];
		} else {
			$return_data = $this->resource_data;
		}

		curl_close( $this->ch );

		return $return_data;
	}


	/**
	 * set_url function.
	 *
	 * Takes an API request string and appends it to the API url
	 *
	 * @access public
	 * @return void
	 */
	public function set_url( $params ) {
        if ( strpos( $params, 'https://' ) === 0 && strpos( $params, '.quickpay.net/' ) !== false ) {
            $api_url = str_replace( $this->api_url, '', $params );
        } else {
            $api_url = $this->api_url . trim( $params, '/' );
        }

        curl_setopt( $this->ch, CURLOPT_URL, $api_url );
	}


	/**
	 * remote_instance function.
	 *
	 * Create a cURL instance if none exists already
	 *
	 * @access public
	 * @return false|resource
	 */
	protected function remote_instance( $post_id = null ) {
		$this->ch = curl_init();

		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );

		curl_setopt( $this->ch, CURLINFO_HEADER_OUT, true );

		$callback_url = ! apply_filters( 'woocommerce_pensopay_block_callback', false, $post_id ) ? WC_PensoPay_Helper::get_callback_url( $post_id ) : null;

		curl_setopt( $this->ch, CURLOPT_HTTPHEADER, [
			'Authorization: Basic ' . base64_encode( ':' . $this->api_key ),
			'Accept-Version: v10',
			'Accept: application/json',
			"QuickPay-Callback-Url: {$callback_url}"
		] );

		return $this->ch;
	}

	/**
	 * Returns the POST ID if available in the form data object
	 *
	 * @param  [type] $form_data [description]
	 *
	 * @return [type]            [description]
	 */
	public function get_post_id_from_form_object( $form_data ) {
		if ( array_key_exists( 'order_post_id', $form_data ) ) {
			return $form_data['order_post_id'];
		}

		return null;
	}
}
