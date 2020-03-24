<?php


namespace WBCR\Titan\Client\Request;

/**
 * Class SetNoticeData
 * @package WBCR\Titan\Client\Request
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class SetNoticeData {
	public $data = [];

	public function add($method, $value) {
		$this->data[$method] = $value;
	}
}