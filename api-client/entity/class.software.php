<?php

namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class Software
 * @package WBCR\Titan\Client\Entity
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Software extends Loader {
	/**
	 * @var CMS[]
	 */
	public $cms = [];

	/**
	 * @var Language[]
	 */
	public $language = [];

	/**
	 * @var array
	 */
	public $server = [];

	/**
	 * Software constructor.
	 *
	 * @param array $cms
	 * @param array $language
	 * @param array $server
	 */
	public function __construct($cms, $language, $server) {
		foreach($cms as $item) {
			$this->cms[] = CMS::from_array($item);
		}

		foreach($language as $item) {
			$this->language[] = Language::from_array($item);
		}

		foreach($server as $item) {
			$this->server[] = Server::from_array($item);
		}
	}
}