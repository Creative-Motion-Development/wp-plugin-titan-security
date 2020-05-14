jQuery(document).ready(function($) {
    $('#js-wtitan-firewall-mode').change(function() {
        var selectmode = $(this);
        console.log(selectmode.val());

        $('.wtitan-status-block').hide();
        $('.wtitan-status-block.wtitan-status--loading').show();

        $.ajax(ajaxurl, {
            type: 'post',
            dataType: 'json',
            data: {
                action: 'wtitan-change-firewall-mode',
                mode: $(this).val(),
                _wpnonce: $(this).data('nonce')
            },
            success: function(data, textStatus, jqXHR) {
                var noticeId;

                console.log(data);

                if( !data || data.error ) {
                    $('.wtitan-status-block.wtitan-status--loading').hide();
                    console.log(data);

                    if( data ) {
                        noticeId = $.wbcr_factory_clearfy_000.app.showNotice(data.error_message, 'danger');
                    }

                    setTimeout(function() {
                        $.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
                    }, 5000);
                    return;
                }
                else {
                    $('.wtitan-status-block.wtitan-status--loading').hide();
                    $('.wtitan-status-block.wtitan-status--' + selectmode.val()).show();
                }

            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('.wtitan-status-block.wtitan-status--loading').hide();
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);

                var noticeId = $.wbcr_factory_clearfy_000.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');
            }
        });
    });

    $('#wt-antispam-status').change(function() {
        var selectmode = $(this);
        var block = $('#wt-antispam-status-block');

        block.addClass('wt-block-loading');

        $.ajax(ajaxurl, {
            type: 'post',
            dataType: 'json',
            data: {
                action: 'wtitan-change-antispam-mode',
                mode: selectmode.val(),
                _wpnonce: $(this).data('nonce')
            },
            success: function(data, textStatus, jqXHR) {
                var noticeId;
                console.log(data);
                block.removeClass('wt-block-loading');

                if( data.error_message ) {
                    noticeId = $.wbcr_factory_clearfy_000.app.showNotice(data.error_message, 'danger');
                }
                else {
                    noticeId = $.wbcr_factory_clearfy_000.app.showNotice(data.message, 'success');
                }

                setTimeout(function() {
                    $.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
                }, 5000);
                return;

            },
            error: function(xhr, ajaxOptions, thrownError) {
                block.removeClass('wt-block-loading');
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);

                var noticeId = $.wbcr_factory_clearfy_000.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');
            }
        });
    });

    $('.wt-scanner-speed-button').click(function() {
        var select_speed = $(this);
        console.log(select_speed.val());

        $('.wt-scanner-speed-button').addClass('disabled');

        $.ajax(ajaxurl, {
            type: 'post',
            dataType: 'json',
            data: {
                action: 'wtitan_change_scanner_speed',
                speed: select_speed.data('value'),
                _wpnonce: wtdashboard.nonce
            },
            success: function(data, textStatus, jqXHR) {
                var noticeId;

                console.log(data);

                if( !data || data.error ) {

                    if( data ) {
                        noticeId = $.wbcr_factory_clearfy_000.app.showNotice(data.error_message, 'danger');
                    }

                    setTimeout(function() {
                        $.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
                    }, 5000);
                    return;
                }
                else {
                    if( data ) {
                        noticeId = $.wbcr_factory_clearfy_000.app.showNotice(data.message, 'success');
                    }
                    setTimeout(function() {
                        $.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
                    }, 5000);
                }
                $('.wt-scanner-speed-button').removeClass('disabled');

            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('.wt-scanner-speed-button').removeClass('disabled');
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);

                var noticeId = $.wbcr_factory_clearfy_000.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');
            }
        });

    });

    $('.wt-scanner-schedule-button').click(function() {
        var select = $(this);
        console.log(select.val());

        $('.wt-scanner-schedule-button').addClass('disabled');

        $.ajax(ajaxurl, {
            type: 'post',
            dataType: 'json',
            data: {
                action: 'wtitan_change_scanner_schedule',
                schedule: select.data('value'),
                _wpnonce: wtdashboard.nonce
            },
            success: function(data, textStatus, jqXHR) {
                var noticeId;

                console.log(data);

                if( !data || data.error ) {

                    if( data ) {
                        noticeId = $.wbcr_factory_clearfy_000.app.showNotice(data.error_message, 'danger');
                    }

                    setTimeout(function() {
                        $.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
                    }, 5000);
                    return;
                }
                else {
                    if( data ) {
                        noticeId = $.wbcr_factory_clearfy_000.app.showNotice(data.message, 'success');
                    }
                    setTimeout(function() {
                        $.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
                    }, 5000);
                }
                $('.wt-scanner-schedule-button').removeClass('disabled');

            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('.wt-scanner-schedule-button').removeClass('disabled');
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);

                var noticeId = $.wbcr_factory_clearfy_000.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');
            }
        });

    });

    jQuery('[data-action="digest-state"]').on('click', function(e) {
        e.preventDefault();
        var btn = jQuery(this);

        var enable = btn.attr('data-value');

        jQuery.post(ajaxurl, {
            action: 'wtitan_change_digest_state',
            _wpnonce: wtdashboard.digest_nonce,
            value: enable,
        }, function(response) {
            console.log(response);

            var msg = (!response || response.error_message) ? response.error_message : response.message;
            var type = (!response || response.error_message) ? 'danger' : 'success';

            jQuery('[data-action="digest-state"][disabled]').removeAttr('disabled');
            btn.attr('disabled', 'disabled');

            var noticeId = jQuery.wbcr_factory_clearfy_000.app.showNotice(msg, type);
            setTimeout(function() {
                jQuery.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
            }, 5000);
        });
    })

    jQuery('#wt-quickstart-scan').on('click', function(e) {
        e.preventDefault();
        var btn = jQuery(this);
        btn.hide();
        jQuery('.wt-scan-icon-loader').show();
        //jQuery('#scan').trigger('click');
        vulnerability_ajax(false);
        audit_ajax(false);
    });

    $('.factory-checkbox--disabled.wtitan-control-premium-label .factory-buttons-group').click(function(e) {
        e.stopPropagation();
        window.location.href = 'https://titansitescanner.com/pricing/';
    });
});
