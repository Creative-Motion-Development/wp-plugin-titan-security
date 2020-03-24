<?php


namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class NoticeData
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class NoticeData extends Loader {
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $type;
	/**
	 * @var string
	 */
	public $value;

	/**
	 * NoticeData constructor.
	 *
	 * @param int    $id
	 * @param string $type
	 * @param string $value
	 */
	public function __construct( $id, $type, $value ) {
		$this->id    = $id;
		$this->type  = $type;
		$this->value = $value;
	}
}