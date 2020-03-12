<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'class.loader.php';
require_once 'class.client.php';
require_once 'request/class.request.php';

function wbcr_load_antispam_client($path, $recursive = true) {
	foreach(scandir($path) as $file) {
		if($file == '.' || $file == '..' || $file == 'boot.php' || $file == 'test.php') {
			continue;
		}

		$newPath = $path . DIRECTORY_SEPARATOR . $file;
		if(is_dir($newPath)) {
			if($recursive) {
				wbcr_load_antispam_client($newPath);
			}
		} else {
			/** @noinspection PhpIncludeInspection */
			require_once $newPath;
		}
	}
}

$path = dirname(__FILE__);
wbcr_load_antispam_client($path . DIRECTORY_SEPARATOR . 'entity');
wbcr_load_antispam_client($path . DIRECTORY_SEPARATOR . 'request');
wbcr_load_antispam_client($path . DIRECTORY_SEPARATOR . 'response');
