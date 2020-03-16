<?php

namespace WBCR\Titan\MalwareScanner;

/**
 * Class HashListPool
 * @package WBCR\Titan\MalwareScanner
 *
 * @author  Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class HashListPool {
	/**
	 * @var string[]
	 */
	private $hashList;

	/**
	 * HashListPool constructor.
	 *
	 * @param string[] $hashList
	 */
	public function __construct( $hashList ) {
		$this->hashList = $hashList;
	}

	/**
	 * @return mixed
	 */
	public function getHashList() {
		return $this->hashList;
	}

	/**
	 * @param $filePath
	 *
	 * @return string|null
	 */
	public function getFileHash( $filePath ) {
		if ( ! isset( $this->hashList[ $filePath ] ) ) {
			return null;
		}

		return $this->hashList[ $filePath ];
	}

	/**
	 * @return string[]
	 */
	public function toArray() {
		return $this->hashList;
	}

	/**
	 * @param $array
	 *
	 * @return $this
	 */
	public static function fromArray( $array ) {
		return new static( $array );
	}
}