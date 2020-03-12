<?php

namespace WBCR\Titan\Client\Response\Method;

use WBCR\Titan\Client\Loader;

/**
 * Class BlackSEO
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class BlackSEO extends Loader {
	/**
	 * @var string[]
	 */
	public $ip;
	/**
	 * @var \WBCR\Titan\Client\Entity\Software
	 */
	public $software;
	/**
	 * @var \WBCR\Titan\Client\Entity\Warnings
	 */
	public $warnings;
	/**
	 * @var \WBCR\Titan\Client\Entity\Ratings
	 */
	public $ratings;

	/**
	 * BlackSEO constructor.
	 *
	 * @param string[]                           $ip
	 * @param \WBCR\Titan\Client\Entity\Software $software
	 * @param \WBCR\Titan\Client\Entity\Warnings $warnings
	 * @param \WBCR\Titan\Client\Entity\Ratings  $ratings
	 */
	public function __construct( $ip, $software, $warnings, $ratings ) {
		$this->ip       = $ip;
		$this->software = $software;
		$this->warnings = $warnings;
		$this->ratings  = $ratings;
	}
}