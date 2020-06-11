jQuery(document).ready(function ($) {
    jQuery('#titan_scanner_schedule_daily').datetimepicker({
        datepicker: false,
        mask: true,
        format: 'H:i'
    });
    jQuery('#titan_scanner_schedule_weekly_time').datetimepicker({
        datepicker: false,
        mask: true,
        format: 'H:i'
    });
    jQuery('#titan_scanner_schedule_custom').datetimepicker({
        dayOfWeekStart: 1,
        timepicker: true,
        mask: true,
        format: 'Y/m/d H:i'
    });

    var schedule = jQuery('#titan_scanner_schedule').val();
    jQuery('.wt-schedule-controls-' + schedule).show();
    jQuery('#titan_scanner_schedule').on('change', function ($) {
        var schedule = jQuery(this).val();
        jQuery('.wt-schedule-controls').hide();
        jQuery('.wt-schedule-controls-' + schedule).show();
    });

    $('.factory-checkbox--disabled.wtitan-control-premium-label').click(function (e) {
        e.stopPropagation();
        window.location.href = 'https://titansitescanner.com/pricing/';
    });

});