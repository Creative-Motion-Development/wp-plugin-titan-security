jQuery(document).ready(function($) {
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