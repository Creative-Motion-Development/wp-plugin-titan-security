<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Base module class
require_once WTITAN_PLUGIN_DIR . "/includes/class.module-base.php";

require_once( 'classes/class-antispam.php' );
require_once( 'classes/class-protector.php' );
