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
class SiteChecker extends Base {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'sitechecker';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-welcome-view-site';

	/**
	 * {@inheritdoc}
	 */
	public $type = 'page';

	/**
	 * {@inheritdoc}
	 */
	public $show_right_sidebar_in_options = false;


	/**
	 * Module folder URL
	 *
	 * @since  7.0
	 * @var bool
	 */
	public $MODULE_URL = WTITAN_PLUGIN_URL . "/includes/sitechecker";

	/**
	 * Path to module files
	 *
	 * @since  7.0
	 * @var bool
	 */
	public $MODULE_PATH = WTITAN_PLUGIN_DIR . "/includes/sitechecker";

	/**
	 * Path to module files
	 *
	 * @since  7.0
	 * @var object
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

		$this->menu_title = __('Site Checker', 'titan-security');
		$this->page_menu_short_description = __('Checking sites for availability', 'titan-security');

		parent::__construct($plugin);
	}

	/**
	 * Init class and page data
	 */
	public function init()
	{
		require_once $this->MODULE_PATH . "/boot.php";
		$this->module = new Titan\SiteChecker();
	}

	/**
	 * Assets
	 *
	 * @return void
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		$this->styles->add($this->MODULE_URL . '/assets/css/sitechecker-dashboard.css');
		$this->scripts->add($this->MODULE_URL . '/assets/js/sitechecker.js', ['jquery']);

		if( $this->plugin->is_premium() ) {
			$this->scripts->add($this->MODULE_URL . '/assets/js/firebase.min.js');
			$this->scripts->localize('wtitan', [
				'path' => $this->MODULE_URL . '/assets/js/firebase-messaging-sw.js',
				'scope' => $this->MODULE_URL . '/assets/js/',
				'pushTokenNonce' => wp_create_nonce('titan-send-push-token'),
				'sitechecker_nonce' => wp_create_nonce('titan-sitechecker'),
			]);
			$this->scripts->add($this->MODULE_URL . '/assets/js/app.js', ['jquery']);
			$this->scripts->localize('wt_app', [
				'https' => __( 'Your site must work on HTTPS to subscribe to notifications', 'titan-security' ),
				'notice' => __( 'Your browser does not support notifications', 'titan-security' ),
				'worker' => __( 'ServiceWorker not supported. Your site must work on HTTPS', 'titan-security' ),
			]);
		}
	}


	/**
	 * Show page content
	 */
	public function showPageContent()
	{
		$this->init();
		$this->module->showPageContent();
	}

}
