<?php


namespace WBCR\Titan\Client\Entity;


use WBCR\Titan\Client\Loader;

/**
 * Class Signature
 * @package WBCR\Titan\Client\Entity
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Signature extends Loader {
	const SEVERITY_CRITICAL = 'c';
	const SEVERITY_WARNING = 'w';
	const SEVERITY_INFO = 'i';

	const FORMAT_REGEXP = 're';
	const FORMAT_CONST = 'const';
	const FORMAT_MD5 = 'md5';

	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var int|null
	 */
	public $child_id;
	/**
	 * @var string
	 */
	public $format;
	/**
	 * @var string
	 */
	public $severity;
	/**
	 * @var string
	 */
	public $title;
	/**
	 * @var string
	 */
	public $content;

	/**
	 * Signature constructor.
	 *
	 * @param int $id
	 * @param int|null $child_id
	 * @param string $format
	 * @param string $severity
	 * @param string $title
	 * @param string $content
	 */
	public function __construct($id, $child_id, $format, $severity, $title, $content) {
		$this->id = $id;
		$this->child_id = $child_id;
		$this->format = $format;
		$this->severity = $severity;
		$this->title = $title;
		$this->content = $content;
	}
}