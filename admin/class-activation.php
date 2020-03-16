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
		}

		// We create db tables for firewall
		require_once WTITAN_PLUGIN_DIR . '/includes/firewall/class-database-schema.php';
		$db_schema = new \WBCR\Titan\Database\Schema();
		$db_schema->create_all();
	}

	/**
	 * Run deactivation actions.
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 */
	public function deactivate()
	{

	}
}
