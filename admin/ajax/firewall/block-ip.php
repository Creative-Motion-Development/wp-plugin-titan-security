<?php
// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

function wtitan_block_ips()
{
	check_ajax_referer('wtitan_block_ip');

	if( !current_user_can('manage_options') ) {
		wp_send_json(array('error' => __('You don\'t have enough capability to edit this information.', 'titan-security')));
	}

	/*$offset = 0;
	if( isset($_POST['offset']) ) {
		$offset = (int)$_POST['offset'];
	}

	$sortColumn = 'type';
	if( isset($_POST['sortColumn']) && in_array($_POST['sortColumn'], array(
			'type',
			'detail',
			'ruleAdded',
			'reason',
			'expiration',
			'blockCount',
			'lastAttempt'
		)) ) {
		$sortColumn = $_POST['sortColumn'];
	}

	$sortDirection = 'ascending';
	if( isset($_POST['sortDirection']) && in_array($_POST['sortDirection'], array('ascending', 'descending')) ) {
		$sortDirection = $_POST['sortDirection'];
	}

	$filter = '';
	if( isset($_POST['blocksFilter']) ) {
		$filter = $_POST['blocksFilter'];
	}*/

	if( !empty($_POST['payload']) ) {
		$payload = \WBCR\Titan\Plugin::app()->request->post('payload', [], true);
		try {
			$error = \WBCR\Titan\Firewall\Model\Block::validate($payload);
			if( $error !== true ) {
				wp_send_json(array('error' => $error));
			}

			\WBCR\Titan\Firewall\Model\Block::create($payload);
			$hasCountryBlock = false;
			//$blocks = self::_blocksAJAXReponse($hasCountryBlock, $offset, $sortColumn, $sortDirection, $filter);
			//return array('success' => true, 'blocks' => $blocks, 'hasCountryBlock' => $hasCountryBlock);

			wp_send_json(array('success' => true, 'hasCountryBlock' => $hasCountryBlock));
		} catch( Exception $e ) {
			wp_send_json(array('error' => __('An error occurred while creating the block.', 'titan-security')));
		}
	}
	wp_send_json(array('error' => __('No block parameters were provided.', 'titan-security')));
	/*$IP = trim($_POST['IP']);
	$perm = (isset($_POST['perm']) && $_POST['perm'] == '1') ? \WBCR\Titan\Firewall\Model\Block::DURATION_FOREVER : wfConfig::getInt('blockedTime');
	if (!wfUtils::isValidIP($IP)) {
		return array('err' => 1, 'errorMsg' => "Please enter a valid IP address to block.");
	}
	if ($IP == wfUtils::getIP()) {
		return array('err' => 1, 'errorMsg' => "You can't block your own IP address.");
	}
	$forcedWhitelistEntry = false;
	if (\WBCR\Titan\Firewall\Model\Block::isWhitelisted($IP, $forcedWhitelistEntry)) {
		$message = "The IP address " . wp_kses($IP, array()) . " is whitelisted and can't be blocked. You can remove this IP from the whitelist on the Wordfence options page.";
		if ($forcedWhitelistEntry) {
			$message = "The IP address " . wp_kses($IP, array()) . " is in a range of IP addresses that Wordfence does not block. The IP range may be internal or belong to a service safe to allow access for.";
		}
		return array('err' => 1, 'errorMsg' => $message);
	}
	if (wfConfig::get('neverBlockBG') != 'treatAsOtherCrawlers') { //Either neverBlockVerified or neverBlockUA is selected which means the user doesn't want to block google
		if (wfCrawl::isVerifiedGoogleCrawler($IP)) {
			return array('err' => 1, 'errorMsg' => "The IP address you're trying to block belongs to Google. Your options are currently set to not block these crawlers. Change this in Wordfence options if you want to manually block Google.");
		}
	}
	\WBCR\Titan\Firewall\Model\Block::createIP($_POST['reason'], $IP, $perm);
	wfActivityReport::logBlockedIP($IP, null, 'manual');
	return array('ok' => 1);*/
}

add_action('wp_ajax_wtitan-block-ips', 'wtitan_block_ips');
