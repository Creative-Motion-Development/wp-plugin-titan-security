<?php

namespace WBCR\Titan\Premium\Api;

/**
 * The class implement some protections ways against spam
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 *
 * @copyright (c) 2018 Webraftic Ltd
 */
class Request {

	//const SERVER_API_URL_V1 = 'https://api.anti-spam.space/api/v1.0';
	const SERVER_API_URL_V1 = 'https://api.titansitescanner.com/api/v1.0';
	//const SERVER_API_URL_V1 = 'https://dev.anti-spam.space/api/v1.0';
	const STAT_ENDPOINT = '/spam/statistics';

	protected $plugin_id;
	protected $license_key;

	public function __construct() {
		$this->plugin_id   = wantispam_get_freemius_plugin_id();
		$this->license_key = wantispam_get_license_key();
	}


	public function get_statistic( $days = 7 ) {
		if ( empty( $days ) ) {
			return new \WP_Error( 'http_request_failed', 'Variable $items is empty! You must pass number days to get statistic for period.' );
		}

		$request_url = \WBCR\Titan\Premium\Api\Request::SERVER_API_URL_V1 . \WBCR\Titan\Premium\Api\Request::STAT_ENDPOINT;
		$request_url = add_query_arg( [ 'days' => (int) $days ], $request_url );

		return $this->request( $request_url, 'GET' );
	}

	/**
	 * Checking spam through  Creative Motion API
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param $items
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	public function check_spam( $items ) {
		if ( empty( $items ) ) {
			return new \WP_Error( 'http_request_failed', 'Variable $items is empty! You must pass one or more items to spam check.' );
		}

		return $this->request( self::SERVER_API_URL_V1 . '/spam', 'POST', $items );
	}

	/**
	 * Checking status queue through  Creative Motion API
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param string|string[] $uid
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	public function check_status_queue( $uid ) {
		if ( empty( $uid ) ) {
			return new \WP_Error( 'http_request_failed', 'Variable $uid is empty!' );
		}

		if ( is_array( $uid ) ) {
			return $this->request( self::SERVER_API_URL_V1 . "/spam/queue-status", 'POST', $uid );
		}

		return $this->request( self::SERVER_API_URL_V1 . "/spam/queue-status/{$uid}", 'GET' );
	}

	/**
	 * Send request to remote server
	 *
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  6.0
	 *
	 * @param string $url    Url for request
	 * @param string $type   Can be only (POST|GET)
	 * @param array  $body   Params which must be pass in request body
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	public function request( $url, $type = 'GET', array $body = [] ) {
		if ( empty( $url ) ) {
			return new \WP_Error( 'http_request_failed', 'Variable $url cannot be empty!' );
		}

		$headers['Accept']        = 'application/json';
		$headers['Authorization'] = 'Bearer ' . base64_encode( $this->license_key );
		$headers['PluginId']      = $this->plugin_id;

		$args = [
			'method'    => $type,
			'headers'   => $headers,
			'sslverify' => false,
			'body'      => []
		];

		if ( 'GET' !== $type ) {
			$args['headers']['Content-Type'] = 'application/json';
			$args['data_format']             = 'body';
			$args['body']                    = json_encode( $body );
		}

		$request = wp_remote_request( $url, $args );

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );

		if ( $response_code != 200 ) {
			\WBCR\Titan\Logger\Writter::error( sprintf( "Http request failed, code: %d!", $response_code ) );

			return new \WP_Error( 'http_request_failed', 'Server response ' . $response_code );
		}

		$response_data = @json_decode( $response_body );

		if ( empty( $response_data ) || ! is_object( $response_data ) ) {
			\WBCR\Titan\Logger\Writter::error( "Server returned empty response. Maybe, there accidented a critical error!" );

			return new \WP_Error( 'http_request_failed', 'Server returned empty response. Maybe, there accidented a critical error.' );
		}

		if ( 'fail' === $response_data->status && ! empty( $response_data->error ) ) {
			\WBCR\Titan\Logger\Writter::error( $response_data->error );

			return new \WP_Error( 'http_server_error', $response_data->error );
		}

		if ( 'ok' === $response_data->status && ! empty( $response_data->response ) ) {
			return $response_data;
		}

		\WBCR\Titan\Logger\Writter::error( 'Unknown http request error!' );
		\WBCR\Titan\Logger\Writter::error( sprintf( "Request body: %s", json_encode( $body, JSON_PRETTY_PRINT ) ) );
		\WBCR\Titan\Logger\Writter::error( sprintf( "Response: %s", $response_body ) );

		return new \WP_Error( 'http_request_failed', 'Unknown http request error!' );
	}
}










