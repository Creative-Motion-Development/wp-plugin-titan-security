<?php

namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class Rating
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Rating extends Loader {
	/**
	 * @var string
	 */
	public $rating;
	/**
	 * @var string|null
	 */
	public $passed;

	/**
	 * Rating constructor.
	 *
	 * @param string      $rating
	 * @param string|null $passed
	 */
	public function __construct( $rating, $passed ) {
		$this->rating = $rating;
		$this->passed = $passed;
	}
}