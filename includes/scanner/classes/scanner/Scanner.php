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

	const SCHEDULE_DAILY = 'daily';
	const SCHEDULE_WEEKLY = 'weekly';
	const SCHEDULE_CUSTOM = 'custom';
	const SCHEDULE_DISABLED = 'disabled';

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

	public $cleaned_count = 0;

	public $suspicious_count = 0;

	public $peak_memory_usage = 0;

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
	 * @param string|string[] $paths
	 * @param SignaturePool $signaturePool
	 * @param HashListPool $hashList
	 * @param array $ignoreFiles
	 */
	public function __construct( $paths, $signaturePool, $hashList = null, $ignoreFiles = [] ) {
		if ( is_null( $hashList ) ) {
			$hashList = HashListPool::fromArray( [] );
		}

		$this->hashList      = $hashList;
		$this->ignoreFiles   = $ignoreFiles;
		$this->signaturePool = $signaturePool;

		if(is_array($paths)) {
		    foreach($paths as $path) {
		        $this->loadFilesFromPath( $path );
            }
        } else {
            $this->loadFilesFromPath( $paths );
        }

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
		return $this->files_count;
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
				$this->fileList[] = $this->loadFile( $newPath );
				$this->files_count ++;
			}
		}
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
	 *
	 * @return \Generator
	 */
	public function scan( $count = 100 ) {
		$i = 0;
		foreach ( $this->fileList as $file ) {
			$i ++;

			$fileMatch = $this->signaturePool->scanFile( $file );
			if ( $fileMatch !== null ) {
				$this->updateData( true );
				yield $fileMatch;
			} else {
				$this->updateData( false );
			}

			if ( $i === $count ) {
				break;
			}
		}

		$this->fileList = array_slice( $this->fileList, $count );
	}

	/**
	 * @param bool $isSuspicious
	 */
	protected function updateData( $isSuspicious ) {
		if ( $this->files_count > 0 ) {
			$this->files_count --;

			if ( $isSuspicious ) {
				$this->suspicious_count ++;
			} else {
				$this->cleaned_count ++;
			}
		}
	}
}
