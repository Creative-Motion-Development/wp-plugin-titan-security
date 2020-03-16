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
	 * @var string|null
	 */
	protected $hashFile;

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * File constructor.
	 *
	 * @param string $path
	 * @param null   $hashFile
	 * @param bool   $loadData
	 */
	public function __construct( $path, $hashFile = null, $loadData = false ) {
		$this->path = $path;
		$this->hashFile = $hashFile;
		if ( $loadData ) {
			$this->loadData();
		}
	}

	protected function read() {
		$resource = fopen( $this->path, 'r' );

		while ( ! feof( $resource ) ) {
			yield trim( fgets( $resource ) );
		}

		fclose( $resource );
	}


	public function loadData() {
		$data = [];
		foreach($this->read() as $line) {
			$data[] = $line;
		}

		return implode('', $data);
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @return string|null
	 */
	public function getFileHash() {
		return $this->hashFile;
	}

	/**
	 * @return string|null
	 */
	public function getContent() {
		return $this->loadData();
	}
}