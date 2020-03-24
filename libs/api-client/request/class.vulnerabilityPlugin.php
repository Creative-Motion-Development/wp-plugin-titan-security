<?php


namespace WBCR\Titan\Client\Request;

/**
 * Class VulnerabilityPlugin
 * @package WBCR\Titan\Client\Request
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class VulnerabilityPlugin {
	public $plugins = [];

	/**
	 * @param $slug
	 * @param $version
	 */
	public function add_plugin( $slug, $version ) {
		$this->plugins[ $slug ] = $version;
	}
}