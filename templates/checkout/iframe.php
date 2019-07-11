<?php

get_template_part('header');
$order_id = WC()->session->get( 'order_awaiting_payment' );
$order = new WC_PensoPay_Order( $order_id );

?>

<?php if (empty($order->get_id())): ?>
	<script type="text/javascript">
        window.location = '<?php echo get_site_url(); ?>';
	</script>
<?php else: ?>
	<iframe src="<?= $order->get_payment_link(); ?>" height="100%" width="100%" style="min-height: 400px; min-height:500px; border: 0;">

	</iframe>

	<script type="text/javascript">
        var poller = setInterval(pollPayment, 5000);

        function pollPayment() {
            jQuery.ajax('<?php echo sprintf('%s?%s', get_site_url(), WC_PensoPay_Helper::PENSOPAY_VAR_IFRAMEPOLL);  ?>', {
                success: function(response) {
                    var obj = response;
                    if (!obj.repeat) {
                        clearInterval(poller);
                    }
                    if (obj.error || obj.success) {
                        document.location = obj.redirect;
                    }
                }
            });
        }
	</script>
<?php endif; ?>

<?php get_template_part('footer'); ?>