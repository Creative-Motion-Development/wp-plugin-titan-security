<?php

namespace WBCR\Titan;

/**
 * Activator for the Antispam
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 26.10.2019, Webcraftic
 * @see           Wbcr_Factory000_Activator
 * @version       1.0
 */

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

class Activation extends \Wbcr_Factory000_Activator {

	/**
	 * Runs activation actions.
	 *
	 * @since  6.0
	 */
	public function activate()
	{
		$plugin_version_in_db = $this->get_plugin_version_in_db();
		$current_plugin_version = $this->plugin->getPluginVersion();

		$tab = "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t";
		$log_message = "Plugin starts activation [START].\r\n";
		$log_message .= "{$tab}-Plugin Version in DB: {$plugin_version_in_db}\r\n";
		$log_message .= "{$tab}-Current Plugin Version: {$current_plugin_version}";

		\WBCR\Titan\Logger\Writter::info($log_message);

		if( !\WBCR\Titan\Plugin::app()->getPopulateOption('firewall_mode') ) {
			require_once(WTITAN_PLUGIN_DIR . '/includes/firewall/class-utils.php');

			\WBCR\Titan\Plugin::app()->updatePopulateOption('firewall_mode', 'disabled');
			\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('wafStatus', 'disabled');
			\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('wafDisabled', true);
			\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('learningModeGracePeriodEnabled', 0);
			\WBCR\Titan\Plugin::app()->fw_storage()->unsetConfig('learningModeGracePeriod');

			//\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('authKey', '/vXIm$ vi0I0sZlgI1tIY=!N>]DnVGVPv};l.!_,#mgRA..*hK]%(xv+8F~?Tng!');

			\WBCR\Titan\Plugin::app()->updatePopulateOption('enckey', substr(\WBCR\Titan\Firewall\Utils::bigRandomHex(), 0, 16));
			\WBCR\Titan\Plugin::app()->updatePopulateOption('long_enc_key', \WBCR\Titan\Firewall\Utils::random_bytes(32));
			/*$configDefaults = array(
				'apiKey'         => wfConfig::get('apiKey'),
				'isPaid'         => !!wfConfig::get('isPaid'),
				'siteURL'        => $siteurl,
				'homeURL'        => $homeurl,
				'whitelistedIPs' => (string) wfConfig::get('whitelisted'),
				'whitelistedServiceIPs' => @json_encode(wfUtils::whitelistedServiceIPs()),
				'howGetIPs'      => (string) wfConfig::get('howGetIPs'),
				'howGetIPs_trusted_proxies' => wfConfig::get('howGetIPs_trusted_proxies', ''),
				'detectProxyRecommendation' => (string) wfConfig::get('detectProxyRecommendation'),
				'other_WFNet'    => !!wfConfig::get('other_WFNet', true),
				'pluginABSPATH'	 => ABSPATH,
				'serverIPs'		 => json_encode(wfUtils::serverIPs()),
				'blockCustomText' => wpautop(wp_strip_all_tags(wfConfig::get('blockCustomText', ''))),
				'betaThreatDefenseFeed' => !!wfConfig::get('betaThreatDefenseFeed'),
				'disableWAFIPBlocking' => wfConfig::get('disableWAFIPBlocking'),
			);
			if (wfUtils::isAdmin()) {
				$errorNonceKey = 'errorNonce_' . get_current_user_id();
				$configDefaults[$errorNonceKey] = wp_create_nonce('wf-waf-error-page'); //Used by the AJAX watcher script
			}
			foreach ($configDefaults as $key => $value) {
				$waf->getStorageEngine()->setConfig($key, $value, 'synced');
			}*/
		}

		// We create db tables for firewall
		require_once WTITAN_PLUGIN_DIR . '/includes/firewall/class-database-schema.php';
		$db_schema = new \WBCR\Titan\Database\Schema();
		$db_schema->create_all();
	}

	/**
	 * Get previous plugin version
	 *
	 * @return number
	 * @since  6.0
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 */
	public function get_plugin_version_in_db()
	{
		if( \WBCR\Titan\Plugin::app()->isNetworkActive() ) {
			return get_site_option(\WBCR\Titan\Plugin::app()->getOptionName('plugin_version'), 0);
		}

		return get_option(\WBCR\Titan\Plugin::app()->getOptionName('plugin_version'), 0);
	}


	/**
	 * Run deactivation actions.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 */
	public function deactivate()
	{
		\WBCR\Titan\Logger\Writter::info("Plugin starts deactivate [START].");
		\WBCR\Titan\Logger\Writter::info("Plugin has been deactivated [END]!");
	}
}
