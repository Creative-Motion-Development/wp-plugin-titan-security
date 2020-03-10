<?php


namespace WBCR\Titan\Client\Entity;


use WBCR\Titan\Client\Loader;

/**
 * Class Security
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Security extends Loader {
	/**
	 * @var Malware[]
	 */
	public $malware = [];

	/**
	 * Security constructor.
	 *
	 * @param array $malware
	 */
	public function __construct( $malware ) {
		foreach ( $malware as $item ) {
			$this->malware[] = Malware::from_array( $item );
		}
	}
}