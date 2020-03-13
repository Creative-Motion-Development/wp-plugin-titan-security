<?php

namespace WBCR\Titan\MalwareScanner;


/**
 * Class Scanner
 *
 * @author Alexander Gorenkov <g.a.androidjc2@ya.ru>
 */
class Scanner {
	/** @var File[] */
	protected $fileList = [];

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
	 * @param string        $path
	 * @param SignaturePool $signaturePool
	 * @param array         $ignoreFiles
	 */
	public function __construct( $path, $signaturePool, $ignoreFiles = [] ) {
		$this->ignoreFiles   = $ignoreFiles;
		$this->signaturePool = $signaturePool;

		$this->loadFilesFromPath( $path );
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

			$newPath = $path . DIRECTORY_SEPARATOR . $newPath;

			if ( is_dir( $newPath ) ) {
				$this->loadFilesFromPath( $newPath );
			} else {
				$this->fileList[ $newPath ] = $this->loadFile( $newPath );
			}
		}
	}

	/**
	 * @param string $filePath
	 *
	 * @return File
	 */
	protected function loadFile( $filePath ) {
		if ( ! file_exists( $filePath ) ) {
			throw new \InvalidArgumentException( "File `$filePath` not found" );
		}

		return new File( $filePath );
	}

	/**
	 * @return Match[]
	 */
	public function scan() {
		$matches = [];

		foreach ( $this->fileList as $file ) {
			$matches = array_merge( $matches, $this->signaturePool->scanFile( $file ) );
		}

		return $matches;
	}
}