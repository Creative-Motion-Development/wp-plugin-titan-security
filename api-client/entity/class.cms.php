<?php

namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class CMS
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class CMS extends Loader {
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $version;

	/**
	 * CMS constructor.
	 *
	 * @param string $name
	 * @param string $version
	 */
	public function __construct( $name, $version ) {
		$this->name    = $name;
		$this->version = $version;
	}
}