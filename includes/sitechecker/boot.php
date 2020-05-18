<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//API Client
require_once WTITAN_PLUGIN_DIR . "/libs/api-client/boot.php";

// Base module class
require_once WTITAN_PLUGIN_DIR . "/includes/class.module-base.php";

//Module class
require_once "classes/class.sitechecker.php";
