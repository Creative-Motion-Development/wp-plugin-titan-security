jQuery(document).ready(function($) {
	jQuery('#js-wtitan-activate-trial-button').click(function(e) {
		e.preventDefault();
		var infosModal = $('#wtitan-tmpl-confirmation-modal');

		if( !infosModal.length ) {
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
		}).then(function(result) {
			if( result.value ) {
				window.location.href = jQuery('#js-wtitan-activate-trial-button').data('url');
			}
			console.log(result);
		});
	});

});