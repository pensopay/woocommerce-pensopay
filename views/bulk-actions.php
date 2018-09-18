<script type="text/javascript">
	jQuery( document ).ready( function() {
		jQuery( '<option>' ).val( 'pensopay_capture_recurring' ).text( '<?php _e( 'Capture payment and activate subscription', 'woo-pensopay' ); ?>' ).appendTo( "select[name='action']" );

		jQuery("select[name='action']").on('change', function () {
			if (this.value  === 'pensopay_capture_recurring') {
				jQuery(this).closest('form').attr('target', '_blank');
			}
		});
	} );
</script>