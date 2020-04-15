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

		self::app()->registerPage('WBCR\Titan\Page\Antispam', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-antispam.php');

		//self::app()->registerPage('WBCR\Titan\Page\QuickStart', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-quickstart.php');
		self::app()->registerPage('WBCR\Titan\Page\Dashboard', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-dashboard.php');

		self::app()->registerPage('WBCR\Titan\Page\Check', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-check.php');
		//self::app()->registerPage('WBCR\Titan\Page\Scanner', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-scanner.php');
		self::app()->registerPage('WBCR\Titan\Page\SiteChecker', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-sitechecker.php');

		self::app()->registerPage('WBCR\Titan\Page\Logs', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-logs.php');

		self::app()->registerPage('WBCR\Titan\Page\Tweaks', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-tweaks.php');

		self::app()->registerPage('WBCR\Titan\Page\Brute_Force', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-bruteforce.php');

		self::app()->registerPage('WBCR\Titan\Page\License', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-license.php');

		self::app()->registerPage('WBCR\Titan\Page\PluginSettings', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-plugin-settings.php');

		// Firewall
		if( !defined('WTITANP_PLUGIN_ACTIVE') ) {
			self::app()->registerPage('WBCR\Titan\Page\Firewall', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall.php');
			self::app()->registerPage('WBCR\Titan\Page\Firewall_Settings', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall-settings.php');
			self::app()->registerPage('WBCR\Titan\Page\Firewall_Blocking', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall-blocking.php');
			//self::app()->registerPage('WBCR\Titan\Page\Firewall_Attacks_Log', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall-attacks-log.php');
		}
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
		require_once(WTITAN_PLUGIN_DIR . '/admin/class-page-titan-basic.php');

		if( defined('DOING_AJAX') && DOING_AJAX ) {
			require(WTITAN_PLUGIN_DIR . '/admin/ajax/logs.php');
		}

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
		// Bruteforce
		//require_once(WTITAN_PLUGIN_DIR . '/includes/bruteforce/class-limit-login-attempts.php');

		// Tweaks
		require_once(WTITAN_PLUGIN_DIR . '/includes/tweaks/class-security-tweaks.php');

		if( $this->getPopulateOption('strong_password') ) {
			require_once(WTITAN_PLUGIN_DIR . '/includes/tweaks/password-requirements/boot.php');
		}

		// Logger
		require_once(WTITAN_PLUGIN_DIR . '/includes/logger/class-logger-writter.php');
		new \WBCR\Titan\Logger\Writter();

		// Antispam
		require_once(WTITAN_PLUGIN_DIR . '/includes/antispam/boot.php');
	}

	/**
	 * @return bool
	 */
	public function currentUserCan()
	{
		$permission = $this->isNetworkActive() ? 'manage_network' : 'manage_options';

		return current_user_can($permission);
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

