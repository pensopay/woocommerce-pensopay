<?php
/**
 * @var string $transaction_status
 * @var WC_PensoPay_API_Subscription $transaction
 * @var string $transaction_brand
 */
?>
<?php if ( ! empty( $transaction_status ) ) : ?>
    <p class="woocommerce-pensopay-<?php echo esc_attr( $transaction_status ) ?>">
        <strong>
			<?php _e( 'Current payment state', 'woo-pensopay' ) ?>: <?php echo $transaction_status ?>
        </strong>
    </p>
<?php endif ?>

<?php if ( isset( $transaction_id, $transaction ) ) : ?>
    <p>
        <small>
            <strong><?php _e( 'Transaction ID', 'woo-pensopay' ) ?>:</strong> <?php echo $transaction_id ?>
            <span class="pp-meta-card">
                <img src="<?php echo esc_attr( WC_PensoPay_Helper::get_payment_type_logo( $transaction_brand ) ) ?>"
                     alt="<?php echo esc_attr( $transaction_brand ) ?>"/>
            </span>
        </small>
    </p>
<?php endif ?>

<?php if ( isset( $transaction_order_id ) ) : ?>
    <p>
        <small>
            <strong><?php _e( 'Transaction Order ID', 'woo-pensopay' ) ?>:</strong> <?php echo $transaction_order_id ?>
        </small>
    </p>
<?php endif ?>
