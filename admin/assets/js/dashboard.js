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