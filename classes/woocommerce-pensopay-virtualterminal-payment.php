<?php

class WC_PensoPay_VirtualTerminal_Payment
{
	public static $instance = null;

	/** @var string POST_TYPE */
    const POST_TYPE = 'vterminal_payment';

    const POST_META_TYPES = [
        'order_id',
        'amount',
        'locale_code',
        'currency',
        'autocapture',
        'autofee',
        'customer_name',
        'customer_email',
        'customer_street',
        'customer_zipcode',
        'customer_city'
    ];

    const POST_META_RESTRICTED = [
        'state',
        'link',
        'order_id',
        'accepted',
        'amount_refunded',
        'amount_captured',
        'operations',
        'metadata',
        'fraud_probability',
        'hash',
        'acquirer'
    ];

    const STATE_INITIAL = 'initial';
    const STATE_NEW     = 'new';
    const STATE_PROCESSED = 'processed';
    const STATE_PENDING = 'pending';
    const STATE_REJECTED = 'rejected';

    const STATUS_APPROVED = 20000;
    const STATUS_WAITING_APPROVAL = 20200;
    const STATUS_3D_SECURE_REQUIRED = 30100;
    const STATUS_REJECTED_BY_ACQUIRER = 40000;
    const STATUS_REQUEST_DATA_ERROR = 40001;
    const STATUS_AUTHORIZATION_EXPIRED = 40002;
    const STATUS_ABORTED = 40003;
    const STATUS_GATEWAY_ERROR = 50000;
    const COMMUNICATIONS_ERROR_ACQUIRER = 50300;

    const OPERATION_CAPTURE = 'capture';
    const OPERATION_AUTHORIZE = 'authorize';
    const OPERATION_CANCEL = 'cancel';
    const OPERATION_REFUND = 'refund';

    const FRAUD_PROBABILITY_HIGH = 'high';
    const FRAUD_PROBABILITY_NONE = 'none';

    protected $_lastOperation = array();

    const STATUS_CODES =
        [
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_WAITING_APPROVAL => 'Waiting approval',
            self::STATUS_3D_SECURE_REQUIRED => '3D Secure is required',
            self::STATUS_REJECTED_BY_ACQUIRER => 'Rejected By Acquirer',
            self::STATUS_REQUEST_DATA_ERROR => 'Request Data Error',
            self::STATUS_AUTHORIZATION_EXPIRED => 'Authorization expired',
            self::STATUS_ABORTED => 'Aborted',
            self::STATUS_GATEWAY_ERROR => 'Gateway Error',
            self::COMMUNICATIONS_ERROR_ACQUIRER => 'Communications Error (with Acquirer)'
        ];

    /**
     * States in which the payment can't be updated anymore
     * Used for cron.
     */
    const FINALIZED_STATES =
        [
            self::STATE_REJECTED,
            self::STATE_PROCESSED
        ];

    public $_postid = null;

	public static function get_instance()
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
        add_action( 'add_meta_boxes', function() {
            remove_meta_box('slugdiv', self::POST_TYPE, 'normal'); // Slug
            remove_meta_box('submitdiv', self::POST_TYPE, 'side'); // Publish box
            add_meta_box('vterminal_payment_form', __('Virtual Terminal Payment', 'woo-pensopay'), 'WC_PensoPay_VirtualTerminal_Payment::output', self::POST_TYPE);
        }, 11);

        add_action('admin_footer', function() {
            global $post;
            if (is_object($post) && $post->post_type === self::POST_TYPE) {
                wp_enqueue_script('form_validation', plugins_url('../assets/javascript/jquery.validate.min.js', __FILE__));
                wp_enqueue_style('vterminal_payment', plugins_url('../assets/stylesheets/vterminal-payment.css', __FILE__));
            }
        }, 999);

        add_action('save_post', [$this, 'save_payment']);

        //Set and manage custom grid columns
        add_filter(sprintf('manage_edit-%s_columns', self::POST_TYPE), array($this, 'manage_columns'));
        add_action(sprintf('manage_%s_posts_custom_column', self::POST_TYPE), array($this, 'manage_column_data'), 10, 2);
        add_filter('bulk_actions-edit-' . self::POST_TYPE, array($this, 'register_bulk_actions'));
        add_filter('handle_bulk_actions-edit-' . self::POST_TYPE, array($this, 'bulk_action_handler'), 10, 3);
        add_action('admin_notices', array($this, 'bulk_action_admin_notices'));
	}

	public static function vterminal_update_payments()
    {
        $args = array(
            'post_type' => self::POST_TYPE,
            'post_status' => array('publish', 'pending', 'future', 'draft'),
            'orderby' => 'ID',
            'order' => 'ASC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'state',
                    'value' => self::FINALIZED_STATES,
                    'compare' => 'NOT IN'
                )
            )
        );

        $query = new WP_Query($args);
        foreach ($query->get_posts() as $post) {
               $payment = new WC_PensoPay_VirtualTerminal_Payment();
               $payment->set_post_id($post->ID);
               $payment->update_from_remote();
        }
    }

    public function set_post_id($id)
    {
        $this->_postid = $id;
    }

    public function register_bulk_actions($bulk_actions)
    {
        unset($bulk_actions['edit']);
        $bulk_actions['capture'] = __('Capture', 'woo-pensopay');
        $bulk_actions['refund'] = __('Refund', 'woo-pensopay');
        $bulk_actions['cancel'] = __('Cancel', 'woo-pensopay');
        return $bulk_actions;
    }

    public function bulk_action_handler($redirect_to, $doaction, $post_ids)
    {
        switch ($doaction) {
            case 'capture':
            case 'refund':
            case 'cancel':
                $success = 0;
                $errors = [];
                foreach ($post_ids as $post_id) {
                    try {
                        $this->_postid = $post_id;
                        if ($this->{'can_' . $doaction}()) {
                            $this->{$doaction}();
                            $this->update_from_remote();
                            $success++;
                        } else {
                            throw new \Exception(sprintf(__('Cannot %s this payment.', 'woo-pensopay'), $doaction));
                        }
                    } catch (\Exception $e) {
                        $errors[$post_id] = $e->getMessage();
                    }
                }
                add_user_meta(get_current_user_id(), 'success', $success);
                add_user_meta(get_current_user_id(), 'errors', $errors);
                break;
            default:
                return $redirect_to;
        }
        return $redirect_to;
    }

    public function bulk_action_admin_notices()
    {
        include WCPP_PATH . 'templates/admin/virtualterminal/grid-notices.php';
    }

    public function manage_columns($columns)
    {
        unset($columns['title'], $columns['date']);
        $columns = array_merge($columns, [
            'order_id' => __('Order ID', 'woo-pensopay'),
            'amount' => __('Amount', 'woo-pensopay'),
            'customer_name' => __('Customer Name', 'woo-pensopay'),
            'customer_email' => __('Customer Email', 'woo-pensopay'),
            'state' => __('State', 'woo-pensopay'),
            'link' => __('Payment Link', 'woo-pensopay'),
            'date' => __('Date', 'woo-pensopay')
        ]);

        return $columns;
    }

    public function manage_column_data($column, $post_id)
    {
        $this->_postid = $post_id;
        switch ($column) {
            case 'order_id':
                echo sprintf('<a href="%s">%s</a>', get_edit_post_link($post_id), $this->get_post_data('order_id'));
                break;
            case 'amount':
                echo sprintf('%s %s', $this->get_post_data('currency'), $this->get_post_data('amount'));
                break;
            case 'state':
                echo sprintf('<span class="payment-status %s">%s</span>', $this->get_status_color_code($this->get_last_code()), $this->get_display_status());
                break;
            case 'link':
                echo sprintf('<a id="payLink" target="_blank" href="%s">%s</a>', $this->get_post_data('link'), __('Link', 'woo-pensopay'));
                break;
            default:
                echo $this->get_post_data($column);
                break;
        }
    }

	public function save_payment($post_id)
    {
        if (isset($_POST['post_type']) && $_POST['post_type'] === self::POST_TYPE && is_admin()) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (!wp_verify_nonce($_POST['pensopay_nonce'], 'save_post')) {
                return;
            }

            $this->_postid = $post_id;

            $doEmail = false;
            switch ($_POST['method_type']) {
                case 'save_and_pay':
                    $this->save_from_request();
                    break;
                case 'save_and_send':
                    $this->save_from_request();
                    $doEmail = true;
                    break;
                case 'capture':
                    if ($this->can_capture()) {
                        $this->capture();
                    }
                    return;
                case 'refund':
                    if ($this->can_refund()) {
                        $this->refund();
                    }
                    return;
                    break;
                case 'cancel':
                    if ($this->can_cancel()) {
                        $this->cancel();
                    }
                    return;
                    break;
                case 'update_status':
                    $this->update_from_remote();
                    return;
                default:
                    return;
            }

            $reference_id = $this->get_post_data('reference_id');
            if (!$reference_id) {
                $payment = $this->create_payment();
            } else {
                $payment = $this->patch_payment();
            }
            $this->import_from_remote($payment);

            $link = $this->create_payment_link();
            if ($doEmail) {
                if (!$this->_doEmail()) {
                    $this->set_post_meta('error_message', __('An error occured sending the email.', 'woo-pensopay'));
                }
            } else {
                $this->set_post_meta('pay_link', $link);
            }
        }
    }

    protected function save_from_request()
    {
        //if is editable
        foreach (self::POST_META_TYPES as $META_TYPE) {
            if (isset($_POST[$META_TYPE])) {
                update_post_meta($this->_postid, $META_TYPE, $_POST[$META_TYPE]);
            }
        }
    }

	public static function register_post_types()
    {
        $result = register_post_type(self::POST_TYPE,
            array(
                'labels' => array(
                    'name'                  => __('Virtual Terminal Payments'),
                    'singular_name'         => __('Virtual Terminal Payment'),
                    'add_new '              => __('Create new'),
                    'add_new_item'          => __('Create new Virtual Payment'),
                    'edit_item'             => __('Edit Virtual Payment'),
                    'view_item'             => __('View Virtual Payment'),
                    'view_items'            => __('View Virtual Payments'),
                    'search_items'          => __('Search Virtual Payments'),
                    'all_items'             => __('PensoPay - Virtual Terminal'),
                    'not_found'             => __('No virtual payments found.')
                ),
                'public' => true,
                'exclude_from_search' => true,
                'publicly_queryable' => false,

                'has_archive' => true,
                'rewrite' => array('slug' => self::POST_TYPE),
                'show_ui'             => true,
                'show_in_menu'        => 'woocommerce',
                'show_in_nav_menus'   => false,
                'show_in_admin_bar'   => true,
                'show_in_rest' => true,
                'supports' => false,
                'delete_with_user' => false

            )
        );
    }



    public function remove_post_meta($key)
    {
        $post_id = $this->_postid ?: $_GET['post'];
        if ($post_id) {
            delete_post_meta($post_id, $key);
        }
    }

    public function _doEmail()
    {
        $siteName = get_bloginfo();
        $to = $this->get_post_data('customer_email');
        $link = $this->get_post_data('link');
        $subject = sprintf(__('Your Payment Link from %s'), $siteName);
        $body = sprintf('<table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="action-content">
                            <h1>%s</h1>
                            <p>%s %s%s: <a href="%s">PensoPay Payment</a></p>
                        </td>
                    </tr>
                </table>',
            __('Thank you for your order.', 'woo-pensopay'),
            __('You can follow this link to complete your payment of', 'woo-pensopay'),
            $this->get_post_data('currency'),
            $this->get_post_data('amount'),
            $link);
        $headers = array('Content-Type: text/html; charset=UTF-8', sprintf('From: %s', get_option( 'admin_email' )));

        return wp_mail( $to, $subject, $body, $headers );
    }

    public function get_display_status()
    {
        $lastCode = $this->get_last_code();

        $status = '';
        if ($lastCode === self::STATUS_APPROVED && $this->get_last_type() === self::OPERATION_CAPTURE) {
            $status = __('Captured', 'woo-pensopay');
        } else if ($lastCode === self::STATUS_APPROVED && $this->get_last_type() === self::OPERATION_CANCEL) {
            $status = __('Cancelled', 'woo-pensopay');
        } else if ($lastCode === self::STATUS_APPROVED && $this->get_last_type() === self::OPERATION_REFUND) {
            $status = __('Refunded', 'woo-pensopay');
        } else if (!empty(self::STATUS_CODES[$lastCode])) {
            $status = self::STATUS_CODES[$lastCode];
        }
        return sprintf('%s (%s)', $status, $this->get_post_data('state'));
    }

    public function get_state_color_code($value)
    {
        switch ($value) {
            case self::STATE_INITIAL:
                $colorCode = 'yellow';
                break;
            case self::STATE_NEW:
            case self::STATE_PENDING:
                $colorCode = 'orange';
                break;
            case self::STATE_REJECTED:
                $colorCode = 'red';
                break;
            case self::STATE_PROCESSED:
            default:
                $colorCode = 'green';
        }
        return $colorCode;
    }

    public function get_status_color_code($value)
    {
        switch ($value) {
            case self::STATUS_WAITING_APPROVAL:
                $colorCode = 'yellow';
                break;
            case self::STATUS_3D_SECURE_REQUIRED:
                $colorCode = 'orange';
                break;
            case self::STATUS_ABORTED:
            case self::STATUS_GATEWAY_ERROR:
            case self::COMMUNICATIONS_ERROR_ACQUIRER:
            case self::STATUS_AUTHORIZATION_EXPIRED:
            case self::STATUS_REJECTED_BY_ACQUIRER:
            case self::STATUS_REQUEST_DATA_ERROR:
                $colorCode = 'red';
                break;
            case self::STATUS_APPROVED:
            default:
                $colorCode = 'green';
        }
        return $colorCode;
    }

    public function can_capture()
    {
        return $this->get_post_data('state') === self::STATE_NEW;
    }

    public function can_cancel()
    {
        return $this->get_post_data('state') === self::STATE_NEW;
    }

    public function can_refund()
    {
        return ($this->get_post_data('state') === self::STATE_PROCESSED && ($this->get_post_data('amount') !== $this->get_post_data('amount_refunded')) && $this->get_post_data('amount_captured') > 0);
    }

    public function get_last_operation()
    {
        if (empty($this->_lastOperation)) {
            if (!empty($this->get_post_data('operations'))) {
                $operations = $this->get_post_data('operations');
                if (!empty($operations) && is_array($operations)) {
                    $lastOp = array_pop($operations);
                    if (!empty($lastOp) && is_array($lastOp)) {
                        $this->_lastOperation = [
                            'type' => $lastOp['type'],
                            'code' => $lastOp['qp_status_code'],
                            'msg'  => $lastOp['qp_status_msg']
                        ];
                    }
                }
            }
        }
        return $this->_lastOperation;
    }

    public function get_last_message()
    {
        return $this->get_last_operation()['msg'];
    }

    public function get_last_type()
    {
        return $this->get_last_operation()['type'];
    }

    public function get_last_code()
    {
        return $this->get_last_operation()['code'] ?? '';
    }

    public function get_metadata()
    {
        if (!empty($this->get_post_data('metadata'))) {
            return $this->get_post_data('metadata');
        }
        return [];
    }

    public function get_first_operation()
    {
        if (!empty($this->get_post_data('operations'))) {
            $operations = $this->get_post_data('operations');
            if (!empty($operations) && is_array($operations)) {
                $firstOp = array_shift($operations);
                if (!empty($firstOp) && is_array($firstOp)) {
                    return [
                        'type' => $firstOp['type'],
                        'code' => $firstOp['qp_status_code'],
                        'msg'  => $firstOp['qp_status_msg']
                    ];
                }
            }
        }
        return [];
    }

    public function set_post_meta($field, $data)
    {
        $post_id = $this->_postid ?: $_GET['post'];
        if ($post_id) {
            update_post_meta($post_id, $field, $data);
        }
    }

    public function get_post_data($field)
    {
        $post_id = $this->_postid ?: ($_GET['post'] ?? false);
        if ($post_id) {
            switch ($field) {
                default:
                    return get_post_meta($post_id, $field, true);
            }
        }
    }

    public function cancel()
    {
        try {
            $api_transaction = new WC_PensoPay_API_Payment();
            $api_transaction->cancel($this->get_post_data('reference_id'));
        } catch (\Exception $e) {}
        $this->update_from_remote();
        return $this;
    }

    public function refund()
    {
        $api_transaction = new WC_PensoPay_API_Payment();
        $api_transaction->post(sprintf('%d/%s', $this->get_post_data('reference_id'), 'refund'), [
            'amount'   => $this->get_post_data('amount') * 100,
            'vat_rate' => 0.25,
        ]);
        $this->update_from_remote();
        return $this;
    }

    public function capture()
    {
        $api_transaction = new WC_PensoPay_API_Payment();
        $api_transaction->post( sprintf( '%d/%s', $this->get_post_data('reference_id'), 'capture'), [
            'amount'   => $this->get_post_data('amount') * 100,
        ]);
        $this->update_from_remote();
        return $this;
    }

    public function patch_payment()
    {
        $api_transaction = new WC_PensoPay_API_Payment();
        $params = $this->get_transaction_object();
        $params['id'] = $this->get_post_data('reference_id');
        $payment = $api_transaction->patch(sprintf('/%s', $params['id']),  $params);
        return $payment;
    }

    public function create_payment()
    {
        $api_transaction = new WC_PensoPay_API_Payment();
        $payment = $api_transaction->post( '/', $this->get_transaction_object() );
        return $payment;
    }

    public function create_payment_link()
    {
        $cardtypelock = WC_PP()->s( 'pensopay_cardtypelock' );

        $payment_method = 'pensopay';

        $base_params = [
            'language'                     => array_shift(explode('_', $this->get_post_data('locale_code'))),
            'amount'                       => $this->get_post_data('amount') * 100,
            'currency'                     => $this->get_post_data('currency'),
            'callbackurl'                  => WC_PensoPay_Helper::get_callback_url(),
            'autocapture'                  => $this->get_post_data('autocapture'),
            'autofee'                      => $this->get_post_data('autofee'),
            'payment_methods'              => apply_filters( 'woocommerce_pensopay_cardtypelock_' . $payment_method, $cardtypelock, $payment_method ),
            'branding_id'                  => WC_PP()->s( 'pensopay_branding_id' ),
            'google_analytics_tracking_id' => WC_PP()->s( 'pensopay_google_analytics_tracking_id' ),
            'customer_email'               => $this->get_post_data('customer_email'),
        ];

        $order_params = $this->get_transaction_object();
        $merged_params = array_merge( $base_params, $order_params );

        $api_transaction = new WC_PensoPay_API_Payment();
        $payment_link = $api_transaction->put( sprintf( '%d/link', $this->get_post_data('reference_id')), $merged_params );
        $this->set_post_meta('link', $payment_link->url);
        return $payment_link->url;
    }

    public function get_transaction_object() {
        $params = [
            'order_id'          => $this->get_post_data('order_id'),
            'currency'          => $this->get_post_data('currency'),
            'text_on_statement' => WC_PP()->s('text_on_statement') ?: '',
            'basket'            => [
                [
                    'qty'           => 1,
                    'item_no'       => 'virtualterminal',
                    'item_name'     => 'Products',
                    'item_price'    => $this->get_post_data('amount') * 100,
                    'vat_rate'      => 0.25,
                ]
            ]
        ];
        return $params;
    }

    public function update_from_remote()
    {
        $api_transaction = new WC_PensoPay_API_Payment();
        $payment = $api_transaction->get($this->get_post_data('reference_id'));
        $this->import_from_remote($payment);
    }

    public function import_from_remote($payment)
    {
        $paymentAsArray = json_decode(json_encode($payment), true); //array

        //TODO:
//        if ($payment['test_mode']) { //No test payments
//            $this->set_post_meta('state', self::STATE_REJECTED);
//            return;
//        }

        $this->set_post_meta('reference_id', $paymentAsArray['id']);
        $this->set_post_meta('amount', $paymentAsArray['basket'][0]['item_price'] / 100);
        $this->set_post_meta('metadata', $paymentAsArray['metadata']);

        if (!empty($paymentAsArray['metadata']) && is_array($paymentAsArray['metadata'])) {
            $this->set_post_meta('fraud_probability', $paymentAsArray['metadata']['fraud_suspected'] || $paymentAsArray['metadata']['fraud_reported'] ? self::FRAUD_PROBABILITY_HIGH : self::FRAUD_PROBABILITY_NONE);
        }
        $this->set_post_meta('operations', $paymentAsArray['operations']);

        unset($paymentAsArray['id'], $paymentAsArray['basket'], $paymentAsArray['metadata'], $paymentAsArray['operations']);

        foreach ($paymentAsArray as $key => $value) {
            $this->set_post_meta($key, $value);
        }
        if (isset($paymentAsArray['link']) && !empty($paymentAsArray['link'])) {
            if (is_array($paymentAsArray['link'])) {
                $link = $paymentAsArray['link']['url'];
            } else {
                $link = $paymentAsArray['link'];
            }
            $this->set_post_meta('link', $link);
        }
        $this->set_post_meta('hash', md5($paymentAsArray['id'] . $this->get_post_data('link') . $this->get_post_data('amount')));

        if (!empty($payment->operations)) {
            $amountCaptured = 0;
            $amountRefunded = 0;
            foreach ($payment->operations as $operation) {
                if ($operation->type === 'capture') {
                    $amountCaptured += $operation->amount;
                } else if ($operation->type === 'refund') {
                    $amountRefunded += $operation->amount;
                }
            }
            $this->set_post_meta('amount_captured', $amountCaptured / 100);
            $this->set_post_meta('amount_refunded', $amountRefunded / 100);
        }
    }

    public static function output( $post )
    {
        include WCPP_PATH . 'templates/admin/virtualterminal/payment.php';
    }
}