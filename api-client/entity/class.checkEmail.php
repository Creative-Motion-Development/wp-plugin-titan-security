<?php


namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class CheckEmail
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class CheckEmail extends Loader {

	/**
	 * @var string
	 */
	public $email;
	/**
	 * @var bool
	 */
	public $exists;
	/**
	 * @var string|null
	 */
	public $comment;

	/**
	 * CheckEmail constructor.
	 *
	 * @param string      $email
	 * @param bool        $exists
	 * @param string|null $comment
	 */
	public function __construct( $email, $exists, $comment ) {
		$this->email   = $email;
		$this->exists  = $exists;
		$this->comment = $comment;
	}
}