<?php

namespace WBCR\Titan\Model;

class Firewall {

	const FIREWALL_MODE_DISABLED = 'disabled';
	const FIREWALL_MODE_LEARNING = 'learning-mode';
	const FIREWALL_MODE_ENABLED = 'enabled';

	const PROTECTION_MODE_EXTENDED = 'extended';
	const PROTECTION_MODE_BASIC = 'basic';

	const RULE_MODE_COMMUNITY = 'community';
	const RULE_MODE_PREMIUM = 'premium';

	const BLACKLIST_MODE_DISABLED = 'disabled';
	const BLACKLIST_MODE_ENABLED = 'enabled';

	const UPDATE_FAILURE_RATELIMIT = 'ratelimit';
	const UPDATE_FAILURE_UNREACHABLE = 'unreachable';
	const UPDATE_FAILURE_FILESYSTEM = 'filesystem';

	public static function getWAFBootstrapPath()
	{
		return ABSPATH . 'titan-firewall.php';
	}

	public static function getWAFBootstrapContent($currentAutoPrependedFile = null)
	{
		$currentAutoPrepend = '';
		//WTITAN_PLUGIN_DIR . '/includes/firewall/titan_logs/'
		$bootstrap_path = var_export(WTITAN_PLUGIN_DIR . '/includes/firewall/bootstrap.php', true);
		//$logs_path = var_export(WFWAF_SUBDIRECTORY_INSTALL ? WP_CONTENT_DIR . '/titan_logs/' : WFWAF_LOG_PATH, true);
		$logs_path = var_export(WFWAF_SUBDIRECTORY_INSTALL ? WTITAN_PLUGIN_DIR . '/includes/firewall/titan_logs/' : WFWAF_LOG_PATH, true);

		if( $currentAutoPrependedFile && is_file($currentAutoPrependedFile) && !WFWAF_SUBDIRECTORY_INSTALL ) {
			$currentAutoPrepend = sprintf('
// This file was the current value of auto_prepend_file during the Wordfence WAF installation (%2$s)
if (file_exists(%1$s)) {
	include_once %1$s;
}', var_export($currentAutoPrependedFile, true), date('r'));
		}

		return sprintf('<?php
// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.
%3$s
if (file_exists(%1$s)) {
	define("WFWAF_LOG_PATH", %2$s);
	include_once %1$s;
}
?>', $bootstrap_path, $logs_path, $currentAutoPrepend);
	}

	/*public static function checkAndCreateBootstrap() {
		$bootstrapPath = self::getWAFBootstrapPath();
		if (!file_exists($bootstrapPath) || !filesize($bootstrapPath)) {
			@file_put_contents($bootstrapPath, self::getWAFBootstrapContent(), LOCK_EX);
			clearstatcache();
		}
		return file_exists($bootstrapPath) && filesize($bootstrapPath);
	}*/

	/**
	 * Returns the percentage calculation of the WAF status, which is displayed under "Web Application
	 * Firewall" on the Firewall page.
	 *
	 * @return float
	 */
	public function wafStatus()
	{
		try {
			//$ruleStatus = $this->ruleStatus(true);
			//$blacklistStatus = $this->blacklistStatus();
			$wafEnabled = !(!WFWAF_ENABLED || \WBCR\Titan\Plugin::app()->fw_storage()->isDisabled());
			$extendedProtection = $wafEnabled && WFWAF_AUTO_PREPEND && !WFWAF_SUBDIRECTORY_INSTALL;
			//$rateLimitingAdvancedBlockingEnabled = wfConfig::get('firewallEnabled', 1);

			if( !$wafEnabled ) {
				return 0.0;
			}

			$percentage = 0.30;
			//$percentage += $this->_normalizedPercentageToDisplay($ruleStatus * 0.35, true);
			//$percentage += $blacklistStatus * 0.35;
			$percentage += ($extendedProtection ? 0.70 : 0.0);

			//$percentage += ($rateLimitingAdvancedBlockingEnabled ? 0.10 : 0.0);
			return $this->_normalizedPercentageToDisplay($percentage, false);
		} catch( \Exception $e ) {
			//Ignore, return 0%
		}

		return 0.0;
	}

	/**
	 * Returns a normalized percentage (i.e., in the range [0, 1]) to the corresponding display percentage
	 * based on license type.
	 *
	 * @param float $percentage
	 * @param bool $adjust Whether or not to adjust the range to [0, 0.7]
	 * @return float
	 */
	protected function _normalizedPercentageToDisplay($percentage, $adjust = true)
	{
		if( !$adjust ) {
			return round($percentage, 2);
		}

		return round($percentage * 0.70, 2);
	}

	/**
	 * Returns a string suitable for display of the firewall status.
	 *
	 * @param null|string $status
	 * @param null|string $protection
	 * @return string
	 */
	public function displayText($status = null, $protection = null)
	{
		if( $status === null ) {
			$status = $this->firewallMode();
		}
		if( $protection === null ) {
			$protection = $this->protectionMode();
		}

		switch( $status ) {
			case self::FIREWALL_MODE_ENABLED:
				$statusText = __('Enabled', 'wordfence');
				break;
			case self::FIREWALL_MODE_LEARNING:
				$statusText = __('Learning Mode', 'wordfence');
				break;
			default:
				return __('Disabled', 'wordfence');
		}

		switch( $protection ) {
			case self::PROTECTION_MODE_EXTENDED:
				$protectionText = __('Extended Protection', 'wordfence');
				break;
			default:
				$protectionText = __('Basic Protection', 'wordfence');
				break;
		}

		return sprintf('%s (%s)', $statusText, $protectionText);
	}

	/**
	 * Tests the WAF configuration and returns true if successful.
	 *
	 * @return bool
	 */
	public function testConfig()
	{
		try {
			\WBCR\Titan\Plugin::app()->fw_storage()->isDisabled();
		} catch( \Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the status of the WAF.
	 *
	 * @return string
	 */
	public function firewallMode()
	{
		try {
			return (!WFWAF_ENABLED ? 'disabled' : \WBCR\Titan\Plugin::app()->fw_storage()->getConfig('wafStatus'));
		} catch( \Exception $e ) {
			//Ignore
		}

		return self::FIREWALL_MODE_DISABLED;
	}

	/**
	 * Returns the current protection mode configured for the WAF.
	 *
	 * @return string
	 */
	public function protectionMode()
	{
		if( defined('WFWAF_AUTO_PREPEND') && WFWAF_AUTO_PREPEND ) {
			return self::PROTECTION_MODE_EXTENDED;
		}

		return self::PROTECTION_MODE_BASIC;
	}

	/**
	 * Returns whether or not this installation is in a subdirectory of another WordPress site with the WAF already optimized.
	 *
	 * @return bool
	 */
	public function isSubDirectoryInstallation()
	{
		if( defined('WFWAF_SUBDIRECTORY_INSTALL') && WFWAF_SUBDIRECTORY_INSTALL ) {
			return true;
		}

		return false;
	}


	/**
	 * Returns the blacklist mode.
	 *
	 * @return string
	 */
	public function blacklistMode()
	{
		$blacklistEnabled = false;
		try {
			$wafEnabled = !(!WFWAF_ENABLED || \WBCR\Titan\Plugin::app()->fw_storage()->isDisabled());
			$blacklistEnabled = $wafEnabled && !\WBCR\Titan\Plugin::app()->fw_storage()->getConfig('disableWAFBlacklistBlocking');
		} catch( \Exception $e ) {
			//Do nothing
		}

		if( $blacklistEnabled ) {
			return self::BLACKLIST_MODE_ENABLED;
		}

		return self::BLACKLIST_MODE_DISABLED;
	}

	/**
	 * Returns the status of the WAF's learning mode.
	 *
	 * @return bool|int Returns true if enabled without an automatic switchover, a timestamp if enabled with one, and false if not in learning mode.
	 */
	public function learningModeStatus()
	{
		if( $this->firewallMode() != self::FIREWALL_MODE_LEARNING ) {
			return false;
		}

		try {
			$config = \WBCR\Titan\Plugin::app()->fw_storage();
			if( $config->getConfig('learningModeGracePeriodEnabled') ) {
				return (int)$config->getConfig('learningModeGracePeriod');
			}

			return true;
		} catch( \Exception $e ) {
			//Ignore, return false
		}

		return false;
	}

}
