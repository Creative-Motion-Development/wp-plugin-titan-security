<?php

namespace WBCR\Titan\MalwareScanner;


use WBCR\Titan\Logger\Writter;

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
	 * @var string|null
	 */
	protected $content;

	/**
	 * File constructor.
	 *
	 * @param string $path
	 * @param null $hashFile
	 * @param bool $loadData
	 */
	public function __construct( $path, $hashFile = null, $loadData = false ) {
		$this->path     = $path;
		$this->hashFile = $hashFile;
		if ( $loadData ) {
			$this->loadData();
		}
	}

	/**
	 * @return \Generator
	 * @see File::loadData()
	 *
	 */
	protected function read() {
		$resource = fopen( $this->path, 'r' );
		if ( $resource === false ) {
			Writter::error( sprintf( "Failed to open the file: %s", $this->path ) );
			return;
		}

		while ( ! feof( $resource ) ) {
			yield trim( fgets( $resource ) );
		}

		fclose( $resource );
	}


	/**
	 * This approach works faster than the usual `file_get_contents`
	 *
	 * @return string
	 */
	public function loadData() {
		if ( is_null( $this->content ) ) {
			$data = [];
			foreach ( $this->read() as $line ) {
				$data[] = $line;
			}

			$this->content = implode( '', $data );
		}

		$content = &$this->content;

		return $content;
	}

	public function clearLoadedData() {
		unset( $this->content );
		$this->content = null;
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