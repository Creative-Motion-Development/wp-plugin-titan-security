<?php


namespace WBCR\Titan\Client\Request;

/**
 * Class CheckSpam
 * @package WBCR\Titan\Client\Request
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class CheckSpam {
	private $uid;
	private $ip;
	private $email;
	private $text;
	private $username;
	private $headers;
	private $referrer;
	private $user_agent;
	private $js_on;
	private $submit_time;
	private $without_queue;

	/**
	 * CheckSpam constructor.
	 *
	 * @param string $uid
	 * @param string $ip
	 * @param string $email
	 * @param string $text
	 * @param string $username
	 * @param string $headers
	 * @param string $referrer
	 * @param string $user_agent
	 * @param bool   $js_on
	 * @param int    $submit_time
	 * @param bool   $without_queue
	 */
	public function __construct( $uid, $ip, $email, $text, $username, $headers, $referrer, $user_agent, $js_on, $submit_time, $without_queue = false ) {
		$this->uid           = $uid;
		$this->ip            = $ip;
		$this->email         = $email;
		$this->text          = $text;
		$this->username      = $username;
		$this->headers       = $headers;
		$this->referrer      = $referrer;
		$this->user_agent    = $user_agent;
		$this->js_on         = $js_on;
		$this->submit_time   = $submit_time;
		$this->without_queue = $without_queue;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return get_object_vars( $this );
	}
}