<?php


namespace WBCR\Titan\Client\Entity;


use WBCR\Titan\Client\Loader;

/**
 * Class Issue
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Issue extends Loader {
	/**
	 * @var string
	 */
	public $location;
	/**
	 * @var string
	 */
	public $msg;

	/**
	 * Issue constructor.
	 *
	 * @param string $location
	 * @param string $msg
	 */
	public function __construct( $location, $msg ) {
		$this->location = $location;
		$this->msg      = $msg;
	}
}