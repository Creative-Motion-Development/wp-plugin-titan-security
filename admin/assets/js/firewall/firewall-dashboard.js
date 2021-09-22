jQuery(document).ready(function ($) {
    $('.js-wtitan-excluded-rules__checkbox').click(function () {
        let excludedRulesFieldElem = $('#js-wtitan-excluded-rules__field'),
            excludedRules = split(',', excludedRulesFieldElem.val());

        console.log(excludedRules);

        return false;
    });

    $("#wtitan-circle-firewall-coverage").fu_popover({
        content: $('#wtitan-status-tooltip').html(),
        dismissable: true,
        placement: 'left',
        trigger: 'hover',
        width: '350px',
        autoHide: false
    });

    $('#js-wtitan-firewall-mode').change(function () {
        console.log($(this).val());

        $('.wtitan-status-block').hide();
        $('.wtitan-status-block.wtitan-status--' + $(this).val()).show();

        $.ajax(ajaxurl, {
            type: 'post',
            dataType: 'json',
            data: {
                action: 'wtitan-change-firewall-mode',
                mode: $(this).val(),
                _wpnonce: $(this).data('nonce')
            },
            success: function (data, textStatus, jqXHR) {
                var noticeId;

                console.log(data);

                if (!data || data.error) {
                    console.log(data);

                    if (data) {
                        noticeId = $.wbcr_factory_templates_000.app.showNotice(data.error_message, 'danger');
                    }

                    setTimeout(function () {
                        $.wbcr_factory_templates_000.app.hideNotice(noticeId);
                    }, 5000);
                    return;
                }

            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(xhr.responseText);
                console.log(thrownError);

                var noticeId = $.wbcr_factory_templates_000.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');
            }
        });
    });

    $('#js-wtitan-optimize-firewall-protection,#js-wtitan-firewall-uninstall').click(function (e) {
        e.preventDefault();
        var infosModal, action;
        infosModal = $('#wtitan-tmpl-default-modal');
        action = 'install';

        if ("js-wtitan-optimize-firewall-protection" !== $(this).attr('id')) {
            action = 'uninstall';
        }

        if (!infosModal.length) {
            console.log('[Error]: Html template for modal not found.');
            return;
        }

        Swal.fire({
            html: infosModal.html(),
            customClass: 'wtitan-modal wtitan-modal-confirm',
            width: 800,
            showCancelButton: true,
            showCloseButton: true,
            confirmButtonText: 'Continue',
            preConfirm: function () {
                return new Promise((resolve, reject) => {
                    $.ajax(ajaxurl, {
                        type: 'post',
                        dataType: 'json',
                        data: {
                            action: "install" === action
                                ? 'wtitan-install-auto-prepend'
                                : 'wtitan-uninstall-auto-prepend',
                            server_configuration: $('#wtitan-server-config').val(),
                            //iniModified: true,
                            //_wpnonce: $(this).data('nonce')
                        },
                        success: function (data, textStatus, jqXHR) {
                            var noticeId;

                            console.log(data);

                            if (!data || data.error) {
                                console.log(data);

                                if (data) {
                                    noticeId = $.wbcr_factory_templates_000.app.showNotice(data.error_message, 'danger');
                                }

                                setTimeout(function () {
                                    $.wbcr_factory_templates_000.app.hideNotice(noticeId);
                                }, 5000);
                                return data;
                            }

                            resolve(data)
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            console.log(xhr.status);
                            console.log(xhr.responseText);
                            console.log(thrownError);

                            var noticeId = $.wbcr_factory_templates_000.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');
                            reject(thrownError)
                        }
                    });
                })

                /*(login) => {
                return fetch(`//api.github.com/users/${login}`)
                    .then(response => {
                        if( !response.ok ) {
                            throw new Error(response.statusText)
                        }
                        return response.json()
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error}`
                        )
                    })*/
            },
            onOpen: function () {

                if ('install' === action) {
                    $('.wtitan-modal').find('.wtitan-install-auto-prepend-modal-content').css('display', 'block');
                } else {
                    $('.wtitan-modal').find('.wtitan-uninstall-auto-prepend-modal-content').css('display', 'block');
                }

                $('#wtitan-include-prepend > li').each(function (index, element) {
                    console.log('1111');
                    $(element).on('click', function (e) {
                        console.log('fsdf');
                        e.preventDefault();
                        e.stopPropagation();

                        var control = $(this).closest('.wtitan-switch');
                        var value = $(this).data('optionValue');

                        control.find('li').each(function () {
                            $(this).toggleClass('wtitan-active', value === $(this).data('optionValue'));
                        });
                    });
                });

                //var nginxNotice = $('.wtitan-nginx-config');
                //var manualNotice = $('.wtitan-manual-config');
                $('#wtitan-server-config').on('change', function () {
                    var el = $(this);
                    /*if( manualNotice.length ) {
                        if( el.val() == 'manual' ) {
                            manualNotice.fadeIn(400, function() {
                                $.wfcolorbox.resize();
                            });
                        } else {
                            manualNotice.fadeOut(400, function() {
                                $.wfcolorbox.resize();
                            });
                        }
                    }*/

                    var identifier = '.wtitan-backups-' + el.val().replace(/[^a-z0-9\-]/i, '');
                    $('.wtitan-backups').hide();
                    $(identifier).show();
                    if ($(identifier).find('.wtitan-backup-file-list').children().length > 0) {
                        $('.wtitan-download-instructions').show();
                    } else {
                        $('.wtitan-download-instructions').hide();
                    }

                    /*if( nginxNotice.length ) { //Install only
                        if( el.val() == 'nginx' ) {
                            nginxNotice.fadeIn(400, function() {
                                $.wfcolorbox.resize();
                            });
                        } else {
                            nginxNotice.fadeOut(400, function() {
                                $.wfcolorbox.resize();
                            });
                        }

                        validateContinue();
                        return;
                    }*/

                    //$.wfcolorbox.resize();
                    //validateContinue();
                }).triggerHandler('change');
            }
        }).then(function (result) {
            console.log(result);
            console.log(action);

            if (result.value && result.value.html) {

                let swalOptions = {
                    html: result.value.html,
                    customClass: 'wtitan-modal wtitan-modal-confirm',
                    showConfirmButton: false,
                    showCancelButton: false,
                    showCloseButton: true
                };

                // Uninstall action
                if ('uninstall' === action && result.value.uninstallation_waiting) {
                    let timeout = 0;

                    if (result.value.timeout) {
                        timeout = parseInt(result.value.timeout) * 1000;
                    }

                    setTimeout(function () {

                        let data = {
                            action: 'wtitan-uninstall-auto-prepend',
                            server_configuration: result.value.server_configuration,
                            ini_modified: true,
                            //_wpnonce: $(this).data('nonce')};
                        };

                        if (result.value.credentials) {
                            data['credentials'] = result.value.credentials;
                        }

                        if (result.value.credentials_signature) {
                            data['credentials_signature'] = result.value.credentials_signature;
                        }

                        $.ajax(ajaxurl, {
                            type: 'post',
                            dataType: 'json',
                            data: data,
                            success: function (data, textStatus, jqXHR) {
                                if (data.uninstallation_success) {
                                    swalOptions.html = data.html;

                                    Swal.fire(swalOptions).then(function (r) {
                                        window.location.reload();
                                    });
                                }
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                                console.log(xhr.status);
                                console.log(xhr.responseText);
                                console.log(thrownError);

                                var noticeId = $.wbcr_factory_templates_000.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');

                            }
                        });
                    }, timeout);
                }

                Swal.fire(swalOptions).then(function (r) {
                    console.log(result);

                    // Install action
                    if ('install' === action) {
                        window.location.reload();
                        return false;
                    }
                });
            }

        });
    });
})
;