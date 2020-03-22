/**
 * Base scripts for log reader
 *
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright (c) 29.10.2019, Webcraftic
 * @version 1.0
 */

(function($) {
	'use strict';

	$('.js-wlogger-export-debug-report').click(function() {
		var btn = $(this),
			currentBtnText = btn.html();

		btn.text(btn.data('working'));

		$.ajax({
			url: ajaxurl,
			method: 'post',
			data: {
				action: 'wtitan-logger-logs-cleanup',
				nonce: btn.data('nonce')
			},
			success: function(data) {
				btn.html(currentBtnText);

				$('#js-wlogger-viewer').html('');
				$('#js-wlogger-size').text('0B');
				$.wbcr_factory_clearfy_000.app.showNotice(data.message, data.type);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				$.wbcr_factory_clearfy_000.app.showNotice('Error: ' + errorThrown + ', status: ' + textStatus, 'danger');
				btn.html(currentBtnText);
			}
		});
	});
})(jQuery);
