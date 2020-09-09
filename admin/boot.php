<?php
/**
 * Usually in this file places the code that is responsible for the notification, compatibility with other plugins,
 * minor functions that must be performed on all pages of the admin panel.
 *
 * This file should contain code that applies only to the administration area.
 *
 * @author    Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright Webcraftic 20.11.2019
 * @version   1.0
 */

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * Этот хук реализует условную логику перенаправления на страницу мастера настроек,
 * сразу после активации плагина.
 */
add_action('admin_init', function () {

	$plugin = \WBCR\Titan\Plugin::app();

	// If the user has updated the plugin or activated it for the first time,
	// you need to show the page "What's new?"
	if( !$plugin->isNetworkAdmin() ) {
		$setup_page_viewed = $plugin->request->get('wtitan_setup_page_viewed', null);
		if( is_null($setup_page_viewed) ) {
			if( \WBCR\Titan\Plugin\Helper::is_need_show_setup_page() ) {
				try {
					$redirect_url = '';
					if( class_exists('Wbcr_FactoryPages000') ) {
						$redirect_url = $plugin->getPluginPageUrl('setup', ['wtitan_setup_page_viewed' => 1]);
					}
					if( $redirect_url ) {
						wp_safe_redirect($redirect_url);
						die();
					}
				} catch( Exception $e ) {
				}
			}
		} else {
			if( \WBCR\Titan\Plugin\Helper::is_need_show_setup_page() ) {
				delete_option($plugin->getOptionName('setup_wizard'));
			}
		}
	}
});

/**
 * Выводит кнопку настроек в шапке интерфейса плагина
 */
/*
add_action( 'wbcr/factory/pages/impressive/header', function ( $plugin_name ) {
	if ( $plugin_name != WBCR\Titan\Plugin::app()->getPluginName() ) {
		return;
	}
	?>
	<a href="<?php echo WBCR\Titan\Plugin::app()->getPluginPageUrl( 'plugin_settings' ) ?>" class="wbcr-factory-button wbcr-factory-type-settings">
		<?php echo apply_filters( 'wbcr/clearfy/settings_button_title', __( 'Titan settings', 'titan-security' ) ); ?>
	</a>
	<?php
} );
*/

/**
 * Print admin notice: "Would you like to send them for spam checking?"
 *
 * If user clicked button "Yes, do it", plugin will exec action,
 * that put all unapproved comments to spam check queue.
 */
add_action('wbcr/factory/admin_notices', function ($notices, $plugin_name) {
	if( $plugin_name != \WBCR\Titan\Plugin::app()->getPluginName() || defined('WTITAN_PLUGIN_ACTIVE') ) {
		return $notices;
	}

	if( !\WBCR\Titan\Plugin::app()->is_premium() ) {
		return $notices;
	}

	$about_plugin_url = "https://anti-spam.space";
	$install_plugin_url = admin_url('update.php?action=install-plugin&plugin=anti-spam&_wpnonce=' . wp_create_nonce('activate-plugin_titan-security'));

	$notice_text = sprintf(__('Thanks for activating the premium Titan security plugin. You got a bonus, premium <a href="%s" target="_blank" rel="noopener">Anti-spam</a> plugin. Want to <a href="%s" target="_blank" rel="noopener">install it now</a>?', "titan-security"), $about_plugin_url, $install_plugin_url);

	$notices[] = [
		'id' => 'wtitan_bonus_suggestion',
		'type' => 'success',
		/*'where' => [
			'edit-comments',
			'plugins',
			'themes',
			'dashboard',
			'edit',
			'settings'
		],*/
		'dismissible' => true,
		'dismiss_expires' => 0,
		'text' => '<p><strong>Titan:</strong><br>' . $notice_text . '</p>'
	];

	return $notices;
}, 10, 2);

//if ( ! \WBCR\Titan\Plugin::app()->getOption( 'trial_notice_dismissed', false ) ) {
/**
 * Trial notice on plugin pages
 */
//add_action('wbcr/factory/pages/impressive/print_all_notices', function ($plugin, $obj) {
/*	if( is_plugin_active('plugins-scanner-premium/plugins-scanner-premium.php') || ($plugin->getPluginName() != \WBCR\Titan\Plugin::app()->getPluginName()) ) {
		return;
	}

	$notice_text = __('Plugins scanner - detect vulnerabilities in any of your plugins before activation', 'titan-security');
	$notice_text .= '&nbsp;<a href="https://titansitescanner.com/plugin-scanner/" target="_blank" rel="noopener" class="wtitan-get-plugins-scanner__btn">' . __('Sign Up for $9.99', 'titan-security') . '</a>';

	echo '<div class="alert alert-warning wbcr-factory-warning-notice wtitan-get-plugins-scanner__notice"><p><span class="dashicons dashicons-plugins-checked"></span> ' . $notice_text . '</p></div>';
	*///$obj->printWarningNotice($notice_text);

/** @var \Wbcr_Factory000_Plugin $plugin */
/** @var \Wbcr_FactoryPages000_ImpressiveThemplate $obj */
/*if ( ( \WBCR\Titan\Plugin::app()->premium->is_activate() ) || ( $plugin->getPluginName() != \WBCR\Titan\Plugin::app()->getPluginName() ) || $obj->id == 'license' ) {
	return;
}

$notice_text = __( 'Get the free trial edition (no credit card) contains all of the features included in the paid-for version of the product.', 'titan-security' );
$notice_text .= '&nbsp;<a href="' . add_query_arg( [ 'trial' => 1 ], \WBCR\Titan\Plugin::app()->getPluginPageUrl( 'license' ) ) . '" class="btn btn-gold btn-sm wt-notice-trial-button">' . __( 'Activate 30 days trial', 'titan-security' ) . '</a>';
$notice_text .= "<span id='wt-notice-hide-link' class='wt-notice-hide-link dashicons dashicons-no'></span>";
$obj->printWarningNotice( $notice_text );*/
//}, 10, 2);

/**
 * Trial notice on all WP admin pages
 */
/* add_action( "wbcr/factory/admin_notices", function ( $notices, $plugin_name )
{
	if ( ( \WBCR\Titan\Plugin::app()->premium->is_activate() ) || ( $plugin_name != \WBCR\Titan\Plugin::app()->getPluginName() ) || ! current_user_can( 'manage_options' ) ) {
		return $notices;
	}

	$notice_text = __( 'Get the free trial edition (no credit card) contains all of the features included in the paid-for version of the product.', 'titan-security' );
	$notice_text .= '&nbsp;<a href="' . add_query_arg( [ 'trial' => 1 ], \WBCR\Titan\Plugin::app()->getPluginPageUrl( 'license' ) ) . '" class="button button-primary">' . __( 'Activate 30 days trial', 'titan-security' ) . '</a>';
	$notices[]   = [
		'id'              => 'get_trial_for_' . \WBCR\Titan\Plugin::app()->getPluginName(),
		'type'            => 'info',
		'dismissible'     => true,
		'dismiss_expires' => 0,
		'text'            => "<p><b>" . \WBCR\Titan\Plugin::app()->getPluginTitle() . ":</b> " . $notice_text . '</p>'
	];

	return $notices;
}, 10, 2 ); */
//}

// Vulner class
require_once WTITAN_PLUGIN_DIR . "/includes/vulnerabilities/boot.php";
// Audit class
require_once WTITAN_PLUGIN_DIR . "/includes/audit/boot.php";
// SiteChecker class
require_once WTITAN_PLUGIN_DIR . "/includes/sitechecker/boot.php";
// Scanner class
require_once WTITAN_PLUGIN_DIR . "/includes/scanner/boot.php";
// Anti-spam class
require_once WTITAN_PLUGIN_DIR . "/includes/antispam/boot.php";
// Audit class
require_once WTITAN_PLUGIN_DIR . "/includes/check/boot.php";

