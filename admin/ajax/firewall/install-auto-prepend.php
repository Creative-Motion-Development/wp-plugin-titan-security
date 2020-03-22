<?php
function wtitan_install_auto_prepend_file()
{
	global $wp_filesystem;

	if( !current_user_can('manage_options') ) {
		wp_send_json(array('error' => __('You don\'t have enough capability to edit this information.', 'titan-security')));
	}

	$currentAutoPrependFile = ini_get('auto_prepend_file');

	$currentAutoPrepend = null;
	if( isset($_POST['currentAutoPrepend']) ) {
		$currentAutoPrepend = $_POST['currentAutoPrepend'];
	}

	$server_configuration = null;
	if( isset($_POST['server_configuration']) && \WBCR\Titan\Server\Helper::isValidServerConfig($_POST['server_configuration']) ) {
		$server_configuration = $_POST['server_configuration'];
	}

	if( $server_configuration === null ) {
		wp_send_json_error(array('error_message' => __('A valid server configuration was not provided.', 'titan')));
	}

	$helper = new \WBCR\Titan\Server\Helper($server_configuration, $currentAutoPrepend === 'override' ? null : $currentAutoPrependFile);

	ob_start();
	$ajaxURL = admin_url('admin-ajax.php');
	$allow_relaxed_file_ownership = true;
	if( false === ($credentials = request_filesystem_credentials($ajaxURL, '', false, ABSPATH, array(
			'version',
			'locale',
			'action',
			'server_configuration',
			'currentAutoPrepend'
		), $allow_relaxed_file_ownership)) ) {
		$credentialsContent = ob_get_clean();
		/*$html = wfView::create('waf/waf-modal-wrapper', array(
			'title' => __('Filesystem Credentials Required', 'titan'),
			'html' => $credentialsContent,
			'helpHTML' => sprintf(__('If you cannot complete the setup process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'titan'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_INSTALL_MANUALLY)),
			'footerHTML' => __('Once you have entered credentials, click Continue to complete the setup.', 'titan'),
		))->render();*/

		$error_message = __('Filesystem Credentials Required', 'titan');
		$error_message .= $credentialsContent;
		$error_message .= __('Once you have entered credentials, click Continue to complete the setup.', 'titan');

		wp_send_json_error(array('error_message' => $error_message, 'error_code' => 'needs_credentials'));
	}
	ob_end_clean();

	if( !WP_Filesystem($credentials, ABSPATH, $allow_relaxed_file_ownership) && $wp_filesystem->errors->get_error_code() ) {
		$credentialsError = '';
		foreach($wp_filesystem->errors->get_error_messages() as $message) {
			if( is_wp_error($message) ) {
				if( $message->get_error_data() && is_string($message->get_error_data()) ) {
					$message = $message->get_error_message() . ': ' . $message->get_error_data();
				} else {
					$message = $message->get_error_message();
				}
			}
			$credentialsError .= "<p>$message</p>\n";
		}

		/*$html = wfView::create('waf/waf-modal-wrapper', array(
			'title' => __('Filesystem Permission Error', 'titan'),
			'html' => $credentialsError,
			'helpHTML' => sprintf(__('If you cannot complete the setup process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'titan'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_INSTALL_MANUALLY)),
			'footerButtonTitle' => __('Cancel', 'titan'),
		))->render();*/

		$error_message = __('Filesystem Permission Error', 'titan');
		$error_message .= $credentialsError;

		wp_send_json_error(array('error_message' => $error_message, 'error_code' => 'credentials_failed'));
	}

	try {
		$helper->performInstallation($wp_filesystem);

		/*$nonce = bin2hex(wfWAFUtils::random_bytes(32));
		wfConfig::set('wafStatusCallbackNonce', $nonce);
		$verifyURL = add_query_arg(array('action' => 'wordfence_wafStatus', 'nonce' => $nonce), $ajaxURL);
		$response = wp_remote_get($verifyURL, array(
			'headers' => array(
				'Referer' => false
				/*, 'Cookie' => 'XDEBUG_SESSION=1'*/ /*)
		));

		$active = false;
		if( !is_wp_error($response) ) {
			$wafStatus = @json_decode(wp_remote_retrieve_body($response), true);
			if( isset($wafStatus['active']) && isset($wafStatus['subdirectory']) ) {
				$active = $wafStatus['active'] && !$wafStatus['subdirectory'];
			}
		}*/

		/*if( $server_configuration == 'manual' ) {
			$html = wfView::create('waf/waf-modal-wrapper', array(
				'title' => __('Manual Installation Instructions', 'titan'),
				'html' => wfView::create('waf/waf-install-manual')->render(),
				'footerButtonTitle' => __('Close', 'titan'),
			))->render();
		} else {
			$html = wfView::create('waf/waf-modal-wrapper', array(
				'title' => __('Installation Successful', 'titan'),
				'html' => wfView::create('waf/waf-install-success', array('active' => $active))->render(),
				'footerButtonTitle' => __('Close', 'titan'),
			))->render();
		}*/

		$response = [
			'html' => \WBCR\Titan\Plugin::app()->view()->get_template('firewall/install-auto-prepend-success', ['activate' => true]),
		];
		wp_send_json($response);
		//return array('ok' => 1);
	} catch( \WBCR\Titan\Server\HelperException $e ) {
		$error_message = "<p>" . $e->getMessage() . "</p>";
		/*$html = wfView::create('waf/waf-modal-wrapper', array(
			'title' => __('Installation Failed', 'titan'),
			'html' => $installError,
			'helpHTML' => sprintf(__('If you cannot complete the setup process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'titan'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_INSTALL_MANUALLY)),
			'footerButtonTitle' => __('Cancel', 'titan'),
		))->render();*/

		wp_send_json_error(array('error_message' => $error_message, 'error_code' => 'installation_failed'));
	}
}

add_action('wp_ajax_wtitan-install-auto-prepend', 'wtitan_install_auto_prepend_file');

function wtitan_uninstall_auto_prepend_file()
{
	global $wp_filesystem;

	if( !current_user_can('manage_options') ) {
		wp_send_json(array('error' => __('You don\'t have enough capability to edit this information.', 'titan-security')));
	}

	$server_configuration = null;
	if( isset($_POST['server_configuration']) && \WBCR\Titan\Server\Helper::isValidServerConfig($_POST['server_configuration']) ) {
		$server_configuration = $_POST['server_configuration'];
	}

	if( $server_configuration === null ) {
		wp_send_json_error(array('error_message' => __('A valid server configuration was not provided.', 'titan')));
	}

	$helper = new \WBCR\Titan\Server\Helper($server_configuration, null);

	if( isset($_POST['credentials']) && isset($_POST['credentials_signature']) ) {
		$salt = wp_salt('logged_in');
		$expected_signature = hash_hmac('sha256', $_POST['credentials'], $salt);
		if( hash_equals($expected_signature, $_POST['credentials_signature']) ) {
			$decrypted = \WBCR\Titan\Firewall\Utils::decrypt($_POST['credentials']);
			$credentials = @json_decode($decrypted, true);
		}
	}

	$ajaxURL = admin_url('admin-ajax.php');
	if( !isset($credentials) ) {
		$allow_relaxed_file_ownership = true;
		ob_start();
		if( false === ($credentials = request_filesystem_credentials($ajaxURL, '', false, ABSPATH, array(
				'version',
				'locale',
				'action',
				'server_configuration',
				'ini_modified'
			), $allow_relaxed_file_ownership)) ) {
			$credentialsContent = ob_get_clean();
			/*$html = wfView::create('waf/waf-modal-wrapper', array(
				'title' => __('Filesystem Credentials Required', 'titan'),
				'html' => $credentialsContent,
				'helpHTML' => sprintf(__('If you cannot complete the uninstall process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'titan'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)),
				'footerHTML' => __('Once you have entered credentials, click Continue to complete uninstallation.', 'titan'),
			))->render();*/

			$error_message = __('Filesystem Credentials Required', 'titan');
			$error_message .= $credentialsContent;
			$error_message .= __('Once you have entered credentials, click Continue to complete the setup.', 'titan');

			wp_send_json_error(array('error_message' => $error_message, 'error_code' => 'needs_credentials'));
		}
		ob_end_clean();
	}

	if( !WP_Filesystem($credentials, ABSPATH, $allow_relaxed_file_ownership) && $wp_filesystem->errors->get_error_code() ) {
		$credentialsError = '';
		foreach($wp_filesystem->errors->get_error_messages() as $message) {
			if( is_wp_error($message) ) {
				if( $message->get_error_data() && is_string($message->get_error_data()) ) {
					$message = $message->get_error_message() . ': ' . $message->get_error_data();
				} else {
					$message = $message->get_error_message();
				}
			}
			$credentialsError .= "<p>$message</p>\n";
		}

		/*$html = wfView::create('waf/waf-modal-wrapper', array(
			'title' => __('Filesystem Permission Error', 'titan'),
			'html' => $credentialsError,
			'helpHTML' => sprintf(__('If you cannot complete the uninstall process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'titan'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)),
			'footerButtonTitle' => __('Cancel', 'titan'),
		))->render();*/

		$error_message = __('Filesystem Permission Error', 'titan');
		$error_message .= $credentialsError;

		wp_send_json_error(array('error_message' => $error_message, 'error_code' => 'credentials_failed'));
	}

	try {
		if( (!isset($_POST['ini_modified']) || (isset($_POST['ini_modified']) && !$_POST['ini_modified'])) ) { //Uses .user.ini but not yet modified
			$hasPreviousAutoPrepend = $helper->performIniRemoval($wp_filesystem);

			$iniTTL = intval(ini_get('user_ini.cache_ttl'));
			if( $iniTTL == 0 ) {
				$iniTTL = 300; //The PHP default
			}
			if( !$helper->usesUserIni() ) {
				$iniTTL = 0; //.htaccess
			}
			$timeout = max(30, $iniTTL);
			$timeoutString = \WBCR\Titan\Firewall\Utils::makeDuration($timeout);

			$waitingResponse = '<p>' . __('The <code>auto_prepend_file</code> setting has been successfully removed from <code>.htaccess</code> and <code>.user.ini</code>. Once this change takes effect, Extended Protection Mode will be disabled.', 'titan') . '</p>';
			if( $hasPreviousAutoPrepend ) {
				$waitingResponse .= '<p>' . __('Any previous value for <code>auto_prepend_file</code> will need to be re-enabled manually if still needed.', 'titan') . '</p>';
			}

			/*$spinner = wfView::create('common/indeterminate-progress', array('size' => 32))->render();
			$waitingResponse .= '<ul class="wf-flex-horizontal"><li>' . $spinner . '</li><li class="wf-padding-add-left">' . sprintf(__('Waiting for it to take effect. This may take up to %s.', 'titan'), $timeoutString) . '</li></ul>';

			$html = wfView::create('waf/waf-modal-wrapper', array(
				'title' => __('Waiting for Changes', 'titan'),
				'html' => $waitingResponse,
				'helpHTML' => sprintf(__('If you cannot complete the uninstall process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'titan'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)),
				'footerButtonTitle' => __('Close', 'titan'),
				'noX' => true,
			))->render();*/

			$response = array(
				'uninstallation_waiting' => 1,
				'html' => \WBCR\Titan\Plugin::app()->view()->get_template('firewall/uninstall-auto-prepend-processing', [
					'uninstallation_waiting' => 1,
					'timeout' => $timeoutString
				]),
				'timeout' => $timeout,
				'server_configuration' => $_POST['server_configuration']
			);

			if( isset($credentials) && is_array($credentials) ) {
				$salt = wp_salt('logged_in');
				$json = json_encode($credentials);
				$encrypted = \WBCR\Titan\Firewall\Utils::encrypt($json);
				$signature = hash_hmac('sha256', $encrypted, $salt);
				$response['credentials'] = $encrypted;
				$response['credentials_signature'] = $signature;
			}

			wp_send_json($response);
		} else { //.user.ini and .htaccess modified if applicable and waiting period elapsed or otherwise ready to advance to next step
			if( WFWAF_AUTO_PREPEND && !WFWAF_SUBDIRECTORY_INSTALL && !WF_IS_WP_ENGINE ) { //.user.ini modified, but the WAF is still enabled
				$retryAttempted = (isset($_POST['retryAttempted']) && $_POST['retryAttempted']);
				$userIniError = '<p class="wf-error">';
				$userIniError .= __('Extended Protection Mode has not been disabled. This may be because <code>auto_prepend_file</code> is configured somewhere else or the value is still cached by PHP.', 'titan');
				if( $retryAttempted ) {
					$userIniError .= ' <strong>' . __('Retrying Failed.', 'titan') . '</strong>';
				}
				$userIniError .= ' <a href="#" class="wf-waf-uninstall-try-again">' . __('Try Again', 'titan') . '</a>';
				$userIniError .= '</p>';
				/*$html = wfView::create('waf/waf-modal-wrapper', array(
					'title' => __('Unable to Uninstall', 'titan'),
					'html' => $userIniError,
					'helpHTML' => sprintf(__('If you cannot complete the uninstall process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'titan'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)),
					'footerButtonTitle' => __('Cancel', 'titan'),
				))->render();*/

				$response = array(
					'uninstallation_failed' => 1,
					'html' => 'Unable to Uninstall',
					'server_configuration' => $_POST['server_configuration']
				);
				if( isset($credentials) && is_array($credentials) ) {
					$salt = wp_salt('logged_in');
					$json = json_encode($credentials);
					$encrypted = \WBCR\Titan\Firewall\Utils::encrypt($json);
					$signature = hash_hmac('sha256', $encrypted, $salt);
					$response['credentials'] = $encrypted;
					$response['credentials_signature'] = $signature;
				}

				return $response;
			}

			$helper->performAutoPrependFileRemoval($wp_filesystem);

			/*$nonce = bin2hex(wfWAFUtils::random_bytes(32));
			wfConfig::set('wafStatusCallbackNonce', $nonce);
			$verifyURL = add_query_arg(array('action' => 'wordfence_wafStatus', 'nonce' => $nonce), $ajaxURL);
			$response = wp_remote_get($verifyURL, array(
				'headers' => array(
					'Referer' => false
					/*, 'Cookie' => 'XDEBUG_SESSION=1'*/ /*)
			));

			$active = true;
			$subdirectory = WFWAF_SUBDIRECTORY_INSTALL;
			if( !is_wp_error($response) ) {
				$wafStatus = @json_decode(wp_remote_retrieve_body($response), true);
				if( isset($wafStatus['active']) && isset($wafStatus['subdirectory']) ) {
					$active = $wafStatus['active'] && !$wafStatus['subdirectory'];
					$subdirectory = $wafStatus['subdirectory'];
				}
			}

			$html = wfView::create('waf/waf-modal-wrapper', array(
				'title' => __('Uninstallation Complete', 'titan'),
				'html' => wfView::create('waf/waf-uninstall-success', array(
					'active' => $active,
					'subdirectory' => $subdirectory
				))->render(),
				'footerButtonTitle' => __('Close', 'titan'),
			))->render();*/

			//return array('ok' => 1, 'html' => $html);
			$response = array(
				'uninstallation_success' => 1,
				'html' => \WBCR\Titan\Plugin::app()->view()->get_template('firewall/uninstall-auto-prepend-processing', [
					'activate' => 1
				])
			);
			wp_send_json($response);
		}
	} catch( \WBCR\Titan\Server\HelperException $e ) {
		/*$installError = "<p>" . $e->getMessage() . "</p>";
		$html = wfView::create('waf/waf-modal-wrapper', array(
			'title' => __('Uninstallation Failed', 'titan'),
			'html' => $installError,
			'helpHTML' => sprintf(__('If you cannot complete the uninstall process, <a target="_blank" rel="noopener noreferrer" href="%s">click here for help</a>', 'titan'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)),
			'footerButtonTitle' => __('Cancel', 'titan'),
		))->render();

		return array('uninstallationFailed' => 1, 'html' => $html);*/
		wp_send_json_error(array('error_message' => 'Unstall failed', 'error_code' => 'credentials_failed'));
	}
}

add_action('wp_ajax_wtitan-uninstall-auto-prepend', 'wtitan_uninstall_auto_prepend_file');