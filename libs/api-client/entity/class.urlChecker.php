<?php


namespace WBCR\Titan\Client\Entity;

use WBCR\Titan\Client\Loader;

/**
 * Class url
 * @package WBCR\Titan\Client\Response\Entity
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class UrlChecker extends Loader {
	/**
	 * Url ID
	 *
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $url;

	/**
	 * Frequency of queries, seconds
	 *
	 * @var int
	 */
	public $frequency;

	/**
	 * Site uptime, percent
	 *
	 * @var float
	 */
	public $uptime;

	/**
	 * Average response time, seconds
	 *
	 * @var float
	 */
	public $avg_request_time;

	/**
	 * Following check in the unix timestamp format
	 *
	 * @var int
	 */
	public $next_check;

	/**
	 * url constructor.
	 *
	 * @param int    $id
	 * @param string $url
	 * @param int    $frequency
	 * @param float  $uptime
	 * @param float  $avg_request_time
	 * @param int    $next_check
	 */
	public function __construct( $id, $url, $frequency, $uptime, $avg_request_time, $next_check ) {
		$this->id               = $id;
		$this->url              = $url;
		$this->frequency        = $frequency;
		$this->uptime           = $uptime;
		$this->avg_request_time = $avg_request_time;
		$this->next_check       = $next_check;
	}
}