<?php


namespace WBCR\Titan\Cert;

/**
 * Class Cert
 * @package WBCR\Titan\Cert
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @version 1.0.0
 */
class Cert {
	const ERROR_NO_ERROR = 0;
	const ERROR_UNAVAILABLE = 1;
	const ERROR_ONLY_HTTPS = 2;
	const ERROR_HTTPS_UNAVAILABLE = 3;
	const ERROR_UNKNOWN_ERROR = - 1;

	/**
	 * @var Cert
	 */
	private static $cert;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var array
	 */
	private $cert_info = [];

	/**
	 * @var bool
	 */
	private $loaded = false;

	private $error = self::ERROR_NO_ERROR;

	/**
	 * Cert constructor.
	 *
	 * @param string $url
	 */
	public function __construct( $url = null ) {
		if ( is_null( $url ) ) {
			$url = get_site_url( null, '', 'https' );
		}

		$this->url = $url;
	}

	public function get_cert_info() {
		if ( ! $this->loaded ) {
			$this->load();
		}

		return $this->cert_info;
	}

	/**
	 * @return int
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * @param bool $load
	 *
	 * @return bool
	 */
	public function is_available( $load = true ) {
		if ( ! function_exists( 'openssl_x509_parse' ) ) {
			$this->error = self::ERROR_UNAVAILABLE;

			return false;
		}

		if ( substr( $this->url, 0, 8 ) !== 'https://' ) {
			$this->error = self::ERROR_ONLY_HTTPS;

			return false;
		}

		if ( $load ) {
			return $this->load();
		}

		return true;
	}

	/**
	 * @return array|false|null
	 */
	public function load() {
		if ( ! $this->is_available( false ) ) {
			return false;
		}

		if ( empty( $this->cert_info ) ) {
			$g = stream_context_create( [ 'ssl' => [ 'capture_peer_cert' => true ] ] );
			$r = @fopen( $this->url, 'rb', false, $g );
			if ( $r === false ) {
				$this->error = self::ERROR_HTTPS_UNAVAILABLE;

				return false;
			}

			$cert = stream_context_get_params( $r );
			fclose( $r );
			$this->cert_info = openssl_x509_parse( $cert['options']['ssl']['peer_certificate'], false );
		}

		$this->loaded = true;

		return true;
	}

	/**
	 * @return int
	 */
	public function get_expiration_timestamp() {
		if ( ! $this->loaded || ! $this->load() ) {
			return 0;
		}

		return $this->cert_info['validTo_time_t'];
	}

	/**
	 * @return string|null
	 */
	public function get_issuer() {
		if ( ! $this->loaded || ! $this->load() ) {
			return null;
		}

		return $this->cert_info['issuer']['organizationName'];
	}

	/**
	 * @return bool
	 */
	public function is_lets_encrypt() {
		return $this->get_issuer() == 'Let\'s Encrypt';
	}

	/**
	 * @return string
	 */
	public function get_error_message() {
		switch ( $this->error ) {
			case self::ERROR_UNKNOWN_ERROR:
				return __( 'Unknown error', 'titan-security' );
				break;

			case self::ERROR_UNAVAILABLE:
				return __( 'PHP openssl extension is missing', 'titan-security' );
				break;

			case self::ERROR_ONLY_HTTPS:
				return __( 'Verification is only available on HTTPS', 'titan-security' );
				break;

			case self::ERROR_HTTPS_UNAVAILABLE:
				return __( 'HTTPS is not activated on this site.', 'titan-security' );
				break;

			case self::ERROR_NO_ERROR:
			default:
				return '';
		}
	}

	/**
	 * @return Cert
	 */
	public static function get_instance() {
		if ( is_null( self::$cert ) ) {
			self::$cert = new Cert();
		}

		return self::$cert;
	}
}