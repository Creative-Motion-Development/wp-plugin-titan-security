<?php

namespace WBCR\Titan;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

/**
 * The file contains a short help info.
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Antispam extends Module_Base {

	/**
	 * @see self::app()
	 * @var Antispam
	 */
	private static $app;

	/**
	 * Request interval in hours
	 *
	 * @since 1.1
	 */
	const DEFAULT_REQUESTS_INTERVAL = 4;

	/**
	 * Request interval in hours, if server is unavailable
	 *
	 * @since 1.1
	 */
	const SERVER_UNAVAILABLE_INTERVAL = 1;

	/**
	 * @var bool
	 */
	public $mode;

	/**
	 * Vulnerabilities constructor.
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		self::$app = $this;

		$this->module_dir = WTITAN_PLUGIN_DIR . "/includes/antispam";
		$this->module_url = WTITAN_PLUGIN_URL . "/includes/antispam";

		$this->mode = $this->plugin->getOption('antispam_mode', true);

		add_action('wp_ajax_wtitan-change-antispam-mode', [$this, 'change_antispam_mode']);
	}

	/**
	 * @return Antispam
	 * @since  7.0
	 */
	public static function app()
	{
		return self::$app;
	}

	/**
	 * AJAX Enable/Disable anti-spam
	 */
	public function change_antispam_mode()
	{
		check_ajax_referer('wtitan_change_antispam_mode');

		if( !current_user_can('manage_options') ) {
			wp_send_json(['error_message' => __('You don\'t have enough capability to edit this information.', 'titan-security')]);
		}

		if( isset($_POST['mode']) ) {

			$mode_name = sanitize_text_field($_POST['mode']);

			\WBCR\Titan\Plugin::app()->updatePopulateOption('antispam_mode', $mode_name);

			if( (bool)$mode_name ) {
				wp_send_json([
					'message' => __("Anti-spam successfully enabled", "titan-security"),
					'mode' => $mode_name,
				]);
			} else {
				wp_send_json(['message' => __("Anti-spam successfully disabled", "titan-security")]);
			}
		}
	}

	/**
	 *
	 * @since  7.0
	 */
	public function showPageContent()
	{
	}

	/**
	 * Get data from cache.
	 *
	 * If data in the cache, not empty and not expired, then get data from cache. Or get data from server.
	 *
	 * @return mixed array
	 * @since  1.1
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 *
	 */

	public function get_statistic_data()
	{
		$key = \WBCR\Titan\Plugin::app()->getPrefix() . 'stats_transient_';

		$cached = get_transient($key);

		if( $cached !== false ) {
			if( isset($cached->error_code) && isset($cached->error) ) {
				delete_transient($key);

				return new \WP_Error($cached->error_code, $cached->error);
			}

			return $cached;
		}

		if( class_exists('\WBCR\Titan\Premium\Api\Request') ) {
			$api = new \WBCR\Titan\Premium\Api\Request();
			$data = $api->get_statistic(7);

			if( is_wp_error($data) ) {
				set_transient($key, (object)[
					'error' => $data->get_error_message(),
					'error_code' => $data->get_error_code(),
				], self::SERVER_UNAVAILABLE_INTERVAL * HOUR_IN_SECONDS);

				return $data;
			}

			set_transient($key, $data->response, self::DEFAULT_REQUESTS_INTERVAL * HOUR_IN_SECONDS);

			return $data->response;
		}

		$obj = new \stdClass();
		$obj->total = 0;

		return $obj;
	}

}