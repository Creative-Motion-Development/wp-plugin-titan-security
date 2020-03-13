<?php

namespace WBCR\Titan\MalwareScanner;


/**
 * Class File
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class File {
	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * File constructor.
	 *
	 * @param string $path
	 */
	public function __construct( $path ) {
		$this->path    = $path;
		$this->content = file_get_contents( $path );
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}
}