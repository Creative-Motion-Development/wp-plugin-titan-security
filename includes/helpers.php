<?php
/**
 * Helpers functions
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 2017 Webraftic Ltd
 * @version 1.0
 */

namespace WBCR\Titan\Plugin;

// Exit if accessed directly
use WBCR\Titan\Plugin;

if( !defined('ABSPATH') ) {
	exit;
}

class Helper {

	/**
	 * Should show a page about the plugin or not.
	 *
	 * @return bool
	 */
	public static function is_need_show_setup_page()
	{
		$need_show_about = (int)get_option(Plugin::app()->getOptionName('setup_wizard'));

		$is_ajax = self::doing_ajax();
		$is_cron = self::doing_cron();
		$is_rest = self::doing_rest_api();

		if( $need_show_about && !$is_ajax && !$is_cron && !$is_rest ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings
	 * Case #3: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @author matzeeable https://wordpress.stackexchange.com/questions/221202/does-something-like-is-rest-exist
	 * @since  2.1.0
	 * @return boolean
	 */
	public static function doing_rest_api()
	{
		$prefix = rest_get_url_prefix();
		$rest_route = Plugin::app()->request->get('rest_route', null);
		if( defined('REST_REQUEST') && REST_REQUEST // (#1)
			|| !is_null($rest_route) // (#2)
			&& strpos(trim($rest_route, '\\/'), $prefix, 0) === 0 ) {
			return true;
		}

		// (#3)
		$rest_url = wp_parse_url(site_url($prefix));
		$current_url = wp_parse_url(add_query_arg([]));

		return strpos($current_url['path'], $rest_url['path'], 0) === 0;
	}

	/**
	 * @return bool
	 * @since 2.1.0
	 */
	public static function doing_ajax()
	{
		if( function_exists('wp_doing_ajax') ) {
			return wp_doing_ajax();
		}

		return defined('DOING_AJAX') && DOING_AJAX;
	}

	/**
	 * @return bool
	 * @since 2.1.0
	 */
	public static function doing_cron()
	{
		if( function_exists('wp_doing_cron') ) {
			return wp_doing_cron();
		}

		return defined('DOING_CRON') && DOING_CRON;
	}

	/**
	 * Try to get variable from JSON-encoded post variable
	 *
	 * Note: we pass some params via json-encoded variables, as via pure post some data (ex empty array) will be absent
	 *
	 * @param string $name $_POST's variable name
	 *
	 * @return array
	 */
	public static function maybeGetPostJson($name)
	{
		return \WBCR\Factory_Templates_000\Helpers::maybeGetPostJson($name);
	}

	/**
	 * Escape json data
	 *
	 * @param array $data
	 *
	 * @return string escaped json string
	 */
	public static function getEscapeJson(array $data)
	{
		return \WBCR\Factory_Templates_000\Helpers::getEscapeJson($data);
	}

	/**
	 * Recursive sanitation for an array
	 *
	 * @param $array
	 *
	 * @return mixed
	 * @since 2.0.5
	 *
	 */
	public static function recursiveSanitizeArray($array, $function)
	{
		return \WBCR\Factory_Templates_000\Helpers::recursiveSanitizeArray($array, $function);
	}

	/*
	 * Flushes as many page cache plugin's caches as possible.
	 *
	 * @return void
	 */
	public static function flushPageCache()
	{
		\WBCR\Factory_Templates_000\Helpers::flushPageCache();
	}

}
