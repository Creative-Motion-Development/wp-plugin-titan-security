<?php


namespace WBCR\Titan\Client;

use WBCR\Titan\Client\Entity\BlacklistedIP;
use WBCR\Titan\Client\Entity\CheckEmail;
use WBCR\Titan\Client\Entity\CmsCheck;
use WBCR\Titan\Client\Entity\NoticeData;
use WBCR\Titan\Client\Entity\Signature;
use WBCR\Titan\Client\Entity\SpamResult;
use WBCR\Titan\Client\Entity\SpamStatistics;
use WBCR\Titan\Client\Entity\Trial;
use WBCR\Titan\Client\Entity\UrlChecker;
use WBCR\Titan\Client\Entity\Vulnerability;
use WBCR\Titan\Client\Request\CheckSpam;
use WBCR\Titan\Client\Request\CreateCheckerUrl;
use WBCR\Titan\Client\Request\SetNoticeData;
use WBCR\Titan\Client\Request\VulnerabilityPlugin;
use WBCR\Titan\Client\Request\VulnerabilityTheme;
use WBCR\Titan\Client\Response\Error;
use WBCR\Titan\Client\Response\Method\BlackSEO;
use WBCR\Titan\Client\Response\Method\UrlCheckerCreate;
use WBCR\Titan\Client\Response\Response;

/**
 * Class Client
 *
 * @package       WBCR\Titan\Client
 *
 * @author        Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @version       1.0
 * @copyright (c) 2020 Creative Motion
 */
class Client {
	const ENDPOINT = 'https://dev.anti-spam.space/api/v1.0/';

	/**
	 * @var string
	 */
	private $license_key;

	/**
	 * @var Error|null
	 */
	private $last_error;

	/**
	 * Client constructor.
	 *
	 * @param $license_key
	 */
	public function __construct( $license_key ) {
		$this->license_key = $license_key;
	}

	/**
	 * @return Error|null
	 */
	public function get_last_error() {
		return $this->last_error;
	}

	// spam

	/**
	 * @param CheckSpam|CheckSpam[] $data
	 *
	 * @return SpamResult|null
	 */
	public function check_spam( $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $item ) {
				$data[ $key ] = $item->toArray();
			}
		} else {
			$data = $data->toArray();
		}

		$response = $this->request( true, 'spam', $data );
		if ( $response->is_error() ) {
			return null;
		}

		return SpamResult::from_array( $response->response );
	}

	/**
	 * @param string $uid
	 *
	 * @return SpamResult|null
	 */
	public function get_queue_status( $uid ) {
		$response = $this->request( false, 'spam/queue-status/' . $uid );
		if ( $response->is_error() ) {
			return null;
		}

		return SpamResult::from_array( $response->response );
	}

	/**
	 * @param string[] $uids
	 *
	 * @return array
	 */
	public function get_queues_status( $uids ) {
		$response = $this->request( true, 'spam/queue-status', $uids );
		if ( $response->is_error() ) {
			return [];
		}

		$results = [];
		foreach ( $response->response as $item ) {
			$results[] = SpamResult::from_array( $item );
		}

		return $results;
	}

	/**
	 * @return SpamStatistics|null
	 */
	public function get_statistics() {
		$response = $this->request( false, 'spam/statistics' );
		if ( $response->is_error() ) {
			return null;
		}

		return SpamStatistics::from_array( $response->response );
	}

	// system integrity

	/**
	 * @param $version
	 * @param $files
	 *
	 * @return CmsCheck|null
	 */
	public function check_cms_premium( $version, $files ) {
		$response = $this->request( true, 'cms/check/premium', [
			'system'  => 'WordPress',
			'version' => $version,
			'files'   => $files,
		] );
		if ( $response->is_error() ) {
			return null;
		}

		return CmsCheck::from_array( $response->response );
	}

	/**
	 * @param $version
	 * @param $files
	 *
	 * @return bool|null
	 */
	public function check_cms_free( $version, $files ) {
		$response = $this->request( true, 'cms/check/free', [
			'system'  => 'WordPress',
			'version' => $version,
			'files'   => $files,
		] );
		if ( $response->is_error() ) {
			return null;
		}

		return $response->response['haveProblems'];
	}

	//
	// email
	//

	/**
	 * @param $email
	 *
	 * @return CheckEmail|null
	 */
	public function check_email( $email ) {
		$response = $this->request( false, 'check-email', [ 'email' => $email ] );
		if ( $response->is_error() ) {
			return null;
		}

		return CheckEmail::from_array( $response->response );
	}

	/**
	 * @param $ips
	 *
	 * @return BlacklistedIP[]|null
	 */
	public function check_ips( $ips ) {
		$blacklistedIps = [];
		$response       = $this->request( true, 'check/ips', $ips );
		if ( ! $response->is_error() ) {
			return null;
		}

		foreach ( $response->response as $ip ) {
			$blacklistedIps[] = BlacklistedIP::from_array( $ip );
		}

		return $blacklistedIps;
	}

	public function check_black_seo( $domain ) {
		$response = $this->request( false, 'check/domain', [ 'domain' => $domain ] );
		if ( $response->is_error() ) {
			return null;
		}

		return BlackSEO::from_array( $response->response );
	}

	//
	// trial
	//

	public function trial_register( $email, $domain ) {
		$response = $this->request( true, 'trial/register', [
			'email'  => $email,
			'domain' => $domain,
		] );
		if ( $response->is_error() ) {
			return null;
		}

		return Trial::from_array( $response->response );
	}

	public function trial_check( $email, $domain ) {
		$response = $this->request( true, 'trial/register', [
			'email'  => $email,
			'domain' => $domain,
		] );
		if ( $response->is_error() ) {
			return null;
		}

		return is_null( $response->response );
	}

	//
	// vulnerabilities
	//

	/**
	 * @param string $version
	 *
	 * @return Vulnerability[]|null
	 */
	public function get_vuln_cms( $version ) {
		$response = $this->request( false, 'vulnerability/cms', [
			'name'    => 'WordPress',
			'version' => $version,
		] );
		if ( $response->is_error() ) {
			return null;
		}

		$vuln = [];
		foreach ( $response->response as $item ) {
			$vuln[] = Vulnerability::from_array( $item );
		}

		return $vuln;
	}

	/**
	 * @param VulnerabilityPlugin $vuln
	 *
	 * @return Vulnerability[]|null
	 */
	public function get_vuln_plugin( $vuln ) {
		$response = $this->request( true, 'vulnerability/plugin', [ 'data' => $vuln->plugins ] );
		if ( $response->is_error() ) {
			return null;
		}

		$vuln = [];
		foreach ( $response->response as $slug => $item ) {
			$vuln[ $slug ] = [];
			foreach ( $item as $v ) {
				$vuln[ $slug ][] = Vulnerability::from_array( $v );
			}
		}

		return $vuln;
	}

	/**
	 * @param VulnerabilityTheme $vuln
	 *
	 * @return Vulnerability[]|null
	 */
	public function get_vuln_theme( $vuln ) {
		$response = $this->request( true, 'vulnerability/theme', [ 'data' => $vuln->themes ] );
		if ( $response->is_error() ) {
			return null;
		}

		$vuln = [];
		foreach ( $response->response as $slug => $item ) {
			$vuln[ $slug ] = [];
			foreach ( $item as $v ) {
				$vuln[ $slug ][] = Vulnerability::from_array( $v );
			}
		}

		return $vuln;
	}

	//
	// signatures
	//

	public function get_signatures() {
		$response = $this->request( false, 'antivirus/signature' );
		if ( $response->is_error() ) {
			return null;
		}

		$s = [];
		foreach ( $response->response as $item ) {
			$s[] = Signature::from_array( $item );
		}

		return $s;
	}

	//
	// notices
	//

	/**
	 * @return string[]|null
	 */
	public function get_allowed_notice_methods() {
		$response = $this->request( false, 'notice/methods/get' );
		if ( $response->is_error() ) {
			return null;
		}

		return $response->response;
	}

	/**
	 * @return NoticeData[]|null
	 */
	public function get_notice_data() {
		$response = $this->request( false, 'notice/users-data/get' );
		if ( $response->is_error() ) {
			return null;
		}

		$data = [];
		foreach ( $response->response as $item ) {
			$data[] = NoticeData::from_array( $item );
		}

		return $data;
	}

	/**
	 * @param SetNoticeData $data
	 *
	 * @return bool|null
	 */
	public function set_notice_data( $data ) {
		$response = $this->request( true, 'notice/users-data/set', $data->data );
		if ( $response->is_error() ) {
			return null;
		}
		
		return $response->response['is_successful_create'];
	}

	/**
	 * @param $ids
	 *
	 * @return bool|null
	 */
	public function delete_notice_data( $ids ) {
		$response = $this->request( true, 'notice/users-data/delete', [ 'ids' => $ids ] );
		if ( $response->is_error() ) {
			return null;
		}

		return $response->response['is_successful_delete'];
	}

	//
	// checker urls
	//

	/**
	 * @return UrlChecker[]|null
	 */
	public function get_checker_urls() {
		$response = $this->request( false, 'url-checker/' );
		if ( $response->is_error() ) {
			return null;
		}

		$urls = [];
		foreach ( $response->response as $url ) {
			$urls[] = UrlChecker::from_array( $url );
		}

		return $urls;
	}

	/**
	 * @param $id
	 *
	 * @return UrlChecker|null
	 */
	public function get_checker_url( $id ) {
		$response = $this->request( false, 'url-checker/' . $id );
		if ( $response->is_error() ) {
			return null;
		}

		return UrlChecker::from_array( $response->response );
	}

	/**
	 * @param int    $id
	 * @param string $url
	 * @param int    $frequency
	 *
	 * @return bool
	 */
	public function update_checker_url( $id, $url, $frequency ) {
		$response = $this->request( true, 'url-checker/' . $id . '/update', [
			'url'       => $url,
			'frequency' => $frequency,
		] );
		if ( $response->is_error() ) {
			return false;
		}

		return true;
	}

	/**
	 * @param int[] $ids
	 *
	 * @return bool
	 */
	public function delete_checker_url( $ids ) {
		$response = $this->request( true, 'url-checker/delete', [
			'ids' => $ids,
		] );
		if ( $response->is_error() ) {
			return false;
		}

		return true;
	}

	/**
	 * @param CreateCheckerUrl $request
	 *
	 * @return bool
	 */
	public function create_checker_url( $request ) {
		$response = $this->request( true, 'url-checker/create', [ 'urls' => $request->urls ] );

		return ! $response->is_error();
	}

	/**
	 * @param bool   $post
	 * @param string $apiMethod
	 * @param array  $body
	 *
	 * @return Response
	 */
	public function request( $post, $apiMethod, $body = [] ) {
//		if(function_exists('wp_remote_post')) {
//			wp_remote_post();
//			wp_remote_get();
//		}

		$url = sprintf( "%s%s", self::ENDPOINT, $apiMethod );

		$ch = curl_init();

		if ( $post ) {
			curl_setopt_array( $ch, [
				CURLOPT_POST       => true,
				CURLOPT_POSTFIELDS => json_encode( $body ),
			] );
		} else {
			$url = sprintf( "%s?%s", $url, http_build_query( $body ) );
		}

		curl_setopt_array( $ch, [
			CURLOPT_URL            => $url,
			CURLOPT_HTTPHEADER     => [
				'Content-Type: application/json',
				'Accept: application/json',
				sprintf( "Authorization: Bearer %s", base64_encode( $this->license_key ) ),
			],
			CURLOPT_RETURNTRANSFER => true,
		] );

		$response = json_decode( curl_exec( $ch ), true );
		curl_close( $ch );

		$response = Response::from_array( $response );
		if ( $response->is_error() ) {
			$this->last_error = $response->error;
		}

		return $response;
	}
}