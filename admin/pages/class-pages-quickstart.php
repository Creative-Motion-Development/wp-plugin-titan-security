<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
use WBCR\Titan\Views;

if( !defined('ABSPATH') ) {
	exit;
}

/**
 * The file contains a short help info.
 *
 * @author        Artem Prihodko <webtemyk@yandex.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class QuickStart extends Titan_PageBase {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'quickstart';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-clock';

	/**
	 * Menu icon (only if a page is placed as a main menu).
	 * For example: '~/assets/img/menu-icon.png'
	 * For example dashicons: '\f321'
	 * @var string
	 */
	public $menu_icon;

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
	public $internal = false;

	/**
	 * {@inheritDoc}
	 *
	 * @since  6.0
	 * @var bool
	 */
	//public $add_link_to_plugin_actions = true;

	/**
	 * Заголовок страницы, также использует в меню, как название закладки
	 *
	 * @var bool
	 */
	public $show_page_title = true;

	/**
	 * @var object|\WBCR\Titan\Views
	 */
	public $view;

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

		$this->menu_title = __('Titan security', 'titan-security');
		$this->menu_sub_title = __( 'Quick start', 'titan-security' );;
		$this->page_menu_short_description = __('Start scanning and information about problems', 'titan-security');
		$this->menu_icon = '~/admin/assets/img/icon.png';

		$this->view = $this->plugin->view();

		parent::__construct($plugin);
	}

	public function getPageTitle()
	{
		return __('Quick start', 'titan-security');
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function assets($scripts, $styles)
	{
		parent::assets($scripts, $styles);

		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/quick-dashboard.css');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/quickstart.js');

		$this->styles->add( WTITAN_PLUGIN_URL . '/includes/vulnerabilities/assets/css/vulnerabilities-dashboard.css');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/includes/vulnerabilities/assets/js/vulnerability_ajax.js', ['jquery']);
		$this->scripts->localize( 'wtvulner', [ 'nonce' => wp_create_nonce('get_vulners')]);

		$this->styles->add( WTITAN_PLUGIN_URL . '/includes/audit/assets/css/audit-dashboard.css');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/includes/audit/assets/js/audit_ajax.js', ['jquery']);
		$this->scripts->localize( 'wtaudit', [ 'nonce' => wp_create_nonce('get_audits')]);

		$this->scripts->add( WTITAN_PLUGIN_URL . '/includes/scanner/assets/js/scanner.js' );
		$this->scripts->localize( 'wpnonce', [
			'start'  => wp_create_nonce( 'titan-start-scan' ),
			'stop'   => wp_create_nonce( 'titan-stop-scan' ),
			'status' => wp_create_nonce( 'titan-status-scan' ),
		] );

	}


	/**
	 * {@inheritdoc}
	 */
	public function showPageContent()
	{
		if( $this->plugin->is_premium() )
		{
			$scanner_started = $this->plugin->getOption( 'scanner_status' ) == 'started';
			$this->view->print_template('quickstart', ['scanner_started' => $scanner_started]);
		}
		else
			require_once WTITAN_PLUGIN_DIR."/admin/view.nolicense.php";
	}

}
