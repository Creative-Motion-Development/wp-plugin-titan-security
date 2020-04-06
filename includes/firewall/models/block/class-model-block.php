<?php

namespace WBCR\Titan\Firewall\Model;

/**
 * Represents an individual block definition.
 *
 * @property int $id
 * @property int $type One of the TYPE_* constants.
 * @property string $ip The human-readable version of the IP if applicable for the block type.
 * @property int $blockedTime The timestamp the block was created.
 * @property string $reason Description of the block.
 * @property int $lastAttempt Timestamp of the last request blocked. If never, this will be 0.
 * @property int $blockedHits Count of the number of hits blocked.
 * @property int $expiration Timestamp when the block will expire. If never, this will be 0.
 * @property mixed $parameters Variable parameters defining the block (e.g., the matchers for a pattern block).
 *
 * @property bool $blockLogin For \WBCR\Titan\Firewall\Model\Block::TYPE_COUNTRY only, this is whether or not to block hits to the login page.
 * @property bool $blockSite For \WBCR\Titan\Firewall\Model\Block::TYPE_COUNTRY only, this is whether or not to block hits to the rest of the site.
 * @property array $countries For \WBCR\Titan\Firewall\Model\Block::TYPE_COUNTRY only, this is the list of countries to block.
 *
 * @property mixed $ipRange For \WBCR\Titan\Firewall\Model\Block::TYPE_PATTERN only, this is the matching IP range if set.
 * @property mixed $hostname For \WBCR\Titan\Firewall\Model\Block::TYPE_PATTERN only, this is the hostname pattern if set.
 * @property mixed $userAgent For \WBCR\Titan\Firewall\Model\Block::TYPE_PATTERN only, this is the user agent pattern if set.
 * @property mixed $referrer For \WBCR\Titan\Firewall\Model\Block::TYPE_PATTERN only, this is the HTTP referrer pattern if set.
 */
class Block {

	//Constants for block record types
	const TYPE_IP_MANUAL = 1; //Same behavior as TYPE_IP_AUTOMATIC_PERMANENT - the reason will be overridden for public display
	const TYPE_WFSN_TEMPORARY = 2;
	const TYPE_COUNTRY = 3;
	const TYPE_PATTERN = 4;
	const TYPE_RATE_BLOCK = 5;
	const TYPE_RATE_THROTTLE = 6;
	const TYPE_LOCKOUT = 7; //Blocks login-related actions only
	const TYPE_IP_AUTOMATIC_TEMPORARY = 8; //Automatic block, still temporary
	const TYPE_IP_AUTOMATIC_PERMANENT = 9; //Automatic block, started as temporary but now permanent as a result of admin action

	//Constants to identify the match type of a block record
	const MATCH_NONE = 0;
	const MATCH_IP = 1;
	const MATCH_COUNTRY_BLOCK = 2;
	const MATCH_COUNTRY_REDIR = 3;
	const MATCH_COUNTRY_REDIR_BYPASS = 4;
	const MATCH_PATTERN = 5;

	//Duration constants
	const DURATION_FOREVER = 0;

	//Constants defining the placeholder IPs for non-IP block records
	const MARKER_COUNTRY = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\xc0\x00\x02\x01";// 192.0.2.1 TEST-NET-1
	const MARKER_PATTERN = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\xc0\x00\x02\x02";// 192.0.2.2 TEST-NET-1

	private $_id;
	private $_type = false;
	private $_ip = false;
	private $_blockedTime = false;
	private $_reason = false;
	private $_lastAttempt = false;
	private $_blockedHits = false;
	private $_expiration = false;
	private $_parameters = false;

	/**
	 * Returns the name of the storage table for the blocks.
	 *
	 * @return string
	 */
	public static function blocksTable()
	{
		return \WBCR\Titan\Database\Schema::get_table_name('blocks7');
	}

	/**
	 * Returns a user-displayable name for the corresponding type constant.
	 *
	 * @param int $type
	 * @return string
	 */
	public static function nameForType($type)
	{
		switch( $type ) {
			case self::TYPE_IP_MANUAL:
			case self::TYPE_IP_AUTOMATIC_TEMPORARY:
			case self::TYPE_IP_AUTOMATIC_PERMANENT:
			case self::TYPE_WFSN_TEMPORARY:
			case self::TYPE_RATE_BLOCK:
				return __('IP Block', 'titan-security');
			case self::TYPE_RATE_THROTTLE:
				return __('IP Throttled', 'titan-security');
			case self::TYPE_LOCKOUT:
				return __('Lockout', 'titan-security');
			case self::TYPE_COUNTRY:
				return __('Country Block', 'titan-security');
			case self::TYPE_PATTERN:
				return __('Advanced Block', 'titan-security');
		}

		return __('Unknown', 'titan-security');
	}

	/**
	 * Returns the number of seconds for a temporary block to last by default.
	 *
	 * @return int
	 */
	public static function blockDuration()
	{
		return (int)\WBCR\Titan\Plugin::app()->getPopulateOption('blocked_time');
	}

	/**
	 * Returns the number of seconds for a rate limit throttle to last by default.
	 *
	 * @return int
	 */
	public static function rateLimitThrottleDuration()
	{
		return 60;
	}

	/**
	 * Returns the number of seconds for a lockout to last by default.
	 *
	 * @return int
	 */
	public static function lockoutDuration()
	{

		//return (int)wfConfig::get('loginSec_lockoutMins') * 60;
		return (int)\WBCR\Titan\Plugin::app()->getPopulateOption('brute_force_lockout_mins', 4 * HOUR_IN_SECONDS) * 60;
	}

	/**
	 * @param string $IP Should be in dot or colon notation (127.0.0.1 or ::1)
	 * @param bool $forcedWhitelistEntry If provided, returns whether or not the IP is on a forced whitelist (i.e., it's not one the user can delete).
	 * @return bool
	 */
	public static function isWhitelisted($IP, &$forcedWhitelistEntry = null)
	{
		if( $forcedWhitelistEntry !== null ) {
			$forcedWhitelistEntry = false;
		}

		if( (defined('DOING_CRON') && DOING_CRON) || //Safe
			(defined('WTITAN_SYNCING_ATTACK_DATA') && WTITAN_SYNCING_ATTACK_DATA) //Safe as long as it will actually run since it then exits
		) {
			$serverIPs = \WBCR\Titan\Firewall\Utils::serverIPs();
			foreach($serverIPs as $testIP) {
				if( \WBCR\Titan\Firewall\Utils::inet_pton($IP) == \WBCR\Titan\Firewall\Utils::inet_pton($testIP) ) {
					if( $forcedWhitelistEntry !== null ) {
						$forcedWhitelistEntry = true;
					}

					return true;
				}
			}
		}

		foreach(\WBCR\Titan\Firewall\Utils::getIPWhitelist() as $subnet) {
			if( $subnet instanceof \WBCR\Titan\Firewall\User_IP_Range ) {
				if( $subnet->isIPInRange($IP) ) {
					return true;
				}
			} elseif( \WBCR\Titan\Firewall\Utils::subnetContainsIP($subnet, $IP) ) {
				if( $forcedWhitelistEntry !== null ) {
					$forcedWhitelistEntry = true;
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Validates the payload for block creation. Returns true if valid, otherwise it'll return the first error found.
	 *
	 * @param $payload
	 * @return bool|string
	 */
	public static function validate($payload)
	{
		if( !isset($payload['type']) || array_search($payload['type'], array(
				'ip-address',
				'country',
				'custom-pattern'
			)) === false ) {
			return __('Invalid block type.', 'titan-security');
		}
		if( !isset($payload['duration']) || intval($payload['duration']) < 0 ) {
			return __('Invalid block duration.', 'titan-security');
		}
		if( !isset($payload['reason']) || empty($payload['reason']) ) {
			return __('A block reason must be provided.', 'titan-security');
		}

		if( $payload['type'] == 'ip-address' ) {
			if( !isset($payload['ip']) || !filter_var(trim($payload['ip']), FILTER_VALIDATE_IP) || @\WBCR\Titan\Firewall\Utils::inet_pton(trim($payload['ip'])) === false ) {
				return __('Invalid IP address.', 'titan-security');
			}
			if( self::isWhitelisted(trim($payload['ip'])) ) {
				return sprintf(__('This IP address is in a range of addresses that Titan does not block. The IP range may be internal or belong to a service that is always allowed. Whitelisting of external services can be disabled. <a href="%s" target="_blank" rel="noopener noreferrer">Learn More</a>', 'titan-security'), '#');
			}
		} else if( $payload['type'] == 'country' ) {
			if( !isset($payload['blockLogin']) || !isset($payload['blockSite']) ) {
				return __('Nothing selected to block.', 'titan-security');
			}
			if( !$payload['blockLogin'] && !$payload['blockSite'] ) {
				return __('Nothing selected to block.', 'titan-security');
			}
			if( !isset($payload['countries']) || empty($payload['countries']) || !is_array($payload['countries']) ) {
				return __('No countries selected.', 'titan-security');
			}

			require(WTITAN_PLUGIN_DIR . '/includes/firewall/class-bulk-countries.php');
			/** @var array $wfBulkCountries */
			foreach($payload['countries'] as $code) {
				if( !isset($wfBulkCountries[$code]) ) {
					return __('An invalid country was selected.', 'titan-security');
				}
			}
		} else if( $payload['type'] == 'custom-pattern' ) {
			$hasOne = false;
			if( isset($payload['ipRange']) && !empty($payload['ipRange']) ) {
				$ipRange = new \WBCR\Titan\Firewall\User_IP_Range($payload['ipRange']);
				if( $ipRange->isValidRange() ) {
					if( $ipRange->isMixedRange() ) {
						return __('Ranges mixing IPv4 and IPv6 addresses are not supported.', 'titan-security');
					}

					$hasOne = true;
				} else {
					return __('Invalid IP range.', 'titan-security');
				}
			}
			if( isset($payload['hostname']) && !empty($payload['hostname']) ) {
				if( preg_match('/^[a-z0-9\.\*\-]+$/i', $payload['hostname']) ) {
					$hasOne = true;
				} else {
					return __('Invalid hostname.', 'titan-security');
				}
			}
			if( isset($payload['userAgent']) && !empty($payload['userAgent']) ) {
				$hasOne = true;
			}
			if( isset($payload['referrer']) && !empty($payload['referrer']) ) {
				$hasOne = true;
			}
			if( !$hasOne ) {
				return __('No block parameters provided.', 'titan-security');
			}
		}

		return true;
	}

	/**
	 * Creates the block. The $payload value is expected to have been validated prior to calling this.
	 *
	 * @param $payload
	 */
	public static function create($payload)
	{
		$type = $payload['type'];
		$duration = max((int)$payload['duration'], 0);
		$reason = $payload['reason'];

		if( $type == 'ip-address' ) {
			$ip = trim($payload['ip']);
			\WBCR\Titan\Firewall\Model\Block::createIP($reason, $ip, $duration);
		} else if( $type == 'country' ) {
			$blockLogin = !!$payload['blockLogin'];
			$blockSite = !!$payload['blockSite'];
			$countries = array_unique($payload['countries']);
			\WBCR\Titan\Firewall\Model\Block::createCountry($reason, $blockLogin, $blockSite, $countries, $duration);
		} else if( $type == 'custom-pattern' ) {
			$ipRange = '';
			if( isset($payload['ipRange']) && !empty($payload['ipRange']) ) {
				$ipRange = new \WBCR\Titan\Firewall\User_IP_Range($payload['ipRange']);
				$ipRange = $ipRange->getIPString();
			}
			$hostname = (isset($payload['hostname']) && !empty($payload['hostname'])) ? $payload['hostname'] : '';
			$userAgent = (isset($payload['userAgent']) && !empty($payload['userAgent'])) ? $payload['userAgent'] : '';
			$referrer = (isset($payload['referrer']) && !empty($payload['referrer'])) ? $payload['referrer'] : '';
			\WBCR\Titan\Firewall\Model\Block::createPattern($reason, $ipRange, $hostname, $userAgent, $referrer, $duration);
		}
	}

	/**
	 * Creates an IP block if one doesn't already exist for the given IP. The parameters are expected to have been validated and sanitized prior to calling this.
	 *
	 * @param string $reason
	 * @param string $ip
	 * @param int $duration Optional. Defaults to forever. This is the number of seconds for the block to last.
	 * @param bool|int $blockedTime Optional. Defaults to the current timestamp.
	 * @param bool|int $lastAttempt Optional. Defaults to 0, which means never.
	 * @param bool|int $blockedHits Optional. Defaults to 0.
	 */
	public static function createIP($reason, $ip, $duration = self::DURATION_FOREVER, $blockedTime = false, $lastAttempt = false, $blockedHits = false, $type = self::TYPE_IP_MANUAL)
	{
		global $wpdb;

		if( self::isWhitelisted($ip) ) {
			return;
		}

		if( $blockedTime === false ) {
			$blockedTime = time();
		}

		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$hasExisting = $wpdb->query($wpdb->prepare("UPDATE `{$blocksTable}` SET `reason` = %s, `expiration` = %d WHERE `expiration` > UNIX_TIMESTAMP() AND `type` = %d AND `IP` = %s", $reason, ($duration ? $blockedTime + $duration : $duration), $type, \WBCR\Titan\Firewall\Utils::inet_pton($ip)));
		if( !$hasExisting ) {
			$wpdb->query($wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`, `parameters`) VALUES (%d, %s, %d, %s, %d, %d, %d, NULL)", $type, \WBCR\Titan\Firewall\Utils::inet_pton($ip), $blockedTime, $reason, (int)$lastAttempt, (int)$blockedHits, ($duration ? $blockedTime + $duration : $duration)));

			self::update_count_total_ip_blocking();
		}

		if( !WFWAF_SUBDIRECTORY_INSTALL && class_exists('\wfWAFIPBlocksController') ) {
			\wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings();
		}
	}

	/**
	 * Creates an IP block for a WFSN response if one doesn't already exist for the given IP. The parameters are expected to have been validated and sanitized prior to calling this.
	 *
	 * @param string $reason
	 * @param string $ip
	 * @param int $duration This is the number of seconds for the block to last.
	 * @param bool|int $blockedTime Optional. Defaults to the current timestamp.
	 * @param bool|int $lastAttempt Optional. Defaults to 0, which means never.
	 * @param bool|int $blockedHits Optional. Defaults to 0.
	 */
	public static function createWFSN($reason, $ip, $duration, $blockedTime = false, $lastAttempt = false, $blockedHits = false)
	{
		global $wpdb;

		if( self::isWhitelisted($ip) ) {
			return;
		}

		if( $blockedTime === false ) {
			$blockedTime = time();
		}

		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$hasExisting = $wpdb->query($wpdb->prepare("UPDATE `{$blocksTable}` SET `reason` = %s, `expiration` = %d WHERE `expiration` > UNIX_TIMESTAMP() AND `type` = %d AND `IP` = %s", $reason, ($duration ? $blockedTime + $duration : $duration), self::TYPE_WFSN_TEMPORARY, \WBCR\Titan\Firewall\Utils::inet_pton($ip)));
		if( !$hasExisting ) {
			$wpdb->query($wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`, `parameters`) VALUES (%d, %s, %d, %s, %d, %d, %d, NULL)", self::TYPE_WFSN_TEMPORARY, \WBCR\Titan\Firewall\Utils::inet_pton($ip), $blockedTime, $reason, (int)$lastAttempt, (int)$blockedHits, ($duration ? $blockedTime + $duration : $duration)));

			self::update_count_total_ip_blocking();
		}

		if( !WFWAF_SUBDIRECTORY_INSTALL && class_exists('\wfWAFIPBlocksController') ) {
			\wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings();
		}
	}

	/**
	 * Creates an IP block for a rate limit if one doesn't already exist for the given IP. The parameters are expected to have been validated and sanitized prior to calling this.
	 *
	 * @param string $reason
	 * @param string $ip
	 * @param int $duration This is the number of seconds for the block to last.
	 * @param bool|int $blockedTime Optional. Defaults to the current timestamp.
	 * @param bool|int $lastAttempt Optional. Defaults to 0, which means never.
	 * @param bool|int $blockedHits Optional. Defaults to 0.
	 */
	public static function createRateBlock($reason, $ip, $duration, $blockedTime = false, $lastAttempt = false, $blockedHits = false)
	{
		global $wpdb;

		if( self::isWhitelisted($ip) ) {
			return;
		}

		if( $blockedTime === false ) {
			$blockedTime = time();
		}

		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$hasExisting = $wpdb->query($wpdb->prepare("UPDATE `{$blocksTable}` SET `reason` = %s, `expiration` = %d WHERE `expiration` > UNIX_TIMESTAMP() AND `type` = %d AND `IP` = %s", $reason, ($duration ? $blockedTime + $duration : $duration), self::TYPE_RATE_BLOCK, \WBCR\Titan\Firewall\Utils::inet_pton($ip)));
		if( !$hasExisting ) {
			$wpdb->query($wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`, `parameters`) VALUES (%d, %s, %d, %s, %d, %d, %d, NULL)", self::TYPE_RATE_BLOCK, \WBCR\Titan\Firewall\Utils::inet_pton($ip), $blockedTime, $reason, (int)$lastAttempt, (int)$blockedHits, ($duration ? $blockedTime + $duration : $duration)));

			self::update_count_total_ip_blocking();
		}

		if( !WFWAF_SUBDIRECTORY_INSTALL && class_exists('\wfWAFIPBlocksController') ) {
			\wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings();
		}
	}

	/**
	 * Creates an IP throttle for a rate limit if one doesn't already exist for the given IP. The parameters are expected to have been validated and sanitized prior to calling this.
	 *
	 * @param string $reason
	 * @param string $ip
	 * @param int $duration This is the number of seconds for the block to last.
	 * @param bool|int $blockedTime Optional. Defaults to the current timestamp.
	 * @param bool|int $lastAttempt Optional. Defaults to 0, which means never.
	 * @param bool|int $blockedHits Optional. Defaults to 0.
	 */
	public static function createRateThrottle($reason, $ip, $duration, $blockedTime = false, $lastAttempt = false, $blockedHits = false)
	{
		global $wpdb;

		if( self::isWhitelisted($ip) ) {
			return;
		}

		if( $blockedTime === false ) {
			$blockedTime = time();
		}

		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$hasExisting = $wpdb->query($wpdb->prepare("UPDATE `{$blocksTable}` SET `reason` = %s, `expiration` = %d WHERE `expiration` > UNIX_TIMESTAMP() AND `type` = %d AND `IP` = %s", $reason, ($duration ? $blockedTime + $duration : $duration), self::TYPE_RATE_THROTTLE, \WBCR\Titan\Firewall\Utils::inet_pton($ip)));
		if( !$hasExisting ) {
			$wpdb->query($wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`, `parameters`) VALUES (%d, %s, %d, %s, %d, %d, %d, NULL)", self::TYPE_RATE_THROTTLE, \WBCR\Titan\Firewall\Utils::inet_pton($ip), $blockedTime, $reason, (int)$lastAttempt, (int)$blockedHits, ($duration ? $blockedTime + $duration : $duration)));

			self::update_count_total_ip_blocking();
		}

		if( !WFWAF_SUBDIRECTORY_INSTALL && class_exists('\wfWAFIPBlocksController') ) {
			\wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings();
		}
	}

	/**
	 * Creates a lockout if one doesn't already exist for the given IP. The parameters are expected to have been validated and sanitized prior to calling this.
	 *
	 * @param string $reason
	 * @param string $ip
	 * @param int $duration This is the number of seconds for the block to last.
	 * @param bool|int $blockedTime Optional. Defaults to the current timestamp.
	 * @param bool|int $lastAttempt Optional. Defaults to 0, which means never.
	 * @param bool|int $blockedHits Optional. Defaults to 0.
	 */
	public static function createLockout($reason, $ip, $duration, $blockedTime = false, $lastAttempt = false, $blockedHits = false)
	{
		global $wpdb;

		if( self::isWhitelisted($ip) ) {
			return;
		}

		if( $blockedTime === false ) {
			$blockedTime = time();
		}

		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$hasExisting = $wpdb->query($wpdb->prepare("UPDATE `{$blocksTable}` SET `reason` = %s, `expiration` = %d WHERE `expiration` > UNIX_TIMESTAMP() AND `type` = %d AND `IP` = %s", $reason, ($duration ? $blockedTime + $duration : $duration), self::TYPE_LOCKOUT, \WBCR\Titan\Firewall\Utils::inet_pton($ip)));
		if( !$hasExisting ) {
			$wpdb->query($wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`, `parameters`) VALUES (%d, %s, %d, %s, %d, %d, %d, NULL)", self::TYPE_LOCKOUT, \WBCR\Titan\Firewall\Utils::inet_pton($ip), $blockedTime, $reason, (int)$lastAttempt, (int)$blockedHits, ($duration ? $blockedTime + $duration : $duration)));

			//wfConfig::inc('totalIPsLocked');
			//\WBCR\Titan\Plugin::app()->updatePopulateOption('total_ip_blocked', );

		}

		if( !WFWAF_SUBDIRECTORY_INSTALL && class_exists('\wfWAFIPBlocksController') ) {
			\wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings();
		}
	}

	/**
	 * Creates a country block. The parameters are expected to have been validated and sanitized prior to calling this.
	 *
	 * @param string $reason
	 * @param string $blockLogin
	 * @param string $blockSite
	 * @param string $countries
	 * @param int $duration Optional. Defaults to forever. This is the number of seconds for the block to last.
	 * @param bool|int $blockedTime Optional. Defaults to the current timestamp.
	 * @param bool|int $lastAttempt Optional. Defaults to 0, which means never.
	 * @param bool|int $blockedHits Optional. Defaults to 0.
	 */
	public static function createCountry($reason, $blockLogin, $blockSite, $countries, $duration = self::DURATION_FOREVER, $blockedTime = false, $lastAttempt = false, $blockedHits = false)
	{
		global $wpdb;

		if( $blockedTime === false ) {
			$blockedTime = time();
		}

		$parameters = array(
			'blockLogin' => $blockLogin ? 1 : 0,
			'blockSite' => $blockSite ? 1 : 0,
			'countries' => $countries,
		);

		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$existing = $wpdb->get_var($wpdb->prepare("SELECT `id` FROM `{$blocksTable}` WHERE `type` = %d LIMIT 1", self::TYPE_COUNTRY));
		if( $existing ) {
			$wpdb->query($wpdb->prepare("UPDATE `{$blocksTable}` SET `reason` = %s, `parameters` = %s WHERE `id` = %d", $reason, json_encode($parameters), $existing));
		} else {
			$wpdb->query($wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`, `parameters`) VALUES (%d, %s, %d, %s, %d, %d, %d, %s)", self::TYPE_COUNTRY, self::MARKER_COUNTRY, $blockedTime, $reason, (int)$lastAttempt, (int)$blockedHits, ($duration ? $blockedTime + $duration : $duration), json_encode($parameters)));
		}

		if( !WFWAF_SUBDIRECTORY_INSTALL && class_exists('\wfWAFIPBlocksController') ) {
			\wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings();
		}
	}

	/**
	 * Creates a pattern block. The parameters are expected to have been validated and sanitized prior to calling this.
	 *
	 * @param string $reason
	 * @param string $ipRange
	 * @param string $hostname
	 * @param string $userAgent
	 * @param string $referrer
	 * @param int $duration Optional. Defaults to forever. This is the number of seconds for the block to last.
	 * @param bool|int $blockedTime Optional. Defaults to the current timestamp.
	 * @param bool|int $lastAttempt Optional. Defaults to 0, which means never.
	 * @param bool|int $blockedHits Optional. Defaults to 0.
	 */
	public static function createPattern($reason, $ipRange, $hostname, $userAgent, $referrer, $duration = self::DURATION_FOREVER, $blockedTime = false, $lastAttempt = false, $blockedHits = false)
	{
		global $wpdb;

		if( $blockedTime === false ) {
			$blockedTime = time();
		}

		$parameters = array(
			'ipRange' => $ipRange,
			'hostname' => $hostname,
			'userAgent' => $userAgent,
			'referrer' => $referrer,
		);

		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$wpdb->query($wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`, `parameters`) VALUES (%d, %s, %d, %s, %d, %d, %d, %s)", self::TYPE_PATTERN, self::MARKER_PATTERN, $blockedTime, $reason, (int)$lastAttempt, (int)$blockedHits, ($duration ? $blockedTime + $duration : $duration), json_encode($parameters)));

		if( !WFWAF_SUBDIRECTORY_INSTALL && class_exists('\wfWAFIPBlocksController') ) {
			\wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings();
		}
	}

	/**
	 * Removes all expired blocks.
	 */
	public static function vacuum()
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$wpdb->query("DELETE FROM `{$blocksTable}` WHERE `expiration` <= UNIX_TIMESTAMP() AND `expiration` != " . self::DURATION_FOREVER);
	}

	/**
	 * Imports all valid blocks in $blocks. If $replaceExisting is true, this will remove all permanent blocks prior to the import.
	 *
	 * @param array $blocks
	 * @param bool $replaceExisting
	 */
	public static function importBlocks($blocks, $replaceExisting = true)
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();

		if( $replaceExisting ) {
			$wpdb->query("DELETE FROM `{$blocksTable}` WHERE `expiration` = " . self::DURATION_FOREVER);
		}

		foreach($blocks as $b) {
			self::_importBlock($b);
		}

		if( !WFWAF_SUBDIRECTORY_INSTALL && class_exists('\wfWAFIPBlocksController') ) {
			\wfWAFIPBlocksController::setNeedsSynchronizeConfigSettings();
		}
	}

	/**
	 * Validates the block import record and inserts it if valid. This validation is identical to what is applied to adding one through the UI.
	 *
	 * @param array $b
	 * @return bool
	 */
	private static function _importBlock($b)
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();

		if( !isset($b['type']) || !isset($b['IP']) || !isset($b['blockedTime']) || !isset($b['reason']) || !isset($b['lastAttempt']) || !isset($b['blockedHits']) ) {
			return false;
		}
		if( empty($b['IP']) || empty($b['reason']) ) {
			return false;
		}

		$ip = @\WBCR\Titan\Firewall\Utils::inet_ntop(\WBCR\Titan\Firewall\Utils::hex2bin($b['IP']));
		if( !\WBCR\Titan\Firewall\Utils::isValidIP($ip) ) {
			return false;
		}

		switch( $b['type'] ) {
			case self::TYPE_IP_MANUAL:
			case self::TYPE_IP_AUTOMATIC_TEMPORARY:
			case self::TYPE_IP_AUTOMATIC_PERMANENT:
			case self::TYPE_WFSN_TEMPORARY:
			case self::TYPE_RATE_BLOCK:
			case self::TYPE_RATE_THROTTLE:
			case self::TYPE_LOCKOUT:
				if( self::isWhitelisted($ip) ) {
					return false;
				}

				return $wpdb->query($wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`, `parameters`) VALUES (%d, %s, %d, %s, %d, %d, %d, NULL)", (int)$b['type'], \WBCR\Titan\Firewall\Utils::inet_pton($ip), (int)$b['blockedTime'], $b['reason'], (int)$b['lastAttempt'], (int)$b['blockedHits'], self::DURATION_FOREVER)) !== false;
			case self::TYPE_COUNTRY:
				if( !isset($b['parameters']) ) {
					return false;
				}
				if( \WBCR\Titan\Firewall\Utils::inet_pton($ip) != self::MARKER_COUNTRY ) {
					return false;
				}
				$parameters = @json_decode($b['parameters'], true);
				if( !isset($parameters['blockLogin']) || !isset($parameters['blockSite']) || !isset($parameters['countries']) ) {
					return false;
				}
				$parameters['blockLogin'] = \WBCR\Titan\Firewall\Utils::truthyToInt($parameters['blockLogin']);
				$parameters['blockSite'] = \WBCR\Titan\Firewall\Utils::truthyToInt($parameters['blockSite']);

				require(WTITAN_PATH . 'lib/class-bulk-countries.php');
				/** @var array $wfBulkCountries */
				foreach($parameters['countries'] as $code) {
					if( !isset($wfBulkCountries[$code]) ) {
						return false;
					}
				}

				$parameters = array(
					'blockLogin' => $parameters['blockLogin'],
					'blockSite' => $parameters['blockSite'],
					'countries' => $parameters['countries']
				);

				return $wpdb->query($wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`, `parameters`) VALUES (%d, %s, %d, %s, %d, %d, %d, %s)", self::TYPE_COUNTRY, self::MARKER_COUNTRY, (int)$b['blockedTime'], $b['reason'], (int)$b['lastAttempt'], (int)$b['blockedHits'], self::DURATION_FOREVER, json_encode($parameters))) !== false;
			case self::TYPE_PATTERN:
				if( !isset($b['parameters']) ) {
					return false;
				}
				if( \WBCR\Titan\Firewall\Utils::inet_pton($ip) != self::MARKER_PATTERN ) {
					return false;
				}
				$parameters = @json_decode($b['parameters'], true);
				if( !isset($parameters['ipRange']) || !isset($parameters['hostname']) || !isset($parameters['userAgent']) || !isset($parameters['referrer']) ) {
					return false;
				}

				$hasOne = false;
				if( !empty($parameters['ipRange']) ) {
					$ipRange = new \WBCR\Titan\Firewall\User_IP_Range($parameters['ipRange']);
					if( $ipRange->isValidRange() ) {
						if( $ipRange->isMixedRange() ) {
							return false;
						}

						$hasOne = true;
					} else {
						return false;
					}
				}
				if( !empty($parameters['hostname']) ) {
					if( preg_match('/^[a-z0-9\.\*\-]+$/i', $parameters['hostname']) ) {
						$hasOne = true;
					} else {
						return false;
					}
				}
				if( !empty($parameters['userAgent']) ) {
					$hasOne = true;
				}
				if( !empty($parameters['referrer']) ) {
					$hasOne = true;
				}
				if( !$hasOne ) {
					return false;
				}

				$ipRange = '';
				if( !empty($parameters['ipRange']) ) {
					$ipRange = new \WBCR\Titan\Firewall\User_IP_Range($parameters['ipRange']);
					$ipRange = $ipRange->getIPString();
				}
				$parameters = array(
					'ipRange' => $ipRange,
					'hostname' => $parameters['hostname'],
					'userAgent' => $parameters['userAgent'],
					'referrer' => $parameters['referrer'],
				);

				return $wpdb->query($wpdb->prepare("INSERT INTO `{$blocksTable}` (`type`, `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `expiration`, `parameters`) VALUES (%d, %s, %d, %s, %d, %d, %d, %s)", self::TYPE_PATTERN, self::MARKER_PATTERN, (int)$b['blockedTime'], $b['reason'], (int)$b['lastAttempt'], (int)$b['blockedHits'], self::DURATION_FOREVER, json_encode($parameters))) !== false;
		}

		return false;
	}

	/**
	 * Returns an array suitable for JSON output of all permanent blocks.
	 *
	 * @return array
	 */
	public static function exportBlocks()
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$query = "SELECT `type`, HEX(`IP`) AS `IP`, `blockedTime`, `reason`, `lastAttempt`, `blockedHits`, `parameters` FROM `{$blocksTable}` WHERE `expiration` = " . self::DURATION_FOREVER;
		$rows = $wpdb->get_results($query, ARRAY_A);

		return $rows;
	}

	/**
	 * Returns all unexpired blocks (including lockouts by default), optionally only of the specified types. These are sorted descending by the time created.
	 *
	 * @param bool $prefetch If true, the full data for the block is fetched rather than using lazy loading.
	 * @param array $ofTypes An optional array of block types to restrict the returned array of blocks to.
	 * @param int $offset The offset to start the result fetch at.
	 * @param int $limit The maximum number of results to return. -1 for all.
	 * @param string $sortColumn The column to sort by.
	 * @param string $sortDirection The direction to sort.
	 * @param string $filter An optional value to filter by.
	 * @return \WBCR\Titan\Firewall\Model\Block[]
	 */
	public static function allBlocks($prefetch = false, $ofTypes = array(), $offset = 0, $limit = -1, $sortColumn = 'type', $sortDirection = 'ascending', $filter = '')
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$columns = '`id`';
		if( $prefetch ) {
			$columns = '*';
		}

		$sort = 'typeSort';
		switch( $sortColumn ) { //Match the display table column to the corresponding schema column
			case 'type':
				//Use default;
				break;
			case 'detail':
				$sort = 'detailSort';
				break;
			case 'ruleAdded':
				$sort = 'blockedTime';
				break;
			case 'reason':
				$sort = 'reason';
				break;
			case 'expiration':
				$sort = 'expiration';
				break;
			case 'blockCount':
				$sort = 'blockedHits';
				break;
			case 'lastAttempt':
				$sort = 'lastAttempt';
				break;
		}

		$order = 'ASC';
		if( $sortDirection == 'descending' ) {
			$order = 'DESC';
		}

		$query = "SELECT {$columns}, CASE 
WHEN `type` = " . self::TYPE_COUNTRY . " THEN 0
WHEN `type` = " . self::TYPE_PATTERN . " THEN 1
WHEN `type` = " . self::TYPE_LOCKOUT . " THEN 2
WHEN `type` = " . self::TYPE_RATE_THROTTLE . " THEN 3
WHEN `type` = " . self::TYPE_RATE_BLOCK . " THEN 4
WHEN `type` = " . self::TYPE_IP_AUTOMATIC_PERMANENT . " THEN 5
WHEN `type` = " . self::TYPE_IP_AUTOMATIC_TEMPORARY . " THEN 6
WHEN `type` = " . self::TYPE_WFSN_TEMPORARY . " THEN 7
WHEN `type` = " . self::TYPE_IP_MANUAL . " THEN 8
ELSE 9999
END AS `typeSort`, CASE 
WHEN `type` = " . self::TYPE_COUNTRY . " THEN `parameters`
WHEN `type` = " . self::TYPE_PATTERN . " THEN `parameters`
WHEN `type` = " . self::TYPE_IP_MANUAL . " THEN `IP`
WHEN `type` = " . self::TYPE_IP_AUTOMATIC_PERMANENT . " THEN `IP`
WHEN `type` = " . self::TYPE_RATE_BLOCK . " THEN `IP`
WHEN `type` = " . self::TYPE_RATE_THROTTLE . " THEN `IP`
WHEN `type` = " . self::TYPE_LOCKOUT . " THEN `IP`
WHEN `type` = " . self::TYPE_WFSN_TEMPORARY . " THEN `IP`
WHEN `type` = " . self::TYPE_IP_AUTOMATIC_TEMPORARY . " THEN `IP`
ELSE 9999
END AS `detailSort`
 FROM `{$blocksTable}` WHERE ";
		if( !empty($ofTypes) ) {
			$sanitizedTypes = array_map('intval', $ofTypes);
			$query .= "`type` IN (" . implode(', ', $sanitizedTypes) . ') AND ';
		}
		$query .= '(`expiration` = ' . self::DURATION_FOREVER . " OR `expiration` > UNIX_TIMESTAMP()) ORDER BY `{$sort}` {$order}, `id` DESC";

		if( $limit > -1 ) {
			$offset = (int)$offset;
			$limit = (int)$limit;
			$query .= " LIMIT {$offset},{$limit}";
		}

		$rows = $wpdb->get_results($query, ARRAY_A);
		$result = array();
		foreach($rows as $r) {
			if( $prefetch ) {
				if( $r['type'] == self::TYPE_COUNTRY || $r['type'] == self::TYPE_PATTERN ) {
					$ip = null;
				} else {
					$ip = \WBCR\Titan\Firewall\Utils::inet_ntop($r['IP']);
				}

				$parameters = null;
				if( $r['type'] == self::TYPE_PATTERN || $r['type'] == self::TYPE_COUNTRY ) {
					$parameters = @json_decode($r['parameters'], true);
				}

				$result[] = new \WBCR\Titan\Firewall\Model\Block($r['id'], $r['type'], $ip, $r['blockedTime'], $r['reason'], $r['lastAttempt'], $r['blockedHits'], $r['expiration'], $parameters);
			} else {
				$result[] = new \WBCR\Titan\Firewall\Model\Block($r['id']);
			}
		}

		return $result;
	}

	/**
	 * Functions identically to \WBCR\Titan\Firewall\Model\Block::allBlocks except that it filters the result. The filtering is done within PHP rather than MySQL, so this will impose a performance penalty and should only
	 * be used when filtering is actually wanted.
	 *
	 * @param bool $prefetch
	 * @param array $ofTypes
	 * @param int $offset
	 * @param int $limit
	 * @param string $sortColumn
	 * @param string $sortDirection
	 * @param string $filter
	 * @return \WBCR\Titan\Firewall\Model\Block[]
	 */
	public static function filteredBlocks($prefetch = false, $ofTypes = array(), $offset = 0, $limit = -1, $sortColumn = 'type', $sortDirection = 'ascending', $filter = '')
	{
		$filter = trim($filter);
		$matchType = '';
		$matchValue = '';
		if( empty($filter) ) {
			return self::allBlocks($prefetch, $ofTypes, $offset, $limit, $sortColumn, $sortDirection);
		} else if( \WBCR\Titan\Firewall\Utils::isValidIP($filter) ) { //e.g., 4.5.6.7, ffe0::, ::0
			$matchType = 'ip';
			$matchValue = \WBCR\Titan\Firewall\Utils::inet_ntop(\WBCR\Titan\Firewall\Utils::inet_pton($filter));
		}

		if( empty($matchType) && preg_match('/^(?:[0-9]+|\*)\.(?:(?:[0-9]+|\*)\.(?!$))*(?:(?:[0-9]+|\*))?$/', trim($filter, '.')) ) { //e.g., possible wildcard IPv4 like 4.5.*
			$components = explode('.', trim($filter, '.'));
			if( count($components) <= 4 ) {
				$components = array_pad($components, 4, '*');
				$matchType = 'ipregex';
				$matchValue = '^';
				foreach($components as $c) {
					if( empty($c) || $c == '*' ) {
						$matchValue .= '\d+';
					} else {
						$matchValue .= (int)$c;
					}

					$matchValue .= '\.';
				}
				$matchValue = substr($matchValue, 0, -2);
				$matchValue .= '$';
			}
		}

		if( empty($matchType) && preg_match('/^(?:[0-9a-f]+\:)(?:[0-9a-f]+\:|\*){1,2}(?:[0-9a-f]+|\*)?$/i', $filter) ) { //e.g., possible wildcard IPv6 like ffe0:*
			$components = explode(':', $filter);
			$matchType = 'ipregex';
			$matchValue = '^';
			for($i = 0; $i < 4; $i++) {
				if( isset($components[$i]) ) {
					$matchValue .= strtoupper(str_pad(dechex($components[$i]), 4, '0', STR_PAD_LEFT));
				} else {
					$matchValue .= '[0-9a-f]{4}';
				}
				$matchValue .= ':';
			}
			$matchValue = substr($matchValue, 0, -1);
			$matchValue .= '$';
		}

		if( empty($matchType) ) {
			$matchType = 'literal';
			$matchValue = $filter;
		}

		$offsetProcessed = 0;
		$limitProcessed = 0;

		$returnBlocks = array();
		for($i = 0; true; $i += WTITAN_BLOCKED_IPS_PER_PAGE) {
			$blocks = \WBCR\Titan\Firewall\Model\Block::allBlocks(true, $ofTypes, $i, WTITAN_BLOCKED_IPS_PER_PAGE, $sortColumn, $sortDirection);
			if( empty($blocks) ) {
				break;
			}

			foreach($blocks as $b) {
				$include = false;

				if( stripos($b->reason, $filter) !== false ) {
					$include = true;
				}

				if( !$include && $b->type == self::TYPE_PATTERN ) {
					if( stripos($b->hostname, $filter) !== false ) {
						$include = true;
					} else if( stripos($b->userAgent, $filter) !== false ) {
						$include = true;
					} else if( stripos($b->referrer, $filter) !== false ) {
						$include = true;
					} else if( stripos($b->ipRange, $filter) !== false ) {
						$include = true;
					}
				}

				if( !$include && stripos(self::nameForType($b->type), $filter) !== false ) {
					$include = true;
				}

				if( !$include ) {
					switch( $matchType ) {
						case 'ip':
							if( $b->matchRequest($matchValue, '', '') != self::MATCH_NONE ) {
								$include = true;
							} else if( $b->type == self::TYPE_LOCKOUT && \WBCR\Titan\Firewall\Utils::inet_pton($matchValue) == \WBCR\Titan\Firewall\Utils::inet_pton($b->ip) ) {
								$include = true;
							}
							break;
						case 'ipregex':
							if( preg_match('/' . $matchValue . '/i', $b->ip) ) {
								$include = true;
							}
							break;
						case 'literal':
							//Already checked above
							break;
					}
				}

				if( $include ) {
					if( $offsetProcessed < $offset ) { //Still searching for the start offset
						$offsetProcessed++;
						continue;
					}

					$returnBlocks[] = $b;
					$limitProcessed++;
				}

				if( $limit != -1 && $limitProcessed >= $limit ) {
					return $returnBlocks;
				}
			}
		}

		return $returnBlocks;
	}

	/**
	 * Returns all unexpired blocks of types \WBCR\Titan\Firewall\Model\Block::TYPE_IP_MANUAL, \WBCR\Titan\Firewall\Model\Block::TYPE_IP_AUTOMATIC_TEMPORARY, \WBCR\Titan\Firewall\Model\Block::TYPE_IP_AUTOMATIC_PERMANENT, \WBCR\Titan\Firewall\Model\Block::TYPE_WFSN_TEMPORARY, \WBCR\Titan\Firewall\Model\Block::TYPE_RATE_BLOCK, and \WBCR\Titan\Firewall\Model\Block::TYPE_RATE_THROTTLE.
	 *
	 * @param bool $prefetch If true, the full data for the block is fetched rather than using lazy loading.
	 * @return \WBCR\Titan\Firewall\Model\Block[]
	 */
	public static function ipBlocks($prefetch = false)
	{
		return self::allBlocks($prefetch, array(
			self::TYPE_IP_MANUAL,
			self::TYPE_IP_AUTOMATIC_TEMPORARY,
			self::TYPE_IP_AUTOMATIC_PERMANENT,
			self::TYPE_WFSN_TEMPORARY,
			self::TYPE_RATE_BLOCK,
			self::TYPE_RATE_THROTTLE
		));
	}

	/**
	 * Finds an IP block matching the given IP, returning it if found. Returns false if none are found.
	 *
	 * @param string $ip
	 * @return bool|\WBCR\Titan\Firewall\Model\Block
	 */
	public static function findIPBlock($ip)
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();

		$query = "SELECT * FROM `{$blocksTable}` WHERE ";

		$ofTypes = array(
			self::TYPE_IP_MANUAL,
			self::TYPE_IP_AUTOMATIC_TEMPORARY,
			self::TYPE_IP_AUTOMATIC_PERMANENT,
			self::TYPE_WFSN_TEMPORARY,
			self::TYPE_RATE_BLOCK,
			self::TYPE_RATE_THROTTLE
		);
		$query .= "`type` IN (" . implode(', ', $ofTypes) . ') AND ';
		$query .= "`IP` = %s AND ";
		$query .= '(`expiration` = ' . self::DURATION_FOREVER . ' OR `expiration` > UNIX_TIMESTAMP()) ORDER BY `blockedTime` DESC LIMIT 1';

		$r = $wpdb->get_row($wpdb->prepare($query, \WBCR\Titan\Firewall\Utils::inet_pton($ip)), ARRAY_A);
		if( is_array($r) ) {
			$ip = \WBCR\Titan\Firewall\Utils::inet_ntop($r['IP']);

			return new \WBCR\Titan\Firewall\Model\Block($r['id'], $r['type'], $ip, $r['blockedTime'], $r['reason'], $r['lastAttempt'], $r['blockedHits'], $r['expiration'], null);
		}

		return false;
	}

	/**
	 * Returns all unexpired blocks of type \WBCR\Titan\Firewall\Model\Block::TYPE_COUNTRY.
	 *
	 * @param bool $prefetch If true, the full data for the block is fetched rather than using lazy loading.
	 * @return \WBCR\Titan\Firewall\Model\Block[]
	 */
	public static function countryBlocks($prefetch = false)
	{
		return self::allBlocks($prefetch, array(self::TYPE_COUNTRY));
	}

	/**
	 * Returns whether or not there is a country block rule.
	 *
	 * @return bool
	 */
	public static function hasCountryBlock()
	{
		$countryBlocks = self::countryBlocks();

		return !empty($countryBlocks);
	}

	/**
	 * Returns the value for the country blocking bypass cookie.
	 *
	 * @return string
	 */
	public static function countryBlockingBypassCookieValue()
	{
		$val = wfConfig::get('cbl_cookieVal', false);
		if( !$val ) {
			$val = uniqid();
			wfConfig::set('cbl_cookieVal', $val);
		}

		return $val;
	}

	/**
	 * Returns all unexpired blocks of type \WBCR\Titan\Firewall\Model\Block::TYPE_PATTERN.
	 *
	 * @param bool $prefetch If true, the full data for the block is fetched rather than using lazy loading.
	 * @return \WBCR\Titan\Firewall\Model\Block[]
	 */
	public static function patternBlocks($prefetch = false)
	{
		return self::allBlocks($prefetch, array(self::TYPE_PATTERN));
	}

	/**
	 * Returns all unexpired lockouts (type \WBCR\Titan\Firewall\Model\Block::TYPE_LOCKOUT).
	 *
	 * @param bool $prefetch If true, the full data for the block is fetched rather than using lazy loading.
	 * @return \WBCR\Titan\Firewall\Model\Block[]
	 */
	public static function lockouts($prefetch = false)
	{
		return self::allBlocks($prefetch, array(self::TYPE_LOCKOUT));
	}

	/**
	 * Returns the lockout record for the given IP if it exists.
	 *
	 * @param string $ip
	 * @return bool|\WBCR\Titan\Firewall\Model\Block
	 */
	public static function lockoutForIP($ip)
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();

		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$blocksTable}` WHERE `IP` = %s AND `type` = %d AND (`expiration` = %d OR `expiration` > UNIX_TIMESTAMP())", \WBCR\Titan\Firewall\Utils::inet_pton($ip), self::TYPE_LOCKOUT, self::DURATION_FOREVER), ARRAY_A);
		if( $row ) {
			return new \WBCR\Titan\Firewall\Model\Block($row['id'], $row['type'], \WBCR\Titan\Firewall\Utils::inet_ntop($row['IP']), $row['blockedTime'], $row['reason'], $row['lastAttempt'], $row['blockedHits'], $row['expiration'], null);
		}

		return false;
	}

	/**
	 * Removes all blocks whose ID is in the given array.
	 *
	 * @param array $blockIDs
	 */
	public static function removeBlockIDs($blockIDs)
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();

		$blockIDs = array_map('intval', $blockIDs);
		$query = "DELETE FROM `{$blocksTable}` WHERE `id` IN (" . implode(', ', $blockIDs) . ")";
		$wpdb->query($query);
	}

	/**
	 * Removes all IP blocks (i.e., manual, wfsn, or rate limited)
	 */
	public static function removeAllIPBlocks()
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$wpdb->query("DELETE FROM `{$blocksTable}` WHERE `type` IN (" . implode(', ', array(
				self::TYPE_IP_MANUAL,
				self::TYPE_IP_AUTOMATIC_TEMPORARY,
				self::TYPE_IP_AUTOMATIC_PERMANENT,
				self::TYPE_WFSN_TEMPORARY,
				self::TYPE_RATE_BLOCK,
				self::TYPE_RATE_THROTTLE,
				self::TYPE_LOCKOUT
			)) . ")");
	}

	/**
	 * Removes all country blocks
	 */
	public static function removeAllCountryBlocks()
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$wpdb->query("DELETE FROM `{$blocksTable}` WHERE `type` IN (" . implode(', ', array(self::TYPE_COUNTRY)) . ")");
	}

	/**
	 * Removes all blocks that were created by WFSN responses.
	 */
	public static function removeTemporaryWFSNBlocks()
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$wpdb->query($wpdb->prepare("DELETE FROM `{$blocksTable}` WHERE `type` = %d", self::TYPE_WFSN_TEMPORARY));
	}

	/**
	 * Converts all blocks to non-expiring whose ID is in the given array.
	 *
	 * @param array $blockIDs
	 */
	public static function makePermanentBlockIDs($blockIDs)
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();

		//TODO: revise this if we support user-customizable durations
		$supportedTypes = array(
			self::TYPE_WFSN_TEMPORARY,
			self::TYPE_RATE_BLOCK,
			self::TYPE_RATE_THROTTLE,
			self::TYPE_LOCKOUT,
			self::TYPE_IP_AUTOMATIC_TEMPORARY,
		);

		$blockIDs = array_map('intval', $blockIDs);
		$query = $wpdb->prepare("UPDATE `{$blocksTable}` SET `expiration` = %d, `type` = %d WHERE `id` IN (" . implode(', ', $blockIDs) . ") AND `type` IN (" . implode(', ', $supportedTypes) . ") AND (`expiration` > UNIX_TIMESTAMP())", self::DURATION_FOREVER, self::TYPE_IP_AUTOMATIC_PERMANENT);
		$wpdb->query($query);

		$supportedTypes = array(
			self::TYPE_IP_MANUAL,
		);

		$blockIDs = array_map('intval', $blockIDs);
		$query = $wpdb->prepare("UPDATE `{$blocksTable}` SET `expiration` = %d, `type` = %d WHERE `id` IN (" . implode(', ', $blockIDs) . ") AND `type` IN (" . implode(', ', $supportedTypes) . ") AND (`expiration` > UNIX_TIMESTAMP())", self::DURATION_FOREVER, self::TYPE_IP_MANUAL);
		$wpdb->query($query);
	}

	/**
	 * Removes all specific IP blocks and lockouts that can result in the given IP being blocked.
	 *
	 * @param string $ip
	 */
	public static function unblockIP($ip)
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$wpdb->query($wpdb->prepare("DELETE FROM `{$blocksTable}` WHERE `IP` = %s", \WBCR\Titan\Firewall\Utils::inet_pton($ip)));
	}

	/**
	 * Removes all lockouts that can result in the given IP being blocked.
	 *
	 * @param string $ip
	 */
	public static function unlockOutIP($ip)
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$wpdb->query($wpdb->prepare("DELETE FROM `{$blocksTable}` WHERE `IP` = %s AND `type` = %d", \WBCR\Titan\Firewall\Utils::inet_pton($ip), self::TYPE_LOCKOUT));
	}

	/**
	 * Constructs a \WBCR\Titan\Firewall\Model\Block instance. This _does not_ create a new record in the table, only fetches or updates an existing one.
	 *
	 * @param $id
	 * @param bool $type
	 * @param bool $ip
	 * @param bool $blockedTime
	 * @param bool $reason
	 * @param bool $lastAttempt
	 * @param bool $blockedHits
	 * @param bool $expiration
	 * @param bool $parameters
	 */
	public function __construct($id, $type = false, $ip = false, $blockedTime = false, $reason = false, $lastAttempt = false, $blockedHits = false, $expiration = false, $parameters = false)
	{
		$this->_id = $id;
		$this->_type = $type;
		$this->_ip = $ip;
		$this->_blockedTime = $blockedTime;
		$this->_reason = $reason;
		$this->_lastAttempt = $lastAttempt;
		$this->_blockedHits = $blockedHits;
		$this->_expiration = $expiration;
		$this->_parameters = $parameters;
	}

	public function __get($key)
	{
		switch( $key ) {
			case 'id':
				return $this->_id;
			case 'type':
				if( $this->_type === false ) {
					$this->_fetch();
				}

				return $this->_type;
			case 'ip':
				if( $this->_type === false ) {
					$this->_fetch();
				}

				return $this->_ip;
			case 'blockedTime':
				if( $this->_type === false ) {
					$this->_fetch();
				}

				return $this->_blockedTime;
			case 'reason':
				if( $this->_type === false ) {
					$this->_fetch();
				}

				return $this->_reason;
			case 'lastAttempt':
				if( $this->_type === false ) {
					$this->_fetch();
				}

				return $this->_lastAttempt;
			case 'blockedHits':
				if( $this->_type === false ) {
					$this->_fetch();
				}

				return $this->_blockedHits;
			case 'expiration':
				if( $this->_type === false ) {
					$this->_fetch();
				}

				return $this->_expiration;
			case 'parameters':
				if( $this->_type === false ) {
					$this->_fetch();
				}

				return $this->_parameters;

			//Country
			case 'blockLogin':
				if( $this->type != self::TYPE_COUNTRY ) {
					throw new OutOfBoundsException("{$key} is not a valid property for this block type");
				}

				return $this->parameters['blockLogin'];
			case 'blockSite':
				if( $this->type != self::TYPE_COUNTRY ) {
					throw new OutOfBoundsException("{$key} is not a valid property for this block type");
				}

				return $this->parameters['blockSite'];
			case 'countries':
				if( $this->type != self::TYPE_COUNTRY ) {
					throw new OutOfBoundsException("{$key} is not a valid property for this block type");
				}

				return $this->parameters['countries'];

			//Pattern
			case 'ipRange':
				if( $this->type != self::TYPE_PATTERN ) {
					throw new OutOfBoundsException("{$key} is not a valid property for this block type");
				}

				return $this->parameters['ipRange'];
			case 'hostname':
				if( $this->type != self::TYPE_PATTERN ) {
					throw new OutOfBoundsException("{$key} is not a valid property for this block type");
				}

				return $this->parameters['hostname'];
			case 'userAgent':
				if( $this->type != self::TYPE_PATTERN ) {
					throw new OutOfBoundsException("{$key} is not a valid property for this block type");
				}

				return $this->parameters['userAgent'];
			case 'referrer':
				if( $this->type != self::TYPE_PATTERN ) {
					throw new OutOfBoundsException("{$key} is not a valid property for this block type");
				}

				return $this->parameters['referrer'];
		}

		throw new OutOfBoundsException("{$key} is not a valid property");
	}

	public function __isset($key)
	{
		switch( $key ) {
			case 'id':
			case 'type':
			case 'ip':
			case 'blockedTime':
			case 'reason':
			case 'lastAttempt':
			case 'blockedHits':
			case 'expiration':
				return true;
			case 'parameters':
				if( $this->_type === false ) {
					$this->_fetch();
				}

				return !empty($this->_parameters);

			//Country
			case 'blockLogin':
				if( $this->type != self::TYPE_COUNTRY ) {
					return false;
				}

				return !empty($this->parameters['blockLogin']);
			case 'blockSite':
				if( $this->type != self::TYPE_COUNTRY ) {
					return false;
				}

				return !empty($this->parameters['blockSite']);
			case 'countries':
				if( $this->type != self::TYPE_COUNTRY ) {
					return false;
				}

				return !empty($this->parameters['countries']);

			//Pattern
			case 'ipRange':
				if( $this->type != self::TYPE_PATTERN ) {
					return false;
				}

				return !empty($this->parameters['ipRange']);
			case 'hostname':
				if( $this->type != self::TYPE_PATTERN ) {
					return false;
				}

				return !empty($this->parameters['hostname']);
			case 'userAgent':
				if( $this->type != self::TYPE_PATTERN ) {
					return false;
				}

				return !empty($this->parameters['userAgent']);
			case 'referrer':
				if( $this->type != self::TYPE_PATTERN ) {
					return false;
				}

				return !empty($this->parameters['referrer']);
		}

		return false;
	}

	/**
	 * Fetches the record for the block from the database and populates the instance variables.
	 */
	private function _fetch()
	{
		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$blocksTable}` WHERE `id` = %d", $this->id), ARRAY_A);
		if( $row !== null ) {
			$this->_type = $row['type'];

			$ip = $row['IP'];
			if( $ip == self::MARKER_COUNTRY || $ip == self::MARKER_PATTERN ) {
				$this->_ip = null;
			} else {
				$this->_ip = \WBCR\Titan\Firewall\Utils::inet_ntop($ip);
			}

			$this->_blockedTime = $row['blockedTime'];
			$this->_reason = $row['reason'];
			$this->_lastAttempt = $row['lastAttempt'];
			$this->_blockedHits = $row['blockedHits'];
			$this->_expiration = $row['expiration'];

			$parameters = $row['parameters'];
			if( $parameters === null ) {
				$this->_parameters = null;
			} else {
				$this->_parameters = @json_decode($parameters, true);
			}
		}
	}

	/**
	 * Tests the block parameters against the given request. If matched, this will return the corresponding \WBCR\Titan\Firewall\Model\Block::MATCH_
	 * constant. If not, it will return \WBCR\Titan\Firewall\Model\Block::MATCH_NONE.
	 *
	 * @param $ip
	 * @param $userAgent
	 * @param $referrer
	 * @return int
	 */
	public function matchRequest($ip, $userAgent, $referrer)
	{
		switch( $this->type ) {
			case self::TYPE_IP_MANUAL:
			case self::TYPE_IP_AUTOMATIC_TEMPORARY:
			case self::TYPE_IP_AUTOMATIC_PERMANENT:
			case self::TYPE_WFSN_TEMPORARY:
			case self::TYPE_RATE_BLOCK:
			case self::TYPE_RATE_THROTTLE:
				if( \WBCR\Titan\Firewall\Utils::inet_pton($ip) == \WBCR\Titan\Firewall\Utils::inet_pton($this->ip) ) {
					return self::MATCH_IP;
				}
				break;
			case self::TYPE_PATTERN:
				$match = (!empty($this->ipRange) || !empty($this->hostname) || !empty($this->userAgent) || !empty($this->referrer));
				if( !empty($this->ipRange) ) {
					$range = new \WBCR\Titan\Firewall\User_IP_Range($this->ipRange);
					$match = $match && $range->isIPInRange($ip);
				}
				if( !empty($this->hostname) ) {
					$hostname = \WBCR\Titan\Firewall\Utils::reverseLookup($ip);
					$match = $match && preg_match(\WBCR\Titan\Firewall\Utils::patternToRegex($this->hostname), $hostname);
				}
				if( !empty($this->userAgent) ) {
					$match = $match && fnmatch($this->userAgent, $userAgent, FNM_CASEFOLD);
				}
				if( !empty($this->referrer) ) {
					$match = $match && fnmatch($this->referrer, $referrer, FNM_CASEFOLD);
				}

				if( $match ) {
					return self::MATCH_PATTERN;
				}

				break;
			case self::TYPE_COUNTRY:
				if( !wfConfig::get('isPaid') ) {
					return self::MATCH_NONE;
				}

				//Bypass Redirect URL Hit
				$bareRequestURI = \WBCR\Titan\Firewall\Utils::extractBareURI($_SERVER['REQUEST_URI']);
				$bareBypassRedirURI = \WBCR\Titan\Firewall\Utils::extractBareURI(wfConfig::get('cbl_bypassRedirURL', ''));
				if( $bareBypassRedirURI && $bareRequestURI == $bareBypassRedirURI ) {
					$bypassRedirDest = wfConfig::get('cbl_bypassRedirDest', '');
					if( $bypassRedirDest ) {
						\WBCR\Titan\Firewall\Utils::setcookie('wfCBLBypass', \WBCR\Titan\Firewall\Model\Block::countryBlockingBypassCookieValue(), time() + (86400 * 365), '/', null, \WBCR\Titan\Firewall\Utils::isFullSSL(), true);

						return self::MATCH_COUNTRY_REDIR_BYPASS;
					}
				}

				//Bypass View URL Hit
				$bareBypassViewURI = \WBCR\Titan\Firewall\Utils::extractBareURI(wfConfig::get('cbl_bypassViewURL', ''));
				if( $bareBypassViewURI && $bareBypassViewURI == $bareRequestURI ) {
					\WBCR\Titan\Firewall\Utils::setcookie('wfCBLBypass', \WBCR\Titan\Firewall\Model\Block::countryBlockingBypassCookieValue(), time() + (86400 * 365), '/', null, \WBCR\Titan\Firewall\Utils::isFullSSL(), true);

					return self::MATCH_NONE;
				}

				//Early exit checks
				if( $this->_shouldBypassCountryBlocking() ) { //Has valid bypass cookie
					return self::MATCH_NONE;
				}

				if( $this->blockLogin ) {
					add_filter('authenticate', array($this, '_checkForBlockedCountryFilter'), 1, 1);
				}

				if( !$this->blockLogin && $this->_isAuthRequest() ) { //Not blocking login and this is a login request
					return self::MATCH_NONE;
				} else if( !$this->blockSite && !$this->_isAuthRequest() ) { //Not blocking site and this may be a site request
					return self::MATCH_NONE;
				} else if( is_user_logged_in() && !wfConfig::get('cbl_loggedInBlocked', false) ) { //Not blocking logged in users and a login session exists
					return self::MATCH_NONE;
				}

				//Block everything
				if( $this->blockSite && $this->blockLogin ) {
					return $this->_checkForBlockedCountry();
				}

				//Block the login form itself and any attempt to authenticate
				if( $this->blockLogin && $this->_isAuthRequest() ) {
					return $this->_checkForBlockedCountry();
				}

				//Block requests that aren't to the login page, xmlrpc.php, or a user already logged in
				if( $this->blockSite && !$this->_isAuthRequest() && !defined('XMLRPC_REQUEST') ) {
					return $this->_checkForBlockedCountry();
				}

				//XMLRPC is inaccesible when public portion of the site and auth is disabled
				if( $this->blockLogin && $this->blockSite && defined('XMLRPC_REQUEST') ) {
					return $this->_checkForBlockedCountry();
				}

				break;
		}

		return self::MATCH_NONE;
	}

	/**
	 * Returns whether or not the current request should be treated as an auth request.
	 *
	 * @return bool
	 */
	private function _isAuthRequest()
	{
		if( (strpos($_SERVER['REQUEST_URI'], '/wp-login.php') !== false) ) {
			return true;
		}

		return false;
	}

	/**
	 * Tests whether or not the country blocking bypass cookie is set and valid.
	 *
	 * @return bool
	 */
	private function _shouldBypassCountryBlocking()
	{
		if( isset($_COOKIE['wfCBLBypass']) && $_COOKIE['wfCBLBypass'] == \WBCR\Titan\Firewall\Model\Block::countryBlockingBypassCookieValue() ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks the country block against the requesting IP, returning the action to take.
	 *
	 * @return int
	 */
	private function _checkForBlockedCountry()
	{
		$blockedCountries = $this->countries;
		$bareRequestURI = untrailingslashit(\WBCR\Titan\Firewall\Utils::extractBareURI($_SERVER['REQUEST_URI']));
		$IP = \WBCR\Titan\Firewall\Utils::getIP();
		if( $country = \WBCR\Titan\Firewall\Utils::IP2Country($IP) ) {
			foreach($blockedCountries as $blocked) {
				if( strtoupper($blocked) == strtoupper($country) ) { //At this point we know the user has been blocked
					if( wfConfig::get('cbl_action') == 'redir' ) {
						$redirURL = wfConfig::get('cbl_redirURL');
						$eRedirHost = \WBCR\Titan\Firewall\Utils::extractHostname($redirURL);
						$isExternalRedir = false;
						if( $eRedirHost && $eRedirHost != \WBCR\Titan\Firewall\Utils::extractHostname(home_url()) ) { //It's an external redirect...
							$isExternalRedir = true;
						}

						if( (!$isExternalRedir) && untrailingslashit(\WBCR\Titan\Firewall\Utils::extractBareURI($redirURL)) == $bareRequestURI ) { //Is this the URI we want to redirect to, then don't block it
							return self::MATCH_NONE;
						} else {
							return self::MATCH_COUNTRY_REDIR;
						}
					} else {
						return self::MATCH_COUNTRY_BLOCK;
					}
				}
			}
		}

		return self::MATCH_NONE;
	}

	/**
	 * Filter hook for the country blocking check. Does nothing if not blocked, otherwise presents the block page and exits.
	 *
	 * Note: Must remain `public` for callback to work.
	 */
	public function _checkForBlockedCountryFilter($user)
	{
		$block = $this->_checkForBlockedCountry();
		if( $block == self::MATCH_NONE ) {
			return $user;
		}

		$log = wfLog::shared();
		$log->getCurrentRequest()->actionDescription = __('blocked access via country blocking', 'titan-security');
		wfConfig::inc('totalCountryBlocked');
		wfActivityReport::logBlockedIP(\WBCR\Titan\Firewall\Utils::getIP(), null, 'country');
		$log->do503(3600, __('Access from your area has been temporarily limited for security reasons', 'titan-security')); //exits
	}

	/**
	 * Adds $quantity to the blocked count and sets the timestamp for lastAttempt.
	 *
	 * @param int $quantity
	 * @param bool|int $timestamp
	 */
	public function recordBlock($quantity = 1, $timestamp = false)
	{
		if( $timestamp === false ) {
			$timestamp = time();
		}

		global $wpdb;
		$blocksTable = \WBCR\Titan\Firewall\Model\Block::blocksTable();
		$wpdb->query($wpdb->prepare("UPDATE `{$blocksTable}` SET `blockedHits` = `blockedHits` + %d, `lastAttempt` = GREATEST(`lastAttempt`, %d) WHERE `id` = %d", $quantity, $timestamp, $this->id));
		$this->_type = false; //Trigger a re-fetch next access
	}

	/**
	 * Returns an array suitable for JSON of the values needed to edit the block.
	 *
	 * @return array
	 */
	public function editValues()
	{
		switch( $this->type ) {
			case self::TYPE_COUNTRY:
				return array(
					'blockLogin' => \WBCR\Titan\Firewall\Utils::truthyToInt($this->blockLogin),
					'blockSite' => \WBCR\Titan\Firewall\Utils::truthyToInt($this->blockSite),
					'countries' => $this->countries,
					'reason' => $this->reason,
					'expiration' => $this->expiration,
				);
		}

		return array();
	}

	public static function update_count_total_ip_blocking()
	{
		$total_ip_blocked = \WBCR\Titan\Plugin::app()->getPopulateOption('total_ip_blocked', 0);
		$total_ip_blocked = (int)$total_ip_blocked + 1;
		\WBCR\Titan\Plugin::app()->updatePopulateOption('total_ip_blocked', $total_ip_blocked);

		return $total_ip_blocked;
	}
}
