<?php

class wfWAFWordPressObserver extends wfWAFBaseObserver {

	public function beforeRunRules()
	{
		// Whitelisted URLs (in WAF config)
		$whitelistedURLs = wfWAF::getInstance()->getStorageEngine()->getConfig('whitelistedURLs', null, 'livewaf');
		if( $whitelistedURLs ) {
			$whitelistPattern = "";
			foreach($whitelistedURLs as $whitelistedURL) {
				$whitelistPattern .= preg_replace('/\\\\\*/', '.*?', preg_quote($whitelistedURL, '/')) . '|';
			}
			$whitelistPattern = '/^(?:' . wfWAFUtils::substr($whitelistPattern, 0, -1) . ')$/i';

			wfWAFRule::create(wfWAF::getInstance(), 0x8000000, 'rule', 'whitelist', 0, 'User Supplied Whitelisted URL', 'allow', new wfWAFRuleComparisonGroup(new wfWAFRuleComparison(wfWAF::getInstance(), 'match', $whitelistPattern, array(
				'request.uri',
			))))->evaluate();
		}

		// Whitelisted IPs (Titan config)
		$whitelistedIPs = wfWAF::getInstance()->getStorageEngine()->getConfig('whitelistedIPs', null, 'synced');
		if( $whitelistedIPs ) {
			if( !is_array($whitelistedIPs) ) {
				$whitelistedIPs = explode(',', $whitelistedIPs);
			}
			foreach($whitelistedIPs as $whitelistedIP) {
				$ipRange = new \WBCR\Titan\Firewall\User_IP_Range($whitelistedIP);
				if( $ipRange->isIPInRange(wfWAF::getInstance()->getRequest()->getIP()) ) {
					throw new wfWAFAllowException('Titan whitelisted IP.');
				}
			}
		}

		// Check plugin blocking
		if( $result = wfWAF::getInstance()->willPerformFinalAction(wfWAF::getInstance()->getRequest()) ) {
			if( $result === true ) {
				$result = 'Not available';
			} // Should not happen but can if the reason in the blocks table is empty
			wfWAF::getInstance()->getRequest()->setMetadata(array_merge(wfWAF::getInstance()->getRequest()->getMetadata(), array('finalAction' => $result)));
		}
	}

	public function afterRunRules()
	{
		//Blacklist
		if( !wfWAF::getInstance()->getStorageEngine()->getConfig('disableWAFBlacklistBlocking') ) {
			$blockedPrefixes = wfWAF::getInstance()->getStorageEngine()->getConfig('blockedPrefixes', null, 'transient');
			if( $blockedPrefixes && wfWAF::getInstance()->getStorageEngine()->getConfig('isPaid', null, 'synced') ) {
				$blockedPrefixes = base64_decode($blockedPrefixes);
				if( $this->_prefixListContainsIP($blockedPrefixes, wfWAF::getInstance()->getRequest()->getIP()) !== false ) {
					$allowedCacheJSON = wfWAF::getInstance()->getStorageEngine()->getConfig('blacklistAllowedCache', '', 'transient');
					$allowedCache = @json_decode($allowedCacheJSON, true);
					if( !is_array($allowedCache) ) {
						$allowedCache = array();
					}

					$cacheTest = base64_encode(wfWAFUtils::inet_pton(wfWAF::getInstance()->getRequest()->getIP()));
					if( !in_array($cacheTest, $allowedCache) ) {
						$guessSiteURL = sprintf('%s://%s/', wfWAF::getInstance()->getRequest()->getProtocol(), wfWAF::getInstance()->getRequest()->getHost());
						try {
							$request = new wfWAFHTTP();
							$response = wfWAFHTTP::get(WFWAF_API_URL_SEC . "?" . http_build_query(array(
									'action' => 'is_ip_blacklisted',
									'ip' => wfWAF::getInstance()->getRequest()->getIP(),
									'k' => wfWAF::getInstance()->getStorageEngine()->getConfig('apiKey', null, 'synced'),
									's' => wfWAF::getInstance()->getStorageEngine()->getConfig('siteURL', null, 'synced') ? wfWAF::getInstance()->getStorageEngine()->getConfig('siteURL', null, 'synced') : $guessSiteURL,
									'h' => wfWAF::getInstance()->getStorageEngine()->getConfig('homeURL', null, 'synced') ? wfWAF::getInstance()->getStorageEngine()->getConfig('homeURL', null, 'synced') : $guessSiteURL,
									't' => microtime(true),
								), null, '&'), $request);

							if( $response instanceof wfWAFHTTPResponse && $response->getBody() ) {
								$jsonData = wfWAFUtils::json_decode($response->getBody(), true);
								if( array_key_exists('data', $jsonData) ) {
									if( preg_match('/^block:(\d+)$/i', $jsonData['data'], $matches) ) {
										wfWAF::getInstance()->getStorageEngine()->blockIP((int)$matches[1] + time(), wfWAF::getInstance()->getRequest()->getIP(), wfWAFStorageInterface::IP_BLOCKS_BLACKLIST);
										$e = new wfWAFBlockException();
										$e->setFailedRules(array('blocked'));
										$e->setRequest(wfWAF::getInstance()->getRequest());
										throw $e;
									} else { //Allowed, cache until the next prefix list refresh
										$allowedCache[] = $cacheTest;
										wfWAF::getInstance()->getStorageEngine()->setConfig('blacklistAllowedCache', json_encode($allowedCache), 'transient');
									}
								}
							}
						} catch( wfWAFHTTPTransportException $e ) {
							error_log($e->getMessage());
						}
					}
				}
			}
		}

		//wfWAFLogException
		$watchedIPs = wfWAF::getInstance()->getStorageEngine()->getConfig('watchedIPs', null, 'transient');
		if( $watchedIPs ) {
			if( !is_array($watchedIPs) ) {
				$watchedIPs = explode(',', $watchedIPs);
			}
			foreach($watchedIPs as $watchedIP) {
				$ipRange = new \WBCR\Titan\Firewall\User_IP_Range($watchedIP);
				if( $ipRange->isIPInRange(wfWAF::getInstance()->getRequest()->getIP()) ) {
					throw new wfWAFLogException('Titan watched IP.');
				}
			}
		}

		if( $reason = wfWAF::getInstance()->getRequest()->getMetadata('finalAction') ) {
			$e = new wfWAFBlockException($reason['action']);
			$e->setRequest(wfWAF::getInstance()->getRequest());
			throw $e;
		}
	}

	private function _prefixListContainsIP($prefixList, $ip)
	{
		$size = ord(wfWAFUtils::substr($prefixList, 0, 1));

		$sha256 = hash('sha256', wfWAFUtils::inet_pton($ip), true);
		$p = wfWAFUtils::substr($sha256, 0, $size);

		$count = ceil((wfWAFUtils::strlen($prefixList) - 1) / $size);
		$low = 0;
		$high = $count - 1;

		while( $low <= $high ) {
			$mid = (int)(($high + $low) / 2);
			$val = wfWAFUtils::substr($prefixList, 1 + $mid * $size, $size);
			$cmp = strcmp($val, $p);
			if( $cmp < 0 ) {
				$low = $mid + 1;
			} else if( $cmp > 0 ) {
				$high = $mid - 1;
			} else {
				return $mid;
			}
		}

		return false;
	}
}