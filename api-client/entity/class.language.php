<?php

namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class Language
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Language extends Loader {
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $version;
	/**
	 * @var string|null
	 */
	public $safe_version;

	/**
	 * Language constructor.
	 *
	 * @param string      $name
	 * @param string      $version
	 * @param string|null $safe_version
	 */
	public function __construct( $name, $version, $safe_version ) {
		$this->name         = $name;
		$this->version      = $version;
		$this->safe_version = $safe_version;
	}
}