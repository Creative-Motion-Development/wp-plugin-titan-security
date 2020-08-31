<?php

namespace WBCR\Titan\Page;

/**
 * Step
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Security_Audit extends \WBCR\FactoryClearfy000\Pages\Step_Custom {

	protected $prev_id = 'step1';
	protected $id = 'step2';
	protected $next_id = 'step3';

	public function get_title()
	{
		return __("Security Audit", "titan-security");
	}

	public function html()
	{
		$site_url = get_home_url();
		?>
		<script>
			jQuery(document).ready(function($) {
				//HIDE
				jQuery(document).on('click', '.wt-scanner-hide-button', function(e) {
					e.preventDefault();
					var btn = jQuery(this);
					var wtitan_hide_target = jQuery(".wtitan-tab-table-container#wtitan-hided");

					jQuery.ajax({
						method: 'POST',
						url: ajaxurl,
						data: {
							action: 'wtitan_scanner_hide',
							type: btn.data('type'),
							id: btn.data('id'),
							_ajax_nonce: "<?php echo wp_create_nonce("hide"); ?>"
						},
						beforeSend: function() {
							btn.parent('td').parent('tr').css('opacity', '0.5');
						},
						success: function(result) {
							if( result.success ) {
								btn.parent('td').parent('tr').animate({
									opacity: 'hide',
									height: 'hide'
								}, 200);
								wtitan_hide_target.html(result.data.html);
								console.log('Hided - ' + btn.data('id'));
							}
						},
						complete: function() {
						}
					});
				});

				jQuery.ajax({
					method: 'POST',
					url: ajaxurl,
					data: {
						action: 'wtitan_audit_all',
						_ajax_nonce: "<?php echo wp_create_nonce('get_audits'); ?>"
					},
					beforeSend: function() {
						$('.wtitan-step-audit__preloader').show();
					},
					success: function(result) {
						console.log('audit - ok');
						$('.wtitan-step-audit__preloader').hide();
						$('#wtitan-step-audit__content').html(result)
					},
					complete: function() {
					}
				});
			});
		</script>
		<div class="w-factory-clearfy-000-setup__inner-wrap">
			<h3><?php _e("Security Audit", "titan-security"); ?> (<?php _e("Issues", "titan-security"); ?>)</h3>
			<p style="text-align: left;">
				<?php _e("We will audit your site for potential threats, vulnerabilities and security issues. Please see the list
				of issues below:", "titan-security"); ?>
			</p>

			<div class="wtitan-step-audit__preloader"></div>
			<div id="wtitan-step-audit__content"></div>
		</div>
		<?php $this->render_button(); ?>
		<?php
	}
}