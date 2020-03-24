<?php


namespace WBCR\Titan\Client\Request;

/**
 * Class VulnerabilityTheme
 * @package WBCR\Titan\Client\Request
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class VulnerabilityTheme {
	public $themes = [];

	/**
	 * @param string $slug
	 * @param string $version
	 */
	public function add_theme( $slug, $version ) {
		$this->themes[ $slug ] = $version;
	}
}