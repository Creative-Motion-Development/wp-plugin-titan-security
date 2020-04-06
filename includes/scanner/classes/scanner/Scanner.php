<?php

namespace WBCR\Titan\MalwareScanner;


use InvalidArgumentException;

/**
 * Class Scanner
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Scanner {
	const SPEED_FREE = 'free';
	const SPEED_SLOW = 'slow';
	const SPEED_MEDIUM = 'medium';
	const SPEED_FAST = 'fast';

	const SPEED_LIST = [
		self::SPEED_FREE,
		self::SPEED_SLOW,
		self::SPEED_MEDIUM,
		self::SPEED_FAST,
	];

	const SPEED_FILES = [
		self::SPEED_FREE   => 25,
		self::SPEED_SLOW   => 60,
		self::SPEED_MEDIUM => 120,
		self::SPEED_FAST   => 250,
	];

	/** @var File[] */
	protected $fileList = [];

	public $files_count = 0;

	/**
	 * @var HashListPool
	 */
	protected $hashList = [];

	/**
	 * @var string[]
	 */
	protected $ignoreFiles;

	/**
	 * @var SignaturePool
	 */
	protected $signaturePool = [];

	/**
	 * Scanner constructor.
	 *
	 * @param string $path
	 * @param SignaturePool $signaturePool
	 * @param HashListPool $hashList
	 * @param array $ignoreFiles
	 */
	public function __construct( $path, $signaturePool, $hashList = null, $ignoreFiles = [] ) {
		if ( is_null( $hashList ) ) {
			$hashList = HashListPool::fromArray( [] );
		}

		$this->hashList      = $hashList;
		$this->ignoreFiles   = $ignoreFiles;
		$this->signaturePool = $signaturePool;

		$this->loadFilesFromPath( $path );
	}

	/**
	 * @return File[]
	 */
	public function getFileList() {
		return $this->fileList;
	}

	/**
	 * @return HashListPool
	 */
	public function getHashList() {
		return $this->hashList;
	}

	/**
	 * @return string[]
	 */
	public function getIgnoreFiles() {
		return $this->ignoreFiles;
	}

	/**
	 * @return SignaturePool
	 */
	public function getSignaturePool() {
		return $this->signaturePool;
	}

	/**
	 * @return int
	 */
	public function get_files_count() {
		return count( $this->fileList );
	}

	/**
	 * @param string $path
	 */
	protected function loadFilesFromPath( $path ) {
		foreach ( scandir( $path ) as $newPath ) {
			if ( $newPath == '.' || $newPath == '..' ) {
				continue;
			}

			if ( in_array( $newPath, $this->ignoreFiles ) ) {
				continue;
			}

			if ( $path[ strlen( $path ) - 1 ] != '/' ) {
				$path .= '/';
			}
			$newPath = $path . $newPath;

			if ( is_dir( $newPath ) ) {
				$this->loadFilesFromPath( $newPath );
			} else {
				$this->fileList[ $newPath ] = $this->loadFile( $newPath );
				$this->files_count ++;
			}
		}
	}

	public function remove_scanned_files( $i = 100 ) {
		$this->files_count -= $i;
		$this->fileList    = array_slice( $this->fileList, $i );
	}

	/**
	 * @param string $filePath
	 *
	 * @param null $fileHash
	 *
	 * @return File
	 */
	protected function loadFile( $filePath, $fileHash = null ) {
		if ( ! file_exists( $filePath ) ) {
			throw new InvalidArgumentException( "File `$filePath` not found" );
		}

		if ( is_null( $fileHash ) ) {
			$fileHash = md5_file( $filePath );
		} else if ( $fileHash === false ) {
			$fileHash = null;
		}

		return new File( $filePath, $fileHash );
	}

	/**
	 * @param int $count
	 * @param array $matchCache
	 *
	 * @return Match[]|null[]
	 */
	public function scan( $count = 100, $matchCache = [] ) {
		$matches = [];

		$i = 0;
		foreach ( $this->fileList as $file ) {
			$i ++;
			$cachedHash = $this->hashList->getFileHash( $file->getPath() );
			if ( $cachedHash && $cachedHash == $file->getFileHash() ) {
				if ( isset( $matchCache[ $file->getPath() ] ) ) {
					$matches[ $file->getPath() ] = $matchCache[ $file->getPath() ];
				} else {
					$matches[ $file->getPath() ] = null;
				}
			} else {
				$fileMatch = $this->signaturePool->scanFile( $file );
				if ( ! empty( $fileMatch ) ) {
					$matches[ $file->getPath() ] = $fileMatch;
				}
			}


			if ( $i == $count ) {
				break;
			}
		}

		return $matches;
	}
}