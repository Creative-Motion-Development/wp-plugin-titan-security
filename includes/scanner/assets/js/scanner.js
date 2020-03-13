function wtitan_progress_status(selector ,status) {
    selector.each(function(index, element){
        jQuery(element).removeAttr('class');
        jQuery(element).addClass('wt-scan-step-icon');
        jQuery(element).addClass('wt-scan-step-icon-'+status);
    });
}

jQuery(document).ready(function($) {
    //TABS
    jQuery('#wtitan-scanner-tabs a').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    jQuery('#wt-scanner-scan').on('click', function(e) {
        e.preventDefault();
        jQuery(this).attr('disabled', true);
        vulnerability_ajax();
        audit_ajax();
    });

    //HIDE
    jQuery('.wt-scanner-hide-button').on('click', function(e) {
        e.preventDefault();
        var btn = jQuery(this);
        var wtitan_hide_target = jQuery(".wtitan-tab-table-container#wtitan-hided");

        jQuery.ajax({
            method: 'POST', url: ajaxurl, data: {
                action: 'wtitan_scanner_hide',
                id: btn.data('id'),
                _ajax_nonce: wtscanner.hide_nonce
            },
            beforeSend: function () {
                btn.parent('td').parent('tr').css('opacity','0.5');
            },
            success: function (result) {
                if(result.success) {
                    btn.parent('td').parent('tr').animate({opacity: 'hide' , height: 'hide'}, 200);
                    wtitan_hide_target.html(result.data.html);
                    console.log('Hided - ' + btn.data('id'));
                }
            },
            complete: function () {
            }
        });
    });
});