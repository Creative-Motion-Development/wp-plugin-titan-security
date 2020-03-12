jQuery(document).ready(function($) {
	jQuery('#js-wantispam-activate-trial-button').click(function(e) {
		e.preventDefault();
		var infosModal = $('#wtitan-tmpl-install-auto-prepend');

		if( !infosModal.length ) {
			console.log('[Error]: Html template for modal not found.');
			return;
		}

		Swal.fire({
			html: infosModal.html(),
			customClass: 'wantispam-modal wantispam-modal-confirm',
			width: 500,
			showCancelButton: true,
			showCloseButton: true,
			confirmButtonText: 'Agree',
		}).then(function(result) {
			if( result.value ) {
				window.location.href = jQuery('#js-wantispam-activate-trial-button').data('url');
			}
			console.log(result);
		});
	});

});