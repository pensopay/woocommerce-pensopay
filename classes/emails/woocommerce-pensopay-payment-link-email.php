<?php

class WC_PensoPay_Payment_Link_Email extends WC_Email {

	private $wcpp_template_path;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->customer_email = true;
		$this->id             = 'woocommerce_pensopay_payment_link';
		$this->title          = __( 'Payment link created', 'woo-pensopay' );
		$this->description    = __( 'This e-mail is sent upon manual payment link creation by a shop admin.', 'woo-pensopay' );
		$this->template_html  = 'emails/customer-pensopay-payment-link.php';
		$this->template_plain = 'emails/plain/customer-pensopay-payment-link.php';
		$this->placeholders   = [
			'{site_title}'   => $this->get_blogname(),
			'{order_date}'   => '',
			'{order_number}' => '',
			'{payment_link}' => '',
		];

        $this->template_base = WCPP_PATH . 'templates/woocommerce/';
		$this->wcpp_template_path = WCPP_PATH . 'templates/woocommerce/';
		// Triggers for this email.

		// Call parent constructor.
		parent::__construct();
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param WC_Order $order
	 * @param null               $payment_link
	 */
	public function trigger( $payment_link, WC_Order $order ) {
		$this->setup_locale();

		$this->object                         = $order;
		$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
		$this->placeholders['{order_number}'] = $this->object->get_order_number();
		$this->placeholders['{payment_link}'] = $payment_link;
		$this->recipient                      = $this->object->get_billing_email();

		if ( $payment_link && $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * Get content html.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, [
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'         => $this,
			'payment_link'  => $this->placeholders['{payment_link}'],
		], '', $this->wcpp_template_path );
	}

	/**
	 * Get content plain.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, [
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'         => $this,
			'payment_link'  => $this->placeholders['{payment_link}'],
		], '', $this->wcpp_template_path );
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields(): void {
		$this->form_fields = [
			'enabled'    => [
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'woocommerce' ),
				'default' => 'yes',
			],
			'subject'    => [
				'title'       => __( 'Subject', 'woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => true,
				/* translators: %s: list of placeholders */
				'description' => sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			],
			'heading'    => [
				'title'       => __( 'Email heading', 'woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => true,
				/* translators: %s: list of placeholders */
				'description' => sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			],
			'email_type' => [
				'title'       => __( 'Email type', 'woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			],
		];
	}

	/**
	 * Get email subject.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Payment link for your order ({order_number})', 'woo-pensopay' );
	}

	/**
	 * Get email heading.
	 *
	 * @since  3.1.0
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'This is your payment link', 'woo-pensopay' );
	}
}
