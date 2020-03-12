<?php

namespace WBCR\Titan\Page;

use WBCR\Titan;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scanner page class
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Scanner extends \Wbcr_FactoryClearfy000_PageBase {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'scanner';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-code-standards';

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
	 * Module URL
	 *
	 * @since  1.0
	 * @var string
	 */
	public $MODULE_URL = WTITAN_PLUGIN_URL."/includes/scanner";

	/**
	 * Module path
	 *
	 * @since  1.0
	 * @var string
	 */
	public $MODULE_PATH = WTITAN_PLUGIN_DIR."/includes/scanner";

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
	public function __construct( \Wbcr_Factory000_Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->menu_title                  = __( 'Scanner', 'titan-security' );
		$this->page_menu_short_description = __( 'Find malware and viruses', 'titan-security' );

		require_once $this->MODULE_PATH."/boot.php";

		$this->module = new Titan\Scanner();

		parent::__construct( $plugin );
	}

	/**
	 * Add assets
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function assets( $scripts, $styles ) {
		parent::assets( $scripts, $styles );

		$this->scripts->request( [
			'bootstrap.tab',
			], 'bootstrap' );

		$this->styles->request( [
			'bootstrap.tab',
		], 'bootstrap' );

		$this->styles->add(  $this->MODULE_URL . '/assets/css/scanner-dashboard.css' );
		$this->scripts->add( $this->MODULE_URL . '/assets/js/scanner.js', [ 'jquery' ]);
		$this->scripts->localize( 'update_nonce', wp_create_nonce("updates"));
		$this->scripts->localize( 'wtscanner', [
			'update_nonce'  => wp_create_nonce("updates"),
			'hide_nonce'  => wp_create_nonce("hide"),
		] );

		$this->styles->add( WTITAN_PLUGIN_URL.'/includes/vulnerabilities/assets/css/vulnerabilities-dashboard.css' );
		$this->styles->add( WTITAN_PLUGIN_URL.'/includes/audit/assets/css/audit-dashboard.css' );

	}

	/**
	 * Show page content
	 */
	public function showPageContent() {
		$this->module->showPageContent();
	}

}
