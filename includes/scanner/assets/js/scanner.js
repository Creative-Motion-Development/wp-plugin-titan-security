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
            $("div.wt-scanner-chart-clean").css('width', '0%');
            $("div.wt-scanner-chart-suspicious").css('width', '0%');
            $("div.wt-scanner-chart-notverified").css('width', '100%');

            $("#wtitan-files-num").html('0');
            $("#wtitan-cleaned-num").html('0');
            $("#wtitan-suspicious-num").html('0');
            $("#wtitan-notverified-num").html('0');
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
                    btn.html('Start scan');
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

            $("div.wt-scanner-chart-clean").css('width',response.data.progress[0] + '%');
            $("div.wt-scanner-chart-suspicious").css('width',response.data.progress[1] + '%');
            $("div.wt-scanner-chart-notverified").css('width',response.data.progress[2] + '%');

            $("#wtitan-files-num").html(response.data.scanned);
            $("#wtitan-cleaned-num").html(response.data.cleaned);
            $("#wtitan-suspicious-num").html(response.data.suspicious);
            $("#wtitan-notverified-num").html(response.data.notfiltered);

            if(response.data.notfiltered === 0)
            {
                $('#scan').attr('data-action', 'start_scan');
                $('#scan').html('Start scan');
            }
        });
    }

})(jQuery);