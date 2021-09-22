/**
 * General
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 10.09.2017, Webcraftic
 * @version 1.0
 */

(function ($) {
    'use strict';

    var wtitan_import = {
        init: function () {
            this.importOptions();
        },
        importOptions: function () {
            var self = this;

            $('.wtitan-import-options-button').click(function () {
                var settings = $('#wbcr-titan-export-textarea').val(),
                    $this = $(this);

                if (!settings) {
                    $.wbcr_factory_templates_000.app.showNotice('Import options is empty!', 'danger');
                    return false;
                }

                if (void 0 == wtitan_ajax || !wtitan_ajax.import_options_nonce) {
                    $.wbcr_factory_templates_000.app.showNotice('Unknown Javascript error, most likely the wtitan_ajax variable does not exist!', 'danger');
                    return false;
                }

                $(this).prop('disabled', true);

                self.sendRequest({
                    action: 'wtitan_import_settings',
                    _wpnonce: wtitan_ajax.import_options_nonce,
                    settings: settings
                }, function (response) {
                    $this.prop('disabled', false);

                    if (response.data.update_notice) {
                        $.wbcr_factory_templates_000.app.showNotice(response.data.update_notice);
                    } else {
                        $('.wbcr-clr-update-package').closest('.wbcr-factory-warning-notice').remove();
                    }
                });

                return false;
            });
        },
        sendRequest: function (request_data, beforeValidateCallback, successCallback) {
            var self = this;

            if (wtitan_ajax === undefined) {
                console.log('Undefinded wtitan_ajax object.');
                return;
            }

            if (typeof request_data === 'object') {
                request_data.security = wtitan_ajax.ajax_nonce;
            }

            $.ajax(ajaxurl, {
                type: 'post',
                dataType: 'json',
                data: request_data,
                success: function (data, textStatus, jqXHR) {
                    var noticeId;

                    beforeValidateCallback && beforeValidateCallback(data);

                    if (!data || data.error) {
                        console.log(data);

                        if (data) {
                            noticeId = $.wbcr_factory_templates_000.app.showNotice(data.error_message, 'danger');
                        } else {
                            if (void 0 !== wtitan_ajax) {
                                noticeId = $.wbcr_factory_templates_000.app.showNotice(wtitan_ajax.i18n.unknown_error, 'danger');
                            }
                        }

                        setTimeout(function () {
                            $.wbcr_factory_templates_000.app.hideNotice(noticeId);
                        }, 5000);
                        return;
                    }

                    successCallback && successCallback(data);

                    if (!request_data.flush_redirect) {
                        if (void 0 !== wtitan_ajax) {
                            noticeId = $.wbcr_factory_templates_000.app.showNotice(wtitan_ajax.i18n.success_update_settings, 'success');

                            setTimeout(function () {
                                $.wbcr_factory_templates_000.app.hideNotice(noticeId);
                            }, 5000);
                        }
                        return;
                    }

                    window.location.href = wtitan_ajax.flush_cache_url;
                    // открыть уведомление

                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(xhr.responseText);
                    console.log(thrownError);

                    var noticeId = $.wbcr_factory_templates_000.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');
                }
            });
        }
    };

    $(document).ready(function () {
        wtitan_import.init();
    });

})(jQuery);


// jQuery(document).ready(function($) {
//     $('.wtitan-import-options-button').click(function() {
//         var settings = $('#wbcr-titan-export-textarea').val(),
//             $this = $(this);
//
//         if( !settings ) {
//             $.wbcr_factory_clearfy_217.app.showNotice('Import options is empty!', 'danger');
//             return false;
//         }
//
//         if( void 0 == wtitan_ajax || !wtitan_ajax.import_options_nonce ) {
//             $.wbcr_factory_clearfy_217.app.showNotice('Unknown Javascript error, most likely the wtitan_ajax variable does not exist!', 'danger');
//             return false;
//         }
//
//         $(this).prop('disabled', true);
//
//         sendRequest({
//             action: 'wtitan_import_settings',
//             _wpnonce: wtitan_ajax.import_options_nonce,
//             settings: settings
//         }, function(response) {
//             $this.prop('disabled', false);
//
//             if( response.data.update_notice ) {
//                 if( !$('.wtitan-update-package').length ) {
//                     $.wbcr_factory_clearfy_217.app.showNotice(response.data.update_notice);
//                 }
//             } else {
//                 if( $('.wtitan-update-package').length ) {
//                     $('.wtitan-update-package').closest('.wbcr-factory-warning-notice').remove();
//                 }
//             }
//         });
//
//         return false;
//     });
//
//     function sendRequest(request_data, beforeValidateCallback, successCallback) {
//
//         if( wtitan_ajax === undefined ) {
//             console.log('Undefinded wtitan_ajax object.');
//             return;
//         }
//
//         if( typeof request_data === 'object' ) {
//             request_data.security = wtitan_ajax.ajax_nonce;
//         }
//
//         $.ajax(ajaxurl, {
//             type: 'post',
//             dataType: 'json',
//             data: request_data,
//             success: function(data, textStatus, jqXHR) {
//                 var noticeId;
//
//                 beforeValidateCallback && beforeValidateCallback(data);
//
//                 if( !data || data.error ) {
//                     console.log(data);
//
//                     if( data ) {
//                         noticeId = $.wbcr_factory_clearfy_217.app.showNotice(data.error_message, 'danger');
//                     } else {
//                         if( void 0 != wtitan_ajax ) {
//                             noticeId = $.wbcr_factory_clearfy_217.app.showNotice(wtitan_ajax.i18n.unknown_error, 'danger');
//                         }
//                     }
//
//                     setTimeout(function() {
//                         $.wbcr_factory_clearfy_217.app.hideNotice(noticeId);
//                     }, 5000);
//                     return;
//                 }
//
//                 successCallback && successCallback(data);
//
//                 if( !request_data.flush_redirect ) {
//                     if( void 0 != wtitan_ajax ) {
//                         noticeId = $.wbcr_factory_clearfy_217.app.showNotice(wtitan_ajax.i18n.success_update_settings, 'success');
//
//                         setTimeout(function() {
//                             $.wbcr_factory_clearfy_217.app.hideNotice(noticeId);
//                         }, 5000);
//                     }
//                     return;
//                 }
//
//                 window.location.href = wtitan_ajax.flush_cache_url;
//                 // открыть уведомление
//
//             },
//             error: function(xhr, ajaxOptions, thrownError) {
//                 console.log(xhr.status);
//                 console.log(xhr.responseText);
//                 console.log(thrownError);
//
//                 var noticeId = $.wbcr_factory_clearfy_217.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');
//             }
//         });
//     }
//
// });