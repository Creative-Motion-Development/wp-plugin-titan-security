<?php

namespace WBCR\Titan\Client\Request;

/**
 * Class AddCheckerUrl
 * @package WBCR\Titan\Client\Request
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class CreateCheckerUrl extends Request {
	/**
	 * @var int[] [key - url, value - frequency]
	 */
	public $urls = [];

	/**
	 * @param $url
	 * @param $frequency
	 */
	public function add_url($url, $frequency) {
		$this->urls[$url] = $frequency;
	}
}