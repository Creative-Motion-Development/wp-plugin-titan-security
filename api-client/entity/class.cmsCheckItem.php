<?php


namespace WBCR\Titan\Client\Entity;


use WBCR\Titan\Client\Loader;

class CmsCheckItem extends Loader {
	const ACTION_REMOVE = 'remove';
	const ACTION_REPAIR = 'repair';

	/**
	 * @var string
	 */
	public $path;

	/**
	 * @var string
	 */
	public $action;

	/**
	 * @var string|null
	 */
	public $url;

	/**
	 * CmsCheckItem constructor.
	 *
	 * @param string      $path
	 * @param string      $action
	 * @param string|null $url
	 */
	public function __construct( $path, $action, $url ) {
		$this->path   = $path;
		$this->action = $action;
		$this->url    = $url;
	}
}