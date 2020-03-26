<?php

namespace WBCR\Titan;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * Transliteration core class
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 20.10.2019, Webcraftic
 */
class Plugin extends \Wbcr_Factory000_Plugin {

	/**
	 * Number of comments that will be sent for verification
	 *
	 * @since 6.2
	 */
	const COUNT_TO_CHECK = 30;

	/**
	 * @see self::app()
	 * @var \Wbcr_Factory000_Plugin
	 */
	private static $app;

	/**
	 * @since  6.0
	 * @var array
	 */
	private $plugin_data;

	/**
	 * @var \wfWAFStorageFile
	 */
	private $firewall_storage;

	/**
	 * @var \WBCR\Titan\Views
	 */
	public $view;

	/**
	 * Конструктор
	 *
	 * Применяет конструктор родительского класса и записывает экземпляр текущего класса в свойство $app.
	 * Подробнее о свойстве $app см. self::app()
	 *
	 * @param string $plugin_path
	 * @param array $data
	 *
	 * @throws \Exception
	 * @since  6.0
	 *
	 */
	public function __construct($plugin_path, $data)
	{
		parent::__construct($plugin_path, $data);

		self::$app = $this;
		$this->plugin_data = $data;

		$this->global_scripts();

		if( is_admin() ) {
			$this->admin_scripts();
		}
	}

	/**
	 * Статический метод для быстрого доступа к интерфейсу плагина.
	 *
	 * Позволяет разработчику глобально получить доступ к экземпляру класса плагина в любом месте
	 * плагина, но при этом разработчик не может вносить изменения в основной класс плагина.
	 *
	 * Используется для получения настроек плагина, информации о плагине, для доступа к вспомогательным
	 * классам.
	 *
	 * @return \Wbcr_Factory000_Plugin|\WBCR\Titan\Plugin
	 * @since  6.0
	 */
	public static function app()
	{
		return self::$app;
	}

	/**
	 * @return \wfWAFStorageFile
	 */
	public function fw_storage()
	{
		require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/libs/waf/init.php');

		if( !empty($this->firewall_storage) ) {
			return $this->firewall_storage;
		}

		$this->firewall_storage = new \wfWAFStorageFile(WFWAF_LOG_PATH . 'attack-data.php', WFWAF_LOG_PATH . 'ips.php', WFWAF_LOG_PATH . 'config.php', WFWAF_LOG_PATH . 'rules.php', WFWAF_LOG_PATH . 'wafRules.rules');

		return $this->firewall_storage;
	}

	public function view()
	{
		require_once WTITAN_PLUGIN_DIR . '/includes/class-views.php';

		if( !empty($this->view) ) {
			return $this->view;
		}
		$this->view = Views::get_instance(WTITAN_PLUGIN_DIR);

		return $this->view;
	}


	/**
	 * @throws \Exception
	 * @since  6.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */

	private function register_pages()
	{
		require_once(WTITAN_PLUGIN_DIR . '/admin/class-page-titan-basic.php');

		self::app()->registerPage('WBCR\Titan\Page\Antispam', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-antispam.php');

		self::app()->registerPage('WBCR\Titan\Page\QuickStart', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-quickstart.php');

		self::app()->registerPage('WBCR\Titan\Page\Check', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-check.php');
		self::app()->registerPage('WBCR\Titan\Page\Scanner', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-scanner.php');
		self::app()->registerPage('WBCR\Titan\Page\SiteChecker', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-sitechecker.php');

		self::app()->registerPage('WBCR\Titan\Page\Firewall', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall.php');
		self::app()->registerPage('WBCR\Titan\Page\Firewall_Settings', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall-settings.php');
		self::app()->registerPage('WBCR\Titan\Page\Firewall_Blocking', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall-blocking.php');
		self::app()->registerPage('WBCR\Titan\Page\Firewall_Attacks_Log', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall-attacks-log.php');
		self::app()->registerPage('WBCR\Titan\Page\Firewall_Login_Attempts', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall-login-attempts.php');

		self::app()->registerPage('WBCR\Titan\Page\Logs', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-logs.php');
		self::app()->registerPage('WBCR\Titan\Page\License', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-license.php');

		self::app()->registerPage('WBCR\Titan\Page\PluginSettings', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-plugin-settings.php');
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 */
	protected function init_activation()
	{
		include_once(WTITAN_PLUGIN_DIR . '/admin/class-activation.php');
		self::app()->registerActivation("\WBCR\Titan\Activation");
	}

	/**
	 * @throws \Exception
	 * @since  6.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	private function admin_scripts()
	{
		$this->init_activation();

		require_once(WTITAN_PLUGIN_DIR . '/admin/boot.php');

		if( defined('DOING_AJAX') && DOING_AJAX ) {
			require(WTITAN_PLUGIN_DIR . '/admin/ajax/firewall/change-firewall-mode.php');
			require(WTITAN_PLUGIN_DIR . '/admin/ajax/firewall/install-auto-prepend.php');
			require(WTITAN_PLUGIN_DIR . '/admin/ajax/firewall/block-ip.php');
			require(WTITAN_PLUGIN_DIR . '/admin/ajax/logs.php');
		}

		add_action( 'admin_bar_menu', [$this, 'admin_bar_menu'], 80 );
		
		add_action('plugins_loaded', function () {
			$this->register_pages();
		}, 30);
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  7.0
	 */
	private function global_scripts()
	{

		$enable_menu = $this->getPopulateOption( 'titan_extra_menu', false );
		if ( $enable_menu ) {
			add_action( 'admin_enqueue_scripts', [$this, 'admin_bar_enqueue'] );
			add_action( 'wp_enqueue_scripts', [$this, 'admin_bar_enqueue'] );
		}

		// Logger
		require_once(WTITAN_PLUGIN_DIR . '/includes/logger/class-logger-writter.php');
		new \WBCR\Titan\Logger\Writter();

		// Firewall
		require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/boot.php');

		// Antispam
		require_once(WTITAN_PLUGIN_DIR . '/includes/antispam/boot.php');

	}

	/**
	 */
	public function admin_bar_enqueue() {
		wp_enqueue_style( 'titan-adminbar-styles', WTITAN_PLUGIN_URL . '/assets/css/admin-bar.css', [], $this->getPluginVersion() );
	}

	/**
	 * @return bool
	 */
	public function currentUserCan() {
		$permission = $this->isNetworkActive() ? 'manage_network' : 'manage_options';

		return current_user_can( $permission );
	}

	/**
	 * Add menu to admin bar
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar
	 * 
	 */
	public function admin_bar_menu($wp_admin_bar)
	{
		$enable_menu = $this->getPopulateOption( 'titan_extra_menu', false );

		if ( ! $this->currentUserCan() || !$enable_menu ) {
			return;
		}

		if ( $this->isNetworkActive() ) {
			$settings_url = network_admin_url( 'settings.php' );
		} else {
			$settings_url = admin_url( 'admin.php' );
		}

		$settings_url     = $settings_url . '?page=quickstart-' . $this->getPluginName();
		$extra_menu_title = apply_filters( 'wbcr/titan/adminbar_menu_title', __( 'Titan Security', 'titan-security' ) );

		$menu_items = [];
		$menu_items = apply_filters( 'wbcr/titan/adminbar_menu_items', $menu_items );

		$menu_items['titan-dashboard']  = [
			'id'    => 'titan-dashboard',
			'title' => '<span class="dashicons dashicons-dashboard"></span> ' . __( 'Dashboard', 'titan-security' ),
			'href'  => $settings_url
		];
		$menu_items['titan-rating']  = [
			'id'    => 'titan-rating',
			'title' => '<span class="dashicons dashicons-heart"></span> ' . __( 'Do you like our plugin?', 'titan-security' ),
			'href'  => 'https://wordpress.org/support/plugin/anti-spam/reviews/'
		];

		if ( empty( $menu_items ) ) {
			return;
		}

		$wp_admin_bar->add_menu( [
			'id'    => 'titan-menu',
			'title' => '<span class="wtitan-admin-bar-menu-icon"></span><span class="wtitan-admin-bar-menu-title">' . $extra_menu_title . ' <span class="dashicons dashicons-arrow-down"></span></span>',
			'href'  => $settings_url
		] );

		foreach ( (array) $menu_items as $id => $item ) {
			$wp_admin_bar->add_menu( [
				'id'     => $id,
				'parent' => 'titan-menu',
				'title'  => $item['title'],
				'href'   => $item['href'],
				'meta'   => [
					'class' => isset( $item['class'] ) ? $item['class'] : ''
				]
			] );
		}
	}

	/**
	 * @return bool
	 */
	public function is_premium()
	{
		if( $this->premium->is_active() && $this->premium->is_activate() ) {
			return true;
		} else {
			return false;
		}
	}
}

