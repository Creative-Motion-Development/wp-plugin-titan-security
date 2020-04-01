<?php


namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class CmsCheck
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class CmsCheck extends Loader {
	/**
	 * @var CmsCheckItem[]
	 */
	public $items = [];

	public function __construct( $data ) {
		foreach ( $data as $item ) {
			$items[] = CmsCheckItem::from_array( $item );
		}
	}
}