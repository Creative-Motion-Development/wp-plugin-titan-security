jQuery(document).ready(function ($) {
    function showNotice(message, type, timeout) {
        if (typeof type === 'undefined') {
            type = 'success';
        }

        if (typeof timeout === 'undefined') {
            timeout = 5000;
        }

        if (typeof $ === 'undefined' || typeof $.wbcr_factory_clearfy_000 === 'undefined') {
            return;
        }

        var noticeId = $.wbcr_factory_clearfy_000.app.showNotice(message, type);
        if (timeout > 0) {
            setTimeout(function () {
                $.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
            }, timeout);
        }
    }

    jQuery(".wt-sitechecker-button-delete").click(function (e) {
        e.preventDefault();

        var $btn = jQuery(this);
        var $spinner = $btn.siblings('#wt-spinner');

        $btn.hide();
        $spinner.show();
        jQuery.ajax({
            method: 'POST', url: ajaxurl, data: {
                action: 'wtitan_sitechecker_delete_url',
                id: $btn.data('id'),
                _ajax_nonce: wtitan.sitechecker_nonce
            },
            success: function (response) {
                //console.log(response);
                if (response.success) {
                    console.log(response.data.notice + ": " + $btn.data('id'));
                    $btn.closest('tr').fadeOut();
                } else {
                    $spinner.hide();
                    $btn.show();
                    console.log(response.data.notice + ": " + $btn.data('id'));
                }
                showNotice(response.data.notice, response.data.type, 5000);
            },
        });

    });

    jQuery(".wt-sitechecker-button-add").click(function (e) {
        e.preventDefault();

        var $btn = jQuery(this);
        var $spinner = jQuery('.wt-sitechecker-form-add #wt-spinner');
        var $url = jQuery('#wt-sitechecker-url').val();

        $btn.hide();
        $spinner.show();
        jQuery.ajax({
            method: 'POST', url: ajaxurl, data: {
                action: 'wtitan_sitechecker_add_url',
                url: $url,
                _ajax_nonce: wtitan.sitechecker_nonce
            },
            success: function (response) {
                //console.log(response);
                if (response.success) {
                    console.log(response.data.notice + ": " + $url);
                    window.location.reload();
                }
                showNotice(response.data.notice, response.data.type, 5000);
                $btn.show();
                $spinner.hide();
            },
        });

    });
});