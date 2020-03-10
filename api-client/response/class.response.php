<?php

namespace WBCR\Titan\Client\Response;

use WBCR\Titan\Client\Loader;

/**
 * Class Response
 *
 * @author        Alexander Gorenkov <g.a.androidjc2@ya.ru>
 * @version       1.0.0
 * @copyright (c) 2020 Creative Motion
 */
class Response extends Loader {
	/**
	 * @var string
	 */
	public $status;

	/**
	 * @var Error|null
	 */
	public $error;

	/**
	 * @var
	 */
	public $response;

	/**
	 * Response constructor.
	 *
	 * @param string     $status
	 * @param \WBCR\Titan\Client\Response\Error|null $error
	 * @param array      $response
	 */
	public function __construct( $status, $error, $response ) {
		$this->status   = $status;
		$this->error    = $error;
		$this->response = $response;
	}

	/**
	 * @return bool
	 */
	public function is_error() {
		return !is_null($this->error);
	}
}