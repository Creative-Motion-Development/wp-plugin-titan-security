<?php

namespace WBCR\Titan\Page;

/**
 * Step
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Congratulation extends \WBCR\Factory_Templates_000\Pages\Step_Custom {

	protected $prev_id = 'step5';
	protected $id = 'step6';

	public function get_title()
	{
		return __("Finish", "titan-security");
	}

	/**
	 * Requests assets (js and css) for the page.
	 *
	 * @return void
	 * @since 1.0.0
	 * @see   FactoryPages000_AdminPage
	 *
	 */
	public function assets($scripts, $styles)
	{
		$styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/firewall/firewall-dashboard.css');
		$scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/libs/circular-progress.js', ['jquery']);
		$scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/libs/popover.min.js');
	}

	public function html()
	{
		$pricing_page_url = $this->plugin->get_support()->get_pricing_url(true, 'setup_wizard');
		?>
		<div class="w-factory-templates-000-setup__inner-wrap">
			<h3><?php _e("Congratulations, the plugin configuration is complete!", "titan-security"); ?></h3>
			<script>
				jQuery(document).ready(function($) {
					$('#wtitan-circle-firewall-coverage').wfCircularProgress({
						endPercent: 0.6,
						color: '#ca4a1f',
						inactiveColor: '#ececec',
						strokeWidth: 2,
						diameter: 100,
					});
					$("#wtitan-circle-firewall-coverage").fu_popover({
						content: $('#wtitan-status-tooltip').html(),
						dismissable: true,
						placement: 'right',
						trigger: 'hover',
						width: '350px',
						autoHide: false
					});
				});
			</script>
			<table>
				<tr>
					<td>
						<div id="wtitan-circle-firewall-coverage" class="wtitan-status-circular"></div>

						<div id="wtitan-status-tooltip" style="display: none">
							<strong><?php _e('How do I get to 100%?', 'titan-security'); ?></strong>
							<ul>
								<li><?php _e('15% Use the Titan Firewall', 'titan-security'); ?></li>
								<li><?php _e('10% Use WordPress Security Scanner PRO', 'titan-security'); ?></li>
								<li><?php _e('10% Use Malware scanner PRO', 'titan-security'); ?></li>
								<li><?php _e('5% Use Site Checker', 'titan-security'); ?></li>
								<li>
									<a href="#"><?php _e('How does Titan determine this?', 'titan-security'); ?></a>
								</li>
							</ul>
						</div>
					</td>
					<td style="vert-align: top">
						<p style="text-align: left;margin:0;">
							<?php _e('You have successfully completed the basic plugin setup! You can go to the general plugin settings to enable other options that we did not offer you. Your site is 60% secure.', 'titan-security'); ?>
						</p>
					</td>
				</tr>
			</table>
			<hr>
			<div>
				<p style="text-align: left;">
					<?php _e("Basic protection will help you avoid many security problems for your site, but to fully protect your site, we recommend that you purchase the premium version of the plugin.", "titan-security") ?>
				</p>
				<table style="width: 100%">
					<thead>
					<tr>
						<th></th>
						<th><?php _e('Free', 'titan-security'); ?></th>
						<th><?php _e('PRO', 'titan-security'); ?></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>Antispam</td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Malware scanner</td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>WordPress Security Scanner</td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Hide login page</td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Strong password</td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Antispam PRO</td>
						<td class="wtitan-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Firewall (WAF)</td>
						<td class="wtitan-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>WordPress Security Scanner PRO</td>
						<td class="wtitan-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Malware scanner PRO</td>
						<td class="wtitan-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Real-time IP Blacklist</td>
						<td class="wtitan-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>
							Detect Malicious Code in Themes and Plugins
						</td>
						<td class="wtitan-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>
							Site Checker
						</td>
						<td class="wtitan-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Premium support</td>
						<td class="wtitan-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
						<td class="wtitan-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					</tbody>
				</table>
				<p>
					<a href="<?php echo esc_url($pricing_page_url); ?>" class="wtitan-setup__install-component-button" target="_blank">
						<?php _e('Get Pro', 'titan-security') ?>
					</a>
				</p>
			</div>
		</div>
		<?php $this->render_button();
		?>
		<?php
	}

	protected function continue_step($skip = false)
	{
		$next_id = $this->get_next_id();
		if( !$next_id ) {
			wp_safe_redirect($this->plugin->getPluginPageUrl('dashboard'));
			die();
		}
		wp_safe_redirect($this->page->getActionUrl($next_id));
		die();
	}
}