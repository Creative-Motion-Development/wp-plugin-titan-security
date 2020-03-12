<?php


namespace WBCR\Titan\Client\Entity;


use WBCR\Titan\Client\Loader;

/**
 * Class Trial
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Trial extends Loader {
	/**
	 * @var string|null
	 */
	public $message;
	/**
	 * @var int|null
	 */
	public $code;
	/**
	 * @var string|null
	 */
	public $license_key;
	/**
	 * @var int|null
	 */
	public $expired_at;

	/**
	 * Trial constructor.
	 *
	 * @param string|null $message
	 * @param int|null    $code
	 * @param string|null $license_key
	 * @param int|null    $expired_at
	 */
	public function __construct( $message, $code, $license_key, $expired_at ) {
		$this->message     = $message;
		$this->code        = $code;
		$this->license_key = $license_key;
		$this->expired_at  = $expired_at;
	}

	/**
	 * @return bool
	 */
	public function is_ok() {
		return is_null( $this->code );
	}
}