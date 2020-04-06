<?php

namespace WBCR\Titan\Firewall;

use WBCR\Titan\Plugin as Plugin;

class Utils {

	public static function doNotCache()
	{
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, must-revalidate, private");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); //In the past
		if( !defined('DONOTCACHEPAGE') ) {
			define('DONOTCACHEPAGE', true);
		}
		if( !defined('DONOTCACHEDB') ) {
			define('DONOTCACHEDB', true);
		}
		if( !defined('DONOTCDN') ) {
			define('DONOTCDN', true);
		}
		if( !defined('DONOTCACHEOBJECT') ) {
			define('DONOTCACHEOBJECT', true);
		}

		if( !defined('WFDONOTCACHE') ) {
			define('WFDONOTCACHE', true);
		}
	}

	public static function makeDuration($secs, $createExact = false)
	{
		$components = array();

		$months = floor($secs / (86400 * 30));
		$secs -= $months * 86400 * 30;
		$days = floor($secs / 86400);
		$secs -= $days * 86400;
		$hours = floor($secs / 3600);
		$secs -= $hours * 3600;
		$minutes = floor($secs / 60);
		$secs -= $minutes * 60;

		if( $months ) {
			$components[] = self::pluralize($months, 'month');
			if( !$createExact ) {
				$hours = $minutes = $secs = 0;
			}
		}
		if( $days ) {
			$components[] = self::pluralize($days, 'day');
			if( !$createExact ) {
				$minutes = $secs = 0;
			}
		}
		if( $hours ) {
			$components[] = self::pluralize($hours, 'hour');
			if( !$createExact ) {
				$secs = 0;
			}
		}
		if( $minutes ) {
			$components[] = self::pluralize($minutes, 'minute');
		}
		if( $secs && $secs >= 1 ) {
			$components[] = self::pluralize($secs, 'second');
		}

		if( empty($components) ) {
			$components[] = 'less than 1 second';
		}

		return implode(' ', $components);
	}

	public static function pluralize($m1, $t1, $m2 = false, $t2 = false)
	{
		if( $m1 != 1 ) {
			$t1 = $t1 . 's';
		}
		if( $m2 != 1 ) {
			$t2 = $t2 . 's';
		}
		if( $m1 && $m2 ) {
			return "$m1 $t1 $m2 $t2";
		} else {
			return "$m1 $t1";
		}
	}

	public static function formatBytes($bytes, $precision = 2)
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		// Uncomment one of the following alternatives
		$bytes /= pow(1024, $pow);

		// $bytes /= (1 << (10 * $pow));

		return round($bytes, $precision) . ' ' . $units[$pow];
	}

	public static function bigRandomHex()
	{
		return bin2hex(self::random_bytes(16));
	}

	public static function encrypt($str)
	{
		$key = Plugin::app()->getPopulateOption('enckey');

		if( !$key ) {
			//wordfence::status(1, 'error', "Titan error: No encryption key found!");

			return false;
		}
		global $wpdb;

		return $wpdb->get_var("select HEX(AES_ENCRYPT('%s', '%s')) as val", $str, $key);
	}

	public static function decrypt($str)
	{
		$key = Plugin::app()->getPopulateOption('enckey');
		if( !$key ) {
			//wordfence::status(1, 'error', "Titan error: No encryption key found!");

			return false;
		}
		global $wpdb;

		return $wpdb->get_var("select AES_DECRYPT(UNHEX('%s'), '%s') as val", $str, $key);
	}

	/**
	 * Polyfill for random_bytes.
	 *
	 * @param int $bytes
	 * @return string
	 */
	public static function random_bytes($bytes)
	{
		$bytes = (int)$bytes;
		if( function_exists('random_bytes') ) {
			try {
				$rand = random_bytes($bytes);
				if( is_string($rand) && self::strlen($rand) === $bytes ) {
					return $rand;
				}
			} catch( \Exception $e ) {
				// Fall through
			} catch( \TypeError $e ) {
				// Fall through
			} catch( \Error $e ) {
				// Fall through
			}
		}
		if( function_exists('mcrypt_create_iv') ) {
			// phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.mcrypt_create_ivDeprecatedRemoved,PHPCompatibility.Extensions.RemovedExtensions.mcryptDeprecatedRemoved,PHPCompatibility.Constants.RemovedConstants.mcrypt_dev_urandomDeprecatedRemoved
			$rand = @mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM);
			if( is_string($rand) && self::strlen($rand) === $bytes ) {
				return $rand;
			}
		}
		if( function_exists('openssl_random_pseudo_bytes') ) {
			$rand = @openssl_random_pseudo_bytes($bytes, $strong);
			if( is_string($rand) && self::strlen($rand) === $bytes ) {
				return $rand;
			}
		}
		// Last resort is insecure
		$return = '';
		for($i = 0; $i < $bytes; $i++) {
			$return .= chr(mt_rand(0, 255));
		}

		return $return;
	}

	/**
	 * Polyfill for random_int.
	 *
	 * @param int $min
	 * @param int $max
	 * @return int
	 */
	public static function random_int($min = 0, $max = 0x7FFFFFFF)
	{
		if( function_exists('random_int') ) {
			try {
				return random_int($min, $max);
			} catch( \Exception $e ) {
				// Fall through
			} catch( \TypeError $e ) {
				// Fall through
			} catch( \Error $e ) {
				// Fall through
			}
		}
		$diff = $max - $min;
		$bytes = self::random_bytes(4);
		if( $bytes === false || self::strlen($bytes) != 4 ) {
			throw new \RuntimeException("Unable to get 4 bytes");
		}
		$val = @unpack("Nint", $bytes);
		$val = $val['int'] & 0x7FFFFFFF;
		$fp = (float)$val / 2147483647.0; // convert to [0,1]

		return (int)(round($fp * $diff) + $min);
	}

	public static function hex2bin($string)
	{ //Polyfill for PHP < 5.4
		if( !is_string($string) ) {
			return false;
		}
		if( strlen($string) % 2 == 1 ) {
			return false;
		}

		return pack('H*', $string);
	}

	/**
	 * Identical to the same functions in wfWAFUtils.
	 *
	 * Set the mbstring internal encoding to a binary safe encoding when func_overload
	 * is enabled.
	 *
	 * When mbstring.func_overload is in use for multi-byte encodings, the results from
	 * strlen() and similar functions respect the utf8 characters, causing binary data
	 * to return incorrect lengths.
	 *
	 * This function overrides the mbstring encoding to a binary-safe encoding, and
	 * resets it to the users expected encoding afterwards through the
	 * `reset_mbstring_encoding` function.
	 *
	 * It is safe to recursively call this function, however each
	 * `mbstring_binary_safe_encoding()` call must be followed up with an equal number
	 * of `reset_mbstring_encoding()` calls.
	 *
	 * @param bool $reset Optional. Whether to reset the encoding back to a previously-set encoding.
	 *                    Default false.
	 * @see wfWAFUtils::reset_mbstring_encoding
	 *
	 * @staticvar array $encodings
	 * @staticvar bool  $overloaded
	 *
	 */
	public static function mbstring_binary_safe_encoding($reset = false)
	{
		static $encodings = array();
		static $overloaded = null;

		if( is_null($overloaded) ) {
			// phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.mbstring_func_overloadDeprecated
			$overloaded = function_exists('mb_internal_encoding') && (ini_get('mbstring.func_overload') & 2);
		}

		if( false === $overloaded ) {
			return;
		}

		if( !$reset ) {
			$encoding = mb_internal_encoding();
			array_push($encodings, $encoding);
			mb_internal_encoding('ISO-8859-1');
		}

		if( $reset && $encodings ) {
			$encoding = array_pop($encodings);
			mb_internal_encoding($encoding);
		}
	}

	/**
	 * Reset the mbstring internal encoding to a users previously set encoding.
	 *
	 * @see wfWAFUtils::mbstring_binary_safe_encoding
	 */
	public static function reset_mbstring_encoding()
	{
		self::mbstring_binary_safe_encoding(true);
	}

	/**
	 * @param callable $function
	 * @param array $args
	 * @return mixed
	 */
	protected static function callMBSafeStrFunction($function, $args)
	{
		self::mbstring_binary_safe_encoding();
		$return = call_user_func_array($function, $args);
		self::reset_mbstring_encoding();

		return $return;
	}

	/**
	 * Multibyte safe strlen.
	 *
	 * @param $binary
	 * @return int
	 */
	public static function strlen($binary)
	{
		$args = func_get_args();

		return self::callMBSafeStrFunction('strlen', $args);
	}

	/**
	 * @param $haystack
	 * @param $needle
	 * @param int $offset
	 * @return int
	 */
	public static function stripos($haystack, $needle, $offset = 0)
	{
		$args = func_get_args();

		return self::callMBSafeStrFunction('stripos', $args);
	}

	/**
	 * @param $string
	 * @return mixed
	 */
	public static function strtolower($string)
	{
		$args = func_get_args();

		return self::callMBSafeStrFunction('strtolower', $args);
	}

	/**
	 * @param $string
	 * @param $start
	 * @param $length
	 * @return mixed
	 */
	public static function substr($string, $start, $length = null)
	{
		if( $length === null ) {
			$length = self::strlen($string);
		}

		return self::callMBSafeStrFunction('substr', array(
			$string,
			$start,
			$length
		));
	}

	/**
	 * @param $haystack
	 * @param $needle
	 * @param int $offset
	 * @return mixed
	 */
	public static function strpos($haystack, $needle, $offset = 0)
	{
		$args = func_get_args();

		return self::callMBSafeStrFunction('strpos', $args);
	}

	/**
	 * @param string $haystack
	 * @param string $needle
	 * @param int $offset
	 * @param int $length
	 * @return mixed
	 */
	public static function substr_count($haystack, $needle, $offset = 0, $length = null)
	{
		if( $length === null ) {
			$length = self::strlen($haystack);
		}

		return self::callMBSafeStrFunction('substr_count', array(
			$haystack,
			$needle,
			$offset,
			$length
		));
	}

	/**
	 * @param $string
	 * @return mixed
	 */
	public static function strtoupper($string)
	{
		$args = func_get_args();

		return self::callMBSafeStrFunction('strtoupper', $args);
	}

	/**
	 * @param string $haystack
	 * @param string $needle
	 * @param int $offset
	 * @return mixed
	 */
	public static function strrpos($haystack, $needle, $offset = 0)
	{
		$args = func_get_args();

		return self::callMBSafeStrFunction('strrpos', $args);
	}

	public static function sets_equal($a1, $a2)
	{
		if( !is_array($a1) || !is_array($a2) ) {
			return false;
		}

		if( count($a1) != count($a2) ) {
			return false;
		}

		sort($a1, SORT_NUMERIC);
		sort($a2, SORT_NUMERIC);

		return $a1 == $a2;
	}

	public static function array_first($array)
	{
		if( empty($array) ) {
			return null;
		}

		$values = array_values($array);

		return $values[0];
	}

	public static function array_last($array)
	{
		if( empty($array) ) {
			return null;
		}

		$values = array_values($array);

		return $values[count($values) - 1];
	}

	public static function array_strtolower($array)
	{
		$result = array();
		foreach($array as $a) {
			$result[] = strtolower($a);
		}

		return $result;
	}

	public static function array_column($input = null, $columnKey = null, $indexKey = null)
	{ //Polyfill from https://github.com/ramsey/array_column/blob/master/src/array_column.php
		$argc = func_num_args();
		$params = func_get_args();
		if( $argc < 2 ) {
			trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);

			return null;
		}

		if( !is_array($params[0]) ) {
			trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);

			return null;
		}

		if( !is_int($params[1]) && !is_float($params[1]) && !is_string($params[1]) && $params[1] !== null && !(is_object($params[1]) && method_exists($params[1], '__toString')) ) {
			trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);

			return false;
		}

		if( isset($params[2]) && !is_int($params[2]) && !is_float($params[2]) && !is_string($params[2]) && !(is_object($params[2]) && method_exists($params[2], '__toString')) ) {
			trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);

			return false;
		}

		$paramsInput = $params[0];
		$paramsColumnKey = ($params[1] !== null) ? (string)$params[1] : null;
		$paramsIndexKey = null;
		if( isset($params[2]) ) {
			if( is_float($params[2]) || is_int($params[2]) ) {
				$paramsIndexKey = (int)$params[2];
			} else {
				$paramsIndexKey = (string)$params[2];
			}
		}

		$resultArray = array();
		foreach($paramsInput as $row) {
			$key = $value = null;
			$keySet = $valueSet = false;
			if( $paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row) ) {
				$keySet = true;
				$key = (string)$row[$paramsIndexKey];
			}

			if( $paramsColumnKey === null ) {
				$valueSet = true;
				$value = $row;
			} elseif( is_array($row) && array_key_exists($paramsColumnKey, $row) ) {
				$valueSet = true;
				$value = $row[$paramsColumnKey];
			}

			if( $valueSet ) {
				if( $keySet ) {
					$resultArray[$key] = $value;
				} else {
					$resultArray[] = $value;
				}
			}
		}

		return $resultArray;
	}

	/**
	 * Check if an IP address is in a network block
	 *
	 * @param string $subnet Single IP or subnet in CIDR notation (e.g. '192.168.100.0' or '192.168.100.0/22')
	 * @param string $ip IPv4 or IPv6 address in dot or colon notation
	 * @return boolean
	 */
	public static function subnetContainsIP($subnet, $ip)
	{
		static $_network_cache = array();
		static $_ip_cache = array();
		static $_masks = array(
			0 => "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			1 => "\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			2 => "\xc0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			3 => "\xe0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			4 => "\xf0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			5 => "\xf8\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			6 => "\xfc\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			7 => "\xfe\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			8 => "\xff\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			9 => "\xff\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			10 => "\xff\xc0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			11 => "\xff\xe0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			12 => "\xff\xf0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			13 => "\xff\xf8\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			14 => "\xff\xfc\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			15 => "\xff\xfe\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			16 => "\xff\xff\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			17 => "\xff\xff\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			18 => "\xff\xff\xc0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			19 => "\xff\xff\xe0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			20 => "\xff\xff\xf0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			21 => "\xff\xff\xf8\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			22 => "\xff\xff\xfc\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			23 => "\xff\xff\xfe\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			24 => "\xff\xff\xff\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			25 => "\xff\xff\xff\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			26 => "\xff\xff\xff\xc0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			27 => "\xff\xff\xff\xe0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			28 => "\xff\xff\xff\xf0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			29 => "\xff\xff\xff\xf8\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			30 => "\xff\xff\xff\xfc\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			31 => "\xff\xff\xff\xfe\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			32 => "\xff\xff\xff\xff\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			33 => "\xff\xff\xff\xff\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			34 => "\xff\xff\xff\xff\xc0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			35 => "\xff\xff\xff\xff\xe0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			36 => "\xff\xff\xff\xff\xf0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			37 => "\xff\xff\xff\xff\xf8\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			38 => "\xff\xff\xff\xff\xfc\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			39 => "\xff\xff\xff\xff\xfe\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			40 => "\xff\xff\xff\xff\xff\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			41 => "\xff\xff\xff\xff\xff\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			42 => "\xff\xff\xff\xff\xff\xc0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			43 => "\xff\xff\xff\xff\xff\xe0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			44 => "\xff\xff\xff\xff\xff\xf0\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			45 => "\xff\xff\xff\xff\xff\xf8\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			46 => "\xff\xff\xff\xff\xff\xfc\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			47 => "\xff\xff\xff\xff\xff\xfe\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			48 => "\xff\xff\xff\xff\xff\xff\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			49 => "\xff\xff\xff\xff\xff\xff\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			50 => "\xff\xff\xff\xff\xff\xff\xc0\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			51 => "\xff\xff\xff\xff\xff\xff\xe0\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			52 => "\xff\xff\xff\xff\xff\xff\xf0\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			53 => "\xff\xff\xff\xff\xff\xff\xf8\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			54 => "\xff\xff\xff\xff\xff\xff\xfc\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			55 => "\xff\xff\xff\xff\xff\xff\xfe\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			56 => "\xff\xff\xff\xff\xff\xff\xff\x00\x00\x00\x00\x00\x00\x00\x00\x00",
			57 => "\xff\xff\xff\xff\xff\xff\xff\x80\x00\x00\x00\x00\x00\x00\x00\x00",
			58 => "\xff\xff\xff\xff\xff\xff\xff\xc0\x00\x00\x00\x00\x00\x00\x00\x00",
			59 => "\xff\xff\xff\xff\xff\xff\xff\xe0\x00\x00\x00\x00\x00\x00\x00\x00",
			60 => "\xff\xff\xff\xff\xff\xff\xff\xf0\x00\x00\x00\x00\x00\x00\x00\x00",
			61 => "\xff\xff\xff\xff\xff\xff\xff\xf8\x00\x00\x00\x00\x00\x00\x00\x00",
			62 => "\xff\xff\xff\xff\xff\xff\xff\xfc\x00\x00\x00\x00\x00\x00\x00\x00",
			63 => "\xff\xff\xff\xff\xff\xff\xff\xfe\x00\x00\x00\x00\x00\x00\x00\x00",
			64 => "\xff\xff\xff\xff\xff\xff\xff\xff\x00\x00\x00\x00\x00\x00\x00\x00",
			65 => "\xff\xff\xff\xff\xff\xff\xff\xff\x80\x00\x00\x00\x00\x00\x00\x00",
			66 => "\xff\xff\xff\xff\xff\xff\xff\xff\xc0\x00\x00\x00\x00\x00\x00\x00",
			67 => "\xff\xff\xff\xff\xff\xff\xff\xff\xe0\x00\x00\x00\x00\x00\x00\x00",
			68 => "\xff\xff\xff\xff\xff\xff\xff\xff\xf0\x00\x00\x00\x00\x00\x00\x00",
			69 => "\xff\xff\xff\xff\xff\xff\xff\xff\xf8\x00\x00\x00\x00\x00\x00\x00",
			70 => "\xff\xff\xff\xff\xff\xff\xff\xff\xfc\x00\x00\x00\x00\x00\x00\x00",
			71 => "\xff\xff\xff\xff\xff\xff\xff\xff\xfe\x00\x00\x00\x00\x00\x00\x00",
			72 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\x00\x00\x00\x00\x00\x00\x00",
			73 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\x80\x00\x00\x00\x00\x00\x00",
			74 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xc0\x00\x00\x00\x00\x00\x00",
			75 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xe0\x00\x00\x00\x00\x00\x00",
			76 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf0\x00\x00\x00\x00\x00\x00",
			77 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf8\x00\x00\x00\x00\x00\x00",
			78 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfc\x00\x00\x00\x00\x00\x00",
			79 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfe\x00\x00\x00\x00\x00\x00",
			80 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x00\x00\x00\x00\x00\x00",
			81 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x80\x00\x00\x00\x00\x00",
			82 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xc0\x00\x00\x00\x00\x00",
			83 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xe0\x00\x00\x00\x00\x00",
			84 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf0\x00\x00\x00\x00\x00",
			85 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf8\x00\x00\x00\x00\x00",
			86 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfc\x00\x00\x00\x00\x00",
			87 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfe\x00\x00\x00\x00\x00",
			88 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x00\x00\x00\x00\x00",
			89 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x80\x00\x00\x00\x00",
			90 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xc0\x00\x00\x00\x00",
			91 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xe0\x00\x00\x00\x00",
			92 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf0\x00\x00\x00\x00",
			93 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf8\x00\x00\x00\x00",
			94 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfc\x00\x00\x00\x00",
			95 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfe\x00\x00\x00\x00",
			96 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x00\x00\x00\x00",
			97 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x80\x00\x00\x00",
			98 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xc0\x00\x00\x00",
			99 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xe0\x00\x00\x00",
			100 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf0\x00\x00\x00",
			101 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf8\x00\x00\x00",
			102 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfc\x00\x00\x00",
			103 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfe\x00\x00\x00",
			104 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x00\x00\x00",
			105 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x80\x00\x00",
			106 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xc0\x00\x00",
			107 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xe0\x00\x00",
			108 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf0\x00\x00",
			109 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf8\x00\x00",
			110 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfc\x00\x00",
			111 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfe\x00\x00",
			112 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x00\x00",
			113 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x80\x00",
			114 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xc0\x00",
			115 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xe0\x00",
			116 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf0\x00",
			117 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf8\x00",
			118 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfc\x00",
			119 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfe\x00",
			120 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x00",
			121 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x80",
			122 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xc0",
			123 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xe0",
			124 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf0",
			125 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xf8",
			126 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfc",
			127 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xfe",
			128 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff",
		);
		/*
		 * The above is generated by:
		 *
		   function gen_mask($prefix, $size = 128) {
				//Workaround to avoid overflow, split into four pieces
				$mask_1 = (pow(2, $size / 4) - 1) ^ (pow(2, min($size / 4, max(0, 1 * $size / 4 - $prefix))) - 1);
				$mask_2 = (pow(2, $size / 4) - 1) ^ (pow(2, min($size / 4, max(0, 2 * $size / 4 - $prefix))) - 1);
				$mask_3 = (pow(2, $size / 4) - 1) ^ (pow(2, min($size / 4, max(0, 3 * $size / 4 - $prefix))) - 1);
				$mask_4 = (pow(2, $size / 4) - 1) ^ (pow(2, min($size / 4, max(0, 4 * $size / 4 - $prefix))) - 1);
				return ($mask_1 ? pack('N', $mask_1) : "\0\0\0\0") . ($mask_2 ? pack('N', $mask_2) : "\0\0\0\0") . ($mask_3 ? pack('N', $mask_3) : "\0\0\0\0") . ($mask_4 ? pack('N', $mask_4) : "\0\0\0\0");
			}

			$masks = array();
			for ($i = 0; $i <= 128; $i++) {
				$mask = gen_mask($i);
				$chars = str_split($mask);
				$masks[] = implode('', array_map(function($c) { return '\\x' . bin2hex($c); }, $chars));
			}

			echo 'array(' . "\n";
			foreach ($masks as $index => $m) {
				echo "\t{$index} => \"{$m}\",\n";
			}
			echo ')';
		 *
		 */

		if( isset($_network_cache[$subnet]) ) {
			list($bin_network, $prefix, $masked_network) = $_network_cache[$subnet];
			$mask = $_masks[$prefix];
		} else {
			list($network, $prefix) = array_pad(explode('/', $subnet, 2), 2, null);
			if( filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
				// If no prefix was supplied, 32 is implied for IPv4
				if( $prefix === null ) {
					$prefix = 32;
				}

				// Validate the IPv4 network prefix
				if( $prefix < 0 || $prefix > 32 ) {
					return false;
				}

				// Increase the IPv4 network prefix to work in the IPv6 address space
				$prefix += 96;
			} else {
				// If no prefix was supplied, 128 is implied for IPv6
				if( $prefix === null ) {
					$prefix = 128;
				}

				// Validate the IPv6 network prefix
				if( $prefix < 1 || $prefix > 128 ) {
					return false;
				}
			}
			$mask = $_masks[$prefix];
			$bin_network = self::inet_pton($network);
			$masked_network = $bin_network & $mask;
			$_network_cache[$subnet] = array($bin_network, $prefix, $masked_network);
		}

		if( isset($_ip_cache[$ip]) && isset($_ip_cache[$ip][$prefix]) ) {
			list($bin_ip, $masked_ip) = $_ip_cache[$ip][$prefix];
		} else {
			$bin_ip = self::inet_pton($ip);
			$masked_ip = $bin_ip & $mask;
			if( !isset($_ip_cache[$ip]) ) {
				$_ip_cache[$ip] = array();
			}
			$_ip_cache[$ip][$prefix] = array($bin_ip, $masked_ip);
		}

		return ($masked_ip === $masked_network);
	}

	/**
	 * Convert CIDR notation to a \WBCR\Titan\Firewall\User_IP_Range object
	 *
	 * @param string $cidr
	 * @return \WBCR\Titan\Firewall\User_IP_Range
	 */
	public static function CIDR2wfUserIPRange($cidr)
	{
		list($network, $prefix) = array_pad(explode('/', $cidr, 2), 2, null);
		$ip_range = new \WBCR\Titan\Firewall\User_IP_Range();

		if( filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
			// If no prefix was supplied, 32 is implied for IPv4
			if( $prefix === null ) {
				$prefix = 32;
			}

			// Validate the IPv4 network prefix
			if( $prefix < 0 || $prefix > 32 ) {
				return $ip_range;
			}

			// Increase the IPv4 network prefix to work in the IPv6 address space
			$prefix += 96;
		} else {
			// If no prefix was supplied, 128 is implied for IPv6
			if( $prefix === null ) {
				$prefix = 128;
			}

			// Validate the IPv6 network prefix
			if( $prefix < 1 || $prefix > 128 ) {
				return $ip_range;
			}
		}

		// Convert human readable address to 128 bit (IPv6) binary string
		// Note: self::inet_pton converts IPv4 addresses to IPv6 compatible versions
		$binary_network = self::inet_pton($network);
		$binary_mask = wfHelperBin::str2bin(str_pad(str_repeat('1', $prefix), 128, '0', STR_PAD_RIGHT));

		// Calculate first and last address
		$binary_first = $binary_network & $binary_mask;
		$binary_last = $binary_network | ~$binary_mask;

		// Convert binary addresses back to human readable strings
		$first = self::inet_ntop($binary_first);
		$last = self::inet_ntop($binary_last);

		if( filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
			$first = self::expandIPv6Address($first);
			$last = self::expandIPv6Address($last);
		}

		// Split addresses into segments
		$first_array = preg_split('/[\.\:]/', $first);
		$last_array = preg_split('/[\.\:]/', $last);

		// Make sure arrays are the same size. IPv6 '::' could cause problems otherwise.
		// The strlen filter should leave zeros in place
		$first_array = array_pad(array_filter($first_array, 'strlen'), count($last_array), '0');

		$range_segments = array();

		foreach($first_array as $index => $segment) {
			if( $segment === $last_array[$index] ) {
				$range_segments[] = str_pad(ltrim($segment, '0'), 1, '0');
			} else if( $segment === '' || $last_array[$index] === '' ) {
				$range_segments[] = '';
			} else {
				$range_segments[] = "[" . str_pad(ltrim($segment, '0'), 1, '0') . "-" . str_pad(ltrim($last_array[$index], '0'), 1, '0') . "]";
			}
		}

		$delimiter = filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? '.' : ':';

		$ip_range->setIPString(implode($delimiter, $range_segments));

		return $ip_range;
	}

	/**
	 * Verify PHP was compiled with IPv6 support.
	 *
	 * Some hosts appear to not have inet_ntop, and others appear to have inet_ntop but are unable to process IPv6 addresses.
	 *
	 * @return bool
	 */
	public static function hasIPv6Support()
	{
		return defined('AF_INET6');
	}

	/**
	 * Return dot notation of IPv4 address.
	 *
	 * @param int $ip
	 * @return string|bool
	 */
	public static function inet_ntoa($ip)
	{
		$long = 4294967295 - ($ip - 1);

		return long2ip(-$long);
	}

	/**
	 * Return string representation of 32 bit int of the IP address.
	 *
	 * @param string $ip
	 * @return string
	 */
	public static function inet_aton($ip)
	{
		$ip = preg_replace('/(?<=^|\.)0+([1-9])/', '$1', $ip);

		return sprintf("%u", ip2long($ip));
	}

	/**
	 * Return dot or colon notation of IPv4 or IPv6 address.
	 *
	 * @param string $ip
	 * @return string|bool
	 */
	public static function inet_ntop($ip)
	{
		// trim this to the IPv4 equiv if it's in the mapped range
		if( strlen($ip) == 16 && substr($ip, 0, 12) == "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff" ) {
			$ip = substr($ip, 12, 4);
		}

		return self::hasIPv6Support() ? @inet_ntop($ip) : self::_inet_ntop($ip);
	}

	/**
	 * Return the packed binary string of an IPv4 or IPv6 address.
	 *
	 * @param string $ip
	 * @return string
	 */
	public static function inet_pton($ip)
	{
		// convert the 4 char IPv4 to IPv6 mapped version.
		$pton = str_pad(self::hasIPv6Support() ? @inet_pton($ip) : self::_inet_pton($ip), 16, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x00\x00\x00\x00", STR_PAD_LEFT);

		return $pton;
	}

	/**
	 * Added compatibility for hosts that do not have inet_pton.
	 *
	 * @param $ip
	 * @return bool|string
	 */
	public static function _inet_pton($ip)
	{
		// IPv4
		if( preg_match('/^(?:\d{1,3}(?:\.|$)){4}/', $ip) ) {
			$octets = explode('.', $ip);
			$bin = chr($octets[0]) . chr($octets[1]) . chr($octets[2]) . chr($octets[3]);

			return $bin;
		}

		// IPv6
		if( preg_match('/^((?:[\da-f]{1,4}(?::|)){0,8})(::)?((?:[\da-f]{1,4}(?::|)){0,8})$/i', $ip) ) {
			if( $ip === '::' ) {
				return "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
			}
			$colon_count = substr_count($ip, ':');
			$dbl_colon_pos = strpos($ip, '::');
			if( $dbl_colon_pos !== false ) {
				$ip = str_replace('::', str_repeat(':0000', (($dbl_colon_pos === 0 || $dbl_colon_pos === strlen($ip) - 2) ? 9 : 8) - $colon_count) . ':', $ip);
				$ip = trim($ip, ':');
			}

			$ip_groups = explode(':', $ip);
			$ipv6_bin = '';
			foreach($ip_groups as $ip_group) {
				$ipv6_bin .= pack('H*', str_pad($ip_group, 4, '0', STR_PAD_LEFT));
			}

			return strlen($ipv6_bin) === 16 ? $ipv6_bin : false;
		}

		// IPv4 mapped IPv6
		if( preg_match('/^(?:\:(?:\:0{1,4}){0,4}\:|(?:0{1,4}\:){5})ffff\:(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/i', $ip, $matches) ) {
			$octets = explode('.', $matches[1]);

			return "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff" . chr($octets[0]) . chr($octets[1]) . chr($octets[2]) . chr($octets[3]);
		}

		return false;
	}

	/**
	 * Added compatibility for hosts that do not have inet_ntop.
	 *
	 * @param $ip
	 * @return bool|string
	 */
	public static function _inet_ntop($ip)
	{
		// IPv4
		if( strlen($ip) === 4 ) {
			return ord($ip[0]) . '.' . ord($ip[1]) . '.' . ord($ip[2]) . '.' . ord($ip[3]);
		}

		// IPv6
		if( strlen($ip) === 16 ) {

			// IPv4 mapped IPv6
			if( substr($ip, 0, 12) == "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff" ) {
				return "::ffff:" . ord($ip[12]) . '.' . ord($ip[13]) . '.' . ord($ip[14]) . '.' . ord($ip[15]);
			}

			$hex = bin2hex($ip);
			$groups = str_split($hex, 4);
			$in_collapse = false;
			$done_collapse = false;
			foreach($groups as $index => $group) {
				if( $group == '0000' && !$done_collapse ) {
					if( $in_collapse ) {
						$groups[$index] = '';
						continue;
					}
					$groups[$index] = ':';
					$in_collapse = true;
					continue;
				}
				if( $in_collapse ) {
					$done_collapse = true;
				}
				$groups[$index] = ltrim($groups[$index], '0');
				if( strlen($groups[$index]) === 0 ) {
					$groups[$index] = '0';
				}
			}
			$ip = join(':', array_filter($groups, 'strlen'));
			$ip = str_replace(':::', '::', $ip);

			return $ip == ':' ? '::' : $ip;
		}

		return false;
	}

	public static function getIP($refreshCache = false)
	{
		static $theIP = null;
		if( isset($theIP) && !$refreshCache ) {
			return $theIP;
		}
		//For debugging.
		//return '54.232.205.132';
		//return self::makeRandomIP();

		// if no REMOTE_ADDR, it's probably running from the command line
		$ip = self::getIPAndServerVariable();
		if( is_array($ip) ) {
			list($IP, $variable) = $ip;
			$theIP = $IP;

			return $IP;
		}

		return false;
	}

	public static function getAllServerVariableIPs()
	{
		$variables = array('REMOTE_ADDR', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR');
		$ips = array();

		foreach($variables as $variable) {
			$ip = isset($_SERVER[$variable]) ? $_SERVER[$variable] : false;

			if( $ip && strpos($ip, ',') !== false ) {
				$ips[$variable] = preg_replace('/[\s,]/', '', explode(',', $ip));
			} else {
				$ips[$variable] = $ip;
			}
		}

		return $ips;
	}

	public static function getIPAndServerVariable($howGet = null, $trustedProxies = null)
	{
		$connectionIP = array_key_exists('REMOTE_ADDR', $_SERVER) ? array(
			$_SERVER['REMOTE_ADDR'],
			'REMOTE_ADDR'
		) : array('127.0.0.1', 'REMOTE_ADDR');

		if( $howGet === null ) {
			$howGet = \WBCR\Titan\Plugin::app()->getPopulateOption('howget_ip');
		}

		if( $howGet ) {
			if( $howGet == 'REMOTE_ADDR' ) {
				return self::getCleanIPAndServerVar(array($connectionIP), $trustedProxies);
			} else {
				$ipsToCheck = array(
					array((isset($_SERVER[$howGet]) ? $_SERVER[$howGet] : ''), $howGet),
					$connectionIP,
				);

				return self::getCleanIPAndServerVar($ipsToCheck, $trustedProxies);
			}
		} else {
			$ipsToCheck = array();

			$recommendedField = Plugin::app()->getPopulateOption('detectProxyRecommendation', ''); //Prioritize the result from our proxy check if done
			if( !empty($recommendedField) && $recommendedField != 'UNKNOWN' && $recommendedField != 'DEFERRED' ) {
				if( isset($_SERVER[$recommendedField]) ) {
					$ipsToCheck[] = array($_SERVER[$recommendedField], $recommendedField);
				}
			}
			$ipsToCheck[] = $connectionIP;
			if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
				$ipsToCheck[] = array($_SERVER['HTTP_X_FORWARDED_FOR'], 'HTTP_X_FORWARDED_FOR');
			}
			if( isset($_SERVER['HTTP_X_REAL_IP']) ) {
				$ipsToCheck[] = array($_SERVER['HTTP_X_REAL_IP'], 'HTTP_X_REAL_IP');
			}

			return self::getCleanIPAndServerVar($ipsToCheck, $trustedProxies);
		}

		return false; //Returns an array with a valid IP and the server variable, or false.
	}

	public static function getIPPreview($howGet = null, $trustedProxies = null)
	{
		$ip = self::getIPAndServerVariable($howGet, $trustedProxies);
		if( is_array($ip) ) {
			list($IP, $variable) = $ip;
			if( isset($_SERVER[$variable]) && strpos($_SERVER[$variable], ',') !== false ) {
				$items = preg_replace('/[\s,]/', '', explode(',', $_SERVER[$variable]));
				$output = '';
				foreach($items as $i) {
					if( $IP == $i ) {
						$output .= ', <strong>' . esc_html($i) . '</strong>';
					} else {
						$output .= ', ' . esc_html($i);
					}
				}

				return substr($output, 2);
			}

			return '<strong>' . esc_html($IP) . '</strong>';
		}

		return false;
	}

	public static function isValidIP($IP)
	{
		return filter_var($IP, FILTER_VALIDATE_IP) !== false;
	}

	public static function isValidCIDRRange($range)
	{
		$components = explode('/', $range);
		if( count($components) != 2 ) {
			return false;
		}

		list($ip, $prefix) = $components;
		if( !self::isValidIP($ip) ) {
			return false;
		}

		if( !preg_match('/^\d+$/', $prefix) ) {
			return false;
		}

		if( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
			if( $prefix < 0 || $prefix > 32 ) {
				return false;
			}
		} else {
			if( $prefix < 1 || $prefix > 128 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Expects an array of items. The items are either IP's or IP's separated by comma, space or tab. Or an array of IP's.
	 * We then examine all IP's looking for a public IP and storing private IP's in an array. If we find no public IPs we return the first private addr we found.
	 *
	 * @param array $arr
	 * @return bool|mixed
	 */
	private static function getCleanIPAndServerVar($arr, $trustedProxies = null)
	{
		$privates = array(); //Store private addrs until end as last resort.
		for($i = 0; $i < count($arr); $i++) {
			list($item, $var) = $arr[$i];
			if( is_array($item) ) {
				foreach($item as $j) {
					// try verifying the IP is valid before stripping the port off
					if( !self::isValidIP($j) ) {
						$j = preg_replace('/:\d+$/', '', $j); //Strip off port
					}
					if( self::isValidIP($j) ) {
						if( self::isIPv6MappedIPv4($j) ) {
							$j = self::inet_ntop(self::inet_pton($j));
						}

						if( self::isPrivateAddress($j) ) {
							$privates[] = array($j, $var);
						} else {
							return array($j, $var);
						}
					}
				}
				continue; //This was an array so we can skip to the next item
			}

			$skipToNext = false;
			if( $trustedProxies === null ) {
				$trustedProxies = explode("\n", Plugin::app()->getPopulateOption('howget_ips_trusted_proxies', ''));
			}

			foreach(array(',', ' ', "\t") as $char) {
				if( strpos($item, $char) !== false ) {
					$sp = explode($char, $item);
					$sp = array_reverse($sp);
					foreach($sp as $index => $j) {
						$j = trim($j);
						if( !self::isValidIP($j) ) {
							$j = preg_replace('/:\d+$/', '', $j); //Strip off port
						}
						if( self::isValidIP($j) ) {
							if( self::isIPv6MappedIPv4($j) ) {
								$j = self::inet_ntop(self::inet_pton($j));
							}

							foreach($trustedProxies as $proxy) {
								if( !empty($proxy) ) {
									if( self::subnetContainsIP($proxy, $j) && $index < count($sp) - 1 ) {
										continue 2;
									}
								}
							}

							if( self::isPrivateAddress($j) ) {
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

			if( !self::isValidIP($item) ) {
				$item = preg_replace('/:\d+$/', '', $item); //Strip off port
			}
			if( self::isValidIP($item) ) {
				if( self::isIPv6MappedIPv4($item) ) {
					$item = self::inet_ntop(self::inet_pton($item));
				}

				if( self::isPrivateAddress($item) ) {
					$privates[] = array($item, $var);
				} else {
					return array($item, $var);
				}
			}
		}
		if( sizeof($privates) > 0 ) {
			return $privates[0]; //Return the first private we found so that we respect the order the IP's were passed to this function.
		} else {
			return false;
		}
	}

	/**
	 * @param string $ip
	 * @return bool
	 */
	public static function isIPv6MappedIPv4($ip)
	{
		return preg_match('/^(?:\:(?:\:0{1,4}){0,4}\:|(?:0{1,4}\:){5})ffff\:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/i', $ip) > 0;
	}

	/**
	 * @param string $addr Should be in dot or colon notation (127.0.0.1 or ::1)
	 * @return bool
	 */
	public static function isPrivateAddress($addr)
	{
		// Run this through the preset list for IPv4 addresses.
		if( filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false ) {
			foreach(self::getIPWhitelist('private') as $a) {
				if( self::subnetContainsIP($a, $addr) ) {
					return true;
				}
			}
		}

		return filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false && filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
	}

	/**
	 * Get the list of whitelisted IPs and networks, which is a combination of preset IPs/ranges and user-entered
	 * IPs/ranges.
	 *
	 * @param string $filter Group name to filter whitelist by
	 * @return array
	 */
	public static function getIPWhitelist($filter = null)
	{
		static $wfIPWhitelist;

		if( !isset($wfIPWhitelist) ) {
			$wfIPWhitelist = self::whitelistedServiceIPs();

			//Append user ranges
			$wfIPWhitelist['user'] = array();
			foreach(array_filter(explode(',', Plugin::app()->getPopulateOption('whitelisted', []))) as $ip) {
				$wfIPWhitelist['user'][] = new \WBCR\Titan\Firewall\User_IP_Range($ip);
			}
		}

		$whitelist = array();
		foreach($wfIPWhitelist as $group => $values) {
			if( $filter === null || $group === $filter ) {
				$whitelist = array_merge($whitelist, $values);
			}
		}

		return $whitelist;
	}

	/**
	 * Returns an array containing all whitelisted service IPs/ranges. The returned array is grouped by service
	 * tag: array('service1' => array('range1', 'range2', range3', ...), ...)
	 *
	 * @return array
	 */
	public static function whitelistedServiceIPs()
	{
		$result = array();
		$whitelistPresets = self::whitelistPresets();

		$whitelisted_services_str = Plugin::app()->getPopulateOption('whitelisted_services');
		$whitelistedServices = explode(',', $whitelisted_services_str);

		foreach($whitelistPresets as $tag => $preset) {
			if( !isset($preset['n']) ) { //Just an array of IPs/ranges
				$result[$tag] = $preset;
				continue;
			}

			if( (isset($preset['h']) && $preset['h']) || (isset($preset['f']) && $preset['f']) ) { //Forced
				$result[$tag] = $preset['r'];
				continue;
			}

			if( (in_array($tag, $whitelistedServices) && isset($preset['d']) && $preset['d']) ) {
				$result[$tag] = $preset['r'];
			}
		}

		return $result;
	}

	/**
	 * Returns the whitelist presets, which first grabs the bundled list and then merges the dynamic list into it.
	 *
	 * @return array
	 */
	public static function whitelistPresets()
	{
		static $_cachedPresets = null;
		if( $_cachedPresets === null ) {
			include(dirname(__FILE__) . '/ip-white-list.php');
			/** @var array $wfIPWhitelist */
			$currentPresets = Plugin::app()->getPopulateOption('whitelist_presets', []);
			if( is_array($currentPresets) ) {
				$_cachedPresets = array_merge($wfIPWhitelist, $currentPresets);
			} else {
				$_cachedPresets = $wfIPWhitelist;
			}
		}

		return $_cachedPresets;
	}

	/**
	 * Returns the known server IPs, ordered by those as the best match for outgoing requests.
	 *
	 * @param bool $refreshCache
	 * @return string[]
	 */
	public static function serverIPs($refreshCache = false)
	{
		static $cachedServerIPs = null;
		if( isset($cachedServerIPs) && !$refreshCache ) {
			return $cachedServerIPs;
		}

		$serverIPs = array();
		$storedIP = Plugin::app()->getPopulateOption('server_ip');
		if( preg_match('/^(\d+);(.+)$/', $storedIP, $matches) ) { //Format is 'timestamp;ip'
			$serverIPs[] = $matches[2];
		}

		if( function_exists('dns_get_record') ) {
			$storedDNS = Plugin::app()->getPopulateOption('server_dns');
			$usingCache = false;
			if( preg_match('/^(\d+);(\d+);(.+)$/', $storedDNS, $matches) ) { //Format is 'timestamp;ttl;ip'
				$timestamp = $matches[1];
				$ttl = $matches[2];
				if( $timestamp + max($ttl, 86400) > time() ) {
					$serverIPs[] = $matches[3];
					$usingCache = true;
				}
			}

			if( !$usingCache ) {
				$home = get_home_url();
				if( preg_match('/^https?:\/\/([^\/]+)/i', $home, $matches) ) {
					$host = strtolower($matches[1]);
					$cnameRaw = @dns_get_record($host, DNS_CNAME);
					$cnames = array();
					$cnamesTargets = array();
					if( $cnameRaw ) {
						foreach($cnameRaw as $elem) {
							if( $elem['host'] == $host ) {
								$cnames[] = $elem;
								$cnamesTargets[] = $elem['target'];
							}
						}
					}

					$aRaw = @dns_get_record($host, DNS_A);
					$a = array();
					if( $aRaw ) {
						foreach($aRaw as $elem) {
							if( $elem['host'] == $host || in_array($elem['host'], $cnamesTargets) ) {
								$a[] = $elem;
							}
						}
					}

					$firstA = self::array_first($a);
					if( $firstA !== null ) {
						$serverIPs[] = $firstA['ip'];
						Plugin::app()->updatePopulateOption('server_dns', time() . ';' . $firstA['ttl'] . ';' . $firstA['ip']);
					}
				}
			}
		}

		if( isset($_SERVER['SERVER_ADDR']) && self::isValidIP($_SERVER['SERVER_ADDR']) ) {
			$serverIPs[] = $_SERVER['SERVER_ADDR'];
		}

		$serverIPs = array_unique($serverIPs);
		$cachedServerIPs = $serverIPs;

		return $serverIPs;
	}

	public static function requestDetectProxyCallback($timeout = 2, $blocking = true, $forceCheck = false)
	{
		$currentRecommendation = Plugin::app()->getPopulateOption('detect_proxy_recommendation', '');

		if( !$forceCheck ) {
			$detectProxyNextCheck = Plugin::app()->getPopulateOption('detect_proxy_next_check', false);
			if( $detectProxyNextCheck !== false && time() < $detectProxyNextCheck ) {
				if( empty($currentRecommendation) ) {
					Plugin::app()->updatePopulateOption('detect_proxy_recommendation', 'DEFERRED');
				}

				return; //Let it pull the currently-stored value
			}
		}

		try {
			$waf = Plugin::app()->fw_storage();
			if( $waf->getConfig('attackDataKey', false) === false ) {
				$waf->setConfig('attackDataKey', mt_rand(0, 0xfff));
			}
			$response = wp_remote_get(sprintf(WFWAF_API_URL_SEC . "proxy-check/%d.txt", $waf->getConfig('attackDataKey')), array('headers' => array('Referer' => false)));

			if( !is_wp_error($response) ) {
				$okToSendBody = wp_remote_retrieve_body($response);
				if( preg_match('/^(ok|wait),\s*(\d+)$/i', $okToSendBody, $matches) ) {
					$command = $matches[1];
					$ttl = $matches[2];
					if( $command == 'wait' ) {
						Plugin::app()->updatePopulateOption('detect_proxy_next_check', time() + $ttl);
						if( empty($currentRecommendation) || $currentRecommendation == 'UNKNOWN' ) {
							Plugin::app()->updatePopulateOption('detect_proxy_recommendation', 'DEFERRED');
						}

						return;
					}

					Plugin::app()->updatePopulateOption('detect_proxy_next_check', time() + $ttl);
				} else { //Unknown response
					Plugin::app()->deletePopulateOption('detect_proxy_next_check');

					if( empty($currentRecommendation) || $currentRecommendation == 'UNKNOWN' ) {
						Plugin::app()->updatePopulateOption('detect_proxy_recommendation', 'DEFERRED');
					}

					return;
				}
			}
		} catch( \Exception $e ) {
			return;
		}

		$nonce = bin2hex(self::random_bytes(32));
		$callback = self::getSiteBaseURL() . '?_wfsf=detectProxy';

		Plugin::app()->updatePopulateOption('detect_proxy_nonce', $nonce);
		Plugin::app()->updatePopulateOption('detect_proxy_recommendation', '');

		$payload = array(
			'nonce' => $nonce,
			'callback' => $callback,
		);

		$homeurl = self::wpHomeURL();
		$siteurl = self::wpSiteURL();

		try {
			$response = wp_remote_post(WFWAF_API_URL_SEC . "?" . http_build_query(array(
					'action' => 'detect_proxy',
					'k' => Plugin::app()->getPopulateOption('api_key', ''),
					's' => $siteurl,
					'h' => $homeurl,
					't' => microtime(true),
				), null, '&'), array(
				'body' => json_encode($payload),
				'headers' => array(
					'Content-Type' => 'application/json',
					'Referer' => false,
				),
				'timeout' => $timeout,
				'blocking' => $blocking,
			));

			if( !is_wp_error($response) ) {
				$jsonResponse = wp_remote_retrieve_body($response);
				$decoded = @json_decode($jsonResponse, true);
				if( is_array($decoded) && isset($decoded['data']) && is_array($decoded['data']) && isset($decoded['data']['ip']) && \WBCR\Titan\Firewall\Utils::isValidIP($decoded['data']['ip']) ) {
					Plugin::app()->updatePopulateOption('server_ip', time() . ';' . $decoded['data']['ip']);
				}
			}
		} catch( \Exception $e ) {
			return;
		}
	}

	/**
	 * @return bool Returns false if the payload is invalid, true if it processed the callback (even if the IP wasn't found).
	 */
	public static function processDetectProxyCallback()
	{
		$nonce = Plugin::app()->getPopulateOption('detect_proxy_nonce', '');

		$testNonce = (isset($_POST['nonce']) ? $_POST['nonce'] : '');

		if( empty($nonce) || empty($testNonce) ) {
			return false;
		}

		if( !hash_equals($nonce, $testNonce) ) {
			return false;
		}

		$ips = (isset($_POST['ips']) ? $_POST['ips'] : array());
		if( empty($ips) ) {
			return false;
		}

		$expandedIPs = array();
		foreach($ips as $ip) {
			$expandedIPs[] = self::inet_pton($ip);
		}

		$checks = array('HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR');
		foreach($checks as $key) {
			if( !isset($_SERVER[$key]) ) {
				continue;
			}

			$testIP = self::getCleanIPAndServerVar(array(array($_SERVER[$key], $key)));
			if( $testIP === false ) {
				continue;
			}

			$testIP = self::inet_pton($testIP[0]);
			if( in_array($testIP, $expandedIPs) ) {
				Plugin::app()->updatePopulateOption('detect_proxy_nonce', '');
				Plugin::app()->updatePopulateOption('detect_proxy_recommendation', $key);

				return true;
			}
		}
		Plugin::app()->updatePopulateOption('detect_proxy_nonce', '');
		Plugin::app()->updatePopulateOption('detect_proxy_recommendation', 'UNKNOWN');

		return true;
	}

	public static function getSiteBaseURL()
	{
		return rtrim(site_url(), '/') . '/';
	}

	public static function wpHomeURL($path = '', $scheme = null)
	{
		$homeurl = Plugin::app()->getPopulateOption('wp_home_url', '');

		if( function_exists('get_bloginfo') && empty($homeurl) ) {
			if( is_multisite() ) {
				$homeurl = network_home_url($path, $scheme);
			} else {
				$homeurl = home_url($path, $scheme);
			}

			$homeurl = rtrim($homeurl, '/'); //Because previously we used get_bloginfo and it returns http://example.com without a '/' char.
		} else {
			$homeurl = set_url_scheme($homeurl, $scheme);
			if( $path && is_string($path) ) {
				$homeurl .= '/' . ltrim($path, '/');
			}
		}

		return $homeurl;
	}

	/**
	 * Equivalent to network_site_url but uses the cached value for the URL if we have it
	 * to avoid breaking on sites that define it based on the requesting hostname.
	 *
	 * @param string $path
	 * @param null|string $scheme
	 * @return string
	 */
	public static function wpSiteURL($path = '', $scheme = null)
	{
		$siteurl = Plugin::app()->getPopulateOption('wp_site_url', '');

		if( function_exists('get_bloginfo') && empty($siteurl) ) {
			if( is_multisite() ) {
				$siteurl = network_site_url($path, $scheme);
			} else {
				$siteurl = site_url($path, $scheme);
			}

			$siteurl = rtrim($siteurl, '/'); //Because previously we used get_bloginfo and it returns http://example.com without a '/' char.
		} else {
			$siteurl = set_url_scheme($siteurl, $scheme);
			if( $path && is_string($path) ) {
				$siteurl .= '/' . ltrim($path, '/');
			}
		}

		return $siteurl;
	}

	public static function refreshCachedSiteURL()
	{
		$pullDirectly = class_exists('WPML_URL_Filters');
		$siteurl = '';
		if( $pullDirectly ) {
			//A version of the native get_home_url without the filter call
			$siteurl = self::_site_url_nofilter();
		}

		if( function_exists('get_bloginfo') && empty($siteurl) ) {
			if( is_multisite() ) {
				$siteurl = network_site_url();
			} else {
				$siteurl = site_url();
			}

			$siteurl = rtrim($siteurl, '/'); //Because previously we used get_bloginfo and it returns http://example.com without a '/' char.
		}

		if( Plugin::app()->getPopulateOption('wp_site_url') !== $siteurl ) {
			Plugin::app()->updatePopulateOption('wp_site_url', $siteurl);
		}
	}

	public static function refreshCachedHomeURL()
	{
		$pullDirectly = class_exists('WPML_URL_Filters');
		$homeurl = '';

		if( $pullDirectly ) {
			//A version of the native get_home_url without the filter call
			$homeurl = self::_home_url_nofilter();
		}

		if( function_exists('get_bloginfo') && empty($homeurl) ) {
			if( is_multisite() ) {
				$homeurl = network_home_url();
			} else {
				$homeurl = home_url();
			}

			$homeurl = rtrim($homeurl, '/'); //Because previously we used get_bloginfo and it returns http://example.com without a '/' char.
		}

		if( Plugin::app()->getPopulateOption('wp_home_url') !== $homeurl ) {
			Plugin::app()->updatePopulateOption('wp_home_url', $homeurl);
		}
	}

	private static function _home_url_nofilter($path = '', $scheme = null)
	{ //A version of the native get_home_url and get_option without the filter calls
		global $pagenow, $wpdb, $blog_id;

		static $cached_url = null;
		if( $cached_url !== null ) {
			return $cached_url;
		}

		if( defined('WP_HOME') && WTITAN_PREFER_WP_HOME_FOR_WPML ) {
			$cached_url = WP_HOME;

			return $cached_url;
		}

		if( empty($blog_id) || !is_multisite() ) {
			$url = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'home' LIMIT 1");
			if( empty($url) ) { //get_option uses siteurl instead if home is empty
				$url = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'siteurl' LIMIT 1");
			}
		} else if( is_multisite() ) {
			$current_network = get_network();
			if( 'relative' == $scheme ) {
				$url = rtrim($current_network->path, '/');
			} else {
				$url = 'http://' . rtrim($current_network->domain, '/') . '/' . trim($current_network->path, '/');
			}
		}

		if( !in_array($scheme, array('http', 'https', 'relative')) ) {
			if( is_ssl() && !is_admin() && 'wp-login.php' !== $pagenow ) {
				$scheme = 'https';
			} else {
				$scheme = parse_url($url, PHP_URL_SCHEME);
			}
		}

		$url = set_url_scheme($url, $scheme);

		if( $path && is_string($path) ) {
			$url .= '/' . ltrim($path, '/');
		}

		$cached_url = $url;

		return $url;
	}

	private static function _site_url_nofilter($path = '', $scheme = null)
	{ //A version of the native get_site_url and get_option without the filter calls
		global $pagenow, $wpdb, $blog_id;

		static $cached_url = null;
		if( $cached_url !== null ) {
			return $cached_url;
		}

		if( defined('WP_SITEURL') && WTITAN_PREFER_WP_HOME_FOR_WPML ) {
			$cached_url = WP_SITEURL;

			return $cached_url;
		}

		if( empty($blog_id) || !is_multisite() ) {
			$url = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'siteurl' LIMIT 1");
		} else if( is_multisite() ) {
			$current_network = get_network();
			if( 'relative' == $scheme ) {
				$url = rtrim($current_network->path, '/');
			} else {
				$url = 'http://' . rtrim($current_network->domain, '/') . '/' . trim($current_network->path, '/');
			}
		}

		if( !in_array($scheme, array('http', 'https', 'relative')) ) {
			if( is_ssl() && !is_admin() && 'wp-login.php' !== $pagenow ) {
				$scheme = 'https';
			} else {
				$scheme = parse_url($url, PHP_URL_SCHEME);
			}
		}

		$url = set_url_scheme($url, $scheme);

		if( $path && is_string($path) ) {
			$url .= '/' . ltrim($path, '/');
		}

		$cached_url = $url;

		return $url;
	}

}