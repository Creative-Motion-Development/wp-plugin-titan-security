<?php
/**
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @version       1.0
 * @copyright (c) 10.02.2020, Webcraftic
 */

use WBCR\Titan\Cert\Cert;
use WBCR\Titan\Client\Client;
use WBCR\Titan\Client\Entity\CmsCheckItem;
use WBCR\Titan\Logger\Writter;
use WBCR\Titan\MalwareScanner\HashListPool;
use WBCR\Titan\MalwareScanner\Match;
use WBCR\Titan\MalwareScanner\Scanner;
use WBCR\Titan\MalwareScanner\Signature;
use WBCR\Titan\Plugin;

add_action( 'plugins_loaded', 'titan_digest_schedule' );
function titan_digest_schedule() {
	$digest = Plugin::app()->getOption( 'digest', 'disable' );

	if ( $digest == 'enable' ) {
		if ( ! wp_next_scheduled( 'titan_malware_weekly_digest' ) ) {
			wp_schedule_event( time(), 'weekly', 'titan_malware_weekly_digest' );
		}
	} else {
		wp_unschedule_hook( 'titan_malware_weekly_digest' );
	}
}

add_filter( 'cron_schedules', 'titan_add_minute_schedule' );
/**
 * @param array $schedules
 */
function titan_add_minute_schedule( $schedules ) {
	$schedules['minute'] = [
		'interval' => 30,
		'display'  => __( 'Once 30 sec', 'titan-security' ),
	];

	$schedules['weekly'] = [
		'interval' => 86400 * 7, // 7 days
		'display'  => __( 'Weekly', 'titan-security' ),
	];

	return $schedules;
}

add_action( 'titan_malware_weekly_digest', 'titan_malware_weekly_digest' );
function titan_malware_weekly_digest() {
	/**
	 * @var Match[] $matched
	 */
	$matched = get_option( Plugin::app()->getPrefix() . 'matched_weekly', [] );

    if(empty($matched)) {
        $matched = get_option( Plugin::app()->getPrefix() . 'scanner_malware_matched', []);
        if(!empty($matched)) {
            Plugin::app()->updatePopulateOption('matched_weekly', $matched);
        }
    }

	Writter::info( "Sending weekly digest" );

	$license_key = Plugin::app()->is_premium() ? Plugin::app()->premium->get_license()->get_key() : '';
	$site        = get_option( 'home' );
	$client      = new Client( $license_key );
	$client->send_notification( 'email', 'digestWeekly', get_option( 'admin_email' ), [
		'infectedFiles'   => $matched,
        'vulnerabilities' => [
            'wordpress' => get_option( Plugin::app()->getPrefix() . 'vulnerabilities_wordpress', [] ),
            'plugins'   => get_option( Plugin::app()->getPrefix() . 'vulnerabilities_plugins', [] ),
            'themes'    => get_option( Plugin::app()->getPrefix() . 'vulnerabilities_themes', [] ),
        ],
        'audit'   => get_option( Plugin::app()->getPrefix() . 'audit_results', []),
		'subject'       => "[{$site}] Weekly security digest",
	] );
}

add_action( 'titan_scheduled_scanner', 'titan_scheduled_scanner' );
/**
 * @throws Exception
 */
function titan_scheduled_scanner() {
	require_once WTITAN_PLUGIN_DIR . '/libs/api-client/boot.php';
	require_once WTITAN_PLUGIN_DIR . '/includes/scanner/classes/scanner/boot.php';

	/** @var Scanner $scanner */
	$scanner = get_option( Plugin::app()->getPrefix() . 'scanner', null );
	if ( is_null( $scanner ) || $scanner === false ) {
		Writter::error( 'Scanner does not exists' );
		error_log( 'Scanner does not exists' );
		titan_remove_scheduler_scanner();

		return;
	}

	set_time_limit( 0 );

	$speed       = Plugin::app()->getPopulateOption( 'scanner_speed', 'slow' );
	$files_count = @Scanner::SPEED_FILES[ $speed ];
	if ( is_null( $files_count ) ) {
		$files_count = Scanner::SPEED_FILES[ Scanner::SPEED_MEDIUM ];
	}

	$matched = Plugin::app()->getOption( 'scanner_malware_matched', [] );

	foreach ( $scanner->scan( $files_count ) as $match ) {
		/** @var Match $match */
		if ( $match->getSignature()->getSever() === Signature::SEVER_CRITICAL ) {
			array_unshift( $matched, $match );
		} else {
			array_push( $matched, $match );
		}
	}

	Plugin::app()->updateOption( 'scanner_malware_matched', $matched );
	Plugin::app()->updateOption( 'scanner', $scanner );

	if ( $scanner->get_files_count() < 1 ) {
		titan_remove_scheduler_scanner();
	}
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

	WBCR\Titan\Logger\Writter::info( sprintf( "Founded %d corrupted files", count( $result->items ) ) );

	foreach ( $result->items as $check_item ) {
		WBCR\Titan\Logger\Writter::debug( sprintf( "File `%s` (action %s)", $check_item->path, $check_item->action ) );
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
		'wp-activate.php',
		'wp-blog-header.php',
		'wp-comments-post.php',
		'wp-config-sample.php',
		'wp-cron.php',
		'wp-links-opml.php',
		'wp-load.php',
		'wp-login.php',
		'wp-mail.php',
		'wp-settings.php',
		'wp-signup.php',
		'wp-trackback.php',
		'xmlrpc.php',
		'debug.log',
		'node_modules',
		'vendor',
		'wp-plugin-titan-security',
		'anti-spam',
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

	try {
		/** @var Match[] $matched */
		$matched       = get_option( Plugin::app()->getPrefix() . 'scanner_malware_matched', [] );
		$weeklyMatched = get_option( Plugin::app()->getPrefix() . 'matched_weekly', [] );

		$weeklyMatched = array_merge( $weeklyMatched, $matched );
		$weeklyMatched = array_unique( $weeklyMatched, SORT_STRING );
		Plugin::app()->updateOption( 'matched_weekly', $weeklyMatched );

		$client = new Client( null );
		$client->send_notification( 'email', 'digestDaily', get_option( 'admin_email' ), [ 'infectedFiles' => $weeklyMatched ] );

//		if ( count( $matched ) > 0 ) {
//			if ( Plugin::app()->is_premium() ) {
//				$license_key = Plugin::app()->premium->get_license()->get_key();
//			} else {
//				$license_key = null;
//			}
//			$client = new Client( $license_key );
//
//			$client->send_notification( 'email', 'virusFound', [
//				'subject' => 'VIRUS',
//				'url'     => get_site_url(),
//				'files'   => $matched
//			] );
//		}
	} catch ( Exception $e ) {

	}
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

/**
 * Displays a notification inside the Antispam interface, on all pages of the plugin.
 * This is necessary to remind the user to update the configuration of the plugin components,
 * Otherwise, the newly activated components will not be involved in the work of the plugin.
 *
 * @param Wbcr_Factory000_Plugin $plugin
 * @param Wbcr_FactoryPages000_ImpressiveThemplate $obj
 *
 * @return bool
 */
add_action( 'wbcr/factory/pages/impressive/print_all_notices', function ( $plugin, $obj )
{
	if ( $plugin->getPluginName() != Plugin::app()->getPluginName() ) {
		return;
	}

	if ( ! empty( $_GET['page'] ) && "sitechecker-" . Plugin::app()->getPluginName() === $_GET['page'] ) {
		require_once WTITAN_PLUGIN_DIR . '/includes/audit/classes/class.cert.php';
		$cert    = Cert::get_instance();
		$output  = false;
		$message = '';
		$type    = 'warning';
		//$plugin_name = WBCR\Titan\Plugin::app()->getPluginTitle();

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
			$type    = 'error';
			$message = $cert->get_error_message();
		}

		if ( $output ) {
			switch ( $type ) {
				case 'error':
					$obj->printErrorNotice( $message );
					break;
				case 'warning':
					$obj->printWarningNotice( $message );
					break;
			}
		}
	}
}, 10, 2 );

add_action( 'init', 'titan_init_https_redirect' );
function titan_init_https_redirect() {
	$strict_https = Plugin::app()->getPopulateOption( 'strict_https', false );
	if ( ! is_ssl() && $strict_https ) {
		wp_redirect( home_url( add_query_arg( $_GET, $_SERVER['REQUEST_URI'] ), 'https' ) );
		die;
	}
}

add_action( Plugin::app()->getPluginName() . "/factory/premium/license_activate", 'titan_set_scanner_speed_active' );
function titan_set_scanner_speed_active() {
	$scanner_speed = Plugin::app()->getPopulateOption( 'scanner_speed', 'free' );
	if ( $scanner_speed == 'free' ) {
		Plugin::app()->updatePopulateOption( 'scanner_speed', 'slow' );
	}

	$scanner_schedule = Plugin::app()->getPopulateOption( 'scanner_schedule', 'disabled' );
	if ( $scanner_schedule == 'disabled' ) {
		Plugin::app()->updatePopulateOption( 'scanner_schedule', 'disabled' );
	}

	$scanner_type = Plugin::app()->getPopulateOption( 'scanner_type', 'basic' );
	if ( $scanner_type == 'basic' ) {
		Plugin::app()->updatePopulateOption( 'scanner_type', 'advanced' );
	}
}

add_action( Plugin::app()->getPluginName() . "/factory/premium/license_deactivate", 'titan_set_scanner_speed_deactive' );
function titan_set_scanner_speed_deactive() {
	$scanner_speed = Plugin::app()->getPopulateOption( 'scanner_speed', 'free' );
	if ( $scanner_speed !== 'free' ) {
		Plugin::app()->updatePopulateOption( 'scanner_speed', 'free' );
	}

	$scanner_schedule = Plugin::app()->getPopulateOption( 'scanner_schedule', 'disabled' );
	if ( $scanner_schedule !== 'disabled' ) {
		Plugin::app()->updatePopulateOption( 'scanner_schedule', 'disabled' );
	}

	$scanner_type = Plugin::app()->getPopulateOption( 'scanner_type', 'basic' );
	if ( $scanner_type !== 'basic' ) {
		Plugin::app()->updatePopulateOption( 'scanner_type', 'basic' );
	}
}

/**
 * @return int|float [Memory limit in MB]
 */
function get_memory_limit() {
	$mem  = ini_get( 'memory_limit' );
	$last = $mem[ strlen( $mem ) - 1 ];
	$mem  = (int) $mem;
	do {
		switch ( $last ) {
			case 'g':
			case 'G':
				$mem  = $mem * 1024;
				$last = 'm';
				break;

			case 'm':
			case 'M':
				break 2;

			default:
				$mem = ( (int) $mem ) / 1024 / 1024; // bytes to mbytes
				break 2;
		}
	} while ( true );

	return $mem;
}

function get_recommended_scanner_speed() {
	$mem = get_memory_limit();
	if ( $mem > 100 ) {
		$recommendation = Scanner::SPEED_FAST;
	} elseif ( $mem > 60 ) {
		$recommendation = Scanner::SPEED_MEDIUM;
	} else {
		$recommendation = Scanner::SPEED_SLOW;
	}

	return $recommendation;
}

/**
 * @param $time
 *
 * @return int
 */
function correct_timezone( $time ) {
	$localOffset = ( new DateTime )->getOffset();

	return $time + $localOffset;
}

add_action( 'plugins_loaded', 'titan_init_check_schedule' );
function titan_init_check_schedule() {
	$format_date = 'Y/m/d H:i';
	$format_time = 'H:i';

	$is_schedule = false;

	$lasttime = Plugin::app()->getPopulateOption( 'scanner_schedule_last_time', date_i18n( $format_date ) );
	$schedule = Plugin::app()->getPopulateOption( 'scanner_schedule', 'disabled' );
	$last     = date_parse_from_format( $format_time, $lasttime );

	switch ( $schedule ) {
		case 'daily':
			$daily = Plugin::app()->getPopulateOption( 'scanner_schedule_daily', '2000/01/01 23:00' );
			$daily = date_parse_from_format( $format_time, $daily );
			$daily = $daily['hour'] * 60 + $daily['minute'];
			$last  = $last['hour'] * 60 + $last['minute'];
			if ( $last <= $daily ) {
				titan_create_scheduler_scanner();
				$is_schedule = true;
			}
			break;
		case 'weekly':
			$week      = Plugin::app()->getPopulateOption( 'scanner_schedule_weekly_day', '7' );
			$time      = Plugin::app()->getPopulateOption( 'scanner_schedule_weekly_time', '2000/01/01 23:00' );
			$time      = date_parse_from_format( $format_time, $time );
			$time      = $time['hour'] * 60 + $time['minute'];
			$last      = $last['hour'] * 60 + $last['minute'];
			$this_week = date( 'N' );
			if ( $this_week == $week && $last <= $time ) {
				titan_create_scheduler_scanner();
				$is_schedule = true;
			}
			break;
		case 'custom':
			$time = Plugin::app()->getPopulateOption( 'scanner_schedule_custom', '2000/01/01 23:00' );
			$time = strtotime( $time );
			$last = strtotime( $lasttime );
			if ( $last <= $time ) {
				titan_create_scheduler_scanner();
				$is_schedule = true;
			}
			break;
		case 'disabled':
			break;
	}
	if ( $is_schedule ) {
		Plugin::app()->updatePopulateOption( 'scanner_schedule_last_time', date_i18n( $format_date ) );
	}
}
