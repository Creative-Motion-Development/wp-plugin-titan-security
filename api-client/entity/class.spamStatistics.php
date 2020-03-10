<?php


namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class SpamStatistics
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class SpamStatistics extends Loader {
	/**
	 * @var int
	 */
	public $total;

	/**
	 * @var array
	 */
	public $stat;

	/**
	 * SpamStatistics constructor.
	 *
	 * @param int   $total
	 * @param array $stat [key=date value=count]
	 */
	public function __construct( $total, $stat ) {
		$this->total = $total;
		$this->stat  = $stat;
	}
}