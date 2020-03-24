<?php
/**
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @version       1.0
 * @copyright (c) 10.02.2020, Webcraftic
 */

use WBCR\Titan\Client\Client;
use WBCR\Titan\MalwareScanner\HashListPool;
use WBCR\Titan\MalwareScanner\Scanner;
use WBCR\Titan\Plugin;

add_filter( 'cron_schedules', 'titan_add_minute_schedule' );
/**
 * @param array $schedules
 */
function titan_add_minute_schedule( $schedules ) {
	$schedules['minute'] = [
		'interval' => 30,
		'display'  => __( 'Once 30 sec', 'titan-security' ),
	];

	return $schedules;
}

add_action( 'titan_scheduled_scanner', 'titan_scheduled_scanner' );
/**
 * @throws Exception
 */
function titan_scheduled_scanner() {
	global $wp_version;
	require_once WTITAN_PLUGIN_DIR . '/api-client/boot.php';
	require_once WTITAN_PLUGIN_DIR . '/includes/scanner/classes/scanner/boot.php';

	/** @var Scanner $scanner */
	$scanner = get_option( Plugin::app()->getPrefix() . 'scanner', null );
	if ( is_null( $scanner ) ) {
		error_log( 'Scanner does not exists' );

		return;
	}

	set_time_limit( 0 );

	$matched = get_option( Plugin::app()->getPrefix() . 'scanner_malware_matched', [] );
	$matched = $scanner->scan( 100, $matched );
	$scanner->remove_scanned_files( 100 );
	$matched = array_merge( $matched, Plugin::app()->getOption( 'scanner_malware_matched', [] ) );
	Plugin::app()->updateOption( 'scanner_malware_matched', $matched );

	if ( $scanner->get_files_count() < 1 ) {
		titan_remove_scheduler_scanner();
		$client = new Client( Plugin::app()->premium->get_license()->get_key() );
		$result = $client->check_cms_premium( $wp_version, collect_wp_hash_sum() );
		if ( ! is_null( $result ) ) {
			$matched = array_merge( $matched, $result->items );
		}
	} else {
		Plugin::app()->updateOption( 'scanner', $scanner, false );
	}

	$matched = array_merge( $matched, Plugin::app()->getOption( 'scanner_malware_matched', [] ) );
	Plugin::app()->updateOption( 'scanner_malware_matched', $matched );
}

/**
 * Creating cron task
 */
function titan_create_scheduler_scanner() {
	// todo: реализовать уровень проверки сайта

	$license_key = Plugin::app()->premium->get_license()->get_key();
	$client = new Client( $license_key );
	/** @var array[]|WBCR\Titan\Client\Entity\Signature[] $signatures */
	$signatures = $client->get_signatures();

	foreach ( $signatures as $key => $signature ) {
		$signatures[ $key ] = $signature->to_array();
	}
	$signature_pool = WBCR\Titan\MalwareScanner\SignaturePool::fromArray( $signatures );

	$file_hash = get_option( Plugin::app()->getPrefix() . 'files_hash' );
	if ( ! $file_hash ) {
		$file_hash_pool = HashListPool::fromArray( $file_hash );
	} else {
		$file_hash_pool = null;
	}

	$scanner = new WBCR\Titan\MalwareScanner\Scanner( ABSPATH, $signature_pool, $file_hash_pool, [
		'wp-admin',
		'wp-includes',
		'debug.log',
	] );

	Plugin::app()->updateOption( 'scanner', $scanner );
	Plugin::app()->updateOption( 'scanner_malware_matched', [] );
	Plugin::app()->updateOption( 'scanner_files_count', $scanner->get_files_count() );
	Plugin::app()->updateOption( 'scanner_status', 'started' );
	wp_schedule_event( time(), 'minute', 'titan_scheduled_scanner' );
}

/**
 * Deleting a cron task
 */
function titan_remove_scheduler_scanner() {
	$scanner = get_option( Plugin::app()->getPrefix() . 'scanner' );
	if ( $scanner ) {
		$file_hash = [];
		foreach ( $scanner->getFileList() as $file ) {
			$file_hash[ $file->getPath() ] = $file->getFileHash();
		}
		Plugin::app()->updateOption( 'files_hash', $file_hash );
	}

	wp_unschedule_hook( 'titan_scheduled_scanner' );
	Plugin::app()->updateOption( 'scanner_status', 'stopped' );
}

/**
 * Collecting hash sums of WP files
 *
 * @param string $path
 *
 * @return array
 */
function collect_wp_hash_sum( $path = ABSPATH ) {
	$hash = [];

	foreach ( scandir( $path ) as $item ) {
		if ( $item == '.' || $item == '..' || $item == 'plugins' || $item == 'themes' ) {
			continue;
		}

		$newPath      = $path . $item;
		$relativePath = str_replace( ABSPATH, '', $newPath );
		if ( is_dir( $newPath ) ) {
			$hash = array_merge( $hash, collect_wp_hash_sum( $newPath . '/' ) );
		} else {
			$hash[ $relativePath ] = md5_file( $newPath );
		}
	}

	return $hash;
}


add_action('admin_notices', 'titan_ssl_cert_notice');
/**
 *
 */
function titan_ssl_cert_notice() {
	require_once WTITAN_PLUGIN_DIR . '/includes/audit/classes/class.cert.php';

	$cert = \WBCR\Titan\Cert\Cert::get_instance();
	$output = false;
	$message = '';
	$type = 'notice-warning';

	if($cert->is_available()) {
		if(!$cert->is_lets_encrypt()) {
			$remaining = $cert->get_expiration_timestamp() - time();
			if($remaining <= 86400 * 90) { // 3 month (90 days)
				$message = 'The SSL certificate expires in less than three months';
				$output = true;
			} else if($remaining <= 86400 * 3) { // 3 days
				$type = 'notice-error';
				$message = 'The SSL certificate expires in less than three days';
				$output = true;
			}
		}
	} else {
		$output = true;
		$type = 'notice-error';
		$message = $cert->get_error_message();
	}

	if($output) {
		echo <<<HTML
<div id="message" class="notice {$type} is-dismissible">
	<p>{$message}</p>
</div>
HTML;
	}
}

add_action('init', 'titan_init_https_redirect');
function titan_init_https_redirect() {
	$strict_https = Plugin::app()->getPopulateOption('strict_https', false);
	if(!is_ssl() && $strict_https) {
		wp_redirect(home_url(add_query_arg($_GET, $_SERVER['REQUEST_URI']), 'https'));
		die;
	}
}