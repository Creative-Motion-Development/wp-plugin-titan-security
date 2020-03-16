<?php

namespace WBCR\Titan\Page;

use WBCR\Titan;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * Scanner page class
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Check extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'check';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-plugins-checked';

	/**
	 * {@inheritdoc}
	 */
	public $type = 'page';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_position = 0;


	/**
	 * Module URL
	 *
	 * @since  1.0
	 * @var string
	 */
	public $MODULE_URL = WTITAN_PLUGIN_URL . "/includes/check";

	/**
	 * Module path
	 *
	 * @since  1.0
	 * @var string
	 */
	public $MODULE_PATH = WTITAN_PLUGIN_DIR . "/includes/check";

	/**
	 * Module object
	 *
	 * @since  1.0
	 * @var object
	 */
	public $module;

	/**
	 * Scanner page constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		$this->plugin = $plugin;

		$this->menu_title = __('Audit', 'titan-security');
		$this->page_menu_short_description = __('Security audit and vulnerability detection', 'titan-security');

		if( $this->plugin->is_premium() ) {
			require_once $this->MODULE_PATH . "/boot.php";
			$this->module = new Titan\Check();
		}

		parent::__construct($plugin);
	}

	/**
	 * Add assets
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		if( $this->plugin->is_premium() ) {
			$this->scripts->request([
				'bootstrap.tab',
			], 'bootstrap');

			$this->styles->request([
				'bootstrap.tab',
			], 'bootstrap');

			$this->styles->add($this->MODULE_URL . '/assets/css/check-dashboard.css');
			$this->scripts->add($this->MODULE_URL . '/assets/js/check.js', ['jquery']);
			$this->scripts->localize('update_nonce', wp_create_nonce("updates"));
			$this->scripts->localize('wtscanner', [
				'update_nonce' => wp_create_nonce("updates"),
				'hide_nonce' => wp_create_nonce("hide"),
			]);

			$this->styles->add(WTITAN_PLUGIN_URL . '/includes/vulnerabilities/assets/css/vulnerabilities-dashboard.css');
			$this->styles->add(WTITAN_PLUGIN_URL . '/includes/audit/assets/css/audit-dashboard.css');

			$this->styles->add(WTITAN_PLUGIN_URL . '/includes/vulnerabilities/assets/css/vulnerabilities-dashboard.css');
		}
	}

	/**
	 * Show page content
	 */
	public function showPageContent()
	{
		if( !$this->plugin->is_premium() ) {
			$this->plugin->view->print_template('require-license-activate');

			return;
		}

		$this->module->showPageContent();
	}

}
