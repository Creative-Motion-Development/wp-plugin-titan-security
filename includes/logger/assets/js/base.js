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
				action: 'wlogger-logs-cleanup',
				nonce: btn.data('nonce')
			},
			success: function(data) {
				btn.html(currentBtnText);

				jQuery('#js-wlogger-viewer').html('');
				jQuery('#js-wlogger-size').text('0B');
				jQuery.wbcr_factory_clearfy_217.app.showNotice(data.message, data.type);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				jQuery.wbcr_factory_clearfy_217.app.showNotice('Error: ' + errorThrown + ', status: ' + textStatus, 'danger');
				btn.html(currentBtnText);
			}
		});
	});
})(jQuery);
