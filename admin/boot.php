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

require_once(WTITAN_PLUGIN_DIR . '/admin/class-page-titan-basic.php');

// Vulner class
require_once WTITAN_PLUGIN_DIR."/includes/vulnerabilities/boot.php";
// Audit class
require_once WTITAN_PLUGIN_DIR."/includes/audit/boot.php";
// SiteChecker class
require_once WTITAN_PLUGIN_DIR."/includes/sitechecker/boot.php";
// Scanner class
require_once WTITAN_PLUGIN_DIR."/includes/scanner/boot.php";
