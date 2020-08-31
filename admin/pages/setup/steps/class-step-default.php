<?php

namespace WBCR\Titan\Page;

/**
 * Step
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Default extends \WBCR\FactoryClearfy000\Pages\Step_Custom {

	protected $id = 'step0';
	protected $next_id = 'step1';

	public function get_title()
	{
		return "Welcome";
	}

	public function html()
	{
		?>
		<div class="w-factory-clearfy-000-setup__inner-wrap">
			<div class="w-factory-clearfy-000-setup-step__new_onboarding-wrapper">
				<p class="w-factory-clearfy-000-setup-step__new_onboarding-welcome">
					<?php _e('Welcome to', 'titan-security') ?>
				</p>
				<h1 class="w-factory-clearfy-000-logo">
					<img src="<?php echo WTITAN_PLUGIN_URL; ?>/admin/assets/img/logo9.png" alt="">
				</h1>
				<p><?php _e('Protect your site from external and internal threats in just 2 minutes.', 'titan-security') ?></p>
			</div>

		</div>
		<?php $this->render_button(true, false, __('Yes, I want to try the wizard'), 'center'); ?>
		<?php
	}
}