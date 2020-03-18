function audit_ajax(action_before = true) {
    var wtitan_target = jQuery(".wtitan-tab-table-container#wtitan-audit");
    var wtitan_hide_target = jQuery(".wtitan-tab-table-container#wtitan-hided");
    var loader = jQuery('.wt-scan-icon-loader');
    jQuery.ajax({
        method: 'POST', url: ajaxurl, data: {
            action: 'wtitan_audit_all',
            _ajax_nonce: wtaudit.nonce
        },
        beforeSend: function () {
            if(action_before) {
                wtitan_progress_status(jQuery('#wt-scan-progress-audit .wt-scan-step-icon'), 'loader');
                wtitan_target.html("");
                wtitan_hide_target.html("");
            }
        },
        success: function (result) {
            console.log('audit - ok');

            var status = loader.attr('data-status');
            if(status === '11') loader.hide();
            else loader.attr('data-status', loader.attr('data-status')+1);

            var noticeId = jQuery.wbcr_factory_clearfy_000.app.showNotice('Security audit success', 'success');
            setTimeout(function() {
                jQuery.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
            }, 5000);

            if(action_before) {
                wtitan_target.html(result);
                wtitan_progress_status(jQuery('#wt-scan-progress-audit .wt-scan-step-icon'), 'ok');
            }
        },
        complete: function () {
        }
    });
}