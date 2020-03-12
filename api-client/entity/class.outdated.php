<?php


namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class Outdated
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Outdated extends Loader {
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $version;
	/**
	 * @var string
	 */
	public $safe_version;
	/**
	 * @var string|null
	 */
	public $message;

	/**
	 * Outdated constructor.
	 *
	 * @param string      $name
	 * @param string      $version
	 * @param string      $safe_version
	 * @param string|null $message
	 */
	public function __construct( $name, $version, $safe_version, $message ) {
		$this->name         = $name;
		$this->version      = $version;
		$this->safe_version = $safe_version;
		$this->message      = $message;
	}
}