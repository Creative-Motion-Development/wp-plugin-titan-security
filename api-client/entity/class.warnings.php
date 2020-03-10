<?php


namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class Warnings
 * @package WBCR\Titan\Client\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Warnings extends Loader {
	/**
	 * @var Outdated[]
	 */
	public $outdated = [];

	/**
	 * @var \WBCR\Titan\Client\Entity\Security|null
	 */
	public $security;

	/**
	 * @var Issue[]
	 */
	public $issue = [];

	/**
	 * Warnings constructor.
	 *
	 * @param array                                   $outdated
	 * @param \WBCR\Titan\Client\Entity\Security|null $security
	 * @param array                                   $site_issue
	 */
	public function __construct( $outdated, $security, $site_issue ) {
		foreach ( $outdated as $item ) {
			$outdated[] = Outdated::from_array( $item );
		}

		$this->security = $security;

		foreach ( $site_issue as $item ) {
			$this->issue[] = Issue::from_array( $item );
		}
	}
}