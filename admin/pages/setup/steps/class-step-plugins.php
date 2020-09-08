<?php

namespace WBCR\Titan\Page;

/**
 * Step
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Plugins extends \WBCR\FactoryClearfy000\Pages\Step_Custom {

	protected $prev_id = 'step0';
	protected $id = 'step1';
	protected $next_id = 'step2';

	public function get_title()
	{
		return "Setup Plugins";
	}

	public function html()
	{
		$install_hide_login_page_btn = $this->plugin->get_install_component_button('wordpress', 'hide-login-page/hide-login-page.php');
		?>
		<div class="w-factory-clearfy-000-setup__inner-wrap">
			<h3><?php _e('Installing plugins', 'titan-security') ?></h3>
			<p style="text-align: left;"><?php _e('We analyzed your site and decided that in order to get the maximum result in
				optimizing your site, you will need to install additional plugins.', 'titan-security') ?></p>
			<table class="form-table">
				<thead>
				<tr>
					<th style="width:300px"><?php _e('Plugin', 'titan-security') ?></th>
					<th style="width:150px"><?php _e('Security score', 'titan-security') ?></th>
					<th style="width:80px"></th>
				</tr>
				</thead>
				<tr>
					<td>Hide login page</td>
					<td style="color:grey">+10</td>
					<td>
						<?php $install_hide_login_page_btn->render_link(); ?>
					</td>
				</tr>
			</table>
		</div>
		<?php $this->render_button(); ?>
		<?php
	}
}