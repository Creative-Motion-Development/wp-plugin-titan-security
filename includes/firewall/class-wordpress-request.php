<?php

class wfWAFWordPressRequest extends wfWAFRequest {

	/**
	 * @param wfWAFRequest|null $request
	 * @return wfWAFRequest
	 */
	public static function createFromGlobals($request = null)
	{
		if( version_compare(phpversion(), '5.3.0') >= 0 ) {
			$class = get_called_class();
			$request = new $class();
		} else {
			$request = new self();
		}

		return parent::createFromGlobals($request);
	}

	public function getIP()
	{
		static $theIP = null;
		if( isset($theIP) ) {
			return $theIP;
		}
		$ips = array();
		$howGet = wfWAF::getInstance()->getStorageEngine()->getConfig('howGetIPs', null, 'synced');
		if( $howGet ) {
			if( is_string($howGet) && is_array($_SERVER) && array_key_exists($howGet, $_SERVER) ) {
				$ips[] = array($_SERVER[$howGet], $howGet);
			}

			if( $howGet != 'REMOTE_ADDR' ) {
				$ips[] = array(
					(is_array($_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER)) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
					'REMOTE_ADDR'
				);
			}
		} else {
			$recommendedField = wfWAF::getInstance()->getStorageEngine()->getConfig('detectProxyRecommendation', null, 'synced');
			if( !empty($recommendedField) && $recommendedField != 'UNKNOWN' && $recommendedField != 'DEFERRED' ) {
				if( isset($_SERVER[$recommendedField]) ) {
					$ips[] = array($_SERVER[$recommendedField], $recommendedField);
				}
			}

			$ips[] = array(
				(is_array($_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER)) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
				'REMOTE_ADDR'
			);
			if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
				$ips[] = array($_SERVER['HTTP_X_FORWARDED_FOR'], 'HTTP_X_FORWARDED_FOR');
			}
			if( isset($_SERVER['HTTP_X_REAL_IP']) ) {
				$ips[] = array($_SERVER['HTTP_X_REAL_IP'], 'HTTP_X_REAL_IP');
			}
		}

		$cleanedIP = $this->_getCleanIPAndServerVar($ips);
		if( is_array($cleanedIP) ) {
			list($ip, $variable) = $cleanedIP;
			$theIP = $ip;

			return $ip;
		}
		$theIP = $cleanedIP;

		return $cleanedIP;
	}

	/**
	 * Expects an array of items. The items are either IPs or IPs separated by comma, space or tab. Or an array of IP's.
	 * We then examine all IP's looking for a public IP and storing private IP's in an array. If we find no public IPs we return the first private addr we found.
	 *
	 * @param array $arr
	 * @return bool|mixed
	 */
	private function _getCleanIPAndServerVar($arr)
	{
		$privates = array(); //Store private addrs until end as last resort.
		foreach($arr as $entry) {
			list($item, $var) = $entry;
			if( is_array($item) ) {
				foreach($item as $j) {
					// try verifying the IP is valid before stripping the port off
					if( !$this->_isValidIP($j) ) {
						$j = preg_replace('/:\d+$/', '', $j); //Strip off port
					}
					if( $this->_isValidIP($j) ) {
						if( $this->_isIPv6MappedIPv4($j) ) {
							$j = wfWAFUtils::inet_ntop(wfWAFUtils::inet_pton($j));
						}

						if( $this->_isPrivateIP($j) ) {
							$privates[] = array($j, $var);
						} else {
							return array($j, $var);
						}
					}
				}
				continue; //This was an array so we can skip to the next item
			}
			$skipToNext = false;
			$trustedProxies = explode("\n", wfWAF::getInstance()->getStorageEngine()->getConfig('howGetIPs_trusted_proxies', '', 'synced'));
			foreach(array(',', ' ', "\t") as $char) {
				if( strpos($item, $char) !== false ) {
					$sp = explode($char, $item);
					$sp = array_reverse($sp);
					foreach($sp as $index => $j) {
						$j = trim($j);
						if( !$this->_isValidIP($j) ) {
							$j = preg_replace('/:\d+$/', '', $j); //Strip off port
						}
						if( $this->_isValidIP($j) ) {
							if( $this->_isIPv6MappedIPv4($j) ) {
								$j = wfWAFUtils::inet_ntop(wfWAFUtils::inet_pton($j));
							}

							foreach($trustedProxies as $proxy) {
								if( !empty($proxy) ) {
									if( wfWAFUtils::subnetContainsIP($proxy, $j) && $index < count($sp) - 1 ) {
										continue 2;
									}
								}
							}

							if( $this->_isPrivateIP($j) ) {
								$privates[] = array($j, $var);
							} else {
								return array($j, $var);
							}
						}
					}
					$skipToNext = true;
					break;
				}
			}
			if( $skipToNext ) {
				continue;
			} //Skip to next item because this one had a comma, space or tab so was delimited and we didn't find anything.

			if( !$this->_isValidIP($item) ) {
				$item = preg_replace('/:\d+$/', '', $item); //Strip off port
			}
			if( $this->_isValidIP($item) ) {
				if( $this->_isIPv6MappedIPv4($item) ) {
					$item = wfWAFUtils::inet_ntop(wfWAFUtils::inet_pton($item));
				}

				if( $this->_isPrivateIP($item) ) {
					$privates[] = array($item, $var);
				} else {
					return array($item, $var);
				}
			}
		}
		if( sizeof($privates) > 0 ) {
			return $privates[0]; //Return the first private we found so that we respect the order the IP's were passed to this function.
		}

		return false;
	}

	/**
	 * @param string $ip
	 * @return bool
	 */
	private function _isValidIP($ip)
	{
		return filter_var($ip, FILTER_VALIDATE_IP) !== false;
	}

	/**
	 * @param string $ip
	 * @return bool
	 */
	private function _isIPv6MappedIPv4($ip)
	{
		return preg_match('/^(?:\:(?:\:0{1,4}){0,4}\:|(?:0{1,4}\:){5})ffff\:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/i', $ip) > 0;
	}

	/**
	 * @param string $addr Should be in dot or colon notation (127.0.0.1 or ::1)
	 * @return bool
	 */
	private function _isPrivateIP($ip)
	{
		// Run this through the preset list for IPv4 addresses.
		if( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false ) {

			include(dirname(__FILE__) . '/ip-white-list.php'); // defines $wfIPWhitelist
			$private = $wfIPWhitelist['private'];

			foreach($private as $a) {
				if( wfWAFUtils::subnetContainsIP($a, $ip) ) {
					return true;
				}
			}
		}

		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
	}
}
