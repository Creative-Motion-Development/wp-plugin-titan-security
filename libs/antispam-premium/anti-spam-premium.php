<?php
/**
 * Plugin Name: Anti-spam premium
 * Plugin URI: http://wordpress.org/plugins/anti-spam/
 * Description: Premium addon for the Antispam plugin.
 * Author: CreativeMotion <wordpress.webraftic@gmail.com>
 * Version: 1.1.4
 * Text Domain: anti-spam
 * Domain Path: /languages/
 * Author URI: http://anti-spam.space
 */

// @formatter:off
// Выход при непосредственном доступе
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wantispamp_premium_load' ) ) {

	function wantispamp_premium_load() {

		# Если бесплатный плагин не установлен или вызвал ошибку, то прерываем выполнение кода
		if ( ! defined( 'WTITAN_PLUGIN_ACTIVE' ) || defined( 'WTITAN_PLUGIN_THROW_ERROR' ) ) {
			return;
		}

		$plugin = \WBCR\Titan\Plugin::app();

		# Если лицензия не активирована, то прерываем выполнение кода
		if ( ! wantispam_is_license_activate() ) {
			return;
		}

		// Устанавливаем контстанту, что плагин уже используется
		define( 'WANTISPAMP_PLUGIN_ACTIVE', true );

		// Устанавливаем контстанту c версией плагина
		define( 'WANTISPAMP_PLUGIN_VERSION', '1.1.4' );

		// Директория плагина
		define( 'WANTISPAMP_PLUGIN_DIR', dirname( __FILE__ ) );

		// Относительный путь к плагину
		define( 'WANTISPAMP_PLUGIN_BASE', plugin_basename( __FILE__ ) );

		// Ссылка к директории плагина
		define( 'WANTISPAMP_PLUGIN_URL', plugins_url( null, __FILE__ ) );

		require_once( WANTISPAMP_PLUGIN_DIR . '/includes/function.php' );
		require_once( WANTISPAMP_PLUGIN_DIR . '/includes/class-request-api.php' );
		require_once( WANTISPAMP_PLUGIN_DIR . '/includes/class-forms-listener.php' );

		// 3rd-party
		require_once( WANTISPAMP_PLUGIN_DIR . '/includes/3rd-party/class-extension.php' );

		if ( $plugin->getPopulateOption( 'protect_contacts_form7' ) && is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			require_once( WANTISPAMP_PLUGIN_DIR . '/includes/3rd-party/class-extention-contact-form7.php' );
		}

		if ( $plugin->getPopulateOption( 'protect_ninja_forms' ) && is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
			require_once( WANTISPAMP_PLUGIN_DIR . '/includes/3rd-party/class-extention-ninja-forms.php' );
		}

		if ( $plugin->getPopulateOption( 'protect_caldera_forms' ) && is_plugin_active( 'caldera-forms/caldera-core.php' ) ) {
			require_once( WANTISPAMP_PLUGIN_DIR . '/includes/3rd-party/class-extention-caldera.php' );
		}

		$is_protect_comments_form = $plugin->getPopulateOption( 'protect_comments_form' );
		$is_protect_register_form = $plugin->getPopulateOption( 'protect_register_form' );

		if ( $is_protect_comments_form || $is_protect_register_form ) {
			require_once( WANTISPAMP_PLUGIN_DIR . '/includes/cron-schedules.php' );

			if ( ! wp_next_scheduled( 'wantispamp_check_status_queue' ) ) {
				wp_schedule_event( time(), 'five_minets', 'wantispamp_check_status_queue' );
				\WBCR\Titan\Logger\Writter::info( "The cron event added for wantispamp_check_status_queue hook!" );
			}
		}

		require_once( WANTISPAMP_PLUGIN_DIR . '/includes/plugin-rest-api.php' );

		if ( is_admin() ) {
			require_once( WANTISPAMP_PLUGIN_DIR . '/admin/pages/class-pages-settings.php' );
			require_once( WANTISPAMP_PLUGIN_DIR . '/admin/includes/class-comments-list-table.php' );
			require_once( WANTISPAMP_PLUGIN_DIR . '/admin/includes/class-users-list-table.php' );
			require_once( WANTISPAMP_PLUGIN_DIR . '/admin/boot.php' );

			// Rewrite free plugin settings page
			$plugin->registerPage( 'WBCR\Titan\Page\Progress', WANTISPAMP_PLUGIN_DIR . '/admin/pages/class-pages-settings.php' );
		}

		if ( ! wp_doing_ajax() || ! isset( $_REQUEST['action'] ) ) {
			return;
		}

		switch ( $_REQUEST['action'] ) {
			case 'waspam-check-existing-comments':
				require_once( WANTISPAMP_PLUGIN_DIR . '/admin/ajax/check-existing-comments.php' );
				break;
		}
	}
	wantispamp_premium_load();
	//add_action( 'plugins_loaded', 'wantispamp_premium_load', 20 );

	/**
	 * Register activation hook
	 * @since 1.0.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	function wantispamp_activate() {
		# Если бесплатный плагин не установлен или вызвал ошибку, то прерываем выполнение кода
		if ( ! defined( 'WTITAN_PLUGIN_ACTIVE' ) || defined( 'WTITAN_PLUGIN_THROW_ERROR' ) ) {
			wp_die( 'The plugin CreativeMotion Anti-spam is not activated!' );
		}

		$log_message = "Premium plugin starts activation [START].\r\n";

		\WBCR\Titan\Logger\Writter::info( $log_message );

		if ( ! add_role( 'spam', __( 'Spam' ), [] ) ) {
			\WBCR\Titan\Logger\Writter::warning( "Role spam is already exists!" );
		}
		if ( ! add_role( 'spam_checking', __( "Spam checking queue", 'titan-security' ), [] ) ) {
			\WBCR\Titan\Logger\Writter::warning( "Role spam_checking is already exists!" );
		}

		\WBCR\Titan\Logger\Writter::info( "Roles have been created!" );
		\WBCR\Titan\Logger\Writter::info( "Premium plugin has been activated [END]!" );
		//register_uninstall_hook( __FILE__, 'wantispamp_uninstall' );
	}

	register_activation_hook( __FILE__, 'wantispamp_activate' );

	/**
	 * Register deactivation hook
	 * @since 1.0.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	function wantispamp_deactivate() {
		if ( wp_next_scheduled( 'wantispamp_check_status_queue' ) ) {
			wp_unschedule_hook( 'wantispamp_check_status_queue' );
		}
	}

	register_deactivation_hook( __FILE__, 'wantispamp_deactivate' );

	// And here goes the uninstallation function:
	/*function wantispamp_uninstall(){
	    //  codes to perform during unistallation
	}*/
}


// @formatter:on