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

		self::app()->registerPage('WBCR\Titan\Page\Firewall', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall.php');
		self::app()->registerPage('WBCR\Titan\Page\Firewall_Settings', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall-settings.php');
		self::app()->registerPage('WBCR\Titan\Page\Firewall_Blocking', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall-blocking.php');
		self::app()->registerPage('WBCR\Titan\Page\Firewall_Attacks_Log', WTITAN_PLUGIN_DIR . '/admin/pages/firewall/class-pages-firewall-attacks-log.php');
		self::app()->registerPage('WBCR\Titan\Page\QuickStart', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-quickstart.php');

		self::app()->registerPage('WBCR\Titan\Page\Check', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-check.php');
		self::app()->registerPage('WBCR\Titan\Page\Scanner', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-scanner.php');
		self::app()->registerPage('WBCR\Titan\Page\SiteChecker', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-sitechecker.php');
		self::app()->registerPage('WBCR\Titan\Page\License', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-license.php');
		self::app()->registerPage('WBCR\Titan\Page\Logs', WTITAN_PLUGIN_DIR . '/admin/pages/class-pages-logs.php');
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

		add_action('plugins_loaded', function () {
			$this->register_pages();
		}, 30);
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.0
	 */
	private function global_scripts()
	{
		// Firewall
		require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/boot.php');

		// Logger
		require_once(WTITAN_PLUGIN_DIR . '/includes/logger/class-logger-writter.php');
		new \WBCR\Titan\Logger\Writter();
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

