<?php

namespace WBCR\Titan;

// Exit if accessed directly
if( !defined('ABSPATH') ) {
	exit;
}

use WBCR\Titan\Client\Client;
use WBCR\Titan\Client\Entity\UrlChecker;
use WBCR\Titan\Client\Request\SetNoticeData;

/**
 * The file contains a short help info.
 *
 * @author        Artem Prihodko     <webtemyk@yandex.ru>
 * @author        Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @version       1.0
 * @copyright (c) 2020 Creative Motion
 */
class SiteChecker extends Module_Base {

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var UrlChecker[]
	 */
	private $sites;

	/**
	 * SiteChecker constructor.
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		$this->module_dir = WTITAN_PLUGIN_DIR . "/includes/sitechecker";
		$this->module_url = WTITAN_PLUGIN_URL . "/includes/sitechecker";

		$this->client = new Client($this->license_key);

		$this->getSites();

		add_action('wp_ajax_push_token', [$this, 'handle_push_token']);
		add_action('wp_ajax_wtitan_sitechecker_delete_url', [$this, 'send_delete_url']);
		add_action('wp_ajax_wtitan_sitechecker_add_url', [$this, 'send_add_url']);
	}

	/**
	 * Get sites
	 *
	 * @return array
	 */
	public function getSites()
	{
		if( Plugin::app()->is_premium() ) {
			$this->sites = $this->client->get_checker_urls();
		} else {
			$this->sites = [];
		}

		return $this->sites;
	}

	/**
	 * Number of sites
	 *
	 * @return int
	 */
	public function get_count()
	{
		return count($this->sites);
	}

	/**
	 * Number of sites
	 *
	 * @return int
	 */
	public function get_average_uptime()
	{
		$up = 0;
		$count = count($this->sites);
		foreach($this->sites as $site) {
			$up = $up + $site->uptime;
		}

		return $count ? round($up / count($this->sites)) : 0;
	}

	/**
	 * Show page content
	 */
	public function showPageContent()
	{

		$urls = $this->sites;

		if( empty($urls) || !is_array($urls) ) {
			$urls = [];
		}
		$args = [
			'urls' => $urls,
			'is_premium' => $this->plugin->is_premium(),
		];
		echo $this->render_template('sitechecker', $args);
	}

	/**
	 * Receives a push token from the user and sends it to the server
	 * If `pushToken` is `null`, it sends a request to delete the token from the server
	 */
	public function handle_push_token()
	{
		check_ajax_referer('titan-send-push-token');

		if( !Plugin::app()->current_user_can() ) {
			wp_send_json_error([
				'error_message' => __("You don't have enough capability to edit this information", "titan-security"),
			]);
		}

		if( !Plugin::app()->premium->is_activate() ) {
			wp_send_json_error([
				'error_message' => __('Available only to premium users', 'titan-security'),
			]);
		}

		$pushToken = sanitize_text_field($_POST['token']);
		$client = new Client($this->license_key);

		if( is_null($pushToken) ) {
			$noticeData = $client->get_notice_data();
			foreach($noticeData as $data) {
				if( $data->value == $pushToken ) {
					$client->delete_notice_data([$data->id]);
					break;
				}
			}

			wp_send_json_success([
				'message' => __('You have unsubscribed to PUSH notifications', 'titan-security')
			]);
		}

		$data = new SetNoticeData();
		$data->add('push', $pushToken);

		$client->set_notice_data($data);

		wp_send_json_success([
			'message' => __('You have subscribed to PUSH notifications', 'titan-security')
		]);
	}

	/**
	 * AJAX: Delete URL via API
	 */
	public function send_delete_url()
	{
		check_ajax_referer('titan-sitechecker');

		if( !Plugin::app()->current_user_can() ) {
			wp_send_json_error([
				'error_message' => __("You don't have enough capability to edit this information", "titan-security"),
			]);
			die;
		}

		if( isset($_POST['id']) && !empty($_POST['id']) ) {
			$id = sanitize_key($_POST['id']);
			$url_data = $this->client->get_checker_url($id);

			$response = $this->client->delete_checker_url([$id]);
			if( $response ) {
				wp_send_json_success([
					'notice' => __("URL successfully deleted", "titan-security") . ": <u>{$url_data->url}</u>",
					'type' => 'success'
				]);
			} else {
				wp_send_json_error([
					'notice' => __("URL not deleted", "titan-security") . ": <u>{$url_data->url}</u>",
					'type' => 'danger'
				]);
			}
		} else {
			wp_send_json_error([
				'notice' => __("URL deletion error", "titan-security"),
				'type' => 'danger'
			]);
		}

		wp_die();
	}

	/**
	 * AJAX: Add URL via API
	 */
	public function send_add_url()
	{
		check_ajax_referer('titan-sitechecker');

		if( !Plugin::app()->current_user_can() ) {
			wp_send_json_error([
				'error_message' => __("You don't have enough capability to edit this information", "titan-security"),
			]);
			die;
		}

		if( isset($_POST['url']) ) {
			if( empty($_POST['url']) ) {
				wp_send_json_error([
					'notice' => __("URL is empty", "titan-security"),
					'type' => 'danger'
				]);
			}
			$url = sanitize_url($_POST['url']);

			$request = new \WBCR\Titan\Client\Request\CreateCheckerUrl();
			$request->add_url($url, 300);
			$response = $this->client->create_checker_url($request);
			if( $response ) {
				wp_send_json_success([
					'notice' => __("URL successfully added", "titan-security") . ": <u>{$url}</u>",
					'type' => 'success'
				]);
			}
		}

		wp_send_json_error([
			'notice' => __("URL adding error", "titan-security"),
			'type' => 'danger'
		]);
		wp_die();
	}

}