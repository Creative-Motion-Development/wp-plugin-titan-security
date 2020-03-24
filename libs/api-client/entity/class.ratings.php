<?php

namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class Ratings
 * @package WBCR\Titan\Client\Entity
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Ratings extends Loader {
	/**
	 * @var Rating|null
	 */
	public $domain;
	/**
	 * @var Rating|null
	 */
	public $security;
	/**
	 * @var Rating|null
	 */
	public $tls;
	/**
	 * @var Rating|null
	 */
	public $total;

	/**
	 * Ratings constructor.
	 *
	 * @param \WBCR\Titan\Client\Entity\Rating|null $domain
	 * @param \WBCR\Titan\Client\Entity\Rating|null $security
	 * @param \WBCR\Titan\Client\Entity\Rating|null $tls
	 * @param \WBCR\Titan\Client\Entity\Rating|null $total
	 */
	public function __construct($domain, $security, $tls, $total) {
		$this->domain = $domain;
		$this->security = $security;
		$this->tls = $tls;
		$this->total = $total;
	}
}