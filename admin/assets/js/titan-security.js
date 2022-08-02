jQuery(document).ready(function ($) {

    jQuery('#wt-notice-hide-link').on('click', function (e) {
        e.preventDefault();
        var btn = jQuery(this);
        jQuery.ajax({
            method: 'POST', url: ajaxurl, data: {
                action: 'wtitan_hide_trial_notice',
                _ajax_nonce: wtitan_trial.nonce
            },
            beforeSend: function () {
                btn.parent().parent('.wbcr-factory-warning-notice').animate({opacity: 'hide', height: 'hide'}, 300);
            },
            success: function (result) {
                if (!result.success) {
                    btn.parent().parent('.wbcr-factory-warning-notice').animate({opacity: 'show', height: 'show'}, 300);
                }
            }
        });
    });

});