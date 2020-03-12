function audit_ajax() {
    var wtitan_target = jQuery(".wtitan-tab-table-container#wtitan-audit");
    var wtitan_hide_target = jQuery(".wtitan-tab-table-container#wtitan-hided");
    jQuery.ajax({
        method: 'POST', url: ajaxurl, data: {
            action: 'wtitan_audit_all',
            _ajax_nonce: wtaudit.nonce
        },
        beforeSend: function () {
            wtitan_progress_status(jQuery('#wt-scan-progress-audit .wt-scan-step-icon'), 'loader');
            wtitan_target.html("");
            wtitan_hide_target.html("");
        },
        success: function (result) {
            console.log('audit - ok');
            wtitan_target.html(result);
            wtitan_progress_status(jQuery('#wt-scan-progress-audit .wt-scan-step-icon'), 'ok');
        },
        complete: function () {
        }
    });
}