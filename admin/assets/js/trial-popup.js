jQuery(document).ready(function ($) {

    jQuery('#wtitan-activate-trial-button').click(function (e) {
        e.preventDefault();
        var infosModal = $('#wtitan-tmpl-confirmation-modal');
        var btn = jQuery(this);

        if (!infosModal.length) {
            console.log('[Error]: Html template for modal not found.');
            return;
        }

        Swal.fire({
            html: infosModal.html(),
            customClass: 'wtitan-modal wtitan-modal-confirm',
            width: 500,
            showCancelButton: true,
            showCloseButton: true,
            confirmButtonText: 'Agree',
        }).then(function (result) {
            if (result.value) {

                jQuery.ajax({
                    method: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'wtitan_activate_trial',
                        email: jQuery('#wtitan-trial-email').val(),
                        _ajax_nonce: wtitan.trial_nonce
                    },
                    beforeSend: function () {
                        btn.attr('disabled', 'disabled');
                        var loader = document.createElement('img');
                        loader.src = jQuery('#wcl-license-wrapper').data('loader');
                        loader.height = '32';
                        btn.after(loader);
                    },
                    success: function (result) {
                        console.log(result);
                        window.location.href = result.data.url;
                    },
                    complete: function () {
                    }
                });

            }
            console.log(result);
        });
    });

});