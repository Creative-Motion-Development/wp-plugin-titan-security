<?php
/*
Plugin Name: Titan security
Plugin URI: http://wordpress.org/plugins/titan-security/
Description: Wordfence Security - Anti-virus, Firewall and Malware Scan
Version: 1.0.0
Author: CreativeMotion
Text Domain: titan-security
Author URI: https://cm-wp.com/
License: GPLv3
*/

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * Developers who contributions in the development plugin:
 *
 * Alexander Kovalev
 * ---------------------------------------------------------------------------------
 * Full plugin development.
 *
 * Email:         alex.kovalevv@gmail.com
 * Personal card: https://alexkovalevv.github.io
 * Personal repo: https://github.com/alexkovalevv
 * ---------------------------------------------------------------------------------
 */

/**
 * -----------------------------------------------------------------------------
 * CHECK REQUIREMENTS
 * Check compatibility with php and wp version of the user's site. As well as checking
 * compatibility with other plugins from Webcraftic.
 * -----------------------------------------------------------------------------
 */

require_once(dirname(__FILE__) . '/libs/factory/core/includes/class-factory-requirements.php');

// @formatter:off
$wtitan_plugin_info = [
	'prefix' => 'titan_',
	'plugin_name' => 'titan_security',
	'plugin_title' => __('Titan security', 'anti-spam'),

	// PLUGIN SUPPORT
	'support_details' => [
		'url' => 'https://anti-spam.space',
		'pages_map' => [
			'support' => 'support',           // {site}/support
			'docs' => 'docs'               // {site}/docs
		]
	],

	// PLUGIN PREMIUM SETTINGS
	'has_premium' => true,
	'license_settings' => [
		'provider' => 'freemius',
		'slug' => 'antispam-premium',
		'plugin_id' => '5079',
		'public_key' => 'pk_98a99846a14067246257d4f43c04a',
		//'plugin_id'          => '4865',
		//'public_key'         => 'pk_05cbde6c0f9c96814c3b3cbff2259',
		'price' => 15,
		'has_updates' => false,
		'updates_settings' => [
			'maybe_rollback' => true,
			'rollback_settings' => [
				'prev_stable_version' => '0.0.0'
			]
		]
	],

	// PLUGIN ADVERTS
	'render_adverts' => true,
	'adverts_settings' => [
		'dashboard_widget' => true, // show dashboard widget (default: false)
		'right_sidebar' => true, // show adverts sidebar (default: false)
		'notice' => true, // show notice message (default: false)
	],

	// FRAMEWORK MODULES
	'load_factory_modules' => [
		['libs/factory/bootstrap', 'factory_bootstrap_000', 'admin'],
		['libs/factory/forms', 'factory_forms_000', 'admin'],
		['libs/factory/pages', 'factory_pages_000', 'admin'],
		['libs/factory/clearfy', 'factory_clearfy_000', 'all'],
		['libs/factory/freemius', 'factory_freemius_000', 'all'],
		//['libs/factory/feedback', 'factory_feedback_000', 'admin']
	]
];

$wtitan_compatibility = new Wbcr_Factory000_Requirements(__FILE__, array_merge($wtitan_plugin_info, [
	'plugin_already_activate' => defined('WTITAN_PLUGIN_ACTIVE'),
	'required_php_version' => '5.4',
	'required_wp_version' => '4.2.0',
	'required_clearfy_check_component' => false
]));

/**
 * If the plugin is compatible, then it will continue its work, otherwise it will be stopped,
 * and the user will throw a warning.
 */
if( !$wtitan_compatibility->check() ) {
	return;
}

/**
 * -----------------------------------------------------------------------------
 * CONSTANTS
 * Install frequently used constants and constants for debugging, which will be
 * removed after compiling the plugin.
 * -----------------------------------------------------------------------------
 */

// This plugin is activated
define('WTITAN_PLUGIN_ACTIVE', true);
define('WTITAN_PLUGIN_VERSION', $wtitan_compatibility->get_plugin_version());
define('WTITAN_PLUGIN_DIR', dirname(__FILE__));
define('WTITAN_PLUGIN_BASE', plugin_basename(__FILE__));
define('WTITAN_PLUGIN_URL', plugins_url(null, __FILE__));

#comp remove
// Эта часть кода для компилятора, не требует редактирования.
// Все отладочные константы будут удалены после компиляции плагина.

// Сборка плагина
// build: free, premium, ultimate
if( !defined('BUILD_TYPE') ) {
	define('BUILD_TYPE', 'free');
}
// Языки уже не используются, нужно для работы компилятора
// language: en_US, ru_RU
if( !defined('LANG_TYPE') ) {
	define('LANG_TYPE', 'en_EN');
}

// Тип лицензии
// license: free, paid
if( !defined('LICENSE_TYPE') ) {
	define('LICENSE_TYPE', 'free');
}

// wordpress language
if( !defined('WPLANG') ) {
	define('WPLANG', LANG_TYPE);
}

/**
 * Включить режим отладки миграций с версии x.x.x до x.x.y. Если true и
 * установлена константа FACTORY_MIGRATIONS_FORCE_OLD_VERSION, ваш файл
 * миграции будет вызваться постоянно.
 */
if( !defined('FACTORY_MIGRATIONS_DEBUG') ) {
	define('FACTORY_MIGRATIONS_DEBUG', false);

	/**
	 * Так как, после первого выполнения миграции, плагин обновляет
	 * опцию plugin_version, чтобы миграция больше не выполнялась,
	 * в тестовом режиме миграций, старая версия плагина берется не
	 * из опции в базе данных, а из текущей константы.
	 *
	 * Новая версия плагина всегда берется из константы WTITAN_PLUGIN_VERSION
	 * или из комментариев к входному файлу плагина.
	 */
	//define( 'FACTORY_MIGRATIONS_FORCE_OLD_VERSION', '1.1.9' );
}

/**
 * Включить режим отладки обновлений плагина и обновлений его премиум версии.
 * Если true, плагин не будет кешировать результаты проверки обновлений, а
 * будет проверять обновления через установленный интервал в константе
 * FACTORY_CHECK_UPDATES_INTERVAL.
 */
if( !defined('FACTORY_UPDATES_DEBUG') ) {
	define('FACTORY_UPDATES_DEBUG', false);

	// Через какой интервал времени проверять обновления на удаленном сервере?
	define('FACTORY_CHECK_UPDATES_INTERVAL', MINUTE_IN_SECONDS);
}

/**
 * Включить режим отладки для рекламного модуля. Если FACTORY_ADVERTS_DEBUG true,
 * то рекламный модуля не будет кешировать запросы к сереверу. Упрощает настройку
 * рекламы.
 */
if( !defined('FACTORY_ADVERTS_DEBUG') ) {
	define('FACTORY_ADVERTS_DEBUG', true);
}

/**
 * Остановить показ рекламы для всех плагинов созданных на Factory фреймворке.
 * Это может пригодиться в некоторых случаях, при неисправностях или из-за
 * файрвола в стране пользователя. Чтобы реклама не обременяла пользователя
 * он может ее заблокировать.
 */
if( !defined('FACTORY_ADVERTS_BLOCK') ) {
	define('FACTORY_ADVERTS_BLOCK', false);
}

// the compiler library provides a set of functions like onp_build and onp_license
// to check how the plugin work for diffrent builds on developer machines

require_once(WTITAN_PLUGIN_DIR . '/libs/onepress/compiler/boot.php');
// creating a plugin via the factory

// #fix compiller bug new Factory000_Plugin

/**
 * Отладочная константа
 */
if( !defined('WTITAN_DEBUG') ) {
	define('WTITAN_DEBUG', true);
}
#endcomp

if( !defined('WFWAF_LOG_PATH') ) {
	if( !defined('WP_CONTENT_DIR') ) { //Loading before WordPress
		exit();
	}
	//define('WFWAF_LOG_PATH', WP_CONTENT_DIR . '/titan_logs/');
	define('WFWAF_LOG_PATH', WTITAN_PLUGIN_DIR . '/includes/firewall/titan_logs/');
}

/**
 * Constant to determine if Wordfence is installed on another WordPress site one or more directories up in
 * auto_prepend_file mode.
 */
define('WFWAF_SUBDIRECTORY_INSTALL', class_exists('wfWAF') && !in_array(realpath(WTITAN_PLUGIN_DIR . '/includes/firewall/libs/wordfence/init.php'), get_included_files()));

if( !WFWAF_SUBDIRECTORY_INSTALL ) {
	require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/libs/wordfence/init.php');
	if( !wfWAF::getInstance() ) {
		define('WFWAF_AUTO_PREPEND', false);
		require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/bootstrap.php');
	}
}

/**
 * -----------------------------------------------------------------------------
 * PLUGIN INIT
 * -----------------------------------------------------------------------------
 */
require_once(WTITAN_PLUGIN_DIR . '/libs/factory/core/boot.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/functions.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/class-titan-security-plugin.php');

try {
	new \WBCR\Titan\Plugin(__FILE__, array_merge($wtitan_plugin_info, [
		'plugin_version' => WTITAN_PLUGIN_VERSION,
		'plugin_text_domain' => $wtitan_compatibility->get_text_domain(),
	]));
} catch( Exception $e ) {
	// Plugin wasn't initialized due to an error
	define('WTITAN_PLUGIN_THROW_ERROR', true);

	$wtitan_plugin_error_func = function () use ($e) {
		$error = sprintf("The %s plugin has stopped. <b>Error:</b> %s Code: %s", 'CreativeMotion Titan security', $e->getMessage(), $e->getCode());
		echo '<div class="notice notice-error"><p>' . $error . '</p></div>';
	};

	add_action('admin_notices', $wtitan_plugin_error_func);
	add_action('network_admin_notices', $wtitan_plugin_error_func);
}
// @formatter:on