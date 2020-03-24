<?php

namespace WBCR\Titan\Page;

use WBCR\Titan;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * Site checker page class
 *
 * @author        Artem Prihodko     <webtemyk@yandex.ru>
 * @version       1.0
 */
class CertInfo extends Base {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'cert-info';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-admin-network';

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
	public $add_link_to_plugin_actions = false;

	/**
	 * Module folder URL
	 *
	 * @since  1.0
	 * @var bool
	 */
	public $MODULE_URL = WTITAN_PLUGIN_URL . "/includes/cert-info";

	/**
	 * Path to module files
	 *
	 * @since  1.0
	 * @var bool
	 */
	public $MODULE_PATH = WTITAN_PLUGIN_DIR . "/includes/cert-info";

	/**
	 * Path to module files
	 *
	 * @since  1.0
	 * @var Titan\Cert\CertInfo
	 */
	public $module;

	/**
	 * Site Checker constructor.
	 *
	 * @param \Wbcr_Factory000_Plugin $plugin
	 *
	 */
	public function __construct(\Wbcr_Factory000_Plugin $plugin)
	{
		$this->plugin = $plugin;

		$this->menu_title = __('Cert info', 'titan-security');
		$this->page_menu_short_description = __('SSL certificate information', 'titan-security');

		require_once $this->MODULE_PATH . "/boot.php";
		$this->module = new Titan\Cert\CertInfo();

		parent::__construct($plugin);
	}

	/**
	 * Assets
	 *
	 * @return void
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);
	}


	/**
	 * Show page content
	 */
	public function showPageContent()
	{
		$this->module->showPageContent();
	}

}
