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

