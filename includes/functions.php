<?php
/**
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @version       1.0
 * @copyright (c) 10.02.2020, Webcraftic
 */

use WBCR\Titan\Client\Client;
use WBCR\Titan\Client\Entity\CmsCheckItem;
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
	require_once WTITAN_PLUGIN_DIR . '/libs/api-client/boot.php';
	require_once WTITAN_PLUGIN_DIR . '/includes/scanner/classes/scanner/boot.php';

	/** @var Scanner $scanner */
	$scanner = get_option( Plugin::app()->getPrefix() . 'scanner', null );
	if ( is_null( $scanner ) ) {
		error_log( 'Scanner does not exists' );

		return;
	}

	set_time_limit( 0 );

	$speed       = Plugin::app()->getPopulateOption( 'scanner_speed', 'slow' );
	$files_count = @Scanner::SPEED_FILES[ $speed ];
	if ( is_null( $files_count ) ) {
		$files_count = Scanner::SPEED_FILES[ Scanner::SPEED_MEDIUM ];
	}

	$matched = get_option( Plugin::app()->getPrefix() . 'scanner_malware_matched', [] );
	$matched = $scanner->scan( $files_count, $matched );
	$scanner->remove_scanned_files( $files_count );
	$matched = array_merge( $matched, Plugin::app()->getOption( 'scanner_malware_matched', [] ) );
	Plugin::app()->updateOption( 'scanner_malware_matched', $matched );

	if ( $scanner->get_files_count() < 1 ) {
		titan_remove_scheduler_scanner();
		$matched = array_merge($matched, titan_check_cms());
	} else {
		Plugin::app()->updateOption( 'scanner', $scanner, false );
	}

	$matched = array_merge( $matched, Plugin::app()->getOption( 'scanner_malware_matched', [] ) );
	Plugin::app()->updateOption( 'scanner_malware_matched', $matched );
}

/**
 * @return CmsCheckItem[]
 */
function titan_check_cms() {
	global $wp_version;

	if ( Plugin::app()->is_premium() ) {
		$license_key = Plugin::app()->premium->get_license()->get_key();
	} else {
		$license_key = null;
	}

	$client = new Client( $license_key );

	if ( Plugin::app()->is_premium() ) {
		$result = $client->check_cms_premium( $wp_version, collect_wp_hash_sum() );
	} else {
		$result = $client->check_cms_free( $wp_version, collect_wp_hash_sum() );
	}

	if ( is_null( $result ) ) {
		return [];
	}

	WBCR\Titan\Logger\Writter::info(sprintf("Founded %d corrupted files", count($result->items)));

	foreach ( $result->items as $check_item ) {
		WBCR\Titan\Logger\Writter::debug(sprintf("File `%s` (action %s)", $check_item->path, $check_item->action));
		$path = dirname( WP_CONTENT_DIR ) . '/' . $check_item->path;
		switch ( $check_item->action ) {
			case CmsCheckItem::ACTION_REMOVE:
				if ( file_exists( $path ) && is_writable( $path ) ) {
					unlink( $path );
				}
				break;

			case CmsCheckItem::ACTION_REPAIR:
				if ( file_exists( $path ) && is_writeable( $path ) ) {
					$data = file_get_contents( $check_item->url );
					file_put_contents( $path, $data );
				}
				break;
		}
	}

	return $result->items;
}

/**
 * Creating cron task
 */
function titan_create_scheduler_scanner() {
	// todo: реализовать уровень проверки сайта

	if ( Plugin::app()->is_premium() ) {
		$license_key = Plugin::app()->premium->get_license()->get_key();
	} else {
		$license_key = null;
	}

	$client = new Client( $license_key );

	if ( Plugin::app()->is_premium() ) {
		$signatures = $client->get_signatures();
	} else {
		$signatures = $client->get_free_signatures();
	}

	/** @var array[]|WBCR\Titan\Client\Entity\Signature[] $signatures */

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


add_action( 'admin_notices', 'titan_ssl_cert_notice' );
/**
 *
 */
function titan_ssl_cert_notice() {
	require_once WTITAN_PLUGIN_DIR . '/includes/audit/classes/class.cert.php';

	$cert        = \WBCR\Titan\Cert\Cert::get_instance();
	$output      = false;
	$message     = '';
	$type        = 'notice-warning';
	$plugin_name = WBCR\Titan\Plugin::app()->getPluginTitle();

	if ( $cert->is_available() ) {
		if ( ! $cert->is_lets_encrypt() ) {
			$remaining = $cert->get_expiration_timestamp() - time();
			if ( $remaining <= 86400 * 90 ) { // 3 month (90 days)
				$message = 'The SSL certificate expires in less than three months';
				$output  = true;
			} else if ( $remaining <= 86400 * 3 ) { // 3 days
				$type    = 'notice-error';
				$message = 'The SSL certificate expires in less than three days';
				$output  = true;
			}
		}
	} else {
		$output  = true;
		$type    = 'notice-error';
		$message = $cert->get_error_message();
	}

	if ( $output ) {
		echo <<<HTML
<div id="message" class="notice {$type} is-dismissible">
	<p>
		<b>{$plugin_name}</b>:<br>
		{$message}
	</p>
</div>
HTML;
	}
}

add_action( 'init', 'titan_init_https_redirect' );
function titan_init_https_redirect() {
	$strict_https = Plugin::app()->getPopulateOption( 'strict_https', false );
	if ( ! is_ssl() && $strict_https ) {
		wp_redirect( home_url( add_query_arg( $_GET, $_SERVER['REQUEST_URI'] ), 'https' ) );
		die;
	}
}