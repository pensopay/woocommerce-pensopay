( function( $ ) {
	"use strict";

	// DOM ready
	$(function() {
		var ppform = $('#pensopay-payment-form');
		if (ppform.length) {
			setTimeout(function () {
				ppform.submit();
			}, 5000);
		}
	});

})(jQuery);