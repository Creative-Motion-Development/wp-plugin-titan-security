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

	const TYPE_SERVER = 'server';
	const TYPE_BROWSER = 'browser';
	const TYPE_BOTH = 'both';

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
	public $type;
	/**
	 * @var int[]
	 */
	public $common_indexes;
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
	 * @param string $type
	 * @param int[]  $common_indexes
	 * @param string $content
	 */
	public function __construct($id, $child_id, $format, $severity, $title, $type, $common_indexes, $content) {
		$this->id = $id;
		$this->child_id = $child_id;
		$this->format = $format;
		$this->severity = $severity;
		$this->title = $title;
		$this->type = $type;
		$this->common_indexes = $common_indexes;
		$this->content = $content;
	}

	public function to_array() {
		return get_object_vars($this);
	}
}