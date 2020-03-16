<?php

namespace WBCR\Titan\Page;

use WBCR\Titan;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * The file contains a short help info.
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 2019 Webraftic Ltd
 * @version       1.0
 */
class Vulnerabilities extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'vulnerabilities';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-buddicons-replies';

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
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $add_link_to_plugin_actions = true;

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $MODULE_URL = WTITAN_PLUGIN_URL . "/includes/vulnerabilities";

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	public $MODULE_PATH = WTITAN_PLUGIN_DIR . "/includes/vulnerabilities";

	/**
	 * Module object
	 *
	 * @since  1.0
	 * @var object
	 */
	public $module;


	/**
	 * Logs constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		$this->plugin = $plugin;

		$this->menu_title = __('Vulnerabilities', 'titan-security');
		$this->page_menu_short_description = __('Vulnerabilities in your Wordpress, plugins, and themes', 'titan-security');

		require_once $this->MODULE_PATH . "/boot.php";

		$this->module = new Titan\Vulnerabilities();

		parent::__construct($plugin);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 1.1.4
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		if( $this->plugin->is_premium() ) {
			$this->styles->add($this->MODULE_URL . '/assets/css/vulnerabilities-dashboard.css');
			$this->scripts->add($this->MODULE_URL . '/assets/js/vulnerabilities.js', ['jquery']);
			$this->scripts->localize('wtvulner', [
				'nonce' => wp_create_nonce('get_vulners'),
			]);
		}
	}


	/**
	 * {@inheritdoc}
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
