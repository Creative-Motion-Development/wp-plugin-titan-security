jQuery(document).ready(function($) {
    if(document.cookie.includes('wt-push-subscribe=1'))
        jQuery('.wt-push-status').html("OK");
    else
        jQuery('.wt-push-status').html("No");

    jQuery('#wt-quickstart-scan').on('click', function(e) {
        e.preventDefault();
        var btn = jQuery(this);
        btn.hide();
        jQuery('.wt-scan-icon-loader').show();
        //jQuery('#scan').trigger('click');
        vulnerability_ajax(false);
        audit_ajax(false);
    });
});