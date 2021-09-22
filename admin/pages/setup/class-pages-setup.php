<?php

namespace WBCR\Titan\Page;

/**
 * The page Settings.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

class Setup extends \WBCR\Factory_Templates_000\Pages\Setup {

	/**
	 * @param \Wbcr_Factory000_Plugin $plugin
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		parent::__construct($plugin);

		$path = WTITAN_PLUGIN_DIR . '/admin/pages/setup/steps';

		#Step 1 is default \WBCR\Factory_Templates_000\Pages\Step_Plugins
		$this->register_step($path . '/class-step-default.php', '\WBCR\Titan\Page\Step_Default');

		#Step 2 \WBCR\Factory_Templates_000\Pages\Step_Plugins
		$this->register_step($path . '/class-step-plugins.php', '\WBCR\Titan\Page\Step_Plugins');
		#Step 3
		$this->register_step($path . '/class-step-security-audit.php', '\WBCR\Titan\Page\Step_Security_Audit');
		#Step 4
		$this->register_step($path . '/class-step-setting-scan-malware.php', '\WBCR\Titan\Page\Step_Scan_Malware');
		#Step 5
		$this->register_step($path . '/class-step-setting-tweaks.php', '\WBCR\Titan\Page\Step_Setting_Tweaks');
		#Step 6
		$this->register_step($path . '/class-step-setting-antispam.php', '\WBCR\Titan\Page\Step_Setting_Antispam');
		#Step 7
		//$this->register_step($path . '/class-step-google-page-speed-after.php', '\WBCR\Titan\Page\Step_Google_Page_Speed_After');
		#Step 8
		$this->register_step($path . '/class-step-congratulation.php', '\WBCR\Titan\Page\Step_Congratulation');
	}

	public function get_close_wizard_url()
	{
		return $this->getBaseUrl('dashboard');
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
		parent::assets($scripts, $styles);

		/* Install addons styles and scripts */
		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/install-addons.css');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/install-addons.js');

		//$this->scripts->add(WCL_PLUGIN_URL . '/admin/assets/js/circular-progress.js');
		//$this->scripts->add(WCL_PLUGIN_URL . '/admin/assets/js/setup.js');
		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/setup/page-setup.css');
	}

}
