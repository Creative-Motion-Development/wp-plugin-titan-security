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
    });
});