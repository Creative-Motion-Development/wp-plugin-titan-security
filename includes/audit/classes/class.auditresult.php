<?php

namespace WBCR\Titan;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AuditResult
 *
 * @package WBCR\Titan
 *
 * @author        Artem Prihodko <webtemyk@ya.ru>
 * @copyright (c) 2020 Creative Motion
 * @version       1.0
 */
class AuditResult implements \JsonSerializable {

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 */
	public $timestamp;

	/**
	 * @var string
	 */
	public $severity;

	/**
	 * @var string
	 *  '' - no fix (button not show)
	 *  'js' - fix via js (button show)
	 *  'http://site.com/wp-admin/?action=do_something' - fix via url (button show)
	 */
	public $fix;

	/**
	 * @var bool
	 */
	public $hided = false;

	/**
	 * AuditResult constructor.
	 *
	 * @param string $title
	 * @param string $description
	 * @param string $severity
	 * @param string $fix
	 * @param bool $hided
	 */
	public function __construct( $title, $description, $severity, $fix, $hided = false ) {
		$this->title       = $title;
		$this->description = $description;
		$this->timestamp   = time();
		$this->severity    = $severity;
		$this->fix         = $fix;
		$this->hided       = $hided;
	}

    public function jsonSerialize() {
        return [
            'title'       => $this->title,
            'description' => $this->description,
            'timestamp'   => $this->timestamp,
            'severity'    => $this->severity,
            'fix'         => $this->fix,
            'hided'       => $this->hided,
        ];
    }
}
