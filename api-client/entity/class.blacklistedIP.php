<?php


namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class BlackistedIP
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class BlacklistedIP extends Loader {

	/**
	 * @var string
	 */
	public $ip;
	/**
	 * @var int
	 */
	public $version;
	/**
	 * @var int
	 */
	public $last_seen;

	/**
	 * BlackistedIP constructor.
	 *
	 * @param string $ip
	 * @param int    $version
	 * @param int    $last_seen [Unix timestamp]
	 */
	public function __construct( $ip, $version, $last_seen ) {
		$this->ip        = $ip;
		$this->version   = $version;
		$this->last_seen = $last_seen;
	}
}