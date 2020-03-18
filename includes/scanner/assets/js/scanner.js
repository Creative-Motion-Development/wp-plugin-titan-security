/**
 * @var {Object} wpnonce
 */

(function($) {
    var intervalId;
    var loader = jQuery('.wt-scan-icon-loader');

    jQuery(document).ready(function($) {
        if($('#scan').attr('data-action') === 'stop_scan')
            intervalId = setInterval(status_scan, 15000);
    });

    $('#scan').on('click', function(event) {
        event.preventDefault();

        var btn = $(this);

        btn.attr('disabled', 'disabled');

        var action = btn.attr('data-action');
        var nonce;

        if(action === 'start_scan') {
            btn.html('Starting...');
            nonce = wpnonce.start;
        } else {
            btn.html('Stopping...');
            nonce = wpnonce.stop;
        }

        $.post(ajaxurl, {
            action: action,
            _wpnonce: nonce
        }, function(response) {
            btn.removeAttr('disabled');
            switch (action) {
                case 'start_scan':
                    btn.attr('data-action', 'stop_scan');
                    btn.html('Stop scanning');
                    intervalId = setInterval(status_scan, 15000);
                    break;

                case 'stop_scan':
                    btn.attr('data-action', 'start_scan');
                    btn.html('Scan');
                    break;

                default:
                    console.error('???');
                    return;
            }

            var type;
            if(response.success) {
                type = 'success';
            } else {
                type = 'warning';
            }

            var status = loader.attr('data-status');
            if(status === '11') loader.hide();
            else loader.attr('data-status', loader.attr('data-status')+1);

            var noticeId = $.wbcr_factory_clearfy_000.app.showNotice(response.data.message, type);
            setTimeout(function() {
                $.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
            }, 5000);
        });
    });

    function status_scan() {
        if($('#scan').attr('data-action') === 'start_scan') clearInterval(intervalId);
        $.post(ajaxurl, {
            action: 'status_scan',
            _wpnonce: wpnonce.status
        }, function(response) {
            if(typeof response.data === 'undefined' || response.data === false) {
                return;
            }

            $("#wt-total-percent").html(response.data.progress.toFixed(1) + '%');
            $("#wt-total-percent-chart").html(response.data.progress.toFixed(1) + '<span>%</span>');
            var canvas = $("#wtitan-scan-chart");
            canvas.attr('data-cleaned', response.data.cleaned);
            canvas.attr('data-suspicious', response.data.suspicious);
            $("#wtitan-cleaned-num").html(response.data.cleaned);
            $("#wtitan-suspicious-num").html(response.data.suspicious);
        });
    }

})(jQuery);