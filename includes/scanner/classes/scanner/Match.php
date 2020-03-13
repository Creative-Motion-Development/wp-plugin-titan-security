<?php

namespace WBCR\Titan\MalwareScanner;


/**
 * Class Match
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Match implements \JsonSerializable {
	/**
	 * @var File
	 */
	private $file;

	/**
	 * @var string
	 */
	private $match;

	/**
	 * Match constructor.
	 *
	 * @param File   $file
	 * @param string $match
	 */
	public function __construct( $file, $match ) {
		$this->file  = $file;
		$this->match = $match;
	}

	/**
	 * @return File
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * @return string
	 */
	public function getMatch() {
		return $this->match;
	}

	public function jsonSerialize() {
		return [
			'file' => $this->file,
			'match' => $this->match,
		];
	}
}