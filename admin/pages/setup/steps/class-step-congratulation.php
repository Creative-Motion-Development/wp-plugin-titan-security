<?php

namespace WBCR\Titan\Page;

/**
 * Step
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Congratulation extends \WBCR\FactoryClearfy000\Pages\Step_Custom {

	protected $prev_id = 'step5';
	protected $id = 'step6';

	//protected $next_id = 'step2';

	public function get_title()
	{
		return __("Finish", "clearfy");
	}

	public function html()
	{
		$pricing_page_url = $this->plugin->get_support()->get_pricing_url(true, 'setup_wizard');
		?>
		<div class="w-factory-clearfy-000-setup__inner-wrap">
			<h3><?php echo __("Congratulations, the plugin configuration is complete!", "clearfy"); ?></h3>
			<p style="text-align: left;">
				<?php _e('You have successfully completed the basic plugin setup! You can go to the general plugin settings to enable other options that we did not offer you.', 'clearfy'); ?>
			</p>
			<hr>
			<div>
				<p style="text-align: left;">
					<?php _e("However, you can still improve your site's Google Page Speed score by simply purchasing the Pro version of our plugin.", "clearfy") ?>
				</p>
				<table style="width: 100%">
					<thead>
					<tr>
						<th></th>
						<th>Free</th>
						<th>PRO</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>Antispam</td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Malware scanner</td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>WordPress Security Scanner</td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Hide login page</td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Strong password</td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Antispam PRO</td>
						<td><span class="dashicons dashicons-minus"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Firewall (WAF)</td>
						<td><span class="dashicons dashicons-minus"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>WordPress Security Scanner PRO</td>
						<td><span class="dashicons dashicons-minus"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Malware scanner PRO</td>
						<td><span class="dashicons dashicons-minus"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Real-time IP Blacklist</td>
						<td><span class="dashicons dashicons-minus"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>
							Detect Malicious Code in Themes and Plugins
						</td>
						<td><span class="dashicons dashicons-minus"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>
							Site Checker
						</td>
						<td><span class="dashicons dashicons-minus"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td>Premium support</td>
						<td><span class="dashicons dashicons-minus"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					</tbody>
				</table>
				<p>
					<a href="<?php echo esc_url($pricing_page_url); ?>" class="wtitan-setup__install-component-button" target="_blank"><?php _e('Go Pro', 'clearfy') ?></a>
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