<?php

namespace WBCR\Titan\Page;

use WBCR\Titan;

use Wbcr_Factory000_Plugin;
use Wbcr_FactoryClearfy000_PageBase;

if( !defined('ABSPATH') ) {
	exit;
}

class Scanner extends Base {

	/**
	 * {@inheritDoc}
	 */
	public $id = 'scanner';

	/**
	 * {@inheritDoc}
	 */
	public $page_menu_dashicon = 'dashicons-code-standards';

	/**
	 * {@inheritDoc}
	 */
	public $type = 'page';

	/**
	 * {@inheritDoc}
	 */
	public $show_right_sidebar_in_options = false;

	/**
	 * Module folder URL
	 */
	public $MODULE_URL = WTITAN_PLUGIN_URL . "/includes/scanner";

	/**
	 * Path to module files
	 *
	 * @since  1.0
	 * @var bool
	 */
	public $MODULE_PATH = WTITAN_PLUGIN_DIR . "/includes/scanner";

	/**
	 * Path to module files
	 *
	 * @since  1.0
	 * @var object
	 */
	public $module;

	/**
	 * Scanner constructor.
	 *
	 * @param Wbcr_Factory000_Plugin $plugin
	 *
	 */
	public function __construct(Wbcr_Factory000_Plugin $plugin)
	{
		$this->plugin = $plugin;

		$this->menu_title = __('Scanner', 'titan-security');
		$this->page_menu_short_description = __('Checking site for viruses', 'titan-security');

		if( $this->plugin->is_premium() ) {
			/** @noinspection PhpIncludeInspection */
			require_once $this->MODULE_PATH . "/boot.php";
			$this->module = new Titan\Scanner();
		}

		parent::__construct($plugin);
	}

	/**
	 * {@inheritDoc}
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		if( $this->plugin->is_premium() ) {
			$this->styles->add($this->MODULE_URL . '/assets/css/scanner-dashboard.css');
			$this->styles->add($this->MODULE_URL . '/assets/css/base-statistic.css');

			$this->scripts->add($this->MODULE_URL . '/assets/js/Chart.min.js');
			$this->scripts->add($this->MODULE_URL . '/assets/js/statistic.js');
			$this->scripts->add($this->MODULE_URL . '/assets/js/scanner.js');
			$this->scripts->localize('wpnonce', [
				'start' => wp_create_nonce('titan-start-scan'),
				'stop' => wp_create_nonce('titan-stop-scan'),
				'status' => wp_create_nonce('titan-status-scan'),
			]);
		}
	}

	/**
	 * {@inheritDoc}
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