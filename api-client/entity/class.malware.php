<?php


namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class Malware
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Malware extends Loader {
	/**
	 * @var string
	 */
	public $type;
	/**
	 * @var string
	 */
	public $location;
	/**
	 * @var string|null
	 */
	public $message;

	/**
	 * Malware constructor.
	 *
	 * @param string      $type
	 * @param string      $location
	 * @param string|null $message
	 */
	public function __construct( $type, $location, $message ) {
		$this->type     = $type;
		$this->location = $location;
		$this->message  = $message;
	}
}