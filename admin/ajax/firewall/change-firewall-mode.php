<?php
// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

function wtitan_change_firewall_mode()
{
	check_ajax_referer('wtitan_change_firewall_mode');

	if( !current_user_can('manage_options') ) {
		wp_send_json(array('error' => __('You don\'t have enough capability to edit this information.', 'clearfy')));
	}

	$mode_name = \WBCR\Titan\Plugin::app()->request->post('mode', false, true);

	if( empty($mode_name) ) {
		wp_send_json(array('error' => __('Undefinded mode.', 'clearfy')));
	}

	\WBCR\Titan\Plugin::app()->updatePopulateOption('firewall_mode', $mode_name);

	//$status = \WBCR\Titan\Plugin::app()->fw_storage()->getConfig('wafStatus');
	//\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('wafStatus', $mode_name);
	//  $status = \WBCR\Titan\Plugin::app()->fw_storage()->getConfig('wafStatus');

	if( 'disabled' === $mode_name ) {
		\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('wafStatus', 'disabled');
		\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('wafDisabled', true);
		\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('learningModeGracePeriodEnabled', 0);
		\WBCR\Titan\Plugin::app()->fw_storage()->unsetConfig('learningModeGracePeriod');
	}

	if( 'enabled' === $mode_name ) {
		\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('wafStatus', 'enabled');
		\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('learningModeGracePeriodEnabled', 0);
		\WBCR\Titan\Plugin::app()->fw_storage()->unsetConfig('learningModeGracePeriod');
		\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('wafDisabled', false);
	}

	if( 'learning-mode' === $mode_name ) {
		\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('wafStatus', 'learning-mode');
		\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('learningModeGracePeriodEnabled', 1);
		\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('learningModeGracePeriod', strtotime("+7 days"));
		\WBCR\Titan\Plugin::app()->fw_storage()->setConfig('wafDisabled', false);
	}

	wp_send_json_success();
}

add_action('wp_ajax_wtitan-change-firewall-mode', 'wtitan_change_firewall_mode');

