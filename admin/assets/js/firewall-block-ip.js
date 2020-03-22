(function($) {
	'use strict';

	jQuery(document).ready(function($) {
		$('#wtitan-blocks-ips').click(function(e) {
			e.preventDefault();

			let self = $(this),
				infosModal = $('#wtitan-tmpl-block-ips-modal'),
				btn = jQuery(this);

			if( !infosModal.length ) {
				console.log('[Error]: Html template for modal not found.');
				return;
			}

			Swal.fire({
				html: infosModal.html(),
				customClass: 'wtitan-modal wtitan-ips-blocking-modal',
				width: 500,
				showCancelButton: true,
				showCloseButton: true,
				confirmButtonText: 'Block',
				preConfirm: function() {
					return new Promise((resolve, reject) => {

						$.ajax(ajaxurl, {
							type: 'post',
							dataType: 'json',
							data: {
								action: 'wtitan-block-ips',
								payload: {
									type: $('.wtitan-ips-blocking-modal__tab--active').find('a').attr('href').replace('#', ''),
									duration: 0,
									reason: $('#wtitan-ips-blocking-modal__form-reason-field').val(),
									ip: $('#wtitan-ips-blocking-modal__form-ip-field').val(),
									ipRange: $('#wtitan-ips-blocking-modal__form-range-ip-field').val(),
									hostname: $('#wtitan-ips-blocking-modal__form-hostname-field').val(),
									userAgent: $('#wtitan-ips-blocking-modal__form-user-agent-field').val(),
									referrer: $('#wtitan-ips-blocking-modal__form-referrer-field').val(),
								},
								_wpnonce: self.data('nonce')
							},
							success: function(data, textStatus, jqXHR) {
								var noticeId;

								console.log(data);

								if( !data || data.error ) {
									console.log(data);

									if( data ) {
										noticeId = $.wbcr_factory_clearfy_000.app.showNotice(data.error_message, 'danger');
									}

									setTimeout(function() {
										$.wbcr_factory_clearfy_000.app.hideNotice(noticeId);
									}, 5000);
									return data;
								}

								resolve(data)
							},
							error: function(xhr, ajaxOptions, thrownError) {
								console.log(xhr.status);
								console.log(xhr.responseText);
								console.log(thrownError);

								var noticeId = $.wbcr_factory_clearfy_000.app.showNotice('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']', 'danger');
								reject(thrownError);
								Swal.close();
							}
						});
					})
				},
				onOpen: function() {
					$('.wtitan-ips-blocking-modal__tab').find('a').click(function() {
						$('.wtitan-ips-blocking-modal__tab').removeClass('wtitan-ips-blocking-modal__tab--active');
						$('.wtitan-ips-blocking-modal__tab-content').removeClass('wtitan-ips-blocking-modal__tab-content--active');

						$(this).parent().addClass('wtitan-ips-blocking-modal__tab--active');

						let tabID = $(this).attr('href').replace('#', '');
						$('#wtitan-ips-blocking-modal__' + tabID + '-tab-content').addClass('wtitan-ips-blocking-modal__tab-content--active')
					});
				}
			}).then(function(result) {
				if( result.value ) {
					console.log(result);
				}
			});
		});

	});
})(jQuery);
