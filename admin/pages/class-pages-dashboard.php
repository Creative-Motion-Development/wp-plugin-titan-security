<?php

namespace WBCR\Titan\Page;

// Exit if accessed directly
use WBCR\Titan\Audit;
use WBCR\Titan\Plugin;
use WBCR\Titan\Views;
use WBCR\Titan\Vulnerabilities;
use WBCR\Titan\SiteChecker;

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
class Dashboard extends Base {

	/**
	 * {@inheritdoc}
	 */
	public $id = 'dashboard';

	/**
	 * {@inheritdoc}
	 */
	public $page_menu_dashicon = 'dashicons-dashboard';

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
	public $page_menu_position = 90;

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
	 * @var object|\WBCR\Titan\Model\Firewall
	 */
	//public $firewall;

	/**
	 * @var object|\WBCR\Titan\Vulnerabilities
	 */
	public $vulnerabilities;

	/**
	 * @var object|\WBCR\Titan\Audit
	 */
	public $audit;

	/**
	 * @var \WBCR\Titan\SiteChecker
	 */
	public $sites;

	/**
	 * @var \WBCR\Titan\Scanner
	 */
	public $scanner;

	/**
	 * @var \WBCR\Titan\Antispam
	 */
	public $antispam;

	/**
	 * @var \WBCR\Titan\Check
	 */
	public $check;

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
		$this->page_title = __('Dashboard', 'titan-security');;
		$this->menu_sub_title = $this->page_title;
		$this->page_menu_short_description = __('Start scanning and information about problems', 'titan-security');
		$this->menu_icon = '~/admin/assets/img/icon.png';

		$this->view = $this->plugin->view();
		//$this->firewall = new \WBCR\Titan\Model\Firewall();
		$this->vulnerabilities = new Vulnerabilities();
		$this->audit = new Audit();
		$this->sites = new SiteChecker();
		$this->scanner = new \WBCR\Titan\Scanner();
		$this->antispam = new \WBCR\Titan\Antispam();
		$this->check = new \WBCR\Titan\Check();

		parent::__construct($plugin);
	}

	/**
	 * @return string
	 */
	public function getMenuTitle()
	{
		return apply_filters('wbcr/titan/admin_menu_title', $this->menu_title);
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

		$this->scripts->request([
			'bootstrap.core',
			'bootstrap.tab',
		], 'bootstrap');

		$this->styles->request([
			'bootstrap.core',
			'bootstrap.tab',
		], 'bootstrap');

		$this->styles->add(WTITAN_PLUGIN_URL . '/includes/vulnerabilities/assets/css/vulnerabilities-dashboard.css');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/includes/vulnerabilities/assets/js/vulnerability_ajax.js', ['jquery']);
		$this->scripts->localize('wtvulner', ['nonce' => wp_create_nonce('get_vulners')]);

		$this->styles->add(WTITAN_PLUGIN_URL . '/includes/audit/assets/css/audit-dashboard.css');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/includes/audit/assets/js/audit_ajax.js', ['jquery']);
		$this->scripts->localize('wtaudit', ['nonce' => wp_create_nonce('get_audits')]);

		//$this->scripts->add(WTITAN_PLUGIN_URL . '/includes/scanner/assets/js/Chart.min.js', ['jquery']);
		//$this->scripts->add(WTITAN_PLUGIN_URL . '/includes/scanner/assets/js/statistic.js', ['jquery']);
		$this->scripts->add(WTITAN_PLUGIN_URL . '/includes/scanner/assets/js/scanner.js', ['jquery']);
		$this->scripts->localize('wpnonce', [
			'start' => wp_create_nonce('titan-start-scan'),
			'stop' => wp_create_nonce('titan-stop-scan'),
			'status' => wp_create_nonce('titan-status-scan'),
		]);

		$this->styles->add(WTITAN_PLUGIN_URL . '/includes/check/assets/css/check-dashboard.css');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/includes/check/assets/js/check.js', ['jquery']);
		$this->scripts->localize('update_nonce', wp_create_nonce("updates"));
		$this->scripts->localize('wtscanner', [
			'update_nonce' => wp_create_nonce("updates"),
			'hide_nonce' => wp_create_nonce("hide"),
		]);

		$this->styles->add(WTITAN_PLUGIN_URL . '/includes/vulnerabilities/assets/css/vulnerabilities-dashboard.css');
		$this->styles->add(WTITAN_PLUGIN_URL . '/includes/audit/assets/css/audit-dashboard.css');

		$this->styles->add(WTITAN_PLUGIN_URL . '/admin/assets/css/dashboard-dashboard.css');
		$this->scripts->add(WTITAN_PLUGIN_URL . '/admin/assets/js/dashboard.js');
		//$this->scripts->add('https://www.gstatic.com/charts/loader.js', [],'', WANTISPAMP_PLUGIN_VERSION);
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
		//FIREWALL
		$firewall = array();

		$firewall['firewall_mode'] = $this->plugin->getPopulateOption('firewall_mode');
		$firewall['firewall_pro_activated'] = defined('WTITANP_PLUGIN_ACTIVE') && WTITANP_PLUGIN_ACTIVE;
		//end FIREWALL

		//AUDIT
		$check_content = $this->check->getPageContent('check');
		//---
		$scanner_started = $this->plugin->getOption('scanner_status') == 'started';

		$this->view->print_template('dashboard', [
			'scanner_started' => $scanner_started,
			'this_plugin' => $this->plugin,
			'firewall' => $firewall,
			'vulnerabilities' => $this->vulnerabilities,
			'audit' => $this->audit,
			'sites' => $this->sites,
			'scanner' => $this->scanner->get_current_results(),
			'antispam' => $this->antispam,
			'check_content' => $check_content,
		]);
	}

}
