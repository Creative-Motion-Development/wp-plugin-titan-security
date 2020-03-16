<?php

namespace WBCR\Titan\Firewall;

class Utils {

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
		$key = \WBCR\Titan\Plugin::app()->getPopulateOption('enckey');

		if( !$key ) {
			//wordfence::status(1, 'error', "Titan error: No encryption key found!");

			return false;
		}
		global $wpdb;

		return $wpdb->get_var("select HEX(AES_ENCRYPT('%s', '%s')) as val", $str, $key);
	}

	public static function decrypt($str)
	{
		$key = \WBCR\Titan\Plugin::app()->getPopulateOption('enckey');
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

}