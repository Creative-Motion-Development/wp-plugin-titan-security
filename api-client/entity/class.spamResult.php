<?php

namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class SpamResult
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class SpamResult extends Loader {
	const STATUS_DONE = 'done';
	const STATUS_PROCESS = 'process';

	/**
	 * @var string
	 */
	public $uid;

	/**
	 * @var string
	 */
	public $status;

	/**
	 * @var bool|null
	 */
	public $spam;

	/**
	 * SpamResult constructor.
	 *
	 * @param string    $uid
	 * @param string    $status
	 * @param bool|null $spam
	 */
	public function __construct( $uid, $status, $spam ) {
		$this->uid    = $uid;
		$this->status = $status;
		$this->spam   = $spam;
	}

	/**
	 * @return bool
	 */
	public function is_done() {
		return $this->status == self::STATUS_DONE;
	}

	/**
	 * @return bool
	 */
	public function is_process() {
		return $this->status == self::STATUS_PROCESS;
	}
}