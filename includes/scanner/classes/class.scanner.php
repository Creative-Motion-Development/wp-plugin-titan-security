<?php
namespace WBCR\Titan;

/**
 * The file contains a short help info.
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class Scanner {

	/**
	 * @var string
	 */
	private $api_url = "http://dev.anti-spam.space/api/v1.0/vulnerability/";

	/**
	 * @var string
	 */
	public $api_endpoint = "";

	/**
	 * @var array
	 */
	public $vulnerabilities = array();

	/**
	 * Vulnerabilities_API constructor.
	 *
	 */
	public function __construct() {

	}

	/**
	 * @return array
	 */
	public function getVulnerabilities() {
		return $this->vulnerabilities;
	}

	/**
	 * @return string
	 */
	public function getApiUrl() {
		return $this->api_url.$this->api_endpoint;
	}

	/**
	 * Render HTML for displaying a vulnerabilities
	 *
	 * @return string
	 */
	public function render_html_table() {
		return "";
	}

	/**
	 * @param array $params
	 *      array(
	 *          'plugin-slug-1' => '1.7.0',
	 *          'plugin-slug-2' => '2.1.5',
	 *      )
	 * @param string $method
	 *
	 * @return array
	 */
	public function request($params, $method = 'POST') {

		if(\WBCR\Titan\Plugin::app()->premium->is_activate())
			$key = \WBCR\Titan\Plugin::app()->premium->get_license()->get_key();
		else
			$key = "";

		$headers = array(
			"Authorization" => "Bearer ".base64_encode( $key),
			"Accept"        => "application/json",
			"Content-Type"  => "application/json",
		);
		$args    = array(
			'headers'     => $headers,
			'sslverify'   => false,
		);
		if($method == 'POST') { //POST
			$body = array( 'data' => $params );
			$args['body'] = json_encode( $body );
			$response = wp_remote_post( $this->getApiUrl(), $args );
		}
		else //GET
		{
			$response = wp_remote_get( add_query_arg( $params, $this->getApiUrl() ), $args );
		}

		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
			$vulners = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( isset( $vulners['status'] ) && $vulners['status'] == 'ok' ) {
				return $this->validate( $vulners['response'], $method );
			}
		}
		return array();
	}

	/**
	 * @param array $response
	 * @param array $method
	 *
	 * @return array
	 */
	public function validate($response, $method = 'POST') {
		$result = array();
		if($method == 'POST') { //POST
			foreach ( $response as $key_p => $plugin ) {
				foreach ( $plugin as $vulner ) {
					$vulner['description'] = wp_strip_all_tags( $vulner['description'] );
					$result[ $key_p ][]    = $vulner;
				}
			}
		}
		else
		{
			foreach ( $response as $item ) {
				$item['description'] = wp_strip_all_tags( $item['description'] );
				$result[]            = $item;
			}

		}

		return $result;
	}

}