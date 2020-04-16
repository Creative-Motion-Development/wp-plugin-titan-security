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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Include dashboard widget
 *
 * Include functionality the output of the widget on the dashboard.
 * Only one dashboard widget must be shown for some plugins with this setting (dashboard_widget).
 *
 * @since 1.0.0 Added
 */
add_action( 'current_screen', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$current_screen = get_current_screen();

	if ( ! in_array( $current_screen->id, [ 'dashboard', 'dashboard-network' ] ) ) {
		return;
	}

	require_once WANTISPAMP_PLUGIN_DIR . '/admin/includes/class-dashboard-widget.php';
	new \WBCR\Titan\Premium\Dashboard_Widget();
}, 10, 2 );

/**
 * Displays a notification inside the Antispam interface, on all pages of the plugin.
 * This is necessary to remind the user to update the configuration of the plugin components,
 * Otherwise, the newly activated components will not be involved in the work of the plugin.
 *
 * @param Wbcr_Factory000_Plugin                   $plugin
 * @param Wbcr_FactoryPages000_ImpressiveThemplate $obj
 *
 * @return bool
 */
add_action( 'wbcr/factory/pages/impressive/print_all_notices', function ( $plugin, $obj ) {
	if ( $plugin->getPluginName() != \WBCR\Titan\Plugin::app()->getPluginName() ) {
		return;
	}
	$count_comments = wantispamp_get_unchecked_comments_count();

	if ( ! $count_comments ) {
		return;
	}

	$manage_comments_link = '<a href="' . admin_url( 'edit-comments.php?comment_status=moderated' ) . '">' . $count_comments . '</a>';
	$action_link          = '<a class="button button-default" href="' . wp_nonce_url( \WBCR\Titan\Plugin::app()->getPluginPageUrl( 'progress', [ 'action' => 'check-existing-comments' ] ), 'wantispam_checking_unapproved_comments' ) . '">' . __( 'Yes, do it' ) . '</a>';

	$notice_text = sprintf( __( "You have %s unapproved comments. Would you like to send them for spam checking? %s", "anti-spam" ), $manage_comments_link, $action_link );

	$obj->printWarningNotice( $notice_text );
}, 10, 2 );

/**
 * Print admin notice: "Would you like to send them for spam checking?"
 *
 * If user clicked button "Yes, do it", plugin will exec action,
 * that put all unapproved comments to spam check queue.
 */
add_action( 'wbcr/factory/admin_notices', function ( $notices, $plugin_name ) {
	if ( $plugin_name != \WBCR\Titan\Plugin::app()->getPluginName() ) {
		return $notices;
	}

	$count_comments = wantispamp_get_unchecked_comments_count();

	if ( ! $count_comments ) {
		return $notices;
	}

	$manage_comments_link = '<a href="' . admin_url( 'edit-comments.php?comment_status=moderated' ) . '">' . $count_comments . '</a>';
	$action_link          = '<a class="button button-default" href="' . wp_nonce_url( \WBCR\Titan\Plugin::app()->getPluginPageUrl( 'progress', [ 'action' => 'check-existing-comments' ] ), 'wantispam_checking_unapproved_comments' ) . '">' . __( 'Yes, do it' ) . '</a>';

	$notice_text = sprintf( __( "You have %s unapproved comments. Would you like to send them for spam checking? %s", "anti-spam" ), $manage_comments_link, $action_link );

	$notices[] = [
		'id'              => 'wantispam_check_unapproved_comments',
		'type'            => 'warning',
		'where'           => [
			'edit-comments',
			'plugins',
			'themes',
			'dashboard',
			'edit',
			'settings'
		],
		'dismissible'     => true,
		'dismiss_expires' => 0,
		'text'            => '<p>' . $notice_text . '</p>'
	];

	return $notices;
}, 10, 2 );

/**
 * Changes plugin title in plugin interface header
 */
add_filter( 'wbcr/factory/pages/impressive/plugin_title', function ( $title, $plugin_name ) {
	if ( \WBCR\Titan\Plugin::app()->getPluginName() == $plugin_name ) {
		return __( 'Anti-spam Pro', 'realforce' );
	}

	return $title;
}, 30, 2 );
