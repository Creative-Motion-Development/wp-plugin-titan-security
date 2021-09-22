/**
 * Checking existing comments
 *
 * @author Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @copyright (c) 03.12.2019, CreativeMotion
 * @version 1.0
 */

jQuery(function($) {
	let contanierElement = $('#wantispam-check-existing-comments'),
		progressBarElement = contanierElement.find('progress'),
		totalComments = 0,
		progressStep,
		stopProgress = false;

	progressBarElement.attr('value', 0);
	progressBarElement.attr('max', 100);

	contanierElement.find('.wantispam-check-existing-comments__left-comments').hide();

	send_request({
		'action': 'waspam-check-existing-comments',
		'_wpnonce': contanierElement.data('nonce')
	});

	function send_request(data) {
		$.post(window.ajaxurl, data, function(response) {
			if( !response || !response.data || !response.success ) {
				console.error("[AntiSpam PRO] Response error:");
				console.log(response);

				$.wbcr_factory_templates_000.app.showNotice("[AntiSpam PRO] Ajax error: " + response.data.error_message, 'danger');
				return;
			}

			if( response.data.remaining === 0 ) {
				progressBarElement.val(100);
				progressBarElement.attr('max', 100);

				contanierElement.find('.wantispam-check-existing-comments__left-comments').show();
				contanierElement.find('.wantispam-check-existing-comments__left-comments').find('span').text(response.data.remaining);

				window.location.href = contanierElement.data('redirect-url');
			} else {
				if( 0 === totalComments ) {
					totalComments = response.data.remaining;
					progressBarElement.attr('max', totalComments);
				}

				progressStep = totalComments - response.data.remaining;
				progressBarElement.val(progressStep);

				contanierElement.find('.wantispam-check-existing-comments__left-comments').show();
				contanierElement.find('.wantispam-check-existing-comments__left-comments').find('span').text(response.data.remaining);

				setTimeout(function() {
					send_request(data);
				}, 1000);
			}
		}).fail(function(xhr, status, error) {
			console.log(xhr);
			console.log(status);
			console.log(error);

			$.wbcr_factory_templates_000.app.showNotice("[AntiSpam PRO] Ajax error: " + error, 'danger');
		});
	}
});