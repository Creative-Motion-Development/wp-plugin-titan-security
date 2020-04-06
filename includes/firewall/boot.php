<?php

use WBCR\Titan\Plugin as Plugin;

define('WTITAN_API_VERSION', '2.26');
define('WTITAN_API_URL_SEC', 'https://noc1.wordfence.com/');
define('WTITAN_API_URL_NONSEC', 'http://noc1.wordfence.com/');
define('WTITAN_API_URL_BASE_SEC', WTITAN_API_URL_SEC . 'v' . WTITAN_API_VERSION . '/');
define('WTITAN_BREACH_URL_BASE_SEC', WTITAN_API_URL_SEC . 'passwords/');
define('WTITAN_HACKATTEMPT_URL_SEC', 'https://noc3.wordfence.com/');

if( !defined('WTITAN_CENTRAL_URL_SEC') ) {
	define('WTITAN_CENTRAL_URL_SEC', 'https://www.wordfence.com/central');
}
if( !defined('WTITAN_CENTRAL_API_URL_SEC') ) {
	define('WTITAN_CENTRAL_API_URL_SEC', 'https://www.wordfence.com/api/wf');
}
if( !defined('WTITAN_CENTRAL_PUBLIC_KEY') ) {
	define('WTITAN_CENTRAL_PUBLIC_KEY', "\xb6\x33\x81\x05\xdf\xdf\xec\xcf\xf3\xe3\x36\xc6\xf0\x99\xc6\xf7\xca\x05\x36\xca\x87\x54\x53\x43\x31\xf2\xc6\x0d\xe1\x3d\x55\x0f");
}

define('WTITAN_MAX_SCAN_LOCK_TIME', 86400); //Increased this from 10 mins to 1 day because very big scans run for a long time. Users can use kill.
define('WTITAN_DEFAULT_MAX_SCAN_TIME', 10800);

if( !defined('WTITAN_SCAN_ISSUES_MAX_REPORT') ) {
	define('WTITAN_SCAN_ISSUES_MAX_REPORT', 1500);
}

define('WTITAN_TRANSIENTS_TIMEOUT', 3600); //how long are items cached in seconds e.g. files downloaded for diffing
define('WTITAN_MAX_IPLOC_AGE', 86400); //1 day
define('WTITAN_CRAWLER_VERIFY_CACHE_TIME', 604800);
define('WTITAN_REVERSE_LOOKUP_CACHE_TIME', 86400);
define('WTITAN_MAX_FILE_SIZE_TO_PROCESS', 52428800); //50 megs
define('WTITAN_TWO_FACTOR_GRACE_TIME_AUTHENTICATOR', 90);
define('WTITAN_TWO_FACTOR_GRACE_TIME_PHONE', 1800);

if( !defined('WTITAN_DISABLE_LIVE_TRAFFIC') ) {
	define('WTITAN_DISABLE_LIVE_TRAFFIC', false);
}
if( !defined('WTITAN_SCAN_ISSUES_PER_PAGE') ) {
	define('WTITAN_SCAN_ISSUES_PER_PAGE', 100);
}
if( !defined('WTITAN_BLOCKED_IPS_PER_PAGE') ) {
	define('WTITAN_BLOCKED_IPS_PER_PAGE', 100);
}
if( !defined('WTITAN_DISABLE_FILE_VIEWER') ) {
	define('WTITAN_DISABLE_FILE_VIEWER', false);
}
if( !defined('WTITAN_SCAN_FAILURE_THRESHOLD') ) {
	define('WTITAN_SCAN_FAILURE_THRESHOLD', 300);
}
if( !defined('WTITAN_SCAN_START_FAILURE_THRESHOLD') ) {
	define('WTITAN_SCAN_START_FAILURE_THRESHOLD', 15);
}
if( !defined('WTITAN_PREFER_WP_HOME_FOR_WPML') ) {
	define('WTITAN_PREFER_WP_HOME_FOR_WPML', false);
} //When determining the unfiltered `home` and `siteurl` with WPML installed, use WP_HOME and WP_SITEURL if set instead of the database values
if( !defined('WTITAN_SCAN_MIN_EXECUTION_TIME') ) {
	define('WTITAN_SCAN_MIN_EXECUTION_TIME', 8);
}
if( !defined('WTITAN_SCAN_MAX_INI_EXECUTION_TIME') ) {
	define('WTITAN_SCAN_MAX_INI_EXECUTION_TIME', 90);
}
if( !defined('WTITAN_ALLOW_DIRECT_MYSQLI') ) {
	define('WTITAN_ALLOW_DIRECT_MYSQLI', true);
}

require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-database-schema.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-user-ip-range.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-utils.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-webserver-info.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-auto-prepend-helper.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/models/firewall/class-model-firewall.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/models/block/class-model-block.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/models/block/class-model.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/models/block/class-model-request.php');
//require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/models/block/class-model-rate-limit.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-browscap.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-crawl.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-browscap.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-browscap-cache.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-admin-user-monitor.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-live-traffic-query.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-live-traffic-query-filter.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-live-traffic-query-group-by.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-live-traffic-query-exception.php');
require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-error-log-handler.php');

//Check the How does Wordfence get IPs setting
\WBCR\Titan\Firewall\Utils::requestDetectProxyCallback();

add_action('plugins_loaded', function () {
	/** @var wpdb $wpdb ; */ global $wpdb;

	$wfFunc = isset($_GET['_wtitan_fsf']) ? @$_GET['_wtitan_fsf'] : false;

	if( $wfFunc == 'detectProxy' ) {
		\WBCR\Titan\Firewall\Utils::doNotCache();
		if( \WBCR\Titan\Firewall\Utils::processDetectProxyCallback() ) {
			//self::getLog()->getCurrentRequest()->action = 'scan:detectproxy'; //Exempt a valid callback from live traffic
			echo Plugin::app()->getPopulateOption('detect_proxy_recommendation', '-');
		} else {
			echo '0';
		}
		exit();
	}
});