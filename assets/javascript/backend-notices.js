(function($, config) {
    $(function() {
        $(document.body).on('click', '.wcpp-notice .notice-dismiss', function() {
            $.post(config.flush)
        });
    });
})(jQuery, window.wcppBackendNotices || {});