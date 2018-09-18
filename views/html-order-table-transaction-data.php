<div class="woocommerce-pensopay-order-transaction-data">
	<table border="0" cellpadding="0" cellspacing="0" class="meta">
		<tr>
			<td><?php _e('ID', 'woo-pensopay' ) ?>:</td>
			<td>#<?php echo $transaction_id ?></td>
		</tr>
		<tr>
			<td><?php _e('Order ID', 'woo-pensopay' ) ?>:</td>
			<td><?php echo $transaction_order_id ?></td>
		</tr>
		<tr>
			<td><?php _e('Method', 'woo-pensopay' ) ?>:</td>
			<td>
				<span class="transaction-brand"><img src="<?php echo $transaction_brand_logo_url ?>" alt="<?php echo $transaction_brand ?>" title="<?php echo $transaction_brand ?>" /></span>
			</td>
		</tr>
	</table>
	<div class="tags">
		<?php if ( $transaction_is_test ) : ?>
			<?php $tip_transaction_test = esc_attr( __( 'This order has been paid with test card data!', 'woo-pensopay' ) ) ?>
			<span class="tag is-test tips" data-tip="<?php echo $tip_transaction_test ?>"><?php _e( 'Test', 'woo-pensopay' ) ?></span>
		<?php endif; ?>
		<span class="tag is-<?php echo $transaction_status ?>">
			<?php echo $transaction_status ?>
		</span>
		<?php if ( $is_cached ) : ?>
			<?php $tip_transaction_cached = esc_attr( __( 'NB: The transaction data is served from cached results. Click to view the order and update the cached data.', 'woo-pensopay' ) )?>
			<span class="tag tips" data-tip="<?php echo $tip_transaction_cached ?>"><?php _e( 'Cached', 'woo-pensopay' ) ?></span>
		<?php endif; ?>

	</div>
</div>